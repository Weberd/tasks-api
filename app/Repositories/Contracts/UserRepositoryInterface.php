<?php

namespace App\Repositories\Contracts;

use App\Dto\User\UserCreateDto;
use App\Dto\User\UserTokenDto;

interface UserRepositoryInterface
{
    public function create(UserCreateDto $user): UserTokenDto;

    public function getIdByEmail(string $email);
}
