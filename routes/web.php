<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use App\Models\Source;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Socialite;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    $repos = Source::query()
        ->whereHas('workspace', fn ($query) => $query->where('user_id', $user->id))
        ->orderBy('identifier')
        ->get()
        ->map(fn ($source) => [
            'id' => $source->id,
            'identifier' => $source->identifier,
            'status' => $source->last_synced_at ? 'indexed' : 'indexing',
        ])
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

Route::middleware('auth')->prefix('auth/github')->group(function () {

    Route::get('/' , fn () => Socialite::driver('github')->scopes(['repo'])->redirect())->name('github.connect');

    Route::get('/callback' , function () {
        $githubUser = Socialite::driver('github')->user();
        auth()->user()->update([
            'github_token' => encrypt($githubUser->token)
        ]);
        return redirect('/dashboard');
    });

});

Route::middleware('auth')->controller(ChatController::class)->group(function (){

    Route::get('/chat' , 'index')->name('chat.index');

    Route::post('/chat' , 'ask')->name('chat.ask');

});

Route::middleware('auth')->group(function () {
    Route::get('/mcp/connect', function () {
        return view('mcp.connect', [
            'apiToken' => auth()->user()->api_token,
        ]);
    })->name('mcp.connect');
});



require __DIR__.'/auth.php';
