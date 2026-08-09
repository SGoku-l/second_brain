<x-guest-layout>
    {{-- Logo / wordmark --}}
    <div class="flex items-center gap-3 mb-8" style="justify-content: center">
        <img src="{{ asset('images/logo.png') }}" alt="Second Brain" class="h-11 w-11">
        <span class="text-2xl font-bold tracking-tight">
            <span class="text-stone-100">Second</span>
            <span class="text-sb-accent">Brain</span>
        </span>
    </div>

    <div class="glass-panel w-full max-w-sm rounded-[26px] p-8">
         <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-stone-300 mb-1.5">Name</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    class="w-full rounded-xl bg-black/30 border border-white/10 px-3.5 py-2.5 text-sm text-stone-100 placeholder:text-stone-500 focus:outline-none focus:border-sb-accent/50 focus:ring-1 focus:ring-sb-accent/30 transition"
                >
                @error('name')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-stone-300 mb-1.5">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
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
                    autocomplete="new-password"
                    class="w-full rounded-xl bg-black/30 border border-white/10 px-3.5 py-2.5 text-sm text-stone-100 placeholder:text-stone-500 focus:outline-none focus:border-sb-accent/50 focus:ring-1 focus:ring-sb-accent/30 transition"
                >
                @error('password')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-stone-300 mb-1.5">Confirm Password</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-xl bg-black/30 border border-white/10 px-3.5 py-2.5 text-sm text-stone-100 placeholder:text-stone-500 focus:outline-none focus:border-sb-accent/50 focus:ring-1 focus:ring-sb-accent/30 transition"
                >
                @error('password_confirmation')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('login') }}" class="text-sm text-stone-400 hover:text-sb-accent underline underline-offset-2 transition">
                    Already registered?
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-sb-accent px-5 py-2.5 text-sm font-semibold text-sb-bg-dark hover:opacity-90 hover:shadow-glow-sm transition"
                >
                    Register
                </button>
            </div>
        </form>
    </div>
   
</x-guest-layout>