<x-guest-layout>
    {{-- Logo / wordmark --}}
    <div class="flex items-center gap-3 mb-8" style="justify-content: center">
        <img src="{{ asset('images/logo.png') }}" alt="Second Brain" class="h-11 w-11">
        <span class="text-2xl font-bold tracking-tight">
            <span class="text-stone-100">Second</span>
            <span class="text-sb-accent">Brain</span>
        </span>
    </div>

    {{-- Session status --}}
    <x-auth-session-status class="mb-4 w-full max-w-sm text-center text-sm text-sb-accent" :status="session('status')" />

    {{-- Glassmorphic card --}}
    <div class="glass-panel w-full max-w-sm rounded-[26px] p-8">
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-stone-300 mb-1.5">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full rounded-xl bg-black/30 border border-white/10 px-3.5 py-2.5 text-sm text-stone-100 placeholder:text-stone-500 focus:outline-none focus:border-sb-accent/50 focus:ring-1 focus:ring-sb-accent/30 transition"
                >
                @error('email')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-stone-300 mb-1.5">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-xl bg-black/30 border border-white/10 px-3.5 py-2.5 text-sm text-stone-100 placeholder:text-stone-500 focus:outline-none focus:border-sb-accent/50 focus:ring-1 focus:ring-sb-accent/30 transition"
                >
                @error('password')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember me --}}
            <label for="remember_me" class="flex items-center gap-2 cursor-pointer select-none">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="h-4 w-4 rounded border-white/20 bg-transparent accent-emerald-500"
                >
                <span class="text-sm text-stone-400">Remember me</span>
            </label>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-2">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-stone-400 hover:text-sb-accent underline underline-offset-2 transition">
                        Forgot your password?
                    </a>
                @endif

                <button
                    type="submit"
                    class="rounded-xl bg-sb-accent px-5 py-2.5 text-sm font-semibold text-sb-bg-dark hover:opacity-90 hover:shadow-glow-sm transition"
                >
                    Log in
                </button>
            </div>
        </form>
    </div>

    {{-- Register link --}}
    <p class="mt-6 text-sm text-stone-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-sb-accent hover:opacity-80 underline underline-offset-2">Register</a>
    </p>
</x-guest-layout>