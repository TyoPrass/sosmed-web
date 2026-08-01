<?php

namespace App\Services;

use App\Models\Like;
use App\Models\Post;

class LikeService
{
    public function __construct(private NotificationService $notificationService) {}

    public function toggleLike(string $userId, string $postId): array
    {
        $like = Like::where('user_id', $userId)->where('post_id', $postId)->first();
        $post = Post::findOrFail($postId);

        if ($like) {
            $like->delete();
            $post->decrement('likes_count');
            $this->notificationService->deleteLikeNotification($post, $userId);
            return ['status' => 'unliked'];
        }

        Like::create([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
        $post->increment('likes_count');
        $this->notificationService->createLikeNotification($post, $userId);
        
        return ['status' => 'liked'];
    }
}