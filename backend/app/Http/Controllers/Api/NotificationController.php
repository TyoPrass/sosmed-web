<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = auth()->guard('api')->id();
        
        $notifications = Notification::with(['actor', 'post'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json(['data' => $notifications]);
    }

    public function markAsRead(): JsonResponse
    {
        $userId = auth()->guard('api')->id();
        
        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Notifications marked as read']);
    }
}
