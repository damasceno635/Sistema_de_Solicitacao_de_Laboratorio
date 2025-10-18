<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

// ROTA PÚBLICA PARA LISTAR LABORATÓRIOS
Route::get('/laboratories', [LaboratoryController::class, 'index'])
    ->name('laboratories.index');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // REMOVA ESTA ROTA DUPLICADA:
    // Route::get('/dashboard', function () {
    //     return view('dashboard');
    // })->name('dashboard');

    // ROTAS ADMIN - SEM MIDDLEWARE COMPLEXO
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->names('users');
        Route::resource('laboratories', LaboratoryController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->names('laboratories');
    });
Route::resource('laboratories', LaboratoryController::class);
    // ROTAS DE REVISÃO
    Route::get('/reservations/{reservation}/review', [ReservationController::class, 'review'])
        ->name('reservations.review');
    Route::post('/reservations/{reservation}/process-course', [ReservationController::class, 'courseProcess'])
        ->name('reservations.courseProcess');

    Route::get('/reservations/{reservation}/lab-review', [ReservationController::class, 'labReview'])
        ->name('reservations.labReview');
    Route::post('/reservations/{reservation}/process-lab', [ReservationController::class, 'labProcess'])
        ->name('reservations.labProcess');

    // ROTAS DE RESERVAS
    Route::resource('reservations', ReservationController::class)
        ->names('reservations');
});