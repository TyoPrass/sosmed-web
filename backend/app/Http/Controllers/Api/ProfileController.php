<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\DTOs\Profile\UpdateProfileDTO;
use App\Services\ProfileService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService) {}

    public function show(): JsonResponse
    {
        return response()->json(auth()->guard('api')->user());
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $avatarPath = auth()->guard('api')->user()->avatar_path;
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $avatarPath = env('APP_URL') . '/storage/' . $path;
        }

        $dto = new UpdateProfileDTO(
            $request->username,
            $request->email,
            $request->bio,
            $avatarPath
        );
        $user = $this->profileService->updateProfile(auth()->guard('api')->user(), $dto);
        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = auth()->guard('api')->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password does not match'], 400);
        }
        
        $this->profileService->updatePassword($user, $request->new_password);
        return response()->json(['message' => 'Password updated successfully']);
    }

    public function showUser(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }
}