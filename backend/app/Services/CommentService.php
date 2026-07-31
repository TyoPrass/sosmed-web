<?php

namespace App\Services;

use App\DTOs\Comment\CreateCommentDTO;
use App\Models\Comment;
use App\Models\Post;

class CommentService
{
    public function createComment(CreateCommentDTO $dto): Comment
    {
        $comment = Comment::create([
            'user_id' => $dto->userId,
            'post_id' => $dto->postId,
            'text' => $dto->text,
        ]);
        
        // Increment post comments count
        Post::where('_id', $dto->postId)->increment('comments_count');
        
        return $comment;
    }

    public function deleteComment(Comment $comment): void
    {
        $postId = $comment->post_id;
        $comment->delete();
        
        // Decrement post comments count
        Post::where('_id', $postId)->decrement('comments_count');
    }
}