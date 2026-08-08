document.addEventListener('alpine:init', () => {
    Alpine.data('usageChart', (config) => ({
        range: config.defaultRange || 'today',
        start: '',
        end: '',
        loading: false,
        labels: [],
        values: [],

        get maxValue() {
            return Math.max(...this.values, 1);
        },

        get points() {
            if (!this.values.length) return '';

            return this.values.map((value, index) => {
                const x = this.values.length === 1 ? 50 : 10 + (index * 80 / (this.values.length - 1));
                const y = 84 - ((value / this.maxValue) * 68);
                return `${x},${y}`;
            }).join(' ');
        },

        get hasData() {
            return this.values.some((value) => value > 0);
        },

        formatNumber(value) {
            return new Intl.NumberFormat().format(value || 0);
        },

        async load() {
            this.loading = true;
            const query = new URLSearchParams({ range: this.range });

            if (this.range === 'custom') {
                if (this.start) query.set('start', this.start);
                if (this.end) query.set('end', this.end);
            }

            try {
                const response = await fetch(`${config.endpoint}?${query.toString()}`, {
                    headers: { Accept: 'application/json' },
                });
                if (!response.ok) throw new Error('Unable to load chart data.');
                const data = await response.json();
                this.labels = data.labels || [];
                this.values = data.values || [];
            } catch (_) {
                this.labels = [];
                this.values = [];
            } finally {
                this.loading = false;
            }
        },
    }));
});
