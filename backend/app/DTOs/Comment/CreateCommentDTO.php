<?php
namespace App\DTOs\Comment;

class CreateCommentDTO {
    public function __construct(
        public readonly string $userId,
        public readonly string $postId,
        public readonly string $text
    ) {}
}