<?php

namespace Tests\Feature;

use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    public function testTheApplicationReturnsRedirectResponse(): void
    {
        $response = $this->get('/');

        $response->assertStatus(JsonResponse::HTTP_FOUND);
    }
}
