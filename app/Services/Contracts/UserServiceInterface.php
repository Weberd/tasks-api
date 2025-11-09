<?php

namespace App\Services\Contracts;

use App\Dto\User\UserCreateDto;
use App\Dto\User\UserTokenDto;
use App\Dto\User\UserLoginDto;

interface UserServiceInterface
{
    public function register(UserCreateDto $registerDto): UserTokenDto;

    public function login(UserLoginDto $loginDto): bool;

    public function logout(mixed $user): void;

    public function get(string $email): UserTokenDto;
}
