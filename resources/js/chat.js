import { marked } from 'marked';
import hljs from 'highlight.js/lib/core';
import javascript from 'highlight.js/lib/languages/javascript';
import typescript from 'highlight.js/lib/languages/typescript';
import php from 'highlight.js/lib/languages/php';
import python from 'highlight.js/lib/languages/python';
import json from 'highlight.js/lib/languages/json';
import bash from 'highlight.js/lib/languages/bash';
import css from 'highlight.js/lib/languages/css';
import xml from 'highlight.js/lib/languages/xml';
import go from 'highlight.js/lib/languages/go';
import rust from 'highlight.js/lib/languages/rust';
import sql from 'highlight.js/lib/languages/sql';
import yaml from 'highlight.js/lib/languages/yaml';

hljs.registerLanguage('javascript', javascript);
hljs.registerLanguage('typescript', typescript);
hljs.registerLanguage('php', php);
hljs.registerLanguage('python', python);
hljs.registerLanguage('json', json);
hljs.registerLanguage('bash', bash);
hljs.registerLanguage('css', css);
hljs.registerLanguage('xml', xml);
hljs.registerLanguage('html', xml);
hljs.registerLanguage('go', go);
hljs.registerLanguage('rust', rust);
hljs.registerLanguage('sql', sql);
hljs.registerLanguage('yaml', yaml);

marked.setOptions({
    highlight(code, lang) {
        if (lang && hljs.getLanguage(lang)) {
            return hljs.highlight(code, { language: lang }).value;
        }
        return hljs.highlightAuto(code).value;
    },
    breaks: true,
    gfm: true,
});

function renderMarkdown(text) {
    return marked.parse(text || '');
}

function initTheme() {
    const stored = localStorage.getItem('sb-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = stored ? stored === 'dark' : prefersDark;

    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.classList.toggle('light', !isDark);

    return isDark;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('secondBrainChat', (config) => ({
        darkMode: initTheme(),
        sidebarOpen: window.innerWidth >= 1024,
        repos: config.repos || [],
        selectedRepos: ['all'],
        messages: config.initialMessages || [],
        input: '',
        loading: false,
        expandedSources: {},

        get scopeLabel() {
            if (this.selectedRepos.includes('all') || this.selectedRepos.length === 0) {
                return 'All repos';
            }
            if (this.selectedRepos.length === 1) {
                const repo = this.repos.find((r) => r.id === this.selectedRepos[0]);
                return repo ? repo.name : '1 repo';
            }
            return `${this.selectedRepos.length} repos`;
        },

        toggleTheme() {
            this.darkMode = !this.darkMode;
            document.documentElement.classList.add('theme-switching');
            document.documentElement.classList.toggle('dark', this.darkMode);
            document.documentElement.classList.toggle('light', !this.darkMode);
            localStorage.setItem('sb-theme', this.darkMode ? 'dark' : 'light');
            setTimeout(() => {
                document.documentElement.classList.remove('theme-switching');
            }, 500);
        },

        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },

        isRepoSelected(id) {
            return this.selectedRepos.includes('all') || this.selectedRepos.includes(id);
        },

        toggleRepo(id) {
            if (id === 'all') {
                this.selectedRepos = ['all'];
                return;
            }

            let selected = this.selectedRepos.filter((r) => r !== 'all');

            if (selected.includes(id)) {
                selected = selected.filter((r) => r !== id);
            } else {
                selected.push(id);
            }

            this.selectedRepos = selected.length === 0 ? ['all'] : selected;
        },

        toggleSource(msgIndex, sourceIndex) {
            const key = `${msgIndex}-${sourceIndex}`;
            this.expandedSources[key] = !this.expandedSources[key];
        },

        isSourceExpanded(msgIndex, sourceIndex) {
            return !!this.expandedSources[`${msgIndex}-${sourceIndex}`];
        },

        renderMarkdown(text) {
            return renderMarkdown(text);
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messagesContainer;
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            });
        },

        async sendMessage() {
            const question = this.input.trim();
            if (!question || this.loading) return;

            this.messages.push({
                role: 'user',
                content: question,
            });

            this.input = '';
            this.loading = true;
            this.scrollToBottom();

            try {
                const response = await fetch(config.askUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                    },
                    body: JSON.stringify({
                        question,
                        repos: this.selectedRepos.includes('all') ? [] : this.selectedRepos,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong.');
                }

                this.messages.push({
                    role: 'assistant',
                    content: data.answer || 'No answer generated.',
                    sources: data.sources || [],
                    error: data.error || null,
                });
            } catch (err) {
                this.messages.push({
                    role: 'assistant',
                    content: err.message || 'Failed to get a response. Please try again.',
                    sources: [],
                    error: true,
                });
            } finally {
                this.loading = false;
                this.scrollToBottom();
            }
        },

        handleKeydown(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        },

        init() {
            this.darkMode = document.documentElement.classList.contains('dark');
            this.scrollToBottom();
            this.$watch('darkMode', () => {
                document.documentElement.classList.toggle('dark', this.darkMode);
                document.documentElement.classList.toggle('light', !this.darkMode);
                localStorage.setItem('sb-theme', this.darkMode ? 'dark' : 'light');
            });
        },
    }));
});
