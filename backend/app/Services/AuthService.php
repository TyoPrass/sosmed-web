<?php

namespace App\Services;

use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\LoginDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(RegisterDTO $dto): User
    {
        return User::create([
            'username' => $dto->username,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);
    }

    public function login(LoginDTO $dto): ?string
    {
        $credentials = ['email' => $dto->email, 'password' => $dto->password];
        if (! $token = auth()->guard('api')->attempt($credentials)) {
            return null;
        }
        return $token;
    }

    public function logout(): void
    {
        auth()->guard('api')->logout();
    }
}