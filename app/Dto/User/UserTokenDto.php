<?php

namespace App\Dto\User;

class UserTokenDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $api_token,
    )
    {
    }
}
