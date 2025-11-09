<?php

namespace App\Dto\User;

class UserCreateDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password = '',
        public string $api_token = '',
    )
    {
    }
}
