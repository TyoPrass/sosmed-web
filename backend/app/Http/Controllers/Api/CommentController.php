<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CreateCommentRequest;
use App\DTOs\Comment\CreateCommentDTO;
use App\Services\CommentService;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function __construct(private CommentService $commentService) {}

    public function store(CreateCommentRequest $request, string $postId): JsonResponse
    {
        $dto = new CreateCommentDTO(
            auth()->guard('api')->id(),
            $postId,
            $request->text
        );
        $comment = $this->commentService->createComment($dto);
        return response()->json(['message' => 'Comment added successfully', 'comment' => $comment], 201);
    }

    public function destroy(string $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);
        if ($comment->user_id !== auth()->guard('api')->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $this->commentService->deleteComment($comment);
        return response()->json(['message' => 'Comment deleted successfully']);
    }
}