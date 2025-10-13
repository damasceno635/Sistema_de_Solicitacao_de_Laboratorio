<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // <<<<< IMPORTANTE: Adicionar o Auth

// -------------------------------------------------------------
// ROTA PRINCIPAL: Adicionada a lógica para redirecionar para o login
// -------------------------------------------------------------
Route::get('/', function () {
    // Se o usuário estiver logado, redireciona para o Dashboard
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    
    // Se o usuário NÃO estiver logado, redireciona para a tela de Login
    return redirect()->route('login');
})->name('home');


// 1. ROTAS DE AUTENTICAÇÃO (Login, Register, Reset, etc.)
// Estas são definidas internamente pelo Jetstream/Fortify e são acessíveis
// (não precisam do middleware 'auth' ou 'can').

// 2. ROTAS PROTEGIDAS (Dashboard, Profile, etc.)
Route::middleware([
    'auth:sanctum', // Exige que o usuário esteja logado
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    
    // Rota padrão após o login (Dashboard)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ---------------------------------------------------------
    // 3. ROTAS DE ADMINISTRAÇÃO - PRECISAM DE AUTENTICAÇÃO E PERMISSÃO
    Route::prefix('admin')->name('admin.')->middleware('can:manage-users')->group(function () {
        // Estas rotas só serão acessíveis se o usuário estiver logado E passar pelo Gate 'manage-users'
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });
    // ---------------------------------------------------------

});