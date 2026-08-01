<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\CreatePostRequest;
use App\DTOs\Post\CreatePostDTO;
use App\Services\PostService;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function __construct(private PostService $postService) {}

    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $posts = $this->postService->getAllPosts($request->query('user_id'));
        return response()->json($posts);
    }

    public function userPosts(string $id): JsonResponse
    {
        $posts = $this->postService->getAllPosts($id);
        return response()->json($posts);
    }

    public function store(CreatePostRequest $request): JsonResponse
    {
        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $imagePath = 'storage/' . $path;
        }

        $dto = new CreatePostDTO(
            auth()->guard('api')->id(),
            $request->caption,
            $imagePath
        );
        $post = $this->postService->createPost($dto);
        return response()->json(['message' => 'Post created successfully', 'post' => $post], 201);
    }

    public function update(\Illuminate\Http\Request $request, string $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        if ($post->user_id !== auth()->guard('api')->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate(['caption' => 'required|string']);
        $post = $this->postService->updatePost($post, $request->caption);
        return response()->json(['message' => 'Post updated successfully', 'post' => $post]);
    }

    public function destroy(string $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        if ($post->user_id !== auth()->guard('api')->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $this->postService->deletePost($post);
        return response()->json(['message' => 'Post deleted successfully']);
    }
}