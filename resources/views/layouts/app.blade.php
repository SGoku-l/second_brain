<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet">

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            (function () {
                const stored = localStorage.getItem('sb-theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = stored ? stored === 'dark' : prefersDark;
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.classList.toggle('light', !isDark);
            })();
        </script>
    </head>
    <body class="font-sans antialiased overflow-x-hidden transition-colors duration-theme">
        <div class="min-h-screen bg-sb-bg-light dark:bg-sb-bg-dark">
            <main>
                {{ $slot }}
            </main>
        </div>
        <div class="fixed right-4 top-4 z-[400] flex w-[min(24rem,calc(100vw-2rem))] flex-col gap-3">
            @if(session('github_connected'))<x-sb-toast message="GitHub connected successfully" type="success" />@endif
            @if(session('repo_ingest_message'))<x-sb-toast :message="session('repo_ingest_message')" type="success" />@endif
            @if(session('limit_error'))<x-sb-toast :message="session('limit_error')" type="warning" />@endif
        </div>
    </body>
</html>
