<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];

        // Mensagem de boas-vindas personalizada
        $data['welcomeMessage'] = $this->getWelcomeMessage($user);

        // Métricas básicas para todos os usuários
        if ($user->role === 'professor') {
            $data = array_merge($data, $this->getProfessorMetrics($user));
        } elseif ($user->role === 'coordenador_curso') {
            $data = array_merge($data, $this->getCoordinatorMetrics($user));
        } elseif ($user->role === 'admin') {
            $data = array_merge($data, $this->getAdminMetrics());
        }

        return view('dashboard', $data);
    }

    private function getWelcomeMessage($user)
    {
        $hour = Carbon::now()->hour;
        $greeting = '';

        if ($hour < 12) {
            $greeting = 'Bom dia';
        } elseif ($hour < 18) {
            $greeting = 'Boa tarde';
        } else {
            $greeting = 'Boa noite';
        }

        $roleMessages = [
            'professor' => 'Acompanhe suas solicitações de reserva de laboratório.',
            'coordenador_curso' => 'Revise as solicitações de reserva do seu curso.',
            'admin' => 'Gerencie todas as reservas e laboratórios do sistema.'
        ];

        return "{$greeting}! {$roleMessages[$user->role]}";
    }

    private function getProfessorMetrics($user)
    {
        $reservations = Reservation::where('user_id', $user->id)->get();

        return [
            'totalReservations' => $reservations->count(),
            'pendingCount' => $reservations->where('status', 'pendente')->count(),
            'approvedCount' => $reservations->where('status', 'aprovada')->count(),
            'rejectedCount' => $reservations->where('status', 'rejeitada')->count(),
            'upcomingReservations' => Reservation::with('laboratory')
                ->where('user_id', $user->id)
                ->where('start_time', '>=', now())
                ->whereIn('status', ['aprovada', 'em andamento'])
                ->orderBy('start_time')
                ->take(5)
                ->get()
        ];
    }

    private function getCoordinatorMetrics($user)
    {
        $reservations = Reservation::with(['user', 'laboratory'])
            ->whereHas('user', function ($query) use ($user) {
                $query->where('course', $user->course);
            })->get();

        return [
            'totalReservations' => $reservations->count(),
            'pendingCount' => $reservations->where('status', 'pendente')->count(),
            'approvedCount' => $reservations->where('status', 'aprovada')->count(),
            'rejectedCount' => $reservations->where('status', 'rejeitada')->count(),
            'upcomingReservations' => Reservation::with(['user', 'laboratory'])
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('course', $user->course);
                })
                ->where('start_time', '>=', now())
                ->whereIn('status', ['aprovada', 'em andamento'])
                ->orderBy('start_time')
                ->take(5)
                ->get()
        ];
    }

    private function getAdminMetrics()
    {
        $reservations = Reservation::all();

        return [
            'totalReservations' => $reservations->count(),
            'pendingCount' => $reservations->where('status', 'pendente')->count(),
            'approvedCount' => $reservations->where('status', 'aprovada')->count(),
            'rejectedCount' => $reservations->where('status', 'rejeitada')->count(),
            'upcomingReservations' => Reservation::with(['user', 'laboratory'])
                ->where('start_time', '>=', now())
                ->whereIn('status', ['aprovada', 'em andamento'])
                ->orderBy('start_time')
                ->take(5)
                ->get(),
            'totalUsers' => User::count(),
            'totalLaboratories' => Laboratory::count(),
            'reservationsThisMonth' => Reservation::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count()
        ];
    }
}