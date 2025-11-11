<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Faker\Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Faker\Factory as Faker;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
    }

    public function test_user_registration_successfully()
    {
        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'user' => [
                    'name',
                    'email',
                ]
            ]);

        $this->assertDatabaseHas('users', ['email' => $payload['email']]);
    }

    public function test_fails_to_register_with_existing_email()
    {
        $email = $this->faker->unique()->safeEmail();

        User::factory()->create([
            'email' => $email,
        ]);

        $payload = [
            'name' => $this->faker->name(),
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_with_valid_credentials()
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => $password,
            'api_token' => $user->api_token,
        ];

        $response = $this->postJson('/api/login', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'user' => [
                    'name',
                    'email',
                ]
            ]);
    }

    public function test_fails_to_login_with_invalid_credentials()
    {
        $payload = [
            'email' => $this->faker->safeEmail(),
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/login', $payload);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_logout_authenticated_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout successful',
            ]);
    }

    public function test_fails_to_logout_if_not_authenticated()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }
}
