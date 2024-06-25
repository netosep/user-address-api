<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use WithFaker;

    const LOGIN_ENDPOINT = '/api/login';

    public function test_auth_with_valid_credentials()
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'code' => 200]);
        $response->assertJsonStructure([
            'success', 'code', 'result' => [
                'access_token',
                'token_type',
                'expires_in',
            ],
        ]);
    }

    public function test_auth_with_invalid_email()
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'invalid_email@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
        $response->assertJsonStructure(['message']);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
            'code' => 401,
        ]);
    }

    public function test_auth_with_invalid_password()
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401);
        $response->assertJsonStructure(['message']);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
            'code' => 401,
        ]);
    }

    public function test_auth_with_empty_email()
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['success', 'message', 'code', 'fields' => ['email']]);
        $response->assertJson([
            'success' => false,
            'message' => 'Validation error',
            'code' => 422,
            'fields' => [
                'email' => ['The email field is required.'],
            ],
        ]);
    }

    public function test_auth_with_empty_password()
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => $user->email,
            'password' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['success', 'message', 'code', 'fields' => ['password']]);
        $response->assertJson([
            'success' => false,
            'message' => 'Validation error',
            'code' => 422,
            'fields' => [
                'password' => ['The password field is required.'],
            ],
        ]);
    }

    public function test_auth_with_no_data()
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, []);

        $response->assertStatus(422);
        $response->assertJsonStructure(['success', 'message', 'code', 'fields' => ['email', 'password']]);
        $response->assertJson([
            'success' => false,
            'message' => 'Validation error',
            'code' => 422,
            'fields' => [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ],
        ]);
    }
}
