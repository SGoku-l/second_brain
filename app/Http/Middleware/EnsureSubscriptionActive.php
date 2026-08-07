<?php

namespace App\Http\Middleware;

use App\Services\Subscriptions\SubscriptionAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    protected SubscriptionAccess $subscriptions;

    public function __construct(SubscriptionAccess $subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $this->subscriptions->allows($request->user())) {
            return redirect()
                ->route('plans.index')
                ->with('subscription_notice', 'Choose a plan to continue.');
        }

        return $next($request);
    }
}