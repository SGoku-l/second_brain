<?php

namespace App\Http\Controllers;

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
