<?php

namespace Tests;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    public const GUARD_API = 'sanctum';

    public function createUser(array $attributes = [], Model $parent = null): User
    {
        return User::factory()->create($attributes, $parent);
    }

    public function createAddress(array $attributes = [], Model $parent = null): Address
    {
        return Address::factory()->create($attributes, $parent);
    }
}
