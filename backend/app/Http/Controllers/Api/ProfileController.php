<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\DTOs\Profile\UpdateProfileDTO;
use App\Services\ProfileService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Follow;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService) {}

    public function show(): JsonResponse
    {
        $user = auth()->guard('api')->user();
        $user->followers_count = Follow::where('following_id', $user->id)->count();
        $user->following_count = Follow::where('follower_id', $user->id)->count();
        return response()->json($user);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $avatarPath = auth()->guard('api')->user()->avatar_path;
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $avatarPath = env('APP_URL') . '/storage/' . $path;
        }

        $dto = new UpdateProfileDTO(
            $request->username,
            $request->email,
            $request->bio,
            $avatarPath
        );
        $user = $this->profileService->updateProfile(auth()->guard('api')->user(), $dto);
        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = auth()->guard('api')->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password does not match'], 400);
        }
        
        $this->profileService->updatePassword($user, $request->new_password);
        return response()->json(['message' => 'Password updated successfully']);
    }

    public function showUser(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->followers_count = Follow::where('following_id', $user->_id)->count();
        $user->following_count = Follow::where('follower_id', $user->_id)->count();
        
        $currentUserId = auth()->guard('api')->id();
        $user->is_following = false;
        if ($currentUserId) {
            $user->is_following = Follow::where('follower_id', $currentUserId)
                                        ->where('following_id', $user->_id)
                                        ->exists();
        }

        return response()->json($user);
    }

    public function search(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = $request->query('q');
        if (!$query) {
            return response()->json([]);
        }

        $users = User::where('username', 'like', '%' . $query . '%')
                     ->take(10)
                     ->get(['_id', 'username', 'avatar_path']);

        return response()->json($users);
    }
}