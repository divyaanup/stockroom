<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingEvent;
use App\Models\Order;

class ShippingWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Signature');

        // Validate HMAC
        $secret = env('SHIPPING_WEBHOOK_SECRET');
        $expected = hash_hmac('sha256', json_encode($payload), $secret);

        if (!hash_equals($expected, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Idempotency: avoid duplicate entries
        $exists = ShippingEvent::where('order_id', $payload['order_id'])
            ->where('tracking_number', $payload['tracking_number'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already processed'], 200);
        }

        // Save event
        $event = ShippingEvent::create([
            'order_id'        => $payload['order_id'],
            'carrier'         => $payload['carrier'],
            'tracking_number' => $payload['tracking_number'],
            'shipped_at'      => $payload['shipped_at'] ?? null,
            'raw_payload'     => $payload,
            'signature'       => $signature,
        ]);

        // Update order
        $order = Order::find($payload['order_id']);
        if ($order) {
            if ($order->status === 'paid') {
                $order->status = 'fulfilled';
                $order->save();
            }
            // else: keep status, but tracking info is logged
        }

        return response()->json(['message' => 'Shipping update processed']);
    }
}
