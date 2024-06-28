<?php

namespace Tests\Unit\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public const USER_ENDPOINT = '/api/user';

    public function testAuthenticatedUserCanRetrieveInformationWithAddresses()
    {
        $user = $this->createUser();
        $user->addresses()->createMany([
            $this->createAddress(parent: $user)->toArray(),
            $this->createAddress(parent: $user)->toArray(),
        ]);

        $this->actingAs($user, self::GUARD_API);

        $response = $this->getJson(self::USER_ENDPOINT);

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJsonStructure([
            'success', 'code',
            'result' => [
                'id',
                'name',
                'email',
                'addresses' => [],
            ],
        ]);
    }

    public function testResponseContainsExpectedStructure()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $response = $this->getJson(self::USER_ENDPOINT);

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJsonStructure(['success', 'code', 'result']);
    }

    public function testUnauthenticatedRequestIsDenied()
    {
        $response = $this->getJson(self::USER_ENDPOINT);

        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testPaginationOfAddressesWorksAsExpected()
    {
        $user = $this->createUser();
        $user->addresses()->createMany([$this->createAddress(parent: $user)->toArray()]);

        $this->actingAs($user, self::GUARD_API);

        $response = $this->getJson(self::USER_ENDPOINT);

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJsonPath('result.addresses.current_page', 1);
    }

    public function testUserObjectContainsCorrectData()
    {
        $user =  $this->createUser([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);

        $this->actingAs($user, self::GUARD_API);

        $response = $this->getJson(self::USER_ENDPOINT);

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson([
            'result' => [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ],
        ]);
    }

    public function testSuccessfulUserUpdate()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $response = $this->putJson(self::USER_ENDPOINT, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson([
            'success' => true,
            'message' => 'User updated successfully',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function testUpdateWithInvalidData()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $response = $this->putJson(self::USER_ENDPOINT, [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure(['message', 'success', 'code', 'fields' => ['email']]);
        $response->assertJson(['message' => 'Validation error']);
    }

    public function testUpdateWithUnauthorizedUser()
    {
        $response = $this->putJson(self::USER_ENDPOINT, [
            'name' => 'Another Name',
        ]);

        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['message']);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testUpdateNameAndEmail()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $originalName = $user->name;
        $originalEmail = $user->email;

        $response = $this->putJson(self::USER_ENDPOINT, [
            'name' => 'New Name',
            'email' => 'newemail@email.com',
        ]);

        $response->assertStatus(JsonResponse::HTTP_OK);

        $user->refresh();
        $this->assertNotEquals($originalName, $user->name);
        $this->assertNotEquals($originalEmail, $user->email);

        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('newemail@email.com', $user->email);
    }

    public function testDeleteUserReturnsSuccessWithValidUser()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $response = $this->deleteJson(self::USER_ENDPOINT);

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJsonStructure(['message', 'success', 'code']);
        $response->assertJson([
            'message' => 'Item deleted successfully',
            'success' => true,
            'code' => JsonResponse::HTTP_OK,
        ]);
    }

    public function testDeleteUserReturnsUnauthorizedWithoutUser()
    {
        $response = $this->deleteJson(self::USER_ENDPOINT);

        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['message']);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}
