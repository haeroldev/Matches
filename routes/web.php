<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MatchController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
Route::post('/signup', [AuthController::class, 'signup'])->middleware('throttle:5,1')->name('signup.store');

Route::middleware('jwt.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');

    Route::middleware('role.admin')->group(function () {
        Route::get('/dashboard', [MatchController::class, 'dashboard'])->name('dashboard');
        Route::get('/fighters', [MatchController::class, 'fighters'])->name('fighters.index');
        Route::get('/matches/create', [MatchController::class, 'create'])->name('matches.create');
        Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
        Route::get('/matches/{match}/edit', [MatchController::class, 'edit'])->name('matches.edit');
        Route::put('/matches/{match}', [MatchController::class, 'update'])->name('matches.update');
        Route::delete('/matches/{match}', [MatchController::class, 'destroy'])->name('matches.destroy');
    });
});
