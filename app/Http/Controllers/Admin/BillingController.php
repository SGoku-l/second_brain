<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function settings(): View
    {
        return view('admin.settings', ['plans' => SubscriptionPlan::query()->latest()->get()]);
    }

    public function storePlan(Request $request): RedirectResponse
    {
        SubscriptionPlan::create($this->validatedPlan($request));

        return back()->with('status', 'Subscription plan created.');
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update($this->validatedPlan($request));

        return back()->with('status', 'Subscription plan updated.');
    }

    public function deactivatePlan(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update(['active' => false]);

        return back()->with('status', 'Subscription plan deactivated.');
    }

    private function validatedPlan(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'], 'duration' => ['required', 'string', 'in:week_1,week_2,week_3,month_1,month_3,month_6,month_9'],
            'price' => ['required', 'numeric', 'min:0'], 'monthly_token_limit' => ['required', 'integer', 'min:0'],
            'monthly_repo_limit' => ['required', 'integer', 'min:0'], 'storage_limit_mb' => ['required', 'integer', 'min:0'],
        ]);

        if (! $request->boolean('is_free') && (float) $data['price'] < 1) {
            throw ValidationException::withMessages(['price' => 'Paid plans must be at least ₹1.00.']);
        }

        [$unit, $count] = explode('_', $data['duration']);
        unset($data['duration']);

        return [...$data, 'duration_months' => $unit === 'month' ? (int) $count : 0, 'duration_days' => $unit === 'week' ? (int) $count * 7 : null, 'is_free' => $request->boolean('is_free'), 'active' => $request->boolean('active')];
    }
}
