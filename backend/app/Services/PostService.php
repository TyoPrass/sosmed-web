<?php

namespace App\Services;

use App\DTOs\Post\CreatePostDTO;
use App\Models\Post;

class PostService
{
    public function createPost(CreatePostDTO $dto): Post
    {
        return Post::create([
            'user_id' => $dto->userId,
            'caption' => $dto->caption,
            'image_path' => $dto->imagePath,
            'likes_count' => 0,
            'comments_count' => 0,
        ]);
    }

    public function deletePost(Post $post): void
    {
        $post->delete();
    }

    public function updatePost(Post $post, string $caption): Post
    {
        $post->update(['caption' => $caption]);
        return $post;
    }

    public function getAllPosts(?string $userId = null)
    {
        $query = Post::with(['user', 'comments.user'])->latest();

        if ($userId) {
            $query->where(function ($q) use ($userId) {
                // Try matching as ObjectId first, then as string fallback
                try {
                    $objectId = new \MongoDB\BSON\ObjectId($userId);
                    $q->where('user_id', $objectId)
                      ->orWhere('user_id', $userId);
                } catch (\Exception $e) {
                    $q->where('user_id', $userId);
                }
            });
        }

        $posts = $query->get();

        $currentUserId = auth()->guard('api')->id();

        if ($currentUserId) {
            $posts->map(function ($post) use ($currentUserId) {
                $post->is_liked = \App\Models\Like::where('post_id', $post->_id)->where('user_id', $currentUserId)->exists();
                $post->is_saved = \App\Models\SavedPost::where('user_id', $currentUserId)->where('post_id', $post->_id)->exists();
                return $post;
            });
        } else {
             $posts->map(function ($post) {
                $post->is_liked = false;
                $post->is_saved = false;
                return $post;
            });
        }

        return $posts;
    }
}