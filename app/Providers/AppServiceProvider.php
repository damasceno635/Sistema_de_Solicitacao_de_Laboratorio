<?php

namespace App\Providers;

use App\Models\User; // Importar o modelo User
use App\Models\Reservation; // Importar o modelo Reservation
use Illuminate\Support\Facades\Gate; // Importar a facade Gate
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // ... (outros métodos)

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Definir a Gate para o Coordenador de Laboratório (Admin)
        // O Gate 'manage-users' é verdadeiro (true) se o usuário tiver a role 'admin'
        Gate::define('manage-users', function (User $user) {
            return $user->role === 'admin';
        });

        // 2. (Opcional, mas recomendado) Definir o super-admin
        // Este Gate::before() permite que o 'admin' ignore qualquer outra verificação de Gate/Policy.
        Gate::before(function (User $user, string $ability) {
            if ($user->role === 'admin') {
                return true;
            }
        });

        // NOVO GATE: Apenas para quem tem o papel 'professor'
        Gate::define('create-reservations', function (User $user) {
            return $user->role === 'professor';
        });

        // NOVO GATE: Para editar/excluir a PRÓPRIA reserva
        Gate::define('modify-reservation', function (User $user, Reservation $reservation) {
            // Permite se for o professor que criou OU se for um administrador
            return $user->id === $reservation->user_id || $user->role === 'admin';
        });

        // NOVO GATE: Apenas para quem tem o papel 'coordenador_curso'
        Gate::define('manage-course-reservations', function (User $user) {
            return $user->role === 'coordenador_curso';
        });
    }
}
