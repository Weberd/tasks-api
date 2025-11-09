<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Создание тестового пользователя
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'api_token' => Str::random(80),
        ]);

        echo "User created:\n";
        echo "Email: test@example.com\n";
        echo "Password: password\n";
        echo "API Token: {$user->api_token}\n\n";

        // Создание тестового проекта
        $project = Project::create([
            'name' => 'Test Project',
            'description' => 'This is a test project for demonstration purposes',
        ]);

        echo "Project created:\n";
        echo "ID: {$project->id}\n";
        echo "Name: {$project->name}\n";
    }
}
