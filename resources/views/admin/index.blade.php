<x-admin-layout title="Admin overview">
    <section class="glass-panel rounded-[26px] p-6">
        <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">System health</p>
        <h2 class="mt-2 text-2xl font-semibold text-stone-900 dark:text-stone-100">Overview</h2>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach(['Users' => $metrics['users'], 'Sources' => $metrics['sources'], 'Chunks' => $metrics['chunks'], 'Errors today' => $metrics['errorsToday'], 'Pending jobs' => $metrics['pendingJobs'], 'Failed jobs' => $metrics['failedJobs']] as $label => $value)
                <div class="rounded-2xl border border-white/10 bg-black/5 p-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">{{ $label }}</p>
                    <p class="mt-2 font-mono text-3xl font-semibold text-stone-900 dark:text-stone-100">{{ number_format($value) }}</p>
                </div>
            @endforeach
        </div>
    </section>
    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-usage-chart title="Token usage" eyebrow="Platform usage" :endpoint="route('admin.charts.tokens')" value-label="tokens" />
        <x-usage-chart title="New users" eyebrow="Growth" :endpoint="route('admin.charts.users')" value-label="signups" />
        <x-usage-chart title="Transactions" eyebrow="Billing" :endpoint="route('admin.charts.transactions')" value-label="transactions" />
    </div>
</x-admin-layout>
