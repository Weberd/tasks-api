<?php

namespace App\Dto\User;

class UserLoginDto
{
    public function __construct(
        public string $email,
        public string $password,
    )
    {
    }
}
