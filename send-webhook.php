<?php

// Load environment variables
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Config
$url = "http://localhost/api/shipping/webhook";   // webhook endpoint
$secret = $_ENV['SHIPPING_WEBHOOK_SECRET'] ?? 'test-secret';

// Load payload
$payloadFile = __DIR__ . '/payload.json';
if (!file_exists($payloadFile)) {
    die("payload.json not found.\n");
}
$payload = file_get_contents($payloadFile);

// Compute HMAC SHA-256
$signature = hash_hmac('sha256', $payload, $secret);

// Init cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Signature: ' . $signature,
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

// Execute
$response = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Output
echo "➡️  Sent to: $url\n";
echo "🔑 Signature: $signature\n";
echo "📦 Payload: $payload\n";
echo "✅ Response ($status): $response\n";
