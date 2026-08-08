<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} · {{ config('app.name', 'Second Brain') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (() => {
            const stored = localStorage.getItem('sb-theme');
            document.documentElement.classList.toggle('dark', stored ? stored === 'dark' : true);
        })();
    </script>
</head>
<body class="font-sans antialiased">
    <div x-data="themeController()" class="min-h-screen bg-sb-bg-light text-stone-800 transition-colors duration-theme dark:bg-sb-bg-dark dark:text-stone-200">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <header class="glass-panel flex flex-col gap-4 rounded-[26px] px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <x-sb-logo size="md" />
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Administration</p>
                        <h1 class="text-sm font-semibold text-stone-600 dark:text-stone-400">Control room</h1>
                    </div>
                </div>
                <nav class="flex flex-wrap items-center gap-1 text-sm">
                    <a href="{{ route('admin.index') }}" @class(['rounded-full px-3 py-1.5 transition', 'bg-sb-accent/10 font-semibold text-sb-accent' => request()->routeIs('admin.index'), 'text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100' => ! request()->routeIs('admin.index')])>Overview</a>
                    <a href="{{ route('admin.users') }}" @class(['rounded-full px-3 py-1.5 transition', 'bg-sb-accent/10 font-semibold text-sb-accent' => request()->routeIs('admin.users*'), 'text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100' => ! request()->routeIs('admin.users*')])>Users</a>
                    <a href="{{ route('admin.errors') }}" @class(['rounded-full px-3 py-1.5 transition', 'bg-sb-accent/10 font-semibold text-sb-accent' => request()->routeIs('admin.errors'), 'text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100' => ! request()->routeIs('admin.errors')])>Errors</a>
                    <a href="{{ route('admin.transactions.index') }}" @class(['rounded-full px-3 py-1.5 transition', 'bg-sb-accent/10 font-semibold text-sb-accent' => request()->routeIs('admin.transactions.*'), 'text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100' => ! request()->routeIs('admin.transactions.*')])>Transactions</a>
                    <a href="{{ route('admin.settings') }}" @class(['rounded-full px-3 py-1.5 transition', 'bg-sb-accent/10 font-semibold text-sb-accent' => request()->routeIs('admin.settings'), 'text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100' => ! request()->routeIs('admin.settings')])>Settings</a>
                    <a href="{{ route('dashboard') }}" class="ml-1 rounded-full border border-white/10 px-3 py-1.5 text-stone-500 transition hover:text-sb-accent dark:text-stone-400">Exit admin</a>
                </nav>
            </header>
            <main class="mt-6">{{ $slot }}</main>
        </div>
    </div>
</body>
</html>
