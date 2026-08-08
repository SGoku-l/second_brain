<?php

namespace Tests\Feature;

use App\Models\Source;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manage_page_renders_for_an_active_subscriber(): void
    {
        [$user] = $this->activeUserWithPlan();

        $this->actingAs($user)->get(route('plans.manage'))
            ->assertOk()
            ->assertSee('Change your capacity');
    }

    public function test_switch_is_blocked_when_current_usage_exceeds_the_new_plan(): void
    {
        [$user, $current] = $this->activeUserWithPlan(['monthly_repo_limit' => 10]);
        $smaller = $this->plan(['monthly_repo_limit' => 2]);
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'Default']);
        foreach (range(1, 3) as $number) {
            Source::create(['workspace_id' => $workspace->id, 'type' => 'github', 'identifier' => "owner/repo-{$number}"]);
        }

        $this->actingAs($user)->post(route('plans.switch'), ['plan_id' => $smaller->id])
            ->assertRedirect(route('plans.manage'))
            ->assertSessionHas('subscription_notice');

        $this->assertDatabaseHas('user_subscriptions', ['user_id' => $user->id, 'subscription_plan_id' => $current->id]);
        $this->assertDatabaseCount('sources', 3);
        $this->assertDatabaseMissing('transactions', ['subscription_plan_id' => $smaller->id, 'razorpay_order_id' => 'switch_']);
    }

    public function test_switch_succeeds_when_usage_fits_and_records_the_change(): void
    {
        [$user] = $this->activeUserWithPlan(['monthly_repo_limit' => 10]);
        $target = $this->plan(['monthly_repo_limit' => 2]);

        $this->actingAs($user)->post(route('plans.switch'), ['plan_id' => $target->id])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('user_subscriptions', ['user_id' => $user->id, 'subscription_plan_id' => $target->id, 'cancel_at_period_end' => false]);
        $this->assertDatabaseHas('transactions', ['user_id' => $user->id, 'subscription_plan_id' => $target->id, 'amount' => 0, 'status' => 'completed']);
    }

    public function test_cancellation_is_scheduled_without_deleting_indexed_repositories(): void
    {
        [$user, $plan, $subscription] = $this->activeUserWithPlan();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'Default']);
        Source::create(['workspace_id' => $workspace->id, 'type' => 'github', 'identifier' => 'owner/repo']);

        $this->actingAs($user)->post(route('plans.cancel'))->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('user_subscriptions', ['id' => $subscription->id, 'status' => 'active', 'cancel_at_period_end' => true]);
        $this->assertDatabaseHas('sources', ['workspace_id' => $workspace->id, 'identifier' => 'owner/repo']);
        $this->assertDatabaseHas('transactions', ['user_id' => $user->id, 'subscription_plan_id' => $plan->id, 'amount' => 0, 'status' => 'completed']);
    }

    private function activeUserWithPlan(array $attributes = []): array
    {
        $user = User::factory()->create();
        $plan = $this->plan($attributes);
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        return [$user, $plan, $subscription];
    }

    private function plan(array $attributes = []): SubscriptionPlan
    {
        return SubscriptionPlan::create(array_merge([
            'name' => 'Plan '.str()->random(6), 'duration_months' => 1, 'price' => 100,
            'monthly_token_limit' => 1000, 'monthly_repo_limit' => 5, 'storage_limit_mb' => 500,
            'active' => true, 'is_free' => false,
        ], $attributes));
    }
}
