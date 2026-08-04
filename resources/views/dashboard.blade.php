<x-app-layout>
    <div x-data="themeController()" class="min-h-screen transition-colors duration-theme">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @include('components.sb-topbar')

            @if(! $githubConnected)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/45 px-4">
                    <div class="glass-panel w-full max-w-lg rounded-[26px] p-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-sb-accent/10 text-sb-accent">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.009-.866-.013-1.7-2.782.605-3.369-1.341-3.369-1.341-.454-1.156-1.11-1.464-1.11-1.464-.908-.621.069-.608.069-.608 1.004.071 1.532 1.031 1.532 1.031.892 1.53 2.341 1.088 2.91.832.09-.646.35-1.088.636-1.339-2.221-.253-4.555-1.111-4.555-4.944 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.272.098-2.65 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0 1 12 6.84c.851.004 1.707.115 2.506.338 1.907-1.294 2.747-1.025 2.747-1.025.546 1.378.203 2.397.1 2.65.64.699 1.028 1.592 1.028 2.683 0 3.842-2.337 4.687-4.566 4.938.359.31.678.92.678 1.854 0 1.338-.012 2.417-.012 2.748 0 .268.18.577.688.48A10.01 10.01 0 0 0 22 12c0-5.523-4.477-10-10-10Z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-stone-900 dark:text-stone-100">Connect GitHub to index repos</h3>
                                <p class="text-sm text-stone-600 dark:text-stone-400">You’ll need a GitHub connection before Second Brain can pull more repositories into your workspace.</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('github.connect') }}" class="inline-flex items-center gap-2 rounded-xl bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700 dark:bg-sb-accent dark:text-sb-bg-dark dark:hover:opacity-90">
                                Connect GitHub
                            </a>
                            <a href="{{ route('chat.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-stone-200 px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50 dark:border-white/10 dark:text-stone-300 dark:hover:bg-white/5">
                                Continue to chat
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-6 grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
                <section class="glass-panel rounded-[26px] p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Connected repos</p>
                            <h3 class="mt-2 text-lg font-semibold text-stone-900 dark:text-stone-100">Your indexed repositories</h3>
                        </div>
                        <a href="{{ route('github.connect') }}" class="inline-flex items-center gap-2 rounded-xl border border-sb-accent/30 bg-sb-accent/10 px-3 py-2 text-sm font-medium text-sb-accent hover:bg-sb-accent/15">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            <span>Add repo</span>
                        </a>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse($repos as $repo)
                            <div class="rounded-2xl border border-white/10 bg-black/5 px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-stone-900 dark:text-stone-100">{{ $repo['identifier'] }}</p>
                                        <p class="text-xs text-stone-500 dark:text-stone-400">Source id: {{ $repo['id'] }}</p>
                                    </div>
                                    <span class="rounded-full bg-sb-accent/10 px-2.5 py-1 text-[11px] font-medium text-sb-accent">
                                        {{ ucfirst($repo['status']) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-stone-300 bg-black/5 px-4 py-8 text-center text-sm text-stone-500 dark:border-white/10 dark:text-stone-400">
                                No repositories are linked yet. Use the GitHub button above to add one.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="glass-panel rounded-[26px] p-6">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Quick actions</p>
                    <div class="mt-4 space-y-3">
                        <a href="{{ route('chat.index') }}" class="flex items-center justify-between rounded-2xl border border-white/10 bg-black/5 px-4 py-3 text-sm font-medium text-stone-700 hover:bg-black/10 dark:text-stone-200 dark:hover:bg-white/5">
                            <span>Open chat</span>
                            <span>→</span>
                        </a>
                        <a href="{{ route('mcp.connect') }}" class="flex items-center justify-between rounded-2xl border border-white/10 bg-black/5 px-4 py-3 text-sm font-medium text-stone-700 hover:bg-black/10 dark:text-stone-200 dark:hover:bg-white/5">
                            <span>Connect to Cursor / Claude Code</span>
                            <span>→</span>
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>

