<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Socialite;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->prefix('auth/github')->group(function () {

    Route::get('/' , fn () => Socialite::driver('github')->scopes(['repo'])->redirect());

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



require __DIR__.'/auth.php';
