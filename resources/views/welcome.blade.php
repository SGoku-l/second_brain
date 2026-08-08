<!DOCTYPE html>
<html lang="en" x-data="themeController()" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Second Brain — AI Second Brain for Developers</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (() => {
            const stored = localStorage.getItem('sb-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = stored ? stored === 'dark' : prefersDark;
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.classList.toggle('light', !isDark);
        })();
    </script>
</head>
<body class="min-h-screen overflow-x-hidden transition-colors duration-theme">

    {{-- Animated background --}}
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-0 bg-code-grid"></div>
        <div class="floating-node" style="top:10%; left:8%; animation-delay:0s;">const</div>
        <div class="floating-node" style="top:22%; left:78%; animation-delay:2s;">function()</div>
        <div class="floating-node" style="top:65%; left:15%; animation-delay:4s;">git commit</div>
        <div class="floating-node" style="top:78%; left:70%; animation-delay:1s;">async/await</div>
        <div class="floating-node" style="top:45%; left:45%; animation-delay:3s;">MCP</div>
        <div class="floating-node" style="top:35%; left:60%; animation-delay:5s;">embed()</div>
        <div class="glow-orb glow-orb-1"></div>
        <div class="glow-orb glow-orb-2"></div>
    </div>

    {{-- Top bar --}}
    <header class="relative z-10 mx-auto max-w-7xl px-6 py-6 flex items-center justify-between">
        <x-sb-logo size="sm" />

        <div class="flex items-center gap-3">
            <button @click="toggleTheme()" class="theme-toggle-track" aria-label="Toggle theme">
                <span class="theme-toggle-thumb">
                    <svg class="theme-icon theme-icon-sun" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"/></svg>
                    <svg class="theme-icon theme-icon-moon" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                </span>
            </button>

            @auth
                <a href="{{ route('dashboard') }}" class="glass-panel rounded-xl px-4 py-2 text-sm font-medium text-stone-800 dark:text-stone-100 hover:opacity-80">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-stone-600 dark:text-stone-300 hover:text-sb-accent">Log in</a>
                <a href="{{ route('register') }}" class="rounded-xl bg-sb-accent px-4 py-2 text-sm font-semibold text-sb-bg-dark hover:opacity-90">Register</a>
            @endauth
        </div>
    </header>

    {{-- Hero --}}
    <section x-data="{ visible: false }" x-init="requestAnimationFrame(() => visible = true)" x-show="visible" x-cloak x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-5" x-transition:enter-end="opacity-100 translate-y-0" class="relative z-10 mx-auto max-w-5xl px-6 pt-16 pb-24 text-center">
        <div class="inline-flex items-center gap-2 rounded-full border border-sb-accent/30 bg-sb-accent/10 px-4 py-1.5 text-xs font-medium text-sb-accent mb-6">
            <span class="h-1.5 w-1.5 rounded-full bg-sb-accent animate-pulse"></span>
            Works with Claude Code & Cursor via MCP
        </div>

        <h1 class="text-4xl sm:text-6xl font-bold tracking-tight text-stone-900 dark:text-stone-100 mb-6">
            The second brain that<br>
            <span class="text-sb-accent">knows your codebase</span>
        </h1>

        <p class="text-lg text-stone-600 dark:text-stone-400 max-w-2xl mx-auto mb-10">
            Chat with your own repos, get answers grounded in your real code
            and commit history — and plug the same context directly into
            the AI coding agents you already use.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="rounded-xl bg-sb-accent px-6 py-3 text-sm font-semibold text-sb-bg-dark hover:opacity-90 hover:shadow-glow-sm transition">
                Get started free
            </a>
            <a href="#plans" class="glass-panel rounded-xl px-6 py-3 text-sm font-medium text-stone-800 dark:text-stone-100 hover:opacity-80">
                View plans
            </a>
        </div>

        {{-- Mock terminal / product preview --}}
        <div class="glass-panel mt-16 rounded-2xl p-6 text-left max-w-2xl mx-auto">
            <div class="flex items-center gap-1.5 mb-4">
                <span class="h-2.5 w-2.5 rounded-full bg-red-400/70"></span>
                <span class="h-2.5 w-2.5 rounded-full bg-amber-400/70"></span>
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-400/70"></span>
            </div>
            <p class="font-mono text-sm text-stone-500 dark:text-stone-500 mb-2">$ ask second-brain</p>
            <p class="font-mono text-sm text-stone-700 dark:text-stone-300 mb-4">"how does auth work in this repo?"</p>
            <div class="border-t border-white/10 pt-4 space-y-1.5">
                <p class="font-mono text-xs text-sb-accent">→ app/Http/Requests/Auth/LoginRequest.php</p>
                <p class="font-mono text-xs text-sb-accent">→ routes/auth.php</p>
                <p class="font-mono text-xs text-stone-500">Session-based auth via Laravel's web guard...</p>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="relative z-10 mx-auto max-w-6xl px-6 pb-24 grid gap-6 sm:grid-cols-3">
        <div x-data="{ visible: false }" x-init="setTimeout(() => visible = true, 120)" x-show="visible" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="glass-panel rounded-2xl p-6">
            <div class="h-10 w-10 rounded-lg bg-sb-accent/10 text-sb-accent flex items-center justify-center mb-4">📦</div>
            <h3 class="font-semibold text-stone-900 dark:text-stone-100 mb-2">Multi-repo memory</h3>
            <p class="text-sm text-stone-600 dark:text-stone-400">Connect and index as many repos as your plan allows — code and commit history included.</p>
        </div>
        <div x-data="{ visible: false }" x-init="setTimeout(() => visible = true, 220)" x-show="visible" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="glass-panel rounded-2xl p-6">
            <div class="h-10 w-10 rounded-lg bg-sb-accent/10 text-sb-accent flex items-center justify-center mb-4">🔌</div>
            <h3 class="font-semibold text-stone-900 dark:text-stone-100 mb-2">Native MCP integration</h3>
            <p class="text-sm text-stone-600 dark:text-stone-400">Claude Code and Cursor can query your second brain directly — no copy-pasting context.</p>
        </div>
        <div x-data="{ visible: false }" x-init="setTimeout(() => visible = true, 320)" x-show="visible" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="glass-panel rounded-2xl p-6">
            <div class="h-10 w-10 rounded-lg bg-sb-accent/10 text-sb-accent flex items-center justify-center mb-4">🧠</div>
            <h3 class="font-semibold text-stone-900 dark:text-stone-100 mb-2">Answers with sources</h3>
            <p class="text-sm text-stone-600 dark:text-stone-400">Every answer cites the exact files and commits it came from — never a guess.</p>
        </div>
    </section>

    {{-- Plans --}}
    <section id="plans" class="relative z-10 mx-auto max-w-6xl px-6 pb-24">
        <div class="text-center mb-10">
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-sb-accent mb-2">Pricing</p>
            <h2 class="text-3xl font-bold text-stone-900 dark:text-stone-100">Plans that fit your workflow</h2>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 max-w-4xl mx-auto">
            @forelse($plans ?? [] as $plan)
                <div class="glass-panel rounded-2xl p-6 flex flex-col">
                    <h3 class="font-semibold text-stone-900 dark:text-stone-100">{{ $plan->name }}</h3>
                    <p class="mt-2 text-3xl font-bold text-sb-accent">₹{{ number_format($plan->price, 2) }}</p>
                    <p class="text-xs text-stone-500 dark:text-stone-400 mb-4">{{ $plan->duration_days ? $plan->duration_days / 7 . ' week(s)' : $plan->duration_months . ' month(s)' }}</p>
                    <ul class="text-sm text-stone-600 dark:text-stone-400 space-y-1.5 mb-6 flex-1">
                        <li>{{ number_format($plan->monthly_token_limit) }} tokens / month</li>
                        <li>{{ number_format($plan->monthly_repo_limit) }} repositories / month</li>
                        <li>{{ $plan->storage_limit_mb }} MB storage</li>
                    </ul>
                    <a href="{{ route('register') }}" class="rounded-xl bg-sb-accent px-4 py-2 text-sm font-semibold text-sb-bg-dark text-center hover:opacity-90">
                        Get started
                    </a>
                </div>
            @empty
                <p class="text-center text-stone-500 dark:text-stone-400 col-span-full">Plans coming soon.</p>
            @endforelse
        </div>
    </section>

    <footer class="relative z-10 mx-auto max-w-6xl px-6 py-10 text-center text-xs text-stone-500 dark:text-stone-500">
        © {{ date('Y') }} Second Brain. Built for developers.
    </footer>
</body>
</html>
