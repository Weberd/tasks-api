<?php

namespace App\Repositories;

use App\Dto\User\UserCreateDto;
use App\Dto\User\UserTokenDto;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function create(UserCreateDto $user): UserTokenDto
    {
        $user = User::create([
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
            'api_token' => $user->api_token,
        ]);

        return new UserTokenDto(
            $user->id,
            $user->name,
            $user->email,
            $user->api_token,
        );
    }

    public function getPasswordHash(int $id): string
    {
        $user = User::where('id', $id)->firstOrFail();
        return $user->password;
    }

    public function get(int $id): UserTokenDto
    {
        $user = User::where('id', $id)->first();

        return new UserTokenDto(
            $user->id,
            $user->name,
            $user->email,
            $user->api_token,
        );
    }

    public function getIdByEmail(string $email): int
    {
        return User::where('email', $email)->value('id');
    }
}
