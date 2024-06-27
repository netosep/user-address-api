<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    const LOGIN_ENDPOINT = '/api/login';
    const REGISTER_ENDPOINT = '/api/register';
    const LOGOUT_ENDPOINT = '/api/logout';

    public function testAuthWithValidCredentials()
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(['success' => true, 'code' => Response::HTTP_OK]);
        $response->assertJsonStructure([
            'success', 'code', 'result' => [
                'access_token',
                'token_type',
                'expires_in',
            ],
        ]);
    }

    public function testAuthWithInvalidEmail()
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'invalid_email@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['message']);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
            'code' => Response::HTTP_UNAUTHORIZED,
        ]);
    }

    public function testAuthWithInvalidPassword()
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['message']);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
            'code' => Response::HTTP_UNAUTHORIZED,
        ]);
    }

    public function testAuthWithEmptyEmail()
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure(['success', 'message', 'code', 'fields' => ['email']]);
        $response->assertJson([
            'success' => false,
            'message' => 'Validation error',
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'fields' => [
                'email' => ['The email field is required.'],
            ],
        ]);
    }

    public function testAuthWithEmptyPassword()
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => $user->email,
            'password' => '',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure(['success', 'message', 'code', 'fields' => ['password']]);
        $response->assertJson([
            'success' => false,
            'message' => 'Validation error',
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'fields' => [
                'password' => ['The password field is required.'],
            ],
        ]);
    }

    public function testAuthWithNoData()
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure(['success', 'message', 'code', 'fields' => ['email', 'password']]);
        $response->assertJson([
            'success' => false,
            'message' => 'Validation error',
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'fields' => [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ],
        ]);
    }

    public function testRegisterWithExistingEmail()
    {
        User::factory()->create([
            'email' => 'existing@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure(['success', 'message', 'code', 'fields' => ['email']]);
        $response->assertJson([
            'success' => false,
            'message' => 'Validation error',
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'fields' => [
                'email' => ['The email has already been taken.'],
            ],
        ]);
    }

    public function testRegisterWithValidData()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure(['result' => ['access_token', 'token_type', 'expires_in']]);
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    public function testRegisterWithNoData()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'success', 'message', 'code',
            'fields' => ['name', 'email', 'password', 'password_confirmation']
        ]);
    }

    public function testLogoutSuccess()
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson(self::LOGOUT_ENDPOINT);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['success', 'message', 'code']);
        $response->assertJson([
            'success' => true,
            'message' => 'Successfully logged out',
            'code' => Response::HTTP_OK,
        ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'user-token',
        ]);
    }

    public function testLogoutUnauthenticated()
    {
        $response = $this->postJson(self::LOGOUT_ENDPOINT);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['message']);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testLogoutInvalidToken()
    {
        $token = 'invalid-token';
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson(self::LOGOUT_ENDPOINT);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['message']);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testLogoutExpiredToken()
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token', expiresAt: now()->subWeek())->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson(self::LOGOUT_ENDPOINT);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['message']);
        $response->assertJson(['message' => 'Unauthenticated.']);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'user-token',
        ]);
    }

    public function testLogoutRevokedToken()
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;

        $user->tokens()->first()->delete();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson(self::LOGOUT_ENDPOINT);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['message']);
        $response->assertJson(['message' => 'Unauthenticated.']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'user-token',
        ]);
    }

    public function testTokenResponseSuccessStatusMessageNull()
    {
        $controller = new AuthController();
        $token = '1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        $expiresIn = new \DateTime('2023-01-01 00:00:00');

        $response = $controller->tokenResponse($token, $expiresIn);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $expected = [
            'success' => true,
            'code' => 200,
            'result' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn->getTimestamp(),
            ],
        ];
        $this->assertEquals($expected, $response->getData(true));
    }

    public function testTokenResponseSuccessStatusMessageProvided()
    {
        $controller = new AuthController();
        $token = '1|yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy';
        $expiresIn = new \DateTime('2024-01-01 00:00:00');
        $message = 'Token generated successfully';

        $response = $controller->tokenResponse($token, $expiresIn, $message);

        $expected = [
            'success' => true,
            'code' => 200,
            'message' => $message,
            'result' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn->getTimestamp(),
            ],
        ];
        $this->assertEquals($expected, $response->getData(true));
    }
}
