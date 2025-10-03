<?php
namespace App\Listeners;

use App\Events\OrderPlaced;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail
{
    public function handle(OrderPlaced $event)
    {
        $order = $event->order;

        // Use a simple Mailable or just raw text (example)
        Mail::raw("Your order #{$order->order_number} has been placed. Total: €{$order->total}", function ($message) use ($order) {
            $message->to($order->customer->email)
                    ->subject('Order Confirmation');
        });
    }
}