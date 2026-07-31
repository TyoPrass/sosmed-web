<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LikeService;
use Illuminate\Http\JsonResponse;

class LikeController extends Controller
{
    public function __construct(private LikeService $likeService) {}

    public function toggle(string $postId): JsonResponse
    {
        $result = $this->likeService->toggleLike(auth()->guard('api')->id(), $postId);
        return response()->json(['message' => $result['status']]);
    }
}