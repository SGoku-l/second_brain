import './bootstrap';
import './chat';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('themeController', () => ({
    darkMode: false,

    init() {
        const stored = localStorage.getItem('sb-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        this.darkMode = stored ? stored === 'dark' : prefersDark;
        document.documentElement.classList.toggle('dark', this.darkMode);
        document.documentElement.classList.toggle('light', !this.darkMode);
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
}));

Alpine.data('repoProgressMonitor', (isIndexing) => ({
    init() {
        if (!isIndexing) {
            return;
        }

        setInterval(() => {
            window.location.reload();
        }, 10000);
    },
}));

Alpine.start();
