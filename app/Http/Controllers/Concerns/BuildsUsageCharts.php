<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait BuildsUsageCharts
{
    private function chartRange(Request $request): array
    {
        $range = $request->string('range', 'today')->toString();
        $end = now()->endOfDay();
        $start = match ($range) {
            '7d' => now()->subDays(6)->startOfDay(),
            '30d' => now()->subDays(29)->startOfDay(),
            'custom' => Carbon::parse($request->input('start', now()->toDateString()))->startOfDay(),
            default => now()->startOfDay(),
        };

        if ($range === 'custom' && $request->filled('end')) {
            $end = Carbon::parse($request->input('end'))->endOfDay();
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $start = $start->max(now()->subDays(364)->startOfDay());

        return [$start, $end];
    }

    private function dailySeries(Builder $query, string $dateColumn, string $aggregate = 'count'): array
    {
        [$start, $end] = $this->chartRange(request());
        $value = $aggregate === 'sum' ? 'SUM(tokens)' : 'COUNT(*)';
        $rows = $query->whereBetween($dateColumn, [$start, $end])
            ->selectRaw("DATE({$dateColumn}) as day, {$value} as value")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('value', 'day');

        $days = CarbonPeriod::create($start->toDateString(), $end->toDateString());
        $labels = [];
        $values = [];

        foreach ($days as $day) {
            $key = $day->toDateString();
            $labels[] = $day->format('M j');
            $values[] = (int) ($rows[$key] ?? 0);
        }

        return compact('labels', 'values');
    }
}
