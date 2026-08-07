<?php

namespace App\Services\Subscriptions;

use App\Models\User;

class SubscriptionAccess
{
    public function allows(User $user): bool
    {
        $subscription = $user->subscription;

        return $subscription?->status === 'active' && $subscription->current_period_end?->isFuture();
    }
}
