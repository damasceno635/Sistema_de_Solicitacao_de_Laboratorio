<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;

class ReservationController extends Controller
{
    /**
     * Gera os horários de reserva disponíveis (intervalos de 1 hora).
     * @return array
     */
    protected function getTimeSlots()
    {
        $slots = [];
        $start = Carbon::createFromTime(7, 0, 0);
        $end = Carbon::createFromTime(22, 0, 0);

        while ($start->lessThan($end)) {
            $slotStart = $start->format('H:i');
            $slotEnd = $start->copy()->addHour()->format('H:i');
            
            if ($start->copy()->addHour()->greaterThan($end)) {
                break;
            }
            
            $slots[$slotStart] = "{$slotStart} - {$slotEnd}";
            $start->addHour();
        }

        return $slots;
    }
    
    /**
     * Exibe a lista de reservas, filtrada por perfil.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            $reservations = Reservation::with(['laboratory', 'user'])
                ->whereIn('status', ['em andamento', 'aprovada'])
                ->orderBy('start_time', 'asc')
                ->get();
                
            return view('reservations.index-coordinator-lab', ['reservations' => $reservations, 'isLabAdmin' => true]);
            
        } elseif ($user->role === 'coordenador_curso') {
            $reservations = Reservation::with(['laboratory', 'user'])
                ->where('status', 'pendente')
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('course', $user->course);
                })
                ->orderBy('start_time', 'asc')
                ->get();
                
            return view('reservations.index-coordinator', ['reservations' => $reservations, 'isCourseCoordinator' => true]);
            
        } else {
            // Professor vê apenas suas próprias reservas
            $reservations = Reservation::with('laboratory')
                ->where('user_id', $user->id)
                ->orderBy('start_time', 'asc')
                ->get();
                
            return view('reservations.index', ['reservations' => $reservations]);
        }
    }

    /**
     * Exibe o formulário de criação de nova reserva.
     */
    public function create()
    {
        $user = Auth::user();
        // Permissão: Apenas Professor e Admin podem criar
        if ($user->role !== 'professor' && $user->role !== 'admin') {
            abort(403, 'Você não tem permissão para criar uma reserva.');
        }

        $laboratories = Laboratory::all();
        $timeSlots = $this->getTimeSlots();

        return view('reservations.create', compact('laboratories', 'timeSlots'));
    }

    /**
     * Salva a nova reserva no banco de dados.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        // Permissão: Apenas Professor e Admin podem criar
        if ($user->role !== 'professor' && $user->role !== 'admin') {
            abort(403, 'Você não tem permissão para submeter esta reserva.');
        }
        
        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'reservation_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'time_slot' => ['required', Rule::in(array_keys($this->getTimeSlots()))],
            'lesson_plan' => 'required|string|min:10|max:1000',
        ]);

        $dateTime = Carbon::parse("{$validated['reservation_date']} {$validated['time_slot']}", config('app.timezone'));
        $endTime = $dateTime->copy()->addHour();

        // Checagem de Conflito
        $existingReservation = Reservation::where('laboratory_id', $validated['laboratory_id'])
            ->where('start_time', $dateTime)
            ->whereIn('status', ['pendente', 'em andamento', 'aprovada'])
            ->first();

        if ($existingReservation) {
            return back()->withInput()->withErrors([
                'reservation_date' => 'Já existe uma reserva para o laboratório, data e horário selecionados.',
            ]);
        }

        $reservation = new Reservation([
            'user_id' => $user->id,
            'laboratory_id' => $validated['laboratory_id'],
            'start_time' => $dateTime,
            'end_time' => $endTime,
            'lesson_plan' => $validated['lesson_plan'],
            'status' => 'pendente',
        ]);

        $reservation->save();

        return redirect()->route('reservations.index')->with('success', 'Solicitação de reserva enviada com sucesso! Aguardando revisão.');
    }

    /**
     * Exibe a reserva específica.
     */
    public function show(Reservation $reservation)
    {
        $user = Auth::user();

        $isCreator = $user->id === $reservation->user_id;
        $isAdmin = $user->role === 'admin';
        $isCourseCoordinator = $user->role === 'coordenador_curso' && $user->course === $reservation->user->course;

        if (!$isCreator && !$isAdmin && !$isCourseCoordinator) {
            abort(403, 'Você não tem permissão para visualizar esta reserva.');
        }

        return view('reservations.show', compact('reservation'));
    }

    /**
     * Exibe o formulário de edição da reserva.
     */
    public function edit(Reservation $reservation)
{
    $user = Auth::user();
    
    // APENAS o criador ou o admin podem editar
    if ($user->id !== $reservation->user_id && $user->role !== 'admin') {
        abort(403, 'Você não tem permissão para editar esta reserva.');
    }

    // CORREÇÃO: Permitir edição de reservas rejeitadas também
    if ($user->id === $reservation->user_id && !in_array($reservation->status, ['pendente', 'em andamento', 'rejeitada'])) {
        abort(403, 'Você só pode editar reservas com status "Pendente", "Em Andamento" ou "Rejeitada".');
    }

    $laboratories = Laboratory::all();
    $timeSlots = $this->getTimeSlots();
    $currentSlot = $reservation->start_time->format('H:i');

    return view('reservations.edit', compact('reservation', 'laboratories', 'timeSlots', 'currentSlot'));
}

    /**
     * Atualiza a reserva no banco de dados.
     */
    public function update(Request $request, Reservation $reservation)
{
    $user = Auth::user();
    
    // APENAS o criador ou o admin podem atualizar
    if ($user->id !== $reservation->user_id && $user->role !== 'admin') {
        abort(403, 'Você não tem permissão para atualizar esta reserva.');
    }

    // CORREÇÃO: Permitir atualização de reservas rejeitadas também
    if ($user->id === $reservation->user_id && !in_array($reservation->status, ['pendente', 'em andamento', 'rejeitada'])) {
        abort(403, 'Você não pode atualizar uma reserva que já foi aprovada.');
    }

    $validated = $request->validate([
        'laboratory_id' => 'required|exists:laboratories,id',
        'reservation_date' => 'required|date_format:Y-m-d|after_or_equal:today',
        'time_slot' => ['required', Rule::in(array_keys($this->getTimeSlots()))],
        'lesson_plan' => 'required|string|min:10|max:1000',
    ]);

    $dateTime = Carbon::parse("{$validated['reservation_date']} {$validated['time_slot']}", config('app.timezone'));
    $endTime = $dateTime->copy()->addHour();
    
    // Checagem de Conflito (Excluindo a reserva atual)
    $existingReservation = Reservation::where('laboratory_id', $validated['laboratory_id'])
        ->where('start_time', $dateTime)
        ->where('id', '!=', $reservation->id)
        ->whereIn('status', ['pendente', 'em andamento', 'aprovada'])
        ->first();

    if ($existingReservation) {
        return back()->withInput()->withErrors([
            'reservation_date' => 'Já existe uma reserva para o laboratório, data e horário selecionados.',
        ]);
    }

    // CORREÇÃO: Se o professor edita uma reserva 'rejeitada', ela volta para 'pendente'
    $newStatus = (in_array($reservation->status, ['em andamento', 'rejeitada']) && $user->id === $reservation->user_id) 
                 ? 'pendente' 
                 : $reservation->status;
    
    $reservation->update([
        'laboratory_id' => $validated['laboratory_id'],
        'start_time' => $dateTime,
        'end_time' => $endTime,
        'lesson_plan' => $validated['lesson_plan'],
        'status' => $newStatus,
        'rejection_feedback' => null, // Limpa o feedback ao reenviar
    ]);

    return redirect()->route('reservations.index')->with('success', 'Solicitação de reserva atualizada com sucesso!');
}

    // -------------------------------------------------------------------------------------------------------------------
    // FUNÇÕES DE REVISÃO DO COORDENADOR DE CURSO (1º Nível)
    // -------------------------------------------------------------------------------------------------------------------

    /**
     * Exibe o formulário de revisão para o Coordenador de Curso.
     */
    public function review(Reservation $reservation)
    {
        $user = Auth::user();
        
        // Autorização: Apenas Coordenador de Curso E do curso do professor solicitante
        if ($user->role !== 'coordenador_curso' || $user->course !== $reservation->user->course) {
            abort(403, 'Você não tem permissão para revisar esta reserva, pois não é o Coordenador do Curso.');
        }

        return view('reservations.review', compact('reservation'));
    }

    /**
     * Processa a decisão do Coordenador de Curso (Aprovar/Rejeitar).
     */
    public function courseProcess(Request $request, Reservation $reservation)
    {
        $user = Auth::user();
        
        // Autorização: Apenas Coordenador de Curso E do curso do professor solicitante
        if ($user->role !== 'coordenador_curso' || $user->course !== $reservation->user->course) {
            abort(403, 'Você não tem permissão para processar esta reserva.');
        }
        
        // A reserva deve estar PENDENTE para ser processada pelo Coordenador de Curso
        if ($reservation->status !== 'pendente') {
            return redirect()->route('reservations.index')->with('error', 'Esta reserva já foi revisada pelo curso e não está mais pendente.');
        }
        
        $action = $request->input('action');
        
        // Regras de validação
        $rules = [
            'action' => ['required', Rule::in(['aprovada', 'rejeitada'])],
        ];

        // Se for rejeitada, o feedback é obrigatório
        if ($action === 'rejeitada') {
            $rules['rejection_feedback'] = 'required|string|min:10';
        }

        $request->validate($rules);
        
        if ($action === 'rejeitada') {
            $reservation->status = 'rejeitada';
            $reservation->rejection_feedback = $request->input('rejection_feedback');
            $message = 'Reserva rejeitada pelo Coordenador de Curso. O professor foi notificado com o feedback.';
        } else {
            // Se for aprovada pelo Coordenador de Curso, muda para 'em andamento'
            $reservation->status = 'em andamento';
            $reservation->rejection_feedback = null;
            $message = 'Reserva aprovada pelo curso e agora está "Em Andamento" (aguardando aprovação final do laboratório).';
        }

        $reservation->save();

        // Redireciona de volta para a listagem do Coordenador de Curso
        return redirect()->route('reservations.index')->with('success', $message);
    }
    
    // -------------------------------------------------------------------------------------------------------------------
    // FUNÇÕES DE REVISÃO DO COORDENADOR DE LABORATÓRIO (2º Nível - Admin)
    // -------------------------------------------------------------------------------------------------------------------
    
    /**
     * Exibe o formulário de revisão para o Coordenador de Laboratório (Admin).
     */
    public function labReview(Reservation $reservation)
    {
        $user = Auth::user();
        
        // Autorização: Apenas Admin (Coordenador de Laboratório)
        if ($user->role !== 'admin') {
            abort(403, 'Você não tem permissão para acessar a revisão de laboratório.');
        }
        
        // A reserva deve estar 'em andamento' para ser processada pelo Coordenador de Laboratório
        if ($reservation->status !== 'em andamento') {
             return redirect()->route('reservations.index')->with('error', 'Esta reserva não está "Em Andamento" e não pode ser revisada nesta etapa.');
        }

        return view('reservations.lab-review', compact('reservation'));
    }

    /**
     * Processa a decisão do Coordenador de Laboratório (Aprovar/Rejeitar FINAL).
     */
    public function labProcess(Request $request, Reservation $reservation)
    {
        $user = Auth::user();
        
        // Autorização: Apenas Admin (Coordenador de Laboratório)
        if ($user->role !== 'admin') {
            abort(403, 'Você não tem permissão para processar a reserva nesta etapa.');
        }

        // A reserva deve estar 'em andamento' para ser processada pelo Coordenador de Laboratório
        if ($reservation->status !== 'em andamento') {
            return redirect()->route('reservations.index')->with('error', 'Esta reserva não está "Em Andamento" e não pode ser processada nesta etapa.');
        }
        
        $action = $request->input('action');
        
        // Regras de validação
        $rules = [
            'action' => ['required', Rule::in(['aprovada', 'rejeitada'])],
        ];

        // Se for rejeitada, o feedback é obrigatório
        if ($action === 'rejeitada') {
            $rules['rejection_feedback'] = 'required|string|min:10';
        }

        $request->validate($rules);
        
        if ($action === 'rejeitada') {
            $reservation->status = 'rejeitada';
            $reservation->rejection_feedback = $request->input('rejection_feedback');
            $message = 'Reserva rejeitada. O professor foi notificado com o feedback do laboratório.';
        } else {
            // APROVAÇÃO FINAL
            $reservation->status = 'aprovada';
            $reservation->rejection_feedback = null;
            $message = 'Reserva aprovada e confirmada pelo Coordenador de Laboratório.';
        }

        $reservation->save();

        // O Admin retorna para a view de Lab Coordinator (index genérica, que ele verá o status)
        return redirect()->route('reservations.index')->with('success', $message);
    }

    /**
     * Remove a reserva.
     */
    public function destroy(Reservation $reservation)
{
    $user = Auth::user();
    
    // APENAS o criador ou o admin podem cancelar/deletar
    if ($user->id !== $reservation->user_id && $user->role !== 'admin') {
        abort(403, 'Você não tem permissão para cancelar esta reserva.');
    }

    // CORREÇÃO: Permitir que professores excluam reservas rejeitadas também
    if ($user->id === $reservation->user_id && !in_array($reservation->status, ['pendente', 'rejeitada'])) {
        abort(403, 'Você só pode cancelar reservas próprias com status "Pendente" ou "Rejeitada".');
    }

    $reservation->delete();

    return redirect()->route('reservations.index')->with('success', 'Solicitação de reserva cancelada com sucesso!');
}
}