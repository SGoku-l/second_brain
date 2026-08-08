<x-admin-layout title="Error details">
    @php
        $context = $error->context ?? [];
        $trace = $context['stack_trace'] ?? null;
        $exceptionMessage = $context['exception_message'] ?? null;
        $requestContext = $context['request'] ?? null;
        $otherContext = collect($context)->except(['stack_trace', 'exception_message', 'request'])->all();
    @endphp
    @if(session('status'))<div class="mb-5 rounded-2xl border border-sb-accent/25 bg-sb-accent/10 px-4 py-3 text-sm text-sb-accent">{{ session('status') }}</div>@endif
    <div class="mb-5"><a href="{{ route('admin.errors') }}" class="text-sm font-semibold text-sb-accent hover:underline">← Back to error logs</a></div>
    <section class="glass-panel rounded-[26px] p-6">
        <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Diagnostics</p><h2 class="mt-2 text-2xl font-semibold text-stone-900 dark:text-stone-100">{{ $error->message }}</h2><p class="mt-2 text-sm text-stone-500">{{ $error->created_at?->format('M j, Y H:i:s T') }}</p></div><div class="flex items-center gap-2"><span @class(['rounded-full px-3 py-1.5 font-mono text-xs', 'bg-red-500/10 text-red-400' => in_array($error->level, ['error', 'critical', 'alert', 'emergency']), 'bg-amber-500/10 text-amber-400' => ! in_array($error->level, ['error', 'critical', 'alert', 'emergency'])])>{{ strtoupper($error->level) }}</span>@if($error->resolved_at)<span class="rounded-full bg-sb-accent/10 px-3 py-1.5 text-xs text-sb-accent">Resolved {{ $error->resolved_at->format('M j, Y H:i') }}</span>@else<form method="POST" action="{{ route('admin.errors.resolve', $error) }}">@csrf @method('PATCH')<button class="rounded-xl border border-sb-accent/30 px-3 py-1.5 text-xs font-semibold text-sb-accent hover:bg-sb-accent/10">Mark as resolved</button></form>@endif</div></div>
        <div class="mt-6 grid gap-4 md:grid-cols-3"><div class="rounded-2xl border border-white/10 bg-black/5 p-4"><p class="text-xs uppercase tracking-wider text-stone-500">Exception</p><p class="mt-2 break-all font-mono text-sm text-stone-800 dark:text-stone-200">{{ $error->exception_class ?? 'No exception class recorded' }}</p></div><div class="rounded-2xl border border-white/10 bg-black/5 p-4"><p class="text-xs uppercase tracking-wider text-stone-500">Affected user</p>@if($error->user)<a href="{{ route('admin.users.show', $error->user) }}" class="mt-2 block text-sm font-semibold text-sb-accent hover:underline">{{ $error->user->name }}<span class="block font-normal text-stone-500">{{ $error->user->email }}</span></a>@else<p class="mt-2 text-sm text-stone-500">System / unknown</p>@endif</div><div class="rounded-2xl border border-white/10 bg-black/5 p-4"><p class="text-xs uppercase tracking-wider text-stone-500">Affected repository</p>@if($error->source)<a href="{{ route('admin.repos', ['source' => $error->source->id]) }}" class="mt-2 block text-sm font-semibold text-sb-accent hover:underline">{{ $error->source->identifier }}</a>@else<p class="mt-2 text-sm text-stone-500">No repository linked</p>@endif</div></div>
    </section>
    @if($exceptionMessage || $trace)
        <section class="glass-panel mt-6 rounded-[26px] p-6">
            <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Exception details</p>
            @if($exceptionMessage)
                <p class="mt-3 break-words text-sm text-stone-700 dark:text-stone-300">{{ $exceptionMessage }}</p>
            @endif
            @if($trace)
                <pre class="mt-4 max-h-[34rem] overflow-auto whitespace-pre-wrap break-words rounded-2xl border border-white/10 bg-stone-900 p-4 font-mono text-xs leading-6 text-stone-100 dark:bg-black/40">{{ $trace }}</pre>
            @endif
        </section>
    @endif
    @if($requestContext)<section class="glass-panel mt-6 rounded-[26px] p-6"><p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Request context</p><pre class="mt-4 max-h-80 overflow-auto whitespace-pre-wrap break-words rounded-2xl border border-white/10 bg-stone-900 p-4 font-mono text-xs leading-6 text-stone-100 dark:bg-black/40">{{ json_encode($requestContext, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></section>@endif
    @if($otherContext)<section class="glass-panel mt-6 rounded-[26px] p-6"><p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Additional context</p><pre class="mt-4 max-h-80 overflow-auto whitespace-pre-wrap break-words rounded-2xl border border-white/10 bg-stone-900 p-4 font-mono text-xs leading-6 text-stone-100 dark:bg-black/40">{{ json_encode($otherContext, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></section>@endif
</x-admin-layout>
