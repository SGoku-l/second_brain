<?php

namespace App\Services\Subscriptions;

use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;

class SubscriptionManager
{
    public function activate(User $user, SubscriptionPlan $plan): UserSubscription
    {
        return DB::transaction(function () use ($user, $plan) {
            $subscription = UserSubscription::query()->lockForUpdate()->firstOrNew(['user_id' => $user->id]);
            $start = $subscription->current_period_end?->isFuture() ? $subscription->current_period_end : now();

            $subscription->fill([
                'subscription_plan_id' => $plan->id,
                'status' => 'active',
                'current_period_start' => $start,
                'current_period_end' => $plan->duration_days
                    ? $start->copy()->addDays($plan->duration_days)
                    : $start->copy()->addMonths($plan->duration_months),
                'tokens_used_current_period' => 0,
                'storage_used_mb' => 0,
                'storage_used_bytes' => 0,
            ]);
            $subscription->save();

            return $subscription;
        });
    }

    public function complete(Transaction $transaction, string $paymentId): void
    {
        DB::transaction(function () use ($transaction, $paymentId) {
            $transaction = Transaction::query()->lockForUpdate()->with(['user', 'plan'])->findOrFail($transaction->id);

            if ($transaction->status === 'completed') {
                return;
            }

            $transaction->update([
                'status' => 'completed',
                'razorpay_payment_id' => $paymentId,
                'completed_at' => now(),
            ]);

            $this->activate($transaction->user, $transaction->plan);
        });
    }
}
