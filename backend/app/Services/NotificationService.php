<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Post;

class NotificationService
{
    public function createNotification(string $userId, string $actorId, string $postId, string $type, ?string $message = null): void
    {
        // Don't notify if the actor is the owner of the post
        if ($userId === $actorId) {
            return;
        }

        Notification::create([
            'user_id' => $userId,
            'actor_id' => $actorId,
            'post_id' => $postId,
            'type' => $type,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    public function deleteNotification(string $userId, string $actorId, string $postId, string $type): void
    {
        Notification::where('user_id', $userId)
            ->where('actor_id', $actorId)
            ->where('post_id', $postId)
            ->where('type', $type)
            ->delete();
    }

    public function createLikeNotification(Post $post, string $actorId): void
    {
        $this->createNotification($post->user_id, $actorId, $post->_id, 'like');
    }

    public function deleteLikeNotification(Post $post, string $actorId): void
    {
        $this->deleteNotification($post->user_id, $actorId, $post->_id, 'like');
    }

    public function createCommentNotification(Post $post, string $actorId, string $message): void
    {
        $this->createNotification($post->user_id, $actorId, $post->_id, 'comment', $message);
    }
}
