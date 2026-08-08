<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use Illuminate\Console\Command;

class ExpireLapsedSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire-lapsed';

    protected $description = 'Expire subscription periods that have ended.';

    public function handle(): int
    {
        $count = UserSubscription::query()->where('status', 'active')->where('current_period_end', '<=', now())->update(['status' => 'expired']);
        $cancelled = UserSubscription::query()
            ->where('status', 'expired')
            ->where('cancel_at_period_end', true)
            ->update(['status' => 'cancelled', 'subscription_plan_id' => null]);
        $this->info("Expired {$count} subscription(s); cancelled {$cancelled} subscription(s).");

        return self::SUCCESS;
    }
}
