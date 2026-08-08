<x-plans-layout title="Manage plan">
    <div x-data="{ modal: null, planName: '', planId: '' }" class="space-y-6">
        @if(session('subscription_notice'))
            <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-200">{{ session('subscription_notice') }}</div>
        @endif

        <section class="glass-panel rounded-[26px] p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Your current plan</p>
                    <h2 class="mt-2 text-2xl font-semibold text-stone-900 dark:text-stone-100">{{ $subscription?->plan?->name ?? 'No active plan' }}</h2>
                    @if($subscription?->plan)
                        <p class="mt-2 text-sm text-stone-500">{{ $subscription->plan->is_free ? 'Free' : '₹'.number_format((float) $subscription->plan->price, 2) }} · Renews / expires {{ $subscription->current_period_end?->format('M j, Y') }}</p>
                    @endif
                </div>
                @if($subscription?->cancel_at_period_end)
                    <span class="rounded-full bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-200">Cancels {{ $subscription->current_period_end?->format('M j, Y') }}</span>
                @endif
            </div>

            @if($subscription?->plan)
                @php
                    $limits = [
                        ['Tokens', $usage['tokens'], $subscription->plan->monthly_token_limit],
                        ['Repositories', $usage['repos'], $subscription->plan->monthly_repo_limit],
                        ['Storage MB', $usage['storage'], $subscription->plan->storage_limit_mb],
                    ];
                @endphp
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    @foreach($limits as [$label, $used, $limit])
                        <div class="rounded-2xl border border-white/10 bg-black/5 p-4">
                            <div class="flex justify-between text-xs text-stone-500"><span>{{ $label }}</span><span>{{ number_format($used) }} / {{ number_format($limit) }}</span></div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-black/10 dark:bg-white/10"><div class="h-full rounded-full bg-sb-accent" style="width: {{ $limit ? min(100, ($used / $limit) * 100) : 0 }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-4 text-sm text-stone-500">Choose a plan to activate chat and repository ingestion.</p>
            @endif
        </section>

        <section class="glass-panel rounded-[26px] p-6">
            <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Available plans</p>
            <h2 class="mt-2 text-2xl font-semibold text-stone-900 dark:text-stone-100">Change your capacity</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @forelse($plans as $plan)
                    @php
                        $reasons = [];
                        if ($usage['tokens'] > $plan->monthly_token_limit) $reasons[] = 'You have '.number_format($usage['tokens']).' tokens used — this plan allows '.number_format($plan->monthly_token_limit).'.';
                        if ($usage['repos'] > $plan->monthly_repo_limit) $reasons[] = 'You have '.number_format($usage['repos']).' repos indexed — this plan allows '.number_format($plan->monthly_repo_limit).'.';
                        if ($usage['storage'] > $plan->storage_limit_mb) $reasons[] = 'You use '.number_format($usage['storage']).' MB storage — this plan allows '.number_format($plan->storage_limit_mb).' MB.';
                        $isCurrent = $subscription?->subscription_plan_id === $plan->id;
                    @endphp
                    <article class="rounded-[26px] border {{ $isCurrent ? 'border-sb-accent/50 bg-sb-accent/5' : 'border-white/10 bg-black/5' }} p-5">
                        <div class="flex items-center justify-between gap-3"><p class="text-lg font-semibold text-stone-900 dark:text-stone-100">{{ $plan->name }}</p>@if($isCurrent)<span class="text-xs font-semibold text-sb-accent">Current</span>@endif</div>
                        <p class="mt-3 font-mono text-3xl text-sb-accent">{{ $plan->is_free ? 'Free' : '₹'.number_format((float) $plan->price, 2) }}</p>
                        <p class="mt-1 text-sm text-stone-500">{{ $plan->duration_days ? $plan->duration_days / 7 .' week(s)' : $plan->duration_months.' month(s)' }}</p>
                        <ul class="mt-5 space-y-2 text-sm text-stone-500 dark:text-stone-400"><li>{{ number_format($plan->monthly_token_limit) }} tokens / month</li><li>{{ number_format($plan->monthly_repo_limit) }} repositories</li><li>{{ number_format($plan->storage_limit_mb) }} MB storage</li></ul>
                        @if($isCurrent)
                            <button disabled class="mt-6 w-full cursor-not-allowed rounded-xl bg-black/10 px-4 py-2 text-sm font-semibold text-stone-500 dark:bg-white/10">Current plan</button>
                        @elseif($reasons)
                            <button disabled title="{{ implode(' ', $reasons) }} Remove usage first to downgrade." class="mt-6 w-full cursor-not-allowed rounded-xl bg-black/10 px-4 py-2 text-sm font-semibold text-stone-500 dark:bg-white/10">Unavailable at current usage</button>
                            <p class="mt-2 text-xs text-amber-700 dark:text-amber-200">{{ $reasons[0] }} Remove usage first to downgrade.</p>
                        @elseif(! $subscription?->plan)
                            <form method="POST" action="{{ route('plans.checkout', $plan) }}" class="mt-6">@csrf<button class="w-full rounded-xl bg-sb-accent px-4 py-2 text-sm font-semibold text-sb-bg-dark">{{ $plan->is_free ? 'Choose free' : 'Continue to checkout' }}</button></form>
                        @else
                            <button type="button" @click="modal = 'switch'; planName = @js($plan->name); planId = @js($plan->id)" class="mt-6 w-full rounded-xl bg-sb-accent px-4 py-2 text-sm font-semibold text-sb-bg-dark">Switch to this plan</button>
                        @endif
                    </article>
                @empty
                    <p class="text-sm text-stone-500">No plans are currently available.</p>
                @endforelse
            </div>
        </section>

        @if($subscription?->plan && ! $subscription->cancel_at_period_end)
            <section class="glass-panel rounded-[26px] p-6"><div class="flex flex-wrap items-center justify-between gap-4"><div><p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-rose-500">Cancel subscription</p><p class="mt-2 text-sm text-stone-500">Your access and indexed data remain available until {{ $subscription->current_period_end?->format('M j, Y') }}. Nothing will be deleted.</p></div><button type="button" @click="modal = 'cancel'" class="rounded-xl border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-600 dark:text-rose-400">Cancel subscription</button></div></section>
        @endif

        <div x-show="modal" x-cloak class="fixed inset-0 z-[300] flex items-center justify-center bg-black/60 px-4" role="dialog" aria-modal="true">
            <div @click.outside="modal = null" class="glass-panel w-full max-w-md rounded-[26px] p-6">
                <template x-if="modal === 'switch'"><div><h3 class="text-xl font-semibold text-stone-900 dark:text-stone-100">Switch plans?</h3><p class="mt-2 text-sm text-stone-500">Your plan will change to <span class="font-semibold" x-text="planName"></span> now. Your current usage and billing-period end date stay the same.</p><form method="POST" action="{{ route('plans.switch') }}" class="mt-6 flex justify-end gap-3">@csrf<input type="hidden" name="plan_id" :value="planId"><button type="button" @click="modal = null" class="rounded-xl border border-white/10 px-4 py-2 text-sm">Keep current</button><button class="rounded-xl bg-sb-accent px-4 py-2 text-sm font-semibold text-sb-bg-dark">Confirm switch</button></form></div></template>
                <template x-if="modal === 'cancel'"><div><h3 class="text-xl font-semibold text-stone-900 dark:text-stone-100">Cancel subscription?</h3><p class="mt-2 text-sm text-stone-500">Your subscription ends at the close of the current period. Your repositories and indexed data will not be deleted.</p><form method="POST" action="{{ route('plans.cancel') }}" class="mt-6 flex justify-end gap-3">@csrf<button type="button" @click="modal = null" class="rounded-xl border border-white/10 px-4 py-2 text-sm">Keep subscription</button><button class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white">Confirm cancellation</button></form></div></template>
            </div>
        </div>
    </div>
</x-plans-layout>
