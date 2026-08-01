<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedPost;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class SavedPostController extends Controller
{
    public function toggle(string $postId): JsonResponse
    {
        $userId = auth()->guard('api')->id();
        
        $savedPost = SavedPost::where('user_id', $userId)
            ->where('post_id', $postId)
            ->first();

        if ($savedPost) {
            $savedPost->delete();
            return response()->json(['message' => 'unsaved']);
        }

        SavedPost::create([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);

        return response()->json(['message' => 'saved']);
    }

    public function index(): JsonResponse
    {
        $userId = auth()->guard('api')->id();
        
        $savedPosts = SavedPost::with(['post.user', 'post.comments.user'])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->pluck('post')
            ->filter(); // Remove nulls if post was deleted
            
        // Append dynamic attributes exactly like PostService does
        $savedPosts = $savedPosts->map(function ($post) use ($userId) {
            $post->is_liked = \App\Models\Like::where('post_id', $post->_id)->where('user_id', $userId)->exists();
            $post->is_saved = true; // By definition, it's saved here
            return $post;
        });

        return response()->json(['data' => $savedPosts->values()]);
    }
}
