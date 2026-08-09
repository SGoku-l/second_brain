<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Second Brain') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased min-h-screen overflow-x-hidden bg-sb-bg-dark text-stone-200">

        {{-- Animated background --}}
        <div class="fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute inset-0 bg-code-grid"></div>
            <div class="floating-node" style="top:12%; left:10%; animation-delay:0s;">const</div>
            <div class="floating-node" style="top:20%; left:80%; animation-delay:2s;">function()</div>
            <div class="floating-node" style="top:70%; left:12%; animation-delay:4s;">git commit</div>
            <div class="floating-node" style="top:75%; left:75%; animation-delay:1s;">async/await</div>
            <div class="floating-node" style="top:45%; left:5%; animation-delay:3s;">MCP</div>
            <div class="floating-node" style="top:40%; left:88%; animation-delay:5s;">embed()</div>
            <div class="glow-orb glow-orb-1"></div>
            <div class="glow-orb glow-orb-2"></div>
        </div>

        <div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 py-10">
            {{-- <a href="/" class="mb-8">
                <x-sb-logo size="lg" />
            </a> --}}

            <div class="glass-panel w-full sm:max-w-md rounded-[26px] px-6 py-8">
                {{ $slot }}
            </div>
        </div>

    </body>
</html>