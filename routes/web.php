<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepoController;
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

Route::middleware('auth')->controller(ChatController::class)->group(function () {

    Route::get('/chat', 'index')->name('chat.index');

    Route::post('/chat', 'ask')->name('chat.ask');

});

Route::middleware('auth')->group(function () {
    Route::get('/mcp/connect', function () {
        return view('mcp.connect', [
            'apiToken' => auth()->user()->api_token,
        ]);
    })->name('mcp.connect');

    Route::get('/repos/available', [RepoController::class, 'available'])->name('repos.available');
    Route::post('/repos/ingest', [RepoController::class, 'ingest'])->name('repos.ingest');
});

require __DIR__.'/auth.php';
