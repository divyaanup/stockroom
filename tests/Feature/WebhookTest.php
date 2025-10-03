<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Order;
use App\Models\Customer;


class WebhookTest extends TestCase
{
    protected $secret = 'super-secret-shipping-key';
    use RefreshDatabase;

    /** @test */
    public function valid_signature_passes_verification()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->for($customer)->create();
        $payload = [
            'order_id' => $order->id,
            'carrier' => 'DHL',
            'tracking_number' => 'ORD-QJIBSDOB',
            'shipped_at' => now()->toISOString(),
        ];
        
        $signature = hash_hmac('sha256', json_encode($payload), $this->secret);

        $response = $this->postJson('/api/shipping/webhook', $payload, [
            'X-Signature' => $signature
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function invalid_signature_fails_verification()
    {
        $payload = ['order_id' => 1];
        $response = $this->postJson('/api/shipping/webhook', $payload, [
            'X-Signature' => 'invalid'
        ]);

        $response->assertStatus(401);
    }
}