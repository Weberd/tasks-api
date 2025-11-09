<?php

namespace App\Dto\Media;

class MediaDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $file_name,
        public string $mime_type,
        public int $size,
        public string $url,
    )
    {
    }
}
