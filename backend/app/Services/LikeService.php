<?php

namespace App\Services;

use App\Models\Like;
use App\Models\Post;

class LikeService
{
    public function toggleLike(string $userId, string $postId): array
    {
        $like = Like::where('user_id', $userId)->where('post_id', $postId)->first();

        if ($like) {
            $like->delete();
            Post::where('_id', $postId)->decrement('likes_count');
            return ['status' => 'unliked'];
        }

        Like::create([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
        Post::where('_id', $postId)->increment('likes_count');
        return ['status' => 'liked'];
    }
}