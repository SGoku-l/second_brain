<?php

namespace App\Http\Controllers;

use App\Models\Source;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Subscriptions\RazorpayGateway;
use App\Services\Subscriptions\SubscriptionManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlansController extends Controller
{
    public function index(): View
    {
        $hasUsedFreePlan = Transaction::query()->where('user_id', request()->user()->id)->where('status', 'completed')->whereHas('plan', fn ($query) => $query->where('is_free', true))->exists();
        $plans = SubscriptionPlan::query()->where('active', true)->when($hasUsedFreePlan, fn ($query) => $query->where('is_free', false))->orderBy('price')->get();

        return view('plans.index', ['plans' => $plans, 'subscription' => request()->user()->subscription?->load('plan')]);
    }

    public function manage(Request $request): View
    {
        $user = $request->user();
        $subscription = $user->subscription?->load('plan');

        return view('plans.manage', [
            'subscription' => $subscription,
            'plans' => $this->availablePlans($user),
            'usage' => $this->usageFor($user, $subscription),
        ]);
    }

    public function switch(Request $request, SubscriptionManager $subscriptions): RedirectResponse
    {
        $data = $request->validate(['plan_id' => ['required', 'exists:subscription_plans,id']]);
        $user = $request->user();
        $plan = SubscriptionPlan::query()->where('active', true)->findOrFail($data['plan_id']);
        $subscription = $user->subscription?->load('plan');

        if (! $subscription || $subscription->status !== 'active' || ! $subscription->current_period_end?->isFuture()) {
            return redirect()->route('plans.manage')->with('subscription_notice', 'You need an active subscription before switching plans.');
        }

        if ($subscription->subscription_plan_id === $plan->id) {
            return redirect()->route('plans.manage')->with('subscription_notice', 'You are already on this plan.');
        }

        $usage = $this->usageFor($user, $subscription);
        $overages = $this->overages($usage, $plan);
        if ($overages !== []) {
            return redirect()->route('plans.manage')->with('subscription_notice', 'Cannot switch plans: '.implode(' ', $overages));
        }

        DB::transaction(function () use ($user, $plan, $subscriptions) {
            $subscriptions->switchPlan($user, $plan);
            Transaction::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'razorpay_order_id' => 'switch_'.str()->uuid(),
                'amount' => 0,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        });

        return redirect()->route('dashboard')->with('repo_ingest_message', "Your plan has been changed to {$plan->name}.");
    }

    public function cancel(Request $request, SubscriptionManager $subscriptions): RedirectResponse
    {
        $user = $request->user();
        $subscription = $user->subscription?->load('plan');
        if (! $subscription || $subscription->status !== 'active' || ! $subscription->current_period_end?->isFuture()) {
            return redirect()->route('plans.manage')->with('subscription_notice', 'There is no active subscription to cancel.');
        }

        if (! $subscription->cancel_at_period_end) {
            DB::transaction(function () use ($user, $subscription, $subscriptions) {
                $subscriptions->cancelAtPeriodEnd($user);
                Transaction::create([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $subscription->subscription_plan_id,
                    'razorpay_order_id' => 'cancel_'.str()->uuid(),
                    'amount' => 0,
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            });
        }

        return redirect()->route('dashboard')->with('repo_ingest_message', 'Your subscription will end on '.$subscription->current_period_end->format('M j, Y').'. Your indexed repositories will be kept.');
    }

    private function availablePlans(User $user)
    {
        $hasUsedFreePlan = Transaction::query()->where('user_id', $user->id)->where('status', 'completed')->whereHas('plan', fn ($query) => $query->where('is_free', true))->exists();

        return SubscriptionPlan::query()->where('active', true)->when($hasUsedFreePlan, fn ($query) => $query->where('is_free', false))->orderBy('price')->get();
    }

    private function usageFor(User $user, $subscription): array
    {
        return [
            'tokens' => (int) ($subscription?->tokens_used_current_period ?? 0),
            'repos' => Source::query()->whereHas('workspace', fn ($query) => $query->where('user_id', $user->id))->count(),
            'storage' => (int) ($subscription?->storage_used_mb ?? 0),
        ];
    }

    private function overages(array $usage, SubscriptionPlan $plan): array
    {
        $checks = [
            ['tokens', 'monthly_token_limit', 'tokens'],
            ['repos', 'monthly_repo_limit', 'repositories'],
            ['storage', 'storage_limit_mb', 'MB storage'],
        ];

        return collect($checks)->filter(fn ($check) => $usage[$check[0]] > $plan->{$check[1]})
            ->map(fn ($check) => 'You have '.number_format($usage[$check[0]])." {$check[2]} — this plan allows ".number_format($plan->{$check[1]}).'. Remove usage first to downgrade.')
            ->values()->all();
    }

    public function checkout(Request $request, SubscriptionPlan $plan, RazorpayGateway $razorpay, SubscriptionManager $subscriptions): View|RedirectResponse
    {
        abort_unless($plan->active, 404);
        if ($plan->is_free) {
            DB::transaction(function () use ($request, $plan, $subscriptions) {
                User::query()->lockForUpdate()->findOrFail($request->user()->id);
                abort_if(Transaction::query()->where('user_id', $request->user()->id)->where('status', 'completed')->whereHas('plan', fn ($query) => $query->where('is_free', true))->exists(), 403);

                $transaction = Transaction::create([
                    'user_id' => $request->user()->id,
                    'subscription_plan_id' => $plan->id,
                    'razorpay_order_id' => 'free_'.str()->uuid(),
                    'amount' => 0,
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                $subscriptions->activate($request->user(), $plan);

                return $transaction;
            });

            return redirect()->route('dashboard')->with('repo_ingest_message', 'Your free plan is active.');
        }

        $transaction = Transaction::create([
            'user_id' => $request->user()->id,
            'subscription_plan_id' => $plan->id,
            'razorpay_order_id' => 'pending_'.str()->uuid(),
            'amount' => (int) round(((float) $plan->price) * 100),
            'status' => 'pending',
            'expires_at' => now()->addMinutes(5),
        ]);

        try {
            $transaction->update(['razorpay_order_id' => $razorpay->createOrder($transaction)]);
        } catch (\Throwable $exception) {
            $transaction->update(['status' => 'failed']);
            report($exception);

            return back()->with('subscription_notice', 'Checkout could not be started. Please try again.');
        }

        return view('plans.checkout', ['transaction' => $transaction->fresh('plan')]);
    }
}
