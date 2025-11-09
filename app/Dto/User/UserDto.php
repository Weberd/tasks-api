<?php

namespace App\Dto\User;

class UserDto
{
    public function __construct(
        public string $name,
        public string $email
    )
    {
    }
}
