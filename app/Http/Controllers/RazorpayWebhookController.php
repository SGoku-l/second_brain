<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Subscriptions\RazorpayGateway;
use App\Services\Subscriptions\SubscriptionManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, RazorpayGateway $razorpay, SubscriptionManager $subscriptions): Response
    {
        $payload = $request->getContent();

        abort_unless($razorpay->validWebhookSignature($payload, $request->header('X-Razorpay-Signature')), 400);
        if ($request->header('X-Razorpay-Event') !== 'payment.captured') {
            return response('', 200);
        }

        $payment = data_get(json_decode($payload, true), 'payload.payment.entity');
        $orderId = $payment['order_id'] ?? null;
        $paymentId = $payment['id'] ?? null;

        if (! is_string($orderId) || ! is_string($paymentId)) {
            return response('', 200);
        }

        $transaction = Transaction::query()->where('razorpay_order_id', $orderId)->first();

        if ($transaction) {
            $subscriptions->complete($transaction, $paymentId);
        }

        return response('', 200);
    }
}
