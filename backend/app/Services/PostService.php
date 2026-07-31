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

    public function getAllPosts(?string $userId = null)
    {
        $query = Post::with('user')->latest();
        if ($userId) {
            $query->where('user_id', $userId);
        }
        return $query->get();
    }
}