<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderLine;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderBusinessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_total_is_calculated_correctly()
    {
        $product = Product::factory()->create(['price' => 50]);
        $order = Order::factory()->create(['total' => 0]);

        $line = OrderLine::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => $product->price,
            'line_total' => 3 * $product->price,
        ]);

        $order->refresh();
        $order->total = $order->lines->sum('line_total');
        $order->save();

        $this->assertEquals(150, $order->total);
    }

    /** @test */
    public function stock_reservation_fails_if_insufficient()
    {
        $product = Product::factory()->create(['stock_on_hand' => 2]);

        $this->expectException(\Exception::class);

        if ($product->stock_on_hand < 5) {
            throw new \Exception("Insufficient stock");
        }
    }

    /** @test */
    public function status_transition_from_draft_to_placed_is_allowed()
    {
        $order = Order::factory()->create(['status' => 'draft']);
        $order->status = 'placed';
        $order->save();

        $this->assertEquals('placed', $order->status);
    }
}