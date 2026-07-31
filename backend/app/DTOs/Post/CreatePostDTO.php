<?php
namespace App\DTOs\Post;

class CreatePostDTO {
    public function __construct(
        public readonly string $userId,
        public readonly string $caption,
        public readonly ?string $imagePath = null
    ) {}
}