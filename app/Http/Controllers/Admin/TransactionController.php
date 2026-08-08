<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:pending,completed,failed,expired'],
            'plan' => ['nullable', 'string'],
            'range' => ['nullable', 'in:all,today,7d,30d,custom'],
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date'],
        ]);
        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? '';
        $plan = $filters['plan'] ?? '';
        $range = $filters['range'] ?? 'all';
        [$start, $end] = $this->dateRange($range, $filters['start'] ?? null, $filters['end'] ?? null);

        return view('admin.transactions', [
            'transactions' => Transaction::query()
                ->with(['user:id,name,email', 'plan:id,name'])
                ->when($search !== '', fn ($query) => $query->whereHas('user', fn ($query) => $query->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($search).'%'])->orWhereRaw('LOWER(email) LIKE ?', ['%'.strtolower($search).'%'])))
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($plan !== '', fn ($query) => $query->where('subscription_plan_id', $plan))
                ->when($start && $end, fn ($query) => $query->whereBetween('created_at', [$start, $end]))
                ->latest()
                ->paginate(25)
                ->withQueryString(),
            'plans' => SubscriptionPlan::query()->where('active', true)->orderBy('price')->get(['id', 'name']),
            'filters' => compact('search', 'status', 'plan', 'range') + ['start' => $filters['start'] ?? '', 'end' => $filters['end'] ?? ''],
        ]);
    }

    private function dateRange(string $range, ?string $startDate, ?string $endDate): array
    {
        if ($range === 'all') {
            return [null, null];
        }

        $end = now()->endOfDay();
        $start = match ($range) {
            '7d' => now()->subDays(6)->startOfDay(),
            '30d' => now()->subDays(29)->startOfDay(),
            'custom' => Carbon::parse($startDate ?? now()->toDateString())->startOfDay(),
            default => now()->startOfDay(),
        };

        if ($range === 'custom' && $endDate) {
            $end = Carbon::parse($endDate)->endOfDay();
        }

        return $start->gt($end) ? [$end->copy()->startOfDay(), $start->copy()->endOfDay()] : [$start, $end];
    }
}
