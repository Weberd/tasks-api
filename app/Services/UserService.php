<?php

namespace App\Services;

use App\Dto\User\UserCreateDto;
use App\Dto\User\UserTokenDto;
use App\Dto\User\UserLoginDto;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    )
    {
    }

    public function register(UserCreateDto $registerDto): UserTokenDto
    {
        $registerDto->password = Hash::make($registerDto->password);
        $registerDto->api_token = Str::random(80);

        return $this->userRepository->create($registerDto);
    }

    public function login(UserLoginDto $loginDto): bool
    {
        $hash = $this->userRepository->getPasswordHash($this->get($loginDto->email)->id);
        return Hash::check($loginDto->password, $hash);
    }

    public function logout(mixed $user): void
    {
        $user->api_token = null;
        $user->save();
    }

    public function get(string $email): UserTokenDto
    {
        $id = $this->userRepository->getIdByEmail($email);
        return $this->userRepository->get($id);
    }
}
