<?php

namespace App\Http\Controllers;

use App\Models\Chunks;
use App\Models\ErrorLog;
use App\Models\Source;
use App\Models\User;
use App\Models\TokenUsage;
use App\Models\Transaction;
use App\Http\Controllers\Concerns\BuildsUsageCharts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    use BuildsUsageCharts;
    public function index(): View
    {
        return view('admin.index', [
            'metrics' => [
                'users' => User::count(),
                'sources' => Source::count(),
                'chunks' => Chunks::count(),
                'errorsToday' => ErrorLog::where('created_at', '>=', now()->startOfDay())->count(),
                'pendingJobs' => DB::table('jobs')->count(),
                'failedJobs' => DB::table('failed_jobs')->count(),
            ],
        ]);
    }

    public function tokenChart(Request $request): JsonResponse
    {
        return response()->json($this->dailySeries(TokenUsage::query(), 'recorded_at', 'sum'));
    }

    public function userChart(Request $request): JsonResponse
    {
        return response()->json($this->dailySeries(User::query(), 'created_at'));
    }

    public function transactionChart(Request $request): JsonResponse
    {
        return response()->json($this->dailySeries(Transaction::query(), 'created_at'));
    }

    public function users(): View
    {
        return view('admin.users', [
            'users' => User::query()->latest()->paginate(20),
        ]);
    }

    public function showUser(User $user): View
    {
        $sources = Source::query()
            ->whereHas('workspace', fn ($query) => $query->where('user_id', $user->id))
            ->withCount('chunks')
            ->orderBy('identifier')
            ->get();

        return view('admin.user-show', [
            'user' => $user,
            'sources' => $sources,
            'chunkCount' => $sources->sum('chunks_count'),
            'repoCount' => $sources->count(),
            'subscription' => $user->subscription?->load('plan'),
        ]);
    }

    public function errors(): View
    {
        return view('admin.errors', [
            'errors' => ErrorLog::query()->with('user:id,name,email')->latest()->paginate(20),
        ]);
    }
}
