<?php

use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\RepoController;
use App\Http\Middleware\EnsureSubscriptionActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\Source;
use App\Models\Workspace;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Socialite;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if (! $user->active_status) {
        $user->update(['active_status' => true]);
    }

    Workspace::firstOrCreate([
        'user_id' => $user->id,
        'name' => 'Default',
    ]);

    $repos = Source::query()
        ->whereHas('workspace', fn ($query) => $query->where('user_id', $user->id))
        ->orderBy('identifier')
        ->get()
        ->map(function ($source) {
            $meta = is_array($source->meta) ? $source->meta : [];

            return [
                'id' => $source->id,
                'identifier' => $source->identifier,
                'status' => $meta['status'] ?? ($source->chunks()->exists() ? 'indexed' : 'indexing'),
                'error' => $meta['last_error'] ?? null,
                'commitsFound' => $meta['commits_found'] ?? null,
                'chunksIndexed' => $meta['chunks_indexed'] ?? null,
            ];
        })
        ->values()
        ->all();

    return view('dashboard', [
        'githubConnected' => ! empty($user->github_token),
        'repos' => $repos,
        'subscription' => $user->subscription?->load('plan'),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->as('admin.')
    ->controller(AdminController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/users', 'users')->name('users');
        Route::get('/users/{user}', 'showUser')->name('users.show');
        Route::get('/errors', 'errors')->name('errors');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/settings', [BillingController::class, 'settings'])->name('settings');
        Route::post('/plans', [BillingController::class, 'storePlan'])->name('plans.store');
        Route::put('/plans/{plan}', [BillingController::class, 'updatePlan'])->name('plans.update');
        Route::patch('/plans/{plan}/deactivate', [BillingController::class, 'deactivatePlan'])->name('plans.deactivate');
    });

Route::post('/razorpay/webhook', RazorpayWebhookController::class)->name('razorpay.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/plans', [PlansController::class, 'index'])->name('plans.index');
    Route::post('/plans/{plan}/checkout', [PlansController::class, 'checkout'])->name('plans.checkout');
});

Route::middleware('auth')->prefix('auth/github')->group(function () {

    Route::get('/', fn () => Socialite::driver('github')->scopes(['repo'])->redirect())->name('github.connect');

    Route::get('/callback', function () {
        $githubUser = Socialite::driver('github')->user();
        auth()->user()->update([
            'github_token' => encrypt($githubUser->token),
        ]);

        return redirect('/dashboard')->with('github_connected', true);
    });

});

Route::middleware(['auth', EnsureSubscriptionActive::class])->controller(ChatController::class)->group(function () {

    Route::get('/chat', 'index')->name('chat.index');

    Route::post('/chat', 'ask')->name('chat.ask');

});

Route::middleware('auth')->group(function () {
    Route::get('/mcp/connect', function () {
        return view('mcp.connect', [
            'apiToken' => auth()->user()->api_token,
        ]);
    })->middleware(EnsureSubscriptionActive::class)->name('mcp.connect');

    Route::get('/repos/available', [RepoController::class, 'available'])->name('repos.available');
    Route::post('/repos/ingest', [RepoController::class, 'ingest'])->middleware(EnsureSubscriptionActive::class)->name('repos.ingest');
    Route::post('/repos/{source}/resync', [RepoController::class, 'resync'])->middleware(EnsureSubscriptionActive::class)->name('repos.resync');
    Route::delete('/repos/{source}', [RepoController::class, 'destroy'])->name('repos.destroy');
});

require __DIR__.'/auth.php';
