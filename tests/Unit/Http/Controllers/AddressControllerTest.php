<?php

namespace Tests\Unit\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class AddressControllerTest extends TestCase
{
    public const USER_ADDRESS_ENDPOINT = '/api/user/address';

    public function testIndexWithoutFilters()
    {
        $user = $this->createUser();
        $user->addresses()->createMany([
            $this->createAddress(parent: $user)->toArray(),
            $this->createAddress(parent: $user)->toArray(),
            $this->createAddress(parent: $user)->toArray(),
        ]);

        $this->actingAs($user, self::GUARD_API);

        $response = $this->getJson(self::USER_ADDRESS_ENDPOINT);

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJsonStructure([
            'success',  'code',
            'result' => [
                'current_page',
                'data' => [],
                'total',
                'per_page',
                'last_page',
            ],
        ]);
        $response->assertJsonCount(3, 'result.data');
    }

    public function testIndexWithCountryFilter()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $user->addresses()->createMany([
            $this->createAddress(['country' => 'BR'], $user)->toArray(),
            $this->createAddress(parent: $user)->toArray(),
            $this->createAddress(parent: $user)->toArray(),
        ]);

        $response = $this->getJson(self::USER_ADDRESS_ENDPOINT . '?country=BR');

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJsonFragment(['country' => 'BR']);
        $response->assertJsonCount(1, 'result.data');
    }

    public function testIndexWithStateFilter()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $user->addresses()->createMany([
            $this->createAddress(['state' => 'TestState'], $user)->toArray(),
            $this->createAddress(parent: $user)->toArray(),
            $this->createAddress(parent: $user)->toArray(),
            $this->createAddress(parent: $user)->toArray(),
        ]);

        $response = $this->getJson(self::USER_ADDRESS_ENDPOINT . '?state=TestState');

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJsonFragment(['state' => 'TestState']);
        $response->assertJsonCount(1, 'result.data');
    }

    public function testIndexWithCityFilter()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $user->addresses()->createMany([
            $this->createAddress(['city' => 'TestCity'], $user)->toArray(),
            $this->createAddress(parent: $user)->toArray(),
        ]);

        $response = $this->getJson(self::USER_ADDRESS_ENDPOINT . '?city=TestCity');

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJsonFragment(['city' => 'TestCity']);
        $response->assertJsonCount(1, 'result.data');
    }

    public function testIndexUnauthenticated()
    {
        $response = $this->getJson(self::USER_ADDRESS_ENDPOINT);

        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testShowAddressWithValidIdAndAuthenticatedUser()
    {
        $user = $this->createUser();
        $user->addresses()->createMany([$this->createAddress(['country' => 'US'], $user)->toArray()]);

        $this->actingAs($user, self::GUARD_API);

        $response = $this->getJson(sprintf('%s/%s', self::USER_ADDRESS_ENDPOINT, $user->addresses->first()->id));

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson([
            'success' => true,
            'code' => JsonResponse::HTTP_OK,
            'result' => [
                'id' => $user->addresses->first()->id,
                'country' => 'US',
            ],
        ]);
    }

    public function testShowAddressWithInvalidId()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $response = $this->getJson(self::USER_ADDRESS_ENDPOINT . '/invalid-id');

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testShowAddressWithoutAuthentication()
    {
        $response = $this->getJson(self::USER_ADDRESS_ENDPOINT . '/some-id');

        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function testShowAddressNotBelongingToUser()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $otherUser->addresses()->createMany([$this->createAddress(parent: $otherUser)->toArray()]);

        $this->actingAs($user, self::GUARD_API);

        $response = $this->getJson(sprintf('%s/%s', self::USER_ADDRESS_ENDPOINT, $otherUser->addresses->first()->id));

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testShowSoftDeletedAddress()
    {
        $user = $this->createUser();
        $user->addresses()->createMany([$this->createAddress(parent: $user)->toArray()]);

        $address = $user->addresses->first();
        $address->delete();

        $this->actingAs($user, self::GUARD_API);

        $response = $this->getJson(sprintf('%s/%s', self::USER_ADDRESS_ENDPOINT, $address->id));

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJson([
            'success' => false,
            'code' => JsonResponse::HTTP_NOT_FOUND,
            'message' => 'Item not found',
        ]);
    }

    public function testStoreAddressSuccessfully()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $addressData = [
            'country' => 'US',
            'state' => 'StateName',
            'city' => 'CityName',
            'street' => 'StreetName',
            'zip_code' => '12345',
            'number' => '123',
            'neighborhood' => 'St Mac',
        ];

        $response = $this->postJson(self::USER_ADDRESS_ENDPOINT, $addressData);

        $response->assertStatus(JsonResponse::HTTP_CREATED);
        $response->assertJson([
            'success' => true,
            'message' => 'Address created successfully',
            'result' => [
                'country' => 'US',
                'state' => 'StateName',
                'city' => 'CityName',
                'street' => 'StreetName',
                'zip_code' => '12345',
                'number' => '123',
                'neighborhood' => 'St Mac',
            ],
        ]);
        $this->assertDatabaseHas('addresses', ['user_id' => $user->id, 'country' => 'US']);
    }

    public function testStoreAddressUnauthenticated()
    {
        $addressData = [
            'country' => 'US',
            'state' => 'StateName',
            'city' => 'CityName',
            'street' => 'StreetName',
            'zip_code' => '12345',
            'number' => '123',
            'neighborhood' => 'St Mac',
        ];

        $response = $this->postJson(self::USER_ADDRESS_ENDPOINT, $addressData);

        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testStoreAddressWithInvalidData()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $addressData = [
            'state' => 'StateName',
            'city' => 'CityName',
            'street' => 'StreetName',
            'zip_code' => '12345',
        ];

        $response = $this->postJson(self::USER_ADDRESS_ENDPOINT, $addressData);

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure(['fields' => ['country']]);
    }

    public function testStoreAddressWithExceedingInputLength()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $addressData = [
            'country' => str_repeat('a', 10),
            'state' => str_repeat('b', 256),
            'city' => 'CityName',
            'street' => 'StreetName',
            'zip_code' => str_repeat('0', 25),
        ];

        $response = $this->postJson(self::USER_ADDRESS_ENDPOINT, $addressData);

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'fields' => [
                'country', 'state', 'zip_code', 'number', 'neighborhood',
            ],
        ]);
    }

    public function testUpdateWithInvalidId()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $addressData = [
            'country' => 'US',
            'state' => 'StateName',
            'city' => 'CityName',
            'street' => 'StreetName',
            'zip_code' => '12345',
            'number' => '123',
            'neighborhood' => 'St Mac',
        ];

        $response = $this->putJson(self::USER_ADDRESS_ENDPOINT . '/invalid-id', $addressData);

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJsonStructure(['message', 'code', 'success']);
    }

    public function testUpdateWithMissingFields()
    {
        $user = $this->createUser();
        $user->addresses()->createMany([$this->createAddress(parent: $user)->toArray()]);
        $address = $user->addresses->first();

        $this->actingAs($user, self::GUARD_API);

        $response = $this->putJson(sprintf('%s/%s', self::USER_ADDRESS_ENDPOINT, $address->id), []);

        $response->assertStatus(JsonResponse::HTTP_BAD_REQUEST);
        $response->assertJson([
            'success' => false,
            'code' => JsonResponse::HTTP_BAD_REQUEST,
            'message' => 'Nothing to update',
        ]);
    }

    public function testUpdateWithInvalidFieldTypes()
    {
        $user = $this->createUser();
        $user->addresses()->createMany([$this->createAddress(parent: $user)->toArray()]);
        $address = $user->addresses->first();

        $this->actingAs($user, self::GUARD_API);

        $response = $this->putJson(sprintf('%s/%s', self::USER_ADDRESS_ENDPOINT, $address->id), [
            'zip_code' => 'invalid_zip_code',
        ]);

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateUnauthorizedUser()
    {
        $user = $this->createUser();
        $address = $this->createAddress();

        $this->actingAs($user, self::GUARD_API);

        $response = $this->putJson(sprintf('%s/%s', self::USER_ADDRESS_ENDPOINT, $address->id), [
            'street' => '123 Main St',
        ]);

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testUpdateUnauthenticated()
    {
        $address = $this->createAddress();

        $response = $this->putJson(sprintf('%s/%s', self::USER_ADDRESS_ENDPOINT, $address->id), [
            'street' => '123 Main St',
        ]);

        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function testSuccessfulAddressDeletion()
    {
        $user = $this->createUser();
        $user->addresses()->createMany([$this->createAddress(parent: $user)->toArray()]);
        $address = $user->addresses->first();

        $this->actingAs($user, self::GUARD_API);

        $response = $this->deleteJson(sprintf('%s/%s', self::USER_ADDRESS_ENDPOINT, $address->id));

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson([
            'message' => 'Item deleted successfully',
            'success' => true,
            'code' => 200,
        ]);

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function testUnauthorizedAddressDeletionAttempt()
    {
        $user = $this->createUser();
        $address = $this->createAddress();

        $this->actingAs($user, self::GUARD_API);

        $response = $this->deleteJson(sprintf('%s/%s', self::USER_ADDRESS_ENDPOINT, $address->id));

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testAddressDeletionWithNonExistentAddressID()
    {
        $user = $this->createUser();
        $nonExistentId = 999;

        $this->actingAs($user, self::GUARD_API);

        $response = $this->deleteJson(sprintf('%s/%s', self::USER_ADDRESS_ENDPOINT, $nonExistentId));

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testAddressDeletionWithoutProvidingAddressID()
    {
        $user = $this->createUser();
        $this->actingAs($user, self::GUARD_API);

        $response = $this->deleteJson(self::USER_ADDRESS_ENDPOINT);

        $response->assertStatus(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testAddressDeletionForAnotherUsersAddress()
    {
        $user = $this->createUser();
        $anotherUser = $this->createUser();
        $anotherUser->addresses()->createMany([$this->createAddress(parent: $anotherUser)->toArray()]);
        $address = $anotherUser->addresses->first();

        $this->actingAs($user, self::GUARD_API);

        $response = $this->deleteJson(sprintf('%s/%s', self::USER_ADDRESS_ENDPOINT, $address->id));

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }
}
