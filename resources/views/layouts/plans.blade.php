<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Plans' }} · {{ config('app.name', 'Second Brain') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>(() => { const stored = localStorage.getItem('sb-theme'); document.documentElement.classList.toggle('dark', stored ? stored === 'dark' : true); })();</script>
</head>
<body class="font-sans antialiased">
    <div x-data="themeController()" class="min-h-screen bg-sb-bg-light text-stone-800 transition-colors duration-theme dark:bg-sb-bg-dark dark:text-stone-200">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <header class="glass-panel flex items-center justify-between gap-4 rounded-[26px] px-5 py-4">
                <div class="flex items-center gap-3">
                    <x-sb-logo size="md" />
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Membership</p>
                        <h1 class="text-sm font-semibold text-stone-600 dark:text-stone-400">Plans</h1>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" class="rounded-full border border-white/10 px-3 py-1.5 text-sm text-stone-500 hover:text-sb-accent">Back to dashboard</a>
            </header>
            <main class="mt-6">{{ $slot }}</main>
        </div>
    </div>
    @if(session('subscription_notice'))<div class="fixed right-4 top-4 z-[400] w-[min(24rem,calc(100vw-2rem))]"><x-sb-toast :message="session('subscription_notice')" type="warning" /></div>@endif
</body>
</html>
