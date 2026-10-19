<?php

use App\Http\Controllers\MatchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MatchController::class, 'dashboard'])->name('dashboard');
Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
Route::get('/matches/create', [MatchController::class, 'create'])->name('matches.create');
Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
Route::get('/matches/{match}/edit', [MatchController::class, 'edit'])->name('matches.edit');
Route::put('/matches/{match}', [MatchController::class, 'update'])->name('matches.update');
Route::delete('/matches/{match}', [MatchController::class, 'destroy'])->name('matches.destroy');
