<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Revisão de Reservas do Curso de ') . Auth::user()->course . ' (1º Nível)' }}
        </h2>
    </x-slot>

    {{-- Alertas de Mensagens --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            
            {{-- Mensagem de sucesso (mantida) --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Professor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Laboratório
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data/Horário
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reservations as $reservation)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $reservation->user->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $reservation->laboratory->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $reservation->start_time->format('d/m/Y \à\s H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if ($reservation->status === 'pendente') bg-yellow-100 text-yellow-800
                                        @elseif ($reservation->status === 'em andamento') bg-blue-100 text-blue-800
                                        @elseif ($reservation->status === 'aprovada') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center space-x-2">
                                        <!-- Botão Ver Detalhes (Modal) -->
                                        <button type="button" 
                                                onclick="openModal('view-details-{{ $reservation->id }}')"
                                                class="inline-flex items-center px-3 py-1 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            {{ __('Ver Detalhes') }}
                                        </button>

                                        @if ($reservation->status === 'pendente')
                                            <!-- Botão Revisar (Aprovar/Rejeitar) -->
                                            <a href="{{ route('reservations.review', $reservation->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                {{ __('Revisar') }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if ($reservations->isEmpty())
                            <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Nenhuma reserva para revisão do curso no momento.</td></tr>
                        @endif
                    </tbody>
                </table>
            
            </div>
        </div>
    </div>
    
    {{-- MODAIS DE DETALHES (FORA DA TABELA) --}}
    @foreach ($reservations as $reservation)
        <div id="view-details-{{ $reservation->id }}" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50" style="display: none;">
            <div class="fixed inset-0 transform transition-all">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-auto">
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Detalhes da Solicitação de Reserva #{{ $reservation->id }}</h3>
                    
                    <div class="border-b pb-3 mb-3">
                        <p class="font-semibold">Informações da Reserva:</p>
                        <p class="text-sm text-gray-600">Laboratório: {{ $reservation->laboratory->name }}</p>
                        <p class="text-sm text-gray-600">Data/Hora: {{ $reservation->start_time->format('d/m/Y \à\s H:i') }}</p>
                        <p class="text-sm text-gray-600">Status Atual: <span class="font-bold">{{ ucfirst($reservation->status) }}</span></p>
                        
                        @if ($reservation->rejection_feedback)
                            <p class="text-sm text-red-600 mt-2">Motivo da Rejeição: {{ $reservation->rejection_feedback }}</p>
                        @endif
                    </div>

                    <div class="border-b pb-3 mb-3">
                        <p class="font-semibold">Professor Solicitante:</p>
                        <p class="text-sm text-gray-600">{{ $reservation->user->name }}</p>
                        <p class="text-sm text-gray-600">E-mail: {{ $reservation->user->email }}</p>
                        <p class="text-sm text-gray-600">Curso: {{ $reservation->user->course }}</p>
                    </div>

                    <div>
                        <p class="font-semibold">Roteiro de Aula:</p>
                        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $reservation->lesson_plan }}</p>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="button" 
                                onclick="closeModal('view-details-{{ $reservation->id }}')"
                                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Fechar') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Fechar modal ao clicar fora
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
                event.target.style.display = 'none';
            }
        });
    </script>
</x-app-layout>