@php
    $isDashboard = request()->routeIs('dashboard');
    $isChat = request()->routeIs('chat.*');
@endphp

<div class="glass-panel relative z-[120] overflow-visible rounded-[28px] px-4 py-3">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-sb-accent/25 bg-sb-accent/10 text-sb-accent">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('dashboard') }}"
                   class="rounded-full px-3 py-1 text-sm transition-colors @if($isDashboard) font-semibold text-sb-accent @else text-stone-500 hover:text-stone-800 dark:text-stone-400 dark:hover:text-stone-200 @endif">
                    Dashboard
                </a>
                <a href="{{ route('chat.index') }}"
                   class="rounded-full px-3 py-1 text-sm transition-colors @if($isChat) font-semibold text-sb-accent @else text-stone-500 hover:text-stone-800 dark:text-stone-400 dark:hover:text-stone-200 @endif">
                    AI Chat
                </a>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if(Auth::user()->active_status)
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-sb-accent" title="Online">
                    <span class="h-2 w-2 rounded-full bg-sb-accent"></span>
                    Online
                </span>
            @endif

            <button @click="toggleTheme()" class="theme-toggle-track" role="switch" :aria-checked="darkMode" aria-label="Toggle dark mode">
                <span class="theme-toggle-thumb">
                    <svg class="theme-icon theme-icon-sun" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                    </svg>
                    <svg class="theme-icon theme-icon-moon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                    </svg>
                </span>
            </button>

            <div class="relative z-[130]" x-data="{ open: false }">
                <button
                    @click="open = !open"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold transition-all duration-200"
                    :class="darkMode ? 'bg-sb-accent/20 text-sb-accent border border-sb-accent/30 hover:shadow-glow-sm' : 'bg-emerald-100 text-sb-accent-light border border-emerald-200'"
                >
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </button>
                <div
                    x-show="open"
                    x-cloak
                    @click.outside="open = false"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="dropdown-panel absolute right-0 z-[140] mt-2 w-48 py-1"
                >
                    <div class="px-4 py-2.5 border-b" :class="darkMode ? 'border-white/10' : 'border-stone-200'">
                        <p class="text-sm font-medium truncate" :class="darkMode ? 'text-stone-200' : 'text-stone-800'">{{ Auth::user()->name }}</p>
                        <p class="text-xs truncate" :class="darkMode ? 'text-stone-500' : 'text-stone-400'">{{ Auth::user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm transition-colors"
                       :class="darkMode ? 'text-stone-400 hover:text-stone-200 hover:bg-white/5' : 'text-stone-600 hover:text-stone-800 hover:bg-stone-50'">
                        Profile
                    </a>
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm transition-colors"
                       :class="darkMode ? 'text-stone-400 hover:text-stone-200 hover:bg-white/5' : 'text-stone-600 hover:text-stone-800 hover:bg-stone-50'">
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm transition-colors"
                                :class="darkMode ? 'text-stone-400 hover:text-stone-200 hover:bg-white/5' : 'text-stone-600 hover:text-stone-800 hover:bg-stone-50'">
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
