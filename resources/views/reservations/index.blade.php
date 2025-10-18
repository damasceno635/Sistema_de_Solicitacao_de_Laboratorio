<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Minhas Solicitações de Reserva') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <!-- Conteúdo da página aqui -->
                <div class="mb-4">
                    <a href="{{ route('reservations.create') }}" 
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Criar Nova Solicitação') }}
                    </a>
                </div>
                
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laboratório</th>
                                @if (Auth::user()->role === 'admin')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Professor</th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Início</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fim</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Roteiro</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($reservations as $reservation)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $reservation->laboratory->name }}</td>
                                    @if (Auth::user()->role === 'admin')
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $reservation->user->name }}</td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $reservation->start_time ? $reservation->start_time->format('d/m/Y H:i') : 'Data Indisponível' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $reservation->end_time ? $reservation->end_time->format('H:i') : 'Não definido' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($reservation->status === 'aprovada') bg-green-100 text-green-800
                                                @elseif($reservation->status === 'em andamento') bg-blue-100 text-blue-800
                                                @elseif($reservation->status === 'rejeitada') bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ ucfirst($reservation->status) }}
                                            </span>
                                            <!-- Botão para ver feedback (apenas se rejeitada e tiver feedback) -->
                                            @if($reservation->status === 'rejeitada' && $reservation->rejection_feedback)
                                                <button type="button" 
                                                        onclick="openModal('feedback-{{ $reservation->id }}')"
                                                        class="ml-2 inline-flex items-center text-red-600 hover:text-red-800 text-xs font-medium">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Ver Feedback
                                                </button>
                                            @endif
                                        </div>
                                    </td>                                   
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <button type="button" 
                                                onclick="openModal('roteiro-{{ $reservation->id }}')"
                                                class="inline-flex items-center text-blue-600 hover:text-blue-900 font-medium">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Ver Roteiro
                                        </button>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if(Auth::user()->id === $reservation->user_id || Auth::user()->role === 'admin')
                                            @if(in_array($reservation->status, ['pendente', 'em andamento', 'rejeitada']) || Auth::user()->role === 'admin')
                                                <a href="{{ route('reservations.edit', $reservation) }}" class="text-indigo-600 hover:text-indigo-900 mr-3 inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    Editar
                                                </a>
                                            @endif
                                            
                                            @if(in_array($reservation->status, ['pendente', 'rejeitada']) || Auth::user()->role === 'admin')
                                                <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta reserva?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 inline-flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        Excluir
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                
                                <!-- Modal Roteiro -->
                                <div id="roteiro-{{ $reservation->id }}" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50" style="display: none;">
                                    <div class="fixed inset-0 transform transition-all">
                                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                    </div>
                                    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-2xl sm:w-full mx-auto">
                                        <div class="p-6">
                                            <div class="flex items-center justify-between mb-4">
                                                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    Roteiro de Aula
                                                </h2>
                                            </div>

                                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                                <div class="grid grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <p class="font-semibold text-gray-600">Laboratório:</p>
                                                        <p class="text-gray-800">{{ $reservation->laboratory->name }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-gray-600">Data e Horário:</p>
                                                        <p class="text-gray-800">
                                                            {{ $reservation->start_time ? $reservation->start_time->format('d/m/Y \à\s H:i') : 'Data Indisponível' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <p class="font-semibold text-gray-700 mb-2">Roteiro Detalhado:</p>
                                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                                    <p class="text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $reservation->lesson_plan }}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-6 flex justify-end">
                                                <button type="button" 
                                                        onclick="closeModal('roteiro-{{ $reservation->id }}')"
                                                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    {{ __('Fechar') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Feedback (APENAS para reservas rejeitadas com feedback) -->
                                @if($reservation->status === 'rejeitada' && $reservation->rejection_feedback)
                                    <div id="feedback-{{ $reservation->id }}" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50" style="display: none;">
                                        <div class="fixed inset-0 transform transition-all">
                                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                        </div>
                                        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-auto">
                                            <div class="p-6">
                                                <div class="flex items-center mb-4">
                                                    <div class="flex-shrink-0">
                                                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="ml-3">
                                                        <h2 class="text-xl font-bold text-red-700">Solicitação Rejeitada</h2>
                                                    </div>
                                                </div>

                                                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4 rounded">      
                                                    <div>
                                                        <p class="font-semibold text-red-700 mb-2">Observações do Coordenador:</p>
                                                        <div class="bg-white border border-red-200 rounded p-3">
                                                            <p class="text-gray-700 leading-relaxed text-sm whitespace-pre-line break-words"> 
                                                                {!! nl2br(e(trim($reservation->rejection_feedback))) !!}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                                                    <div class="flex">
                                                        <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <p class="text-sm text-blue-700">
                                                            <strong>Dica:</strong> Considere estas observações ao editar e reenviar sua solicitação.
                                                        </p>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex justify-between items-center mt-6">
                                                    <a href="{{ route('reservations.edit', $reservation) }}" 
                                                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                        Editar Solicitação
                                                    </a>
                                                    
                                                    <button type="button" 
                                                            onclick="closeModal('feedback-{{ $reservation->id }}')"
                                                            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        Fechar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            @if ($reservations->isEmpty())
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-500 text-lg">Nenhuma reserva encontrada.</p>
                                        <p class="text-gray-400 text-sm mt-1">Clique em "Criar Nova Solicitação" para fazer sua primeira reserva.</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Fechar modal com ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('[id^="roteiro-"], [id^="feedback-"]');
                modals.forEach(modal => {
                    if (modal.style.display === 'block') {
                        closeModal(modal.id);
                    }
                });
            }
        });
    </script>
</x-app-layout>