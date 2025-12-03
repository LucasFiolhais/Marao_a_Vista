<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Alojamento;
use App\Http\Controllers\Admin\UtilizadoresController;

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

// Rota para a página de Contactos
Route::get('/contactos', function () {
    return Inertia::render('Contactos');  // Aqui estamos renderizando a página de "Contactos"
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
])
    ->prefix('admin')          // todas as rotas começam com /admin
    ->name('admin.')           // todas as rotas começam com admin.
    ->group(function () {

        //admin  (DASHBOARD)
        Route::get('/', fn () => Inertia::render('Admin/Dashboard'))
            ->name('dashboard');
        Route::get('/reservas', fn () => Inertia::render('Admin/RservasAdmin'))
            ->name('reservas');
        Route::get('/alojamento', fn () => Inertia::render('Admin/AlojamentoAdmin'))
            ->name('alojamento');
        Route::get('/comentarios', fn () => Inertia::render('Admin/ComentariosAdmin'))
            ->name('comentarios');


        // 👉 /admin/utilizadores  (Página Inertia)
        Route::get('/utilizadores', fn () => Inertia::render('Admin/Utilizadores/Index'))
            ->name('utilizadores');

        // 👉 /admin/utilizadores/criar
        Route::get('/utilizadores/criar', fn () => Inertia::render('Admin/Utilizadores/Create'))
            ->name('utilizadores.create');

        // 👉 /admin/utilizadores/{id}/editar
        Route::get('/utilizadores/{id}/editar', fn ($id) => 
            Inertia::render('Admin/Utilizadores/Edit', ['id' => $id])
        )->name('utilizadores.edit');

        // Rotas API para utilizadores (para Axios)
        // 👉 /admin/utilizadores-lista
        Route::get('/utilizadores-lista', [UtilizadoresController::class, 'index'])
            ->name('utilizadores.lista');

        // 👉 /admin/utilizadores  (store)
        Route::post('/utilizadores', [UtilizadoresController::class, 'store'])
            ->name('utilizadores.store');

        // 👉 /admin/utilizadores/{user}  (show)
        Route::get('/utilizadores/{user}', [UtilizadoresController::class, 'show'])
            ->name('utilizadores.show');

        // 👉 /admin/utilizadores/{user}  (update)
        Route::match(['put', 'patch'], '/utilizadores/{user}', [UtilizadoresController::class, 'update'])
            ->name('utilizadores.update');

        // 👉 /admin/utilizadores/{user}  (destroy)
        Route::delete('/utilizadores/{user}', [UtilizadoresController::class, 'destroy'])
            ->name('utilizadores.destroy');
    });
    // Página Inertia
    

Route::get('/alojamentos', function () {
    // Buscar todos os alojamentos
    $alojamentos = Alojamento::all(); // Ou qualquer lógica que você queira para buscar os alojamentos
    return Inertia::render('Alojamentos', [
        'alojamentos' => $alojamentos,
    ]);
});

Route::get('/alojamentos/{id}', function ($id) {
    // Buscar alojamento específico
    $alojamento = Alojamento::findOrFail($id);

    return Inertia::render('AlojamentoDetalhes', [
        'id' => $id,                  // necessário para o Vue
        'alojamento' => $alojamento, // envia dados completos também
    ]);
});