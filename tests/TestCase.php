<?php

namespace Tests;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    const GUARD_API = 'sanctum';

    use WithFaker;
    use DatabaseTransactions;

    public function createUser(array $attributes = [], Model $parent = null): User
    {
        return User::factory()->create($attributes, $parent);
    }

    public function createAddress(array $attributes = [], Model $parent = null): Address
    {
        return Address::factory()->create($attributes, $parent);
    }
}
