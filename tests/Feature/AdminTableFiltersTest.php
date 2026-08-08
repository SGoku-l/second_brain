<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTableFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_filters_combine_search_status_and_plan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $free = $this->plan('Free', true);
        $paid = $this->plan('Pro', false);
        $matching = User::factory()->create(['name' => 'Ada Filter', 'email' => 'ada@example.test', 'active_status' => true]);
        $other = User::factory()->create(['name' => 'Ada Inactive', 'active_status' => false]);
        UserSubscription::create(['user_id' => $matching->id, 'subscription_plan_id' => $paid->id, 'status' => 'active', 'current_period_end' => now()->addMonth()]);
        UserSubscription::create(['user_id' => $other->id, 'subscription_plan_id' => $free->id, 'status' => 'active', 'current_period_end' => now()->addMonth()]);

        $this->actingAs($admin)->get(route('admin.users', ['search' => 'ada', 'status' => 'active', 'plan' => $paid->id]))
            ->assertOk()->assertSee('Ada Filter')->assertDontSee('Ada Inactive');

        $this->actingAs($admin)->get(route('admin.users', ['search' => 'not-found']))
            ->assertOk()->assertSee('No users match your filters.');
    }

    public function test_transactions_filters_search_status_plan_and_date_range(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pro = $this->plan('Pro', false);
        $user = User::factory()->create(['name' => 'Billing Ada', 'email' => 'billing@example.test']);
        Transaction::create(['user_id' => $user->id, 'subscription_plan_id' => $pro->id, 'razorpay_order_id' => 'test_'.str()->uuid(), 'amount' => 100, 'status' => 'completed', 'completed_at' => now()]);

        $this->actingAs($admin)->get(route('admin.transactions.index', ['search' => 'billing', 'status' => 'completed', 'plan' => $pro->id, 'range' => 'today']))
            ->assertOk()->assertSee('Billing Ada');

        $this->actingAs($admin)->get(route('admin.transactions.index', ['search' => 'not-found']))
            ->assertOk()->assertSee('No transactions match your filters.');
    }

    private function plan(string $name, bool $free): SubscriptionPlan
    {
        return SubscriptionPlan::create(['name' => $name, 'duration_months' => 1, 'price' => $free ? 0 : 100, 'monthly_token_limit' => 1000, 'monthly_repo_limit' => 5, 'storage_limit_mb' => 500, 'is_free' => $free, 'active' => true]);
    }
}
