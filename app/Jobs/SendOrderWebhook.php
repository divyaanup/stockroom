<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WebhookEndpoint;

class SendOrderWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public Order $order;

    public $tries = 5; // retry up to 5 times
    public $backoff = [60, 120, 300]; // 1m, 2m, 5m backoff
    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $endpoints = WebhookEndpoint::where('type', 'outbound')->get();

        foreach ($endpoints as $endpoint) {
            $payload = [
                'order_id'  => $this->order->id,
                'total'     => $this->order->total,
                'lines'     => $this->order->orderLines()->get(['product_id','quantity','unit_price','line_total']),
                'timestamp' => now()->toISOString(),
            ];

            $signature = hash_hmac('sha256', json_encode($payload), $endpoint->secret);

            $response = Http::withHeaders([
                'X-Signature' => $signature,
                'Content-Type' => 'application/json',
            ])->post($endpoint->url, $payload);

            // Store status
            $endpoint->update([
                'last_status' => $response->status(),
                'last_response_at' => now(),
            ]);

            if (!$response->successful()) {
                // rethrow so Laravel retries this job
                throw new \Exception("Webhook failed: " . $response->status());
            }
        }

    }
}
