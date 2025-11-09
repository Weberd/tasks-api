<?php

namespace App\Dto\Project;

class ProjectDto
{
    public function __construct(
        public string $name,
        public string $description,
    )
    {
    }
}
