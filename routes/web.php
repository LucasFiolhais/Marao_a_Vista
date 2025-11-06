<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Alojamento;

/* Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
}); */

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/reservas', function () {
    return Inertia::render('Reservas');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:admin',
])->prefix('admin')->group(function () {
    Route::get('/', fn() => Inertia::render('Admin/Dashboard'))->name('admin.dashboard');
    Route::get('/reservas', fn() => Inertia::render('Admin/ReservasAdmin'))->name('admin.reservas');
    Route::get('/utilizadores', fn() => Inertia::render('Admin/Utilizadores'))->name('admin.utilizadores');
});


Route::get('/alojamentos', function () {
    // Buscar todos os alojamentos
    $alojamentos = Alojamento::all(); // Ou qualquer lógica que você queira para buscar os alojamentos
    return Inertia::render('Alojamentos', [
        'alojamentos' => $alojamentos,
    ]);
});
