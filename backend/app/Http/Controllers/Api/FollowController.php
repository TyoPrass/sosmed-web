<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class FollowController extends Controller
{
    public function toggle(string $userId): JsonResponse
    {
        $currentUserId = auth()->guard('api')->id();
        
        if ($currentUserId === $userId) {
            return response()->json(['error' => 'You cannot follow yourself'], 400);
        }

        // Check if user exists
        User::findOrFail($userId);

        $follow = Follow::where('follower_id', $currentUserId)
                        ->where('following_id', $userId)
                        ->first();

        if ($follow) {
            $follow->delete();
            return response()->json(['message' => 'unfollowed']);
        }

        Follow::create([
            'follower_id' => $currentUserId,
            'following_id' => $userId,
        ]);

        return response()->json(['message' => 'followed']);
    }

    public function followers(string $userId): JsonResponse
    {
        $followers = Follow::where('following_id', $userId)->with('follower')->get()->pluck('follower');
        return response()->json($followers);
    }

    public function following(string $userId): JsonResponse
    {
        $following = Follow::where('follower_id', $userId)->with('following')->get()->pluck('following');
        return response()->json($following);
    }
}
