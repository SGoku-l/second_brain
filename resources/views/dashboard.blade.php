<x-app-layout>
    @php($limitMessage = session('limit_error') ?? collect($repos)->firstWhere('status', 'limit_reached')['error'] ?? null)
    @if($limitMessage)
        <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-[300] flex items-center justify-center bg-black/60 px-4" role="alertdialog" aria-modal="true">
            <div class="glass-panel w-full max-w-md rounded-[26px] p-6 text-center">
                <p class="text-lg font-semibold text-stone-900 dark:text-stone-100">Plan limit reached</p>
                <p class="mt-3 text-sm text-stone-600 dark:text-stone-400">{{ $limitMessage }}</p>
                <button type="button" @click="open = false" class="mt-5 rounded-xl bg-sb-accent px-4 py-2 text-sm font-semibold text-sb-bg-dark">Okay</button>
            </div>
        </div>
    @endif
    <div x-data="themeController()" class="min-h-screen transition-colors duration-theme">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @include('components.sb-topbar')

            @if(session('github_connected'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 3500)"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    class="mt-4"
                >
                    <div class="glass-panel rounded-[20px] px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-sb-accent/10 text-sb-accent">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.009-.866-.013-1.7-2.782.605-3.369-1.341-3.369-1.341-.454-1.156-1.11-1.464-1.11-1.464-.908-.621.069-.608.069-.608 1.004.071 1.532 1.031 1.532 1.031.892 1.53 2.341 1.088 2.91.832.09-.646.35-1.088.636-1.339-2.221-.253-4.555-1.111-4.555-4.944 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.272.098-2.65 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0 1 12 6.84c.851.004 1.707.115 2.506.338 1.907-1.294 2.747-1.025 2.747-1.025.546 1.378.203 2.397.1 2.65.64.699 1.028 1.592 1.028 2.683 0 3.842-2.337 4.687-4.566 4.938.359.31.678.92.678 1.854 0 1.338-.012 2.417-.012 2.748 0 .268.18.577.688.48A10.01 10.01 0 0 0 22 12c0-5.523-4.477-10-10-10Z"/>
                                    </svg>
                                </span>
                                <p class="text-sm font-medium text-stone-900 dark:text-stone-100">GitHub connected successfully</p>
                            </div>
                            <button
                                type="button"
                                @click="show = false"
                                class="rounded-full border border-white/10 px-2 py-1 text-xs text-stone-500 transition hover:text-stone-800 dark:text-stone-400 dark:hover:text-stone-200"
                                aria-label="Dismiss GitHub success message"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('repo_ingest_message'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 4000)"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    class="mt-4"
                >
                    <div class="glass-panel rounded-[20px] px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-sb-accent">{{ session('repo_ingest_message') }}</p>
                            <button
                                type="button"
                                @click="show = false"
                                class="rounded-full border border-white/10 px-2 py-1 text-xs text-stone-500 transition hover:text-stone-800 dark:text-stone-400 dark:hover:text-stone-200"
                                aria-label="Dismiss message"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if(! $githubConnected)
                <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/45 px-4">
                    <div class="glass-panel relative w-full max-w-lg rounded-[26px] p-6">
                        <button
                            type="button"
                            @click="open = false"
                            class="absolute right-4 top-4 inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-black/5 text-sm text-stone-500 transition hover:border-sb-accent/30 hover:bg-sb-accent/10 hover:text-sb-accent dark:text-stone-400 dark:hover:text-sb-accent"
                            aria-label="Close GitHub connect prompt"
                        >
                            ×
                        </button>
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

            @if($githubConnected && (! $subscription || $subscription->status !== 'active' || ! $subscription->current_period_end?->isFuture()))
                <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/45 px-4">
                    <div class="glass-panel relative w-full max-w-lg rounded-[26px] p-6">
                        <button type="button" @click="open = false" class="absolute right-4 top-4 rounded-full border border-white/10 px-2 py-1 text-sm text-stone-500" aria-label="Close plan prompt">×</button>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Choose a plan</p>
                        <h3 class="mt-2 text-lg font-semibold text-stone-900 dark:text-stone-100">Activate your Second Brain</h3>
                        <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">Your GitHub account is connected. Select a plan to start indexing repositories and using chat.</p>
                        <a href="{{ route('plans.index') }}" class="mt-6 inline-flex rounded-xl bg-sb-accent px-4 py-2 text-sm font-semibold text-sb-bg-dark">View plans</a>
                    </div>
                </div>
            @endif

            <div class="mt-6 grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
                <section class="glass-panel rounded-[26px] p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Connected repos</p>
                                <h3 class="mt-2 text-lg font-semibold text-stone-900 dark:text-stone-100">Your indexed repositories</h3>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if($githubConnected)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-sb-accent/30 bg-sb-accent/10 px-2.5 py-1 text-[11px] font-medium text-sb-accent">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.009-.866-.013-1.7-2.782.605-3.369-1.341-3.369-1.341-.454-1.156-1.11-1.464-1.11-1.464-.908-.621.069-.608.069-.608 1.004.071 1.532 1.031 1.532 1.031.892 1.53 2.341 1.088 2.91.832.09-.646.35-1.088.636-1.339-2.221-.253-4.555-1.111-4.555-4.944 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.272.098-2.65 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0 1 12 6.84c.851.004 1.707.115 2.506.338 1.907-1.294 2.747-1.025 2.747-1.025.546 1.378.203 2.397.1 2.65.64.699 1.028 1.592 1.028 2.683 0 3.842-2.337 4.687-4.566 4.938.359.31.678.92.678 1.854 0 1.338-.012 2.417-.012 2.748 0 .268.18.577.688.48A10.01 10.01 0 0 0 22 12c0-5.523-4.477-10-10-10Z"/>
                                        </svg>
                                        <span class="inline-flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-full bg-sb-accent"></span>
                                            GitHub connected
                                        </span>
                                    </span>
                                @else
                                    <a href="{{ route('github.connect') }}" class="inline-flex items-center gap-2 rounded-full border border-stone-300/70 bg-black/5 px-2.5 py-1 text-[11px] font-medium text-stone-500 transition hover:border-sb-accent/30 hover:bg-sb-accent/10 hover:text-sb-accent dark:border-white/10 dark:text-stone-400 dark:hover:text-sb-accent">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.009-.866-.013-1.7-2.782.605-3.369-1.341-3.369-1.341-.454-1.156-1.11-1.464-1.11-1.464-.908-.621.069-.608.069-.608 1.004.071 1.532 1.031 1.532 1.031.892 1.53 2.341 1.088 2.91.832.09-.646.35-1.088.636-1.339-2.221-.253-4.555-1.111-4.555-4.944 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.272.098-2.65 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0 1 12 6.84c.851.004 1.707.115 2.506.338 1.907-1.294 2.747-1.025 2.747-1.025.546 1.378.203 2.397.1 2.65.64.699 1.028 1.592 1.028 2.683 0 3.842-2.337 4.687-4.566 4.938.359.31.678.92.678 1.854 0 1.338-.012 2.417-.012 2.748 0 .268.18.577.688.48A10.01 10.01 0 0 0 22 12c0-5.523-4.477-10-10-10Z"/>
                                        </svg>
                                        <span>GitHub not connected — click to link</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Add repo modal trigger + modal --}}
                        <div>

                            @if($githubConnected)
                                <button
                                    type="button"
                                    @click="$dispatch('open-repo-modal')"
                                    class="inline-flex items-center gap-2 rounded-xl border border-sb-accent/30 bg-sb-accent/10 px-3 py-2 text-sm font-medium text-sb-accent hover:bg-sb-accent/15"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M12 5v14M5 12h14" />
                                    </svg>
                                    <span>Add repo</span>
                                </button>
                            @else
                                <a
                                    href="{{ route('github.connect') }}"
                                    class="inline-flex items-center gap-2 rounded-xl border border-sb-accent/30 bg-sb-accent/10 px-3 py-2 text-sm font-medium text-sb-accent hover:bg-sb-accent/15"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M12 5v14M5 12h14" />
                                    </svg>
                                    <span>Add repo</span>
                                </a>
                            @endif

                            <template x-teleport="body">
                                <div
                                     x-data="{
                                         modalOpen: false,
                                         loading: false,
                                         search: '',
                                         availableRepos: [],
                                         selectedRepos: [],
                                         async loadRepos() {
                                             this.loading = true;
                                             try {
                                                 const res = await fetch('{{ route('repos.available') }}');
                                                 const data = await res.json();
                                                 this.availableRepos = data.repos ?? [];
                                             } catch (e) {
                                                 this.availableRepos = [];
                                             }
                                             this.loading = false;
                                         },
                                         toggleRepo(fullName) {
                                             if (this.selectedRepos.includes(fullName)) {
                                                 this.selectedRepos = this.selectedRepos.filter(r => r !== fullName);
                                             } else {
                                                 this.selectedRepos.push(fullName);
                                             }
                                         },
                                         get filteredRepos() {
                                             if (!this.search) return this.availableRepos;
                                             return this.availableRepos.filter(r =>
                                                 r.full_name.toLowerCase().includes(this.search.toLowerCase())
                                             );
                                         }
                                     }"
                                     @open-repo-modal.window="modalOpen = true; loadRepos()"
                                     x-show="modalOpen" x-cloak
                                     class="fixed inset-0 z-[200] flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
                                <div @click.outside="modalOpen = false"
                                     class="glass-panel w-full max-w-lg rounded-[26px] p-6 relative">

                                    <button type="button" @click="modalOpen = false"
                                            class="absolute right-4 top-4 inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-black/5 text-sm text-stone-500 transition hover:border-sb-accent/30 hover:bg-sb-accent/10 hover:text-sb-accent dark:text-stone-400 dark:hover:text-sb-accent"
                                            aria-label="Close">
                                        ×
                                    </button>

                                    <h3 class="text-lg font-semibold text-stone-900 dark:text-stone-100 mb-4">Select repositories to index</h3>

                                    <input type="text" x-model="search" placeholder="Search repos..."
                                           class="w-full mb-4 px-3 py-2 rounded-xl bg-black/5 dark:bg-black/30 border border-white/10 text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-sb-accent/50">

                                    <div class="max-h-72 overflow-y-auto scrollbar-hide space-y-2 mb-4">
                                        <template x-if="loading">
                                            <p class="text-sm text-stone-500 dark:text-stone-400">Loading repositories...</p>
                                        </template>

                                        <template x-if="!loading && filteredRepos.length === 0">
                                            <p class="text-sm text-stone-500 dark:text-stone-400">No repositories found.</p>
                                        </template>

                                        <template x-for="repo in filteredRepos" :key="repo.full_name">
                                            <label class="flex items-center gap-3 px-3 py-2 rounded-xl"
                                                   :class="repo.already_added ? 'opacity-40' : 'hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer'">
                                                <input type="checkbox"
                                                       :disabled="repo.already_added"
                                                       :checked="selectedRepos.includes(repo.full_name)"
                                                       @change="toggleRepo(repo.full_name)"
                                                       class="h-4 w-4 shrink-0 rounded border-stone-400 bg-white accent-emerald-500 dark:border-white/20 dark:bg-transparent">
                                                <span class="flex-1 text-sm text-stone-900 dark:text-stone-100 truncate" x-text="repo.full_name"></span>
                                                <span x-show="repo.already_added" class="text-xs text-sb-accent shrink-0">Indexed</span>
                                                <span x-show="!repo.already_added && repo.private" class="shrink-0 text-xs text-stone-600 dark:text-stone-400">Private</span>
                                            </label>
                                        </template>
                                    </div>

                                    <form method="POST" action="{{ route('repos.ingest') }}">
                                        @csrf
                                        <template x-for="r in selectedRepos" :key="r">
                                            <input type="hidden" name="repo_full_names[]" :value="r">
                                        </template>

                                        <button type="submit"
                                                :disabled="selectedRepos.length === 0"
                                                class="w-full py-2 rounded-xl bg-sb-accent text-sb-bg-dark font-medium disabled:opacity-30 disabled:cursor-not-allowed hover:opacity-90 transition">
                                            Index selected repos
                                        </button>
                                    </form>
                                </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div
                        x-data="repoProgressMonitor({{ collect($repos)->contains('status', 'indexing') ? 'true' : 'false' }})"
                        class="mt-5 space-y-3"
                    >
                        @forelse($repos as $repo)
                            <div x-data="{ confirmDelete: false }" class="rounded-2xl border border-white/10 bg-black/5 px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-stone-900 dark:text-stone-100">{{ $repo['identifier'] }}</p>
                                        <p class="text-xs text-stone-500 dark:text-stone-400">Source id: {{ $repo['id'] }}</p>
                                        @if(in_array($repo['status'], ['error', 'limit_reached'], true) && ! empty($repo['error']))
                                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $repo['error'] }}</p>
                                        @elseif($repo['status'] === 'indexed' && $repo['commitsFound'] === 0)
                                            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">No commit details were returned during the last sync.</p>
                                        @elseif($repo['status'] === 'indexing' && $repo['chunksIndexed'] !== null)
                                            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">{{ $repo['chunksIndexed'] }} chunks indexed so far.</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($repo['status'] === 'indexed')
                                            <form method="POST" action="{{ route('repos.resync', $repo['id']) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-sb-accent/30 bg-sb-accent/10 text-sb-accent transition hover:bg-sb-accent/20" aria-label="Re-sync {{ $repo['identifier'] }}" title="Re-sync">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 11a8.1 8.1 0 0 0-15.5-2M4 5v4h4M4 13a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" @click="confirmDelete = true" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-500/30 bg-rose-500/10 text-rose-600 transition hover:bg-rose-500/20 dark:text-rose-400" aria-label="Delete {{ $repo['identifier'] }}" title="Delete">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18M9 6V4h6v2M8 6l1 14h6l1-14M10 10v6M14 10v6"/></svg>
                                        </button>
                                        <span @class([
                                            'rounded-full px-2.5 py-1 text-[11px] font-medium',
                                            'bg-red-500/10 text-red-600 dark:text-red-400' => in_array($repo['status'], ['error', 'limit_reached'], true),
                                            'bg-sb-accent/10 text-sb-accent' => ! in_array($repo['status'], ['error', 'limit_reached'], true),
                                        ])>
                                            {{ ucfirst(str_replace('_', ' ', $repo['status'])) }}
                                        </span>
                                    </div>
                                </div>

                                @if($repo['status'] === 'indexing')
                                    <div
                                        class="mt-3 h-1.5 overflow-hidden rounded-full bg-stone-200/70 dark:bg-white/10"
                                        role="progressbar"
                                        aria-label="Repository indexing is in progress"
                                        aria-valuetext="Indexing in progress"
                                    >
                                        <div class="h-full w-2/5 rounded-full bg-sb-accent animate-pulse"></div>
                                    </div>
                                @endif

                                <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-[250] flex items-center justify-center bg-black/60 px-4" role="dialog" aria-modal="true">
                                    <div @click.outside="confirmDelete = false" class="glass-panel w-full max-w-md rounded-[26px] p-6">
                                        <h3 class="text-lg font-semibold text-stone-900 dark:text-stone-100">Remove repository?</h3>
                                        <p class="mt-3 text-sm text-stone-600 dark:text-stone-400">Are you sure you want to remove <span class="font-semibold">{{ $repo['identifier'] }}</span>? This will delete all indexed data for this repository.</p>
                                        <div class="mt-6 flex justify-end gap-3">
                                            <button type="button" @click="confirmDelete = false" class="rounded-xl border border-white/10 px-4 py-2 text-sm text-stone-600 dark:text-stone-300">Cancel</button>
                                            <form method="POST" action="{{ route('repos.destroy', $repo['id']) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Delete repository</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-stone-300 bg-black/5 px-4 py-8 text-center text-sm text-stone-500 dark:border-white/10 dark:text-stone-400">
                                No repositories are linked yet. Use the Add repo button above to add one.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="glass-panel rounded-[26px] p-6">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Subscription</p>
                    <div class="mt-4 rounded-2xl border border-white/10 bg-black/5 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-stone-900 dark:text-stone-100">{{ $subscription?->plan?->name ?? 'No active plan' }}</p>
                                <p class="mt-1 font-mono text-xs text-stone-500 dark:text-stone-400">{{ number_format($subscription?->tokens_used_current_period ?? 0) }} / {{ number_format($subscription?->plan?->monthly_token_limit ?? 0) }} tokens</p>
                                <p class="mt-1 font-mono text-xs text-stone-500 dark:text-stone-400">{{ number_format($subscription?->storage_used_mb ?? 0) }} / {{ number_format($subscription?->plan?->storage_limit_mb ?? 0) }} MB storage</p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <a href="{{ route('plans.index') }}" class="rounded-xl border border-sb-accent/30 px-3 py-1.5 text-xs font-semibold text-sb-accent hover:bg-sb-accent/10">Upgrade</a>
                                <a href="{{ route('plans.manage') }}" class="text-xs font-semibold text-stone-500 hover:text-sb-accent dark:text-stone-400">Manage plan</a>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-stone-500 dark:text-stone-400">Expairy at: {{ $subscription?->current_period_end?->format('M j, Y') ?? 'Choose a plan' }}</p>
                    </div>

                    <p class="mt-6 text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Quick actions</p>
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
            <div class="mt-6">
                <x-usage-chart title="Your token usage" eyebrow="Usage" :endpoint="route('dashboard.chart.tokens')" default-range="7d" value-label="tokens" />
            </div>
        </div>
    </div>
</x-app-layout>
