<?php

namespace App\Repositories;

use App\Dto\Project\ProjectDto;
use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;

final class ProjectRepository implements ProjectRepositoryInterface
{
    public function projectExists(int $id): bool
    {
        return Project::where('id', $id)->exists();
    }

    public function getById(int $id): ProjectDto
    {
        $project = Project::where('id', $id)->findOrFail($id);
        return new ProjectDto($project->name, $project->description);
    }
}
