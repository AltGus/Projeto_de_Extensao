<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redireciona para o dashboard como página inicial
Route::get('/', function () {
    return redirect('/dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Participants (CRUD completo)
Route::resource('participants', ParticipantController::class);

// Workshops / Oficinas (CRUD completo)
Route::resource('workshops', WorkshopController::class);

// Productions / Produções (CRUD completo)
Route::resource('productions', ProductionController::class);