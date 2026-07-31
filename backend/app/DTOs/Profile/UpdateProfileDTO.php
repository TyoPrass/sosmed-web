<?php
namespace App\DTOs\Profile;

class UpdateProfileDTO {
    public function __construct(
        public readonly ?string $username = null,
        public readonly ?string $email = null,
        public readonly ?string $bio = null,
        public readonly ?string $avatar_path = null
    ) {}
}