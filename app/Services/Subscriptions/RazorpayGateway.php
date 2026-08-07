<?php

namespace App\Services\Subscriptions;

use App\Models\Transaction;
use GuzzleHttp\Client;
use RuntimeException;

class RazorpayGateway
{
    public function createOrder(Transaction $transaction): string
    {
        $response = (new Client(['base_uri' => 'https://api.razorpay.com/v1/', 'timeout' => 15]))->post('orders', [
            'auth' => [config('services.razorpay.key_id'), config('services.razorpay.key_secret')],
            'json' => [
                'amount' => $transaction->amount,
                'currency' => 'INR',
                'receipt' => 'sub_'.str_replace('-', '', $transaction->id),
                'notes' => ['transaction_id' => $transaction->id],
            ],
        ]);

        $orderId = json_decode($response->getBody()->getContents(), true)['id'] ?? null;

        if (! is_string($orderId) || $orderId === '') {
            throw new RuntimeException('Razorpay did not return an order ID.');
        }

        return $orderId;
    }

    public function validWebhookSignature(string $payload, ?string $signature): bool
    {
        if (! is_string($signature) || $signature === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $payload, config('services.razorpay.key_secret')), $signature);
    }
}
