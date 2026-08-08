<?php

namespace App\Services\Subscriptions;

use App\Exceptions\SubscriptionLimitExceeded;
use App\Models\Source;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\TokenUsage;
use Illuminate\Support\Facades\DB;

class SubscriptionLimits
{
    public function ensureCanAddRepositories(User $user, array $identifiers): void
    {
        DB::transaction(function () use ($user, $identifiers) {
            $subscription = $this->activeSubscription($user);
            $workspaceIds = $user->workspaces()->pluck('id');
            $existing = Source::query()
                ->whereIn('workspace_id', $workspaceIds)
                ->where('type', 'github')
                ->pluck('identifier');
            $newCount = collect($identifiers)->unique()->diff($existing)->count();

            if ($existing->count() + $newCount > $subscription->plan->monthly_repo_limit) {
                throw new SubscriptionLimitExceeded('Repository limit reached. Your plan allows '.$subscription->plan->monthly_repo_limit.' repositories.');
            }
        });
    }

    public function reserveStorage(User $user, int $bytes): void
    {
        DB::transaction(function () use ($user, $bytes) {
            $subscription = $this->activeSubscription($user);
            $limitBytes = $subscription->plan->storage_limit_mb * 1024 * 1024;

            if ($subscription->storage_used_bytes + $bytes > $limitBytes) {
                throw new SubscriptionLimitExceeded('Storage limit reached. Your plan allows '.$subscription->plan->storage_limit_mb.' MB of storage.');
            }

            $usedBytes = $subscription->storage_used_bytes + $bytes;
            $subscription->update([
                'storage_used_bytes' => $usedBytes,
                'storage_used_mb' => (int) ceil($usedBytes / (1024 * 1024)),
            ]);
        });
    }

    public function releaseStorage(User $user, int $bytes): void
    {
        DB::transaction(function () use ($user, $bytes) {
            $subscription = UserSubscription::query()->lockForUpdate()->where('user_id', $user->id)->first();

            if (! $subscription) {
                return;
            }
            $usedBytes = max(0, $subscription->storage_used_bytes - max(0, $bytes));

            $subscription->update([
                'storage_used_bytes' => $usedBytes,
                'storage_used_mb' => (int) ceil($usedBytes / (1024 * 1024)),
            ]);
        });
    }

    public function ensureActive(User $user): void
    {
        $this->activeSubscription($user);
    }

    public function ensureCanUseTokens(User $user): void
    {
        $subscription = $this->activeSubscription($user);

        if ($subscription->tokens_used_current_period >= $subscription->plan->monthly_token_limit) {
            throw new SubscriptionLimitExceeded('Monthly token limit reached. Please upgrade your plan or wait for renewal.');
        }
    }

    public function recordTokens(User $user, int $tokens): void
    {
        DB::transaction(function () use ($user, $tokens) {
            $subscription = $this->activeSubscription($user);
            $tokens = max(0, $tokens);
            $subscription->increment('tokens_used_current_period', $tokens);

            if ($tokens > 0) {
                TokenUsage::create([
                    'user_id' => $user->id,
                    'tokens' => $tokens,
                    'recorded_at' => now(),
                ]);
            }
        });
    }

    private function activeSubscription(User $user): UserSubscription
    {
        $subscription = UserSubscription::query()->with('plan')->lockForUpdate()->where('user_id', $user->id)->first();

        if (! $subscription || $subscription->status !== 'active' || ! $subscription->current_period_end?->isFuture() || ! $subscription->plan) {
            throw new SubscriptionLimitExceeded('Your subscription is no longer active. Please choose a plan to continue.');
        }

        return $subscription;
    }
}
