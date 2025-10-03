<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_rate_limit_is_enforced()
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 60; $i++) {
            $this->actingAs($user, 'sanctum')->getJson('/api/products');
        }

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/products');

        $response->assertStatus(429); // Too Many Requests
    }
}