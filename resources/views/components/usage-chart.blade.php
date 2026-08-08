@props(['title', 'eyebrow' => 'Usage', 'endpoint', 'defaultRange' => 'today', 'valueLabel' => 'Events'])

<section x-data="usageChart({ endpoint: '{{ $endpoint }}', defaultRange: '{{ $defaultRange }}' })" x-init="load()" class="glass-panel rounded-[26px] p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">{{ $eyebrow }}</p>
            <h2 class="mt-2 text-lg font-semibold text-stone-900 dark:text-stone-100">{{ $title }}</h2>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select x-model="range" @change="load()" class="theme-select text-xs">
                <option value="today">Today</option>
                <option value="7d">Last 7 days</option>
                <option value="30d">Last 30 days</option>
                <option value="custom">Custom range</option>
            </select>
            <template x-if="range === 'custom'">
                <div class="flex items-center gap-2">
                    <input x-model="start" @change="load()" type="date" class="theme-select px-2 text-xs">
                    <input x-model="end" @change="load()" type="date" class="theme-select px-2 text-xs">
                </div>
            </template>
        </div>
    </div>

    <div class="relative mt-5 h-64">
        <div x-show="loading" class="absolute inset-0 animate-pulse rounded-2xl bg-black/5 dark:bg-white/5"></div>
        <template x-if="!loading && !hasData">
            <div class="flex h-full items-center justify-center text-sm text-stone-500 dark:text-stone-400">No {{ strtolower($valueLabel) }} in this period.</div>
        </template>
        <svg x-show="!loading && hasData" viewBox="0 0 100 100" preserveAspectRatio="none" class="h-full w-full overflow-visible" aria-label="{{ $title }} chart" role="img">
            <line x1="10" x2="90" y1="16" y2="16" class="stroke-stone-300/70 dark:stroke-white/10" stroke-width="0.35"/>
            <line x1="10" x2="90" y1="50" y2="50" class="stroke-stone-300/70 dark:stroke-white/10" stroke-width="0.35"/>
            <line x1="10" x2="90" y1="84" y2="84" class="stroke-stone-300/70 dark:stroke-white/10" stroke-width="0.35"/>
            <polyline :points="points" fill="none" stroke="#34d399" stroke-width="1.2" vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round"/>
            <template x-for="(value, index) in values" :key="index">
                <circle :cx="values.length === 1 ? 50 : 10 + (index * 80 / (values.length - 1))" :cy="84 - ((value / maxValue) * 68)" r="1.5" fill="#34d399" vector-effect="non-scaling-stroke"/>
            </template>
        </svg>
        <div x-show="!loading && hasData" class="absolute inset-x-3 bottom-0 flex justify-between text-[10px] text-stone-500 dark:text-stone-400">
            <span x-text="labels[0]"></span>
            <span><span x-text="formatNumber(maxValue)"></span> {{ $valueLabel }}</span>
            <span x-text="labels[labels.length - 1]"></span>
        </div>
    </div>
</section>
