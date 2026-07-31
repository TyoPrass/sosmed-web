<?php

namespace App\Services;

use App\DTOs\Profile\UpdateProfileDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    public function updateProfile(User $user, UpdateProfileDTO $dto): User
    {
        if ($dto->username !== null) $user->username = $dto->username;
        if ($dto->email !== null) $user->email = $dto->email;
        if ($dto->bio !== null) $user->bio = $dto->bio;
        if ($dto->avatar_path !== null) $user->avatar_path = $dto->avatar_path;
        $user->save();
        return $user;
    }

    public function updatePassword(User $user, string $newPassword): User
    {
        $user->password = Hash::make($newPassword);
        $user->save();
        return $user;
    }
}