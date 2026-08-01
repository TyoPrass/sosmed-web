<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;

// Auth Routes (Public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Routes
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update']);
    Route::match(['put', 'patch'], '/profile/password', [ProfileController::class, 'updatePassword']);
    Route::get('/users/{id}', [ProfileController::class, 'showUser']);
    Route::get('/users/{id}/posts', [PostController::class, 'userPosts']);

    // Posts
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::match(['put', 'patch'], '/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    // Comments & Likes
    Route::post('/posts/{id}/like', [LikeController::class, 'toggle']);
    Route::post('/posts/{id}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);

    // Saved Posts
    Route::get('/saved-posts', [App\Http\Controllers\Api\SavedPostController::class, 'index']);
    Route::post('/posts/{id}/save', [App\Http\Controllers\Api\SavedPostController::class, 'toggle']);

    // Follow & Search
    Route::post('/users/{id}/follow', [App\Http\Controllers\Api\FollowController::class, 'toggle']);
    Route::get('/search', [ProfileController::class, 'search']);
});
