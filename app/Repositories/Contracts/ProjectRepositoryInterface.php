<?php

namespace App\Repositories\Contracts;

use App\Dto\Project\ProjectDto;
use Illuminate\Database\Eloquent\Model;

interface ProjectRepositoryInterface
{
    public function projectExists(int $id): bool;
    public function getById(int $id): ProjectDto;
}
