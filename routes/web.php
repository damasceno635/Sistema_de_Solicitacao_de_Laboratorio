<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\ReservationController;

// -------------------------------------------------------------
// ROTA PRINCIPAL: Redireciona automaticamente conforme login
// -------------------------------------------------------------
Route::get('/', function () {
    // Se o usuário estiver logado, redireciona para o Dashboard
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    // Se o usuário NÃO estiver logado, redireciona para o Login
    return redirect()->route('login');
})->name('home');


// -------------------------------------------------------------
// ROTA PÚBLICA: Listagem de Laboratórios
// -------------------------------------------------------------
Route::get('/laboratories', [LaboratoryController::class, 'index'])
    ->name('laboratories.index');


// -------------------------------------------------------------
// ROTAS PROTEGIDAS (Autenticação + Verificação)
// -------------------------------------------------------------
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // ---------------------------------------------------------
    // DASHBOARD PADRÃO (Pós-login)
    // ---------------------------------------------------------
    Route::get('/dashboard', function () {
        // Redirecionamento condicional do dashboard se necessário, ou apenas retorna a view padrão
        return view('dashboard');
    })->name('dashboard');
    
    // ---------------------------------------------------------
    // ROTAS ADMINISTRATIVAS (APENAS ADMIN E COORDENADORES)
    // ---------------------------------------------------------
    Route::middleware('can:manage-laboratories') // Este Gate deve checar se a role é 'admin' ou 'coordenador_curso'
        ->prefix('admin')->name('admin.')->group(function () {
            // Gerenciamento de Usuários (APENAS ADMIN - Coordenador de Laboratório)
            Route::resource('users', UserController::class)->names('users');

            // Gerenciamento de Laboratórios (ADMIN e Coordenadores)
            Route::resource('laboratories', LaboratoryController::class)
                ->only(['create', 'store', 'edit', 'update', 'destroy'])
                ->names('laboratories');
        });

    // ---------------------------------------------------------
    // ROTAS DE RESERVA
    // ---------------------------------------------------------

    // Ações de Revisão (Apenas para Coordenador de Curso)
    Route::middleware('can:manage-course-reservations')
        ->group(function () {
        Route::get('/reservations/{reservation}/review', [ReservationController::class, 'review'])->name('reservations.review');
        Route::post('/reservations/{reservation}/process', [ReservationController::class, 'process'])->name('reservations.process');
    });
    
    // Todas as outras rotas de CRUD de Reservas. 
    // O controle de acesso (quem pode criar/editar/deletar) é feito estritamente no ReservationController,
    // utilizando os Gates 'create-reservations' e 'modify-reservation'.
    Route::resource('reservations', ReservationController::class)
        ->names('reservations');
});
