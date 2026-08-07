<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;

class ExpirePendingSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire-pending';

    protected $description = 'Expire unpaid Razorpay transactions after their checkout window.';

    public function handle(): int
    {
        $count = Transaction::query()->where('status', 'pending')->where('expires_at', '<=', now())->update(['status' => 'expired']);
        $this->info("Expired {$count} pending transaction(s).");

        return self::SUCCESS;
    }
}
