<x-chat-layout>
    <div
        x-data="secondBrainChat({
            repos: {{ Js::from($repos) }},
            askUrl: '{{ route('chat.ask') }}',
            csrfToken: '{{ csrf_token() }}',
            initialMessages: {{ Js::from($initialMessages ?? []) }},
        })"
        class="h-screen flex flex-col transition-colors duration-theme"
        :class="darkMode ? 'bg-sb-bg-dark' : 'bg-sb-bg-light'"
    >
        {{-- Top bar --}}
        <header class="glass-panel border-b shrink-0 z-20">
            <div class="flex items-center justify-between h-14 px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button
                        @click="toggleSidebar()"
                        class="lg:hidden p-2 -ml-2 rounded-lg transition-colors"
                        :class="darkMode ? 'text-stone-400 hover:text-stone-200 hover:bg-white/5' : 'text-stone-500 hover:text-stone-700 hover:bg-stone-100'"
                        aria-label="Toggle sidebar"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-shadow duration-200"
                             :class="darkMode ? 'bg-sb-accent/15 border border-sb-accent/25 group-hover:shadow-glow-sm' : 'bg-emerald-100 border border-emerald-200'">
                            <svg class="w-4 h-4 text-sb-accent" :class="!darkMode && 'text-sb-accent-light'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-base tracking-tight transition-colors"
                              :class="darkMode ? 'text-stone-100' : 'text-stone-900'">
                            Second Brain
                        </span>
                    </a>
                </div>

                <div class="flex items-center gap-3 sm:gap-4">
                    {{-- Dark / Light mode toggle --}}
                    <button
                        @click="toggleTheme()"
                        class="theme-toggle-track"
                        role="switch"
                        :aria-checked="darkMode"
                        aria-label="Toggle dark mode"
                    >
                        <span class="theme-toggle-thumb">
                            {{-- Sun icon (light mode) --}}
                            <svg class="theme-icon theme-icon-sun" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                            </svg>
                            {{-- Moon icon (dark mode) --}}
                            <svg class="theme-icon theme-icon-moon" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                            </svg>
                        </span>
                    </button>

                    {{-- User avatar --}}
                    <div class="relative" x-data="{ open: false }">
                        <button
                            @click="open = !open"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold transition-all duration-200"
                            :class="darkMode ? 'bg-sb-accent/20 text-sb-accent border border-sb-accent/30 hover:shadow-glow-sm' : 'bg-emerald-100 text-sb-accent-light border border-emerald-200'"
                        >
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </button>
                        <div
                            x-show="open"
                            @click.outside="open = false"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 glass-panel rounded-xl py-1 z-50 shadow-lg"
                            style="display: none;"
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
        </header>

        <div class="flex flex-1 overflow-hidden relative">
            {{-- Sidebar overlay (mobile) --}}
            <div
                x-show="sidebarOpen"
                @click="sidebarOpen = false"
                x-transition:enter="transition-opacity ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/50 z-20 lg:hidden"
                style="display: none;"
            ></div>

            {{-- Left sidebar --}}
            <aside
                class="fixed lg:relative z-30 w-72 flex flex-col glass-panel border-r transform transition-transform duration-300 ease-in-out lg:translate-x-0 top-14 lg:top-auto h-[calc(100vh-3.5rem)] lg:h-auto"
                :class="[
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                    darkMode ? '' : 'shadow-glass-light'
                ]"
            >
                <div class="p-4 border-b" :class="darkMode ? 'border-white/10' : 'border-stone-200/80'">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xs font-semibold uppercase tracking-wider"
                            :class="darkMode ? 'text-stone-500' : 'text-stone-400'">
                            Repositories
                        </h2>
                        <button
                            @click="sidebarOpen = false"
                            class="lg:hidden p-1 rounded transition-colors"
                            :class="darkMode ? 'text-stone-500 hover:text-stone-300' : 'text-stone-400 hover:text-stone-600'"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto chat-scroll p-3 space-y-1">
                    {{-- All repos chip --}}
                    <button
                        @click="toggleRepo('all')"
                        class="repo-chip"
                        :class="{ 'selected': selectedRepos.includes('all') }"
                    >
                        <span class="sync-dot indexed"></span>
                        <span class="flex-1 text-left">All repos</span>
                        <svg x-show="selectedRepos.includes('all')" class="w-4 h-4 text-sb-accent shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <template x-if="repos.length === 0">
                        <div class="px-3 py-6 text-center">
                            <p class="text-sm" :class="darkMode ? 'text-stone-500' : 'text-stone-400'">No repos connected yet.</p>
                            <a href="{{ route('dashboard') }}" class="text-xs text-sb-accent hover:underline mt-1 inline-block">Connect a repo →</a>
                        </div>
                    </template>

                    <template x-for="repo in repos" :key="repo.id">
                        <button
                            @click="toggleRepo(repo.id)"
                            class="repo-chip"
                            :class="{ 'selected': isRepoSelected(repo.id) && !selectedRepos.includes('all') }"
                        >
                            <span class="sync-dot" :class="repo.status"></span>
                            <span class="flex-1 text-left truncate font-mono text-xs" x-text="repo.name"></span>
                            <svg x-show="isRepoSelected(repo.id) && !selectedRepos.includes('all')" class="w-4 h-4 text-sb-accent shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </template>
                </div>

                <div class="p-4 border-t" :class="darkMode ? 'border-white/10' : 'border-stone-200/80'">
                    <p class="text-xs leading-relaxed" :class="darkMode ? 'text-stone-600' : 'text-stone-400'">
                        Select repos to scope your search. Status dots show sync state.
                    </p>
                </div>
            </aside>

            {{-- Main chat area --}}
            <main class="flex-1 flex flex-col min-w-0">
                {{-- Messages --}}
                <div
                    x-ref="messagesContainer"
                    class="flex-1 overflow-y-auto chat-scroll px-4 sm:px-8 py-6 space-y-6"
                >
                    {{-- Empty state --}}
                    <template x-if="messages.length === 0 && !loading">
                        <div class="flex flex-col items-center justify-center h-full text-center px-4 animate-fade-in">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-5"
                                 :class="darkMode ? 'bg-sb-accent/10 border border-sb-accent/20' : 'bg-emerald-50 border border-emerald-100'">
                                <svg class="w-7 h-7 text-sb-accent" :class="!darkMode && 'text-sb-accent-light'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold mb-2" :class="darkMode ? 'text-stone-200' : 'text-stone-800'">
                                Ask about your codebase
                            </h2>
                            <p class="text-sm max-w-md leading-relaxed" :class="darkMode ? 'text-stone-500' : 'text-stone-400'">
                                Search across indexed repositories. Get answers with source citations from your actual code.
                            </p>
                            <div class="flex flex-wrap gap-2 mt-6 justify-center">
                                <button @click="input = 'How is authentication handled?'; $refs.chatInput.focus()"
                                        class="text-xs px-3 py-1.5 rounded-lg border transition-colors"
                                        :class="darkMode ? 'border-white/10 text-stone-400 hover:border-sb-accent/30 hover:text-sb-accent' : 'border-stone-200 text-stone-500 hover:border-emerald-300 hover:text-sb-accent-light'">
                                    How is auth handled?
                                </button>
                                <button @click="input = 'Where are API routes defined?'; $refs.chatInput.focus()"
                                        class="text-xs px-3 py-1.5 rounded-lg border transition-colors"
                                        :class="darkMode ? 'border-white/10 text-stone-400 hover:border-sb-accent/30 hover:text-sb-accent' : 'border-stone-200 text-stone-500 hover:border-emerald-300 hover:text-sb-accent-light'">
                                    Where are API routes?
                                </button>
                                <button @click="input = 'Explain the database schema'; $refs.chatInput.focus()"
                                        class="text-xs px-3 py-1.5 rounded-lg border transition-colors"
                                        :class="darkMode ? 'border-white/10 text-stone-400 hover:border-sb-accent/30 hover:text-sb-accent' : 'border-stone-200 text-stone-500 hover:border-emerald-300 hover:text-sb-accent-light'">
                                    Explain the schema
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Message list --}}
                    <template x-for="(msg, msgIndex) in messages" :key="msgIndex">
                        <div>
                            {{-- User message --}}
                            <template x-if="msg.role === 'user'">
                                <div class="flex justify-end">
                                    <div class="msg-user" x-text="msg.content"></div>
                                </div>
                            </template>

                            {{-- AI message --}}
                            <template x-if="msg.role === 'assistant'">
                                <div class="msg-ai">
                                    <div class="msg-ai-panel">
                                        <template x-if="msg.error">
                                            <div class="text-xs px-3 py-2 rounded-lg mb-3 border"
                                                 :class="darkMode ? 'bg-amber-500/10 border-amber-500/20 text-amber-400' : 'bg-amber-50 border-amber-200 text-amber-700'"
                                                 x-text="typeof msg.error === 'string' ? msg.error : 'Answer generation encountered an issue.'">
                                            </div>
                                        </template>

                                        <div class="markdown-body" x-html="renderMarkdown(msg.content)"></div>

                                        {{-- Sources section --}}
                                        <template x-if="msg.sources && msg.sources.length > 0">
                                            <div class="mt-5 pt-4 border-t" :class="darkMode ? 'border-white/10' : 'border-stone-200'">
                                                <p class="text-xs font-semibold uppercase tracking-wider mb-3"
                                                   :class="darkMode ? 'text-stone-500' : 'text-stone-400'">
                                                    Sources
                                                </p>
                                                <div class="space-y-2">
                                                    <template x-for="(source, sourceIndex) in msg.sources" :key="sourceIndex">
                                                        <div>
                                                            <button
                                                                @click="toggleSource(msgIndex, sourceIndex)"
                                                                class="source-chip"
                                                                :class="{ 'expanded': isSourceExpanded(msgIndex, sourceIndex) }"
                                                            >
                                                                <svg class="w-3 h-3 shrink-0 transition-transform duration-200"
                                                                     :class="isSourceExpanded(msgIndex, sourceIndex) ? 'rotate-90' : ''"
                                                                     fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                                                </svg>
                                                                <span x-text="source.file_path"></span>
                                                            </button>
                                                            <div
                                                                x-show="isSourceExpanded(msgIndex, sourceIndex)"
                                                                x-transition:enter="transition ease-out duration-200"
                                                                x-transition:enter-start="opacity-0 -translate-y-1"
                                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                                class="source-snippet"
                                                                x-text="source.content"
                                                                style="display: none;"
                                                            ></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Typing indicator --}}
                    <template x-if="loading">
                        <div class="msg-ai animate-fade-in">
                            <div class="typing-indicator">
                                <span class="typing-dot"></span>
                                <span class="typing-dot"></span>
                                <span class="typing-dot"></span>
                                <span class="text-xs ml-2" :class="darkMode ? 'text-stone-500' : 'text-stone-400'">Thinking…</span>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Input bar --}}
                <div class="shrink-0 px-4 sm:px-8 pb-5 pt-2">
                    <div class="chat-input-bar max-w-4xl mx-auto">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-md border"
                                      :class="darkMode ? 'bg-sb-accent/10 border-sb-accent/20 text-sb-accent' : 'bg-emerald-50 border-emerald-200 text-sb-accent-light'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                    </svg>
                                    <span x-text="scopeLabel"></span>
                                </span>
                            </div>
                            <textarea
                                x-ref="chatInput"
                                x-model="input"
                                @keydown="handleKeydown($event)"
                                rows="1"
                                placeholder="Ask about your codebase..."
                                class="w-full bg-transparent border-0 resize-none text-sm leading-relaxed focus:ring-0 focus:outline-none placeholder:transition-colors"
                                :class="darkMode ? 'text-stone-200 placeholder-stone-600' : 'text-stone-800 placeholder-stone-400'"
                                :disabled="loading"
                            ></textarea>
                        </div>
                        <button
                            @click="sendMessage()"
                            :disabled="loading || !input.trim()"
                            class="send-btn"
                            aria-label="Send message"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-center text-xs mt-2.5" :class="darkMode ? 'text-stone-600' : 'text-stone-400'">
                        Enter to send · Shift+Enter for new line
                    </p>
                </div>
            </main>
        </div>
    </div>
</x-chat-layout>
