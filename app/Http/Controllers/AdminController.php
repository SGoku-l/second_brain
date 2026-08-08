<?php

namespace App\Http\Controllers;

use App\Models\Chunks;
use App\Models\ErrorLog;
use App\Models\Source;
use App\Models\User;
use App\Models\TokenUsage;
use App\Models\Transaction;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Jobs\IngestRepoJob;
use App\Services\Subscriptions\SubscriptionManager;
use App\Http\Controllers\Concerns\BuildsUsageCharts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'users' => User::query()->with('subscription.plan')->latest()->paginate(20),
        ]);
    }

    public function repos(): View
    {
        return view('admin.repos', ['sources' => Source::query()->with('workspace.user:id,name,email')->withCount('chunks')->orderByDesc('updated_at')->get()]);
    }

    public function adminResync(Source $source): \Illuminate\Http\RedirectResponse
    {
        $source->update(['meta' => array_merge($source->meta ?? [], ['status' => 'indexing', 'last_error' => null, 'last_started_at' => now()->toIso8601String()])]);
        dispatch(new IngestRepoJob($source->id, $source->identifier, true));
        return back()->with('status', 'Admin re-sync started for '.$source->identifier.'.');
    }

    public function adminDeleteSource(Source $source): \Illuminate\Http\RedirectResponse
    {
        $user = $source->workspace?->user;
        abort_unless($user, 404);
        return $this->forceDeleteSource($user, $source);
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
            'plans' => SubscriptionPlan::query()->where('active', true)->orderBy('price')->get(),
            'transactions' => $user->transactions()->with('plan:id,name')->latest()->limit(8)->get(),
            'tokenUsage' => TokenUsage::query()
                ->where('user_id', $user->id)
                ->where('recorded_at', '>=', now()->subDays(6)->startOfDay())
                ->selectRaw('DATE(recorded_at) as day, SUM(tokens) as tokens')
                ->groupBy('day')->orderBy('day')->get(),
        ]);
    }

    public function toggleUser(User $user): \Illuminate\Http\RedirectResponse
    {
        $user->update(['active_status' => ! $user->active_status]);

        return back()->with('status', $user->name.' is now '.($user->active_status ? 'active' : 'inactive').'.');
    }

    public function changePlan(Request $request, User $user, SubscriptionManager $subscriptions): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate(['plan_id' => ['required', 'exists:subscription_plans,id']]);
        $plan = SubscriptionPlan::query()->where('active', true)->findOrFail($data['plan_id']);
        $subscriptions->activate($user, $plan);

        return back()->with('status', $user->name.' was moved to '.$plan->name.'.');
    }

    public function regenerateApiToken(User $user): \Illuminate\Http\RedirectResponse
    {
        $user->update(['api_token' => Str::random(80)]);

        return back()->with('status', 'API token regenerated for '.$user->name.'.');
    }

    public function forceReindex(User $user, Source $source): \Illuminate\Http\RedirectResponse
    {
        abort_unless($source->workspace?->user_id === $user->id, 404);
        $source->update(['meta' => array_merge($source->meta ?? [], ['status' => 'indexing', 'last_error' => null, 'last_started_at' => now()->toIso8601String()])]);
        dispatch(new IngestRepoJob($source->id, $source->identifier, true));

        return back()->with('status', 'Re-index started for '.$source->identifier.'.');
    }

    public function forceDeleteSource(User $user, Source $source): \Illuminate\Http\RedirectResponse
    {
        abort_unless($source->workspace?->user_id === $user->id, 404);
        $name = $source->identifier;
        DB::transaction(function () use ($user, $source) {
            $bytes = (int) Chunks::query()->where('source_id', $source->id)->sum(DB::raw('octet_length(content)'));
            Chunks::query()->where('source_id', $source->id)->delete();
            $subscription = UserSubscription::query()->where('user_id', $user->id)->lockForUpdate()->first();
            if ($subscription) {
                $usedBytes = max(0, $subscription->storage_used_bytes - $bytes);
                $subscription->update([
                    'storage_used_bytes' => $usedBytes,
                    'storage_used_mb' => (int) ceil($usedBytes / (1024 * 1024)),
                ]);
            }
            $source->delete();
        });

        return back()->with('status', $name.' was deleted.');
    }

    public function errors(): View
    {
        return view('admin.errors', [
            'errors' => ErrorLog::query()->with('user:id,name,email')->latest()->paginate(20),
        ]);
    }
}
