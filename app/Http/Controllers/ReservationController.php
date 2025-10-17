<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule; // Importação essencial
use App\Models\User; // Importação essencial

class ReservationController extends Controller
{
    /**
     * Gera os horários de reserva disponíveis (intervalos de 1 hora).
     * @return array
     */
    protected function getTimeSlots()
    {
        $slots = [];
        // Defina o horário de início e fim que o professor pode reservar
        $start = Carbon::createFromTime(7, 0, 0); // Ex: Começa às 07:00
        $end = Carbon::createFromTime(22, 0, 0); // Ex: Termina às 22:00

        while ($start->lessThan($end)) {
            $slotStart = $start->format('H:i');
            $slotEnd = $start->copy()->addHour()->format('H:i');
            
            // Verifica se o slot de término não ultrapassa o limite
            if ($start->copy()->addHour()->greaterThan($end)) {
                break;
            }
            
            // O valor do campo será apenas a hora de INÍCIO (H:i)
            $slots[$slotStart] = "{$slotStart} - {$slotEnd}";
            
            $start->addHour();
        }

        return $slots;
    }
    
    /**
     * Exibe a lista de reservas.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. ADMIN (Coordenador de Laboratório - Vê TUDO)
        if ($user->role === 'admin') {
            $reservations = Reservation::with('laboratory', 'user')->latest()->get();
            $view = 'reservations.index'; 
        } 
        // 2. COORDENADOR DE CURSO (Vê APENAS as do seu curso)
        elseif ($user->role === 'coordenador_curso') {
            $reservations = Reservation::whereHas('user', function ($query) use ($user) {
                // Filtra usuários que pertencem ao mesmo curso do coordenador
                $query->where('course', $user->course);
            })->with('laboratory', 'user')->latest()->get();
            
            $view = 'reservations.index-coordinator';
        }
        // 3. PROFESSOR e outros (Vê apenas as suas)
        else {
            $reservations = Reservation::where('user_id', $user->id)
                                       ->with('laboratory')
                                       ->latest()
                                       ->get();
            $view = 'reservations.index';
        }
        
        return view($view, compact('reservations'));
    }

    /**
     * Mostra o formulário para criação de uma nova reserva.
     */
    public function create()
    {
        Gate::authorize('create-reservations');

        $laboratories = Laboratory::all();
        $timeSlots = $this->getTimeSlots();

        return view('reservations.create', compact('laboratories', 'timeSlots'));
    }

    /**
     * Armazena uma nova reserva.
     */
    public function store(Request $request)
    {
        Gate::authorize('create-reservations');

        // Validação (usando time_slot para H:i)
        $request->validate([
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'date' => ['required', 'date', 'after_or_equal:' . Carbon::now()->format('Y-m-d')],
            'time_slot' => ['required', 'date_format:H:i', Rule::in(array_keys($this->getTimeSlots()))], // Garante que o slot seja válido
            'lesson_plan' => ['required', 'string', 'min:10'],
        ]);

        // COMBINAÇÃO DA DATA E HORA (1 hora de duração padrão)
        $start_time_hour = $request->input('time_slot');
        $start_time = Carbon::parse($request->input('date') . ' ' . $start_time_hour);
        $end_time = $start_time->copy()->addHour();

        // CHECAGEM DE CONFLITO (APENAS com reservas já 'aprovada' ou 'em andamento')
        $conflict = Reservation::where('laboratory_id', $request->laboratory_id)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->where(function ($q) use ($start_time, $end_time) {
                    $q->where('start_time', '<', $end_time)
                      ->where('end_time', '>', $start_time);
                });
            })
            ->whereIn('status', ['aprovada', 'em andamento']) 
            ->exists();

        if ($conflict) {
            return back()->withInput()->withErrors(['time_slot' => 'O laboratório já está reservado (ou em processo de aprovação final) neste horário.']);
        }
        
        // CRIAÇÃO DA RESERVA
        Reservation::create([
            'user_id' => Auth::id(),
            'laboratory_id' => $request->laboratory_id,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'lesson_plan' => $request->lesson_plan,
            'status' => 'pendente', // NOVO FLUXO: Sempre começa como 'pendente' para o coordenador revisar
            'rejection_feedback' => null,
        ]);

        return redirect()->route('reservations.index')->with('success', 'Solicitação de reserva enviada para revisão do Coordenador de Curso.');
    }
    
    /**
     * Mostra o formulário de edição da reserva.
     */
    public function edit(Reservation $reservation)
    {
        // Garante que APENAS o criador ou o admin (Coordenador de Laboratório) pode editar
        Gate::authorize('modify-reservation', $reservation);

        // NOVA REGRA: Se o usuário é o criador E não é Admin E o status não é 'pendente', barra.
        $user = Auth::user();
        if ($user->id === $reservation->user_id && $user->role !== 'admin' && $reservation->status !== 'pendente') {
            abort(403, 'Você só pode editar reservas que estão com o status "Pendente".');
        }

        $laboratories = Laboratory::all();
        $timeSlots = $this->getTimeSlots();

        // Prepara a data e o slot para preencher o formulário
        $reservation->time_slot_value = $reservation->start_time->format('H:i'); 

        return view('reservations.edit', compact('reservation', 'laboratories', 'timeSlots'));
    }

    /**
     * Atualiza a reserva no banco de dados.
     */
    public function update(Request $request, Reservation $reservation)
    {
        // Garante que APENAS o criador ou o admin pode atualizar
        Gate::authorize('modify-reservation', $reservation);

        // NOVA REGRA: Se o usuário é o criador E não é Admin E o status não é 'pendente', barra.
        $user = Auth::user();
        if ($user->id === $reservation->user_id && $user->role !== 'admin' && $reservation->status !== 'pendente') {
            abort(403, 'Você só pode atualizar reservas que estão com o status "Pendente".');
        }

        // Validação (usando time_slot para H:i)
        $request->validate([
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'date' => ['required', 'date', 'after_or_equal:' . Carbon::now()->format('Y-m-d')],
            'time_slot' => ['required', 'date_format:H:i', Rule::in(array_keys($this->getTimeSlots()))], // Garante que o slot seja válido
            'lesson_plan' => ['required', 'string', 'min:10'],
        ]);

        // COMBINAÇÃO DA DATA E HORA (1 hora de duração padrão)
        $start_time_hour = $request->input('time_slot');
        $start_time = Carbon::parse($request->input('date') . ' ' . $start_time_hour);
        $end_time = $start_time->copy()->addHour();

        // CHECAGEM DE CONFLITO NO UPDATE
        // Ignora a própria reserva que está sendo editada
        $conflict = Reservation::where('laboratory_id', $request->laboratory_id)
            ->where('id', '!=', $reservation->id)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->where(function ($q) use ($start_time, $end_time) {
                    $q->where('start_time', '<', $end_time)
                      ->where('end_time', '>', $start_time);
                });
            })
            ->whereIn('status', ['aprovada', 'em andamento']) 
            ->exists();

        if ($conflict) {
            return back()->withInput()->withErrors(['time_slot' => 'O laboratório já está reservado (ou em processo de aprovação final) neste horário.']);
        }

        $reservation->update([
            'laboratory_id' => $request->laboratory_id,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'lesson_plan' => $request->lesson_plan
        ]);

        return redirect()->route('reservations.index')->with('success', 'Solicitação de reserva atualizada com sucesso!');
    }


    /**
     * Exibe o formulário de revisão para o Coordenador de Curso.
     */
    public function review(Reservation $reservation)
    {
        // Certifique-se de que o usuário tem permissão para gerenciar as reservas do curso
        Gate::authorize('manage-course-reservations', $reservation);

        // Ações de revisão só devem ocorrer para reservas 'pendente'
        if ($reservation->status !== 'pendente') {
             return redirect()->route('reservations.index')->with('error', 'Esta reserva já foi revisada.');
        }

        return view('reservations.review', compact('reservation'));
    }

    /**
     * Processa a decisão do coordenador sobre a reserva (Aprovar/Rejeitar).
     */
    public function process(Request $request, Reservation $reservation)
    {
        // Certifique-se de que o usuário tem permissão para gerenciar as reservas do curso
        Gate::authorize('manage-course-reservations', $reservation);

        // Ações de revisão só devem ocorrer para reservas 'pendente'
        if ($reservation->status !== 'pendente') {
             return redirect()->route('reservations.index')->with('error', 'Esta reserva já foi revisada.');
        }
        
        $action = $request->input('action');
        
        // Regra de validação de 'action'
        $rules = [
            'action' => ['required', Rule::in(['em_andamento', 'rejeitada'])], 
        ];

        if ($action === 'rejeitada') {
            $rules['rejection_feedback'] = ['required', 'string', 'min:10'];
        }

        $request->validate($rules);
        
        if ($action === 'rejeitada') {
            $reservation->status = 'rejeitada';
            $reservation->rejection_feedback = $request->input('rejection_feedback');
            $message = 'Reserva rejeitada. O professor foi notificado com o feedback.';
        } else {
            // Se for aprovada pelo Coordenador de Curso, muda para 'em andamento'
            $reservation->status = 'em andamento';
            $reservation->rejection_feedback = null;
            $message = 'Reserva aprovada pelo curso e agora está "Em Andamento".';
        }

        $reservation->save();

        return redirect()->route('reservations.index')->with('success', $message);
    }
    
    /**
     * Remove a reserva.
     */
    public function destroy(Reservation $reservation)
    {
        // Garante que APENAS o criador ou o admin pode cancelar
        Gate::authorize('modify-reservation', $reservation);

        // NOVA REGRA: Se o usuário é o criador E não é Admin E o status não é 'pendente', barra.
        $user = Auth::user();
        if ($user->id === $reservation->user_id && $user->role !== 'admin' && $reservation->status !== 'pendente') {
            abort(403, 'Você só pode cancelar reservas que estão com o status "Pendente".');
        }

        $reservation->delete();

        return redirect()->route('reservations.index')->with('success', 'Solicitação de reserva cancelada com sucesso!');
    }
}
