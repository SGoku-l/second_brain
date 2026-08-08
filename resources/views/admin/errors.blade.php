<x-admin-layout title="Error logs">
    <section class="glass-panel rounded-[26px] p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Diagnostics</p><h2 class="mt-2 text-2xl font-semibold text-stone-900 dark:text-stone-100">Error logs</h2></div>
            <div class="flex rounded-xl border border-white/10 p-1 text-xs font-semibold">
                <a href="{{ route('admin.errors', ['filter' => 'unresolved']) }}" @class(['rounded-lg px-3 py-1.5', 'bg-sb-accent/15 text-sb-accent' => $filter !== 'all', 'text-stone-500 hover:text-stone-900 dark:text-stone-400' => $filter === 'all'])>Unresolved</a>
                <a href="{{ route('admin.errors', ['filter' => 'all']) }}" @class(['rounded-lg px-3 py-1.5', 'bg-sb-accent/15 text-sb-accent' => $filter === 'all', 'text-stone-500 hover:text-stone-900 dark:text-stone-400' => $filter !== 'all'])>All</a>
            </div>
        </div>

        @if($errors->isEmpty())
            <p class="mt-6 rounded-2xl border border-dashed border-white/10 p-8 text-center text-sm text-stone-500">No {{ $filter === 'all' ? '' : 'unresolved ' }}application errors have been recorded.</p>
        @else
            <div class="mt-6 space-y-3">
                @foreach($errors as $error)
                    <a href="{{ route('admin.errors.show', $error) }}" @class(['block rounded-2xl border p-4 transition hover:border-sb-accent/40 hover:bg-sb-accent/5', 'border-white/10 bg-black/5' => ! $error->resolved_at, 'border-white/5 bg-black/[0.02] opacity-60' => $error->resolved_at])>
                        <div class="flex items-start justify-between gap-4">
                            <div><p @class(['font-medium text-stone-900 dark:text-stone-100', 'line-through' => $error->resolved_at])>{{ $error->message }}</p><p class="mt-1 text-xs text-stone-500">{{ $error->user?->email ?? 'System' }} · {{ $error->source?->identifier ?? 'No repository' }} · {{ $error->created_at?->format('M j, Y H:i:s') }}</p></div>
                            <div class="flex shrink-0 items-center gap-2"><span @class(['rounded-full px-2.5 py-1 font-mono text-xs', 'bg-red-500/10 text-red-400' => in_array($error->level, ['error', 'critical', 'alert', 'emergency']), 'bg-amber-500/10 text-amber-400' => ! in_array($error->level, ['error', 'critical', 'alert', 'emergency'])])>{{ strtoupper($error->level) }}</span>@if($error->resolved_at)<span class="rounded-full bg-sb-accent/10 px-2.5 py-1 text-xs text-sb-accent">Resolved</span>@endif</div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
        <div class="mt-6">{{ $errors->links() }}</div>
    </section>
</x-admin-layout>
