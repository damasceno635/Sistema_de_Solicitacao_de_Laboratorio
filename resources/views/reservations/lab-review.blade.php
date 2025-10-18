<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Revisar Solicitação de Reserva (Nível Laboratório)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <h3 class="text-2xl font-bold mb-4">Solicitação de: {{ $reservation->user->name }} (Curso: {{ $reservation->user->course }})</h3>
                
                <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                    <p><strong>Status do Curso:</strong> <span class="text-blue-600 font-semibold">Em Andamento</span></p>
                    <p><strong>Laboratório:</strong> {{ $reservation->laboratory->name }}</p>
                    <p><strong>Data/Hora:</strong> {{ $reservation->start_time->format('d/m/Y \\à\\s H:i') }}</p>
                    <p class="mt-3"><strong>Roteiro de Aula:</strong></p>
                    <p class="whitespace-pre-wrap text-sm text-gray-700">{{ $reservation->lesson_plan }}</p>
                    
                    @if ($reservation->rejection_feedback)
                        <div class="mt-4 p-3 bg-red-100 border border-red-400 rounded-md">
                            <p class="font-semibold text-red-700">Feedback de Rejeição Anterior:</p>
                            <p class="text-sm text-red-600 whitespace-pre-wrap">{{ $reservation->rejection_feedback }}</p>
                        </div>
                    @endif
                </div>
                
                {{-- Verifica se a reserva está 'em andamento' (aprovada pelo curso e aguardando lab) --}}
                @if ($reservation->status == 'em andamento')
                    <h4 class="text-xl font-semibold mb-4 text-gray-700">Decisão do Coordenador de Laboratório (2º Nível - Final)</h4>
                    
                    {{-- Ação agora é labProcess --}}
                    <form method="POST" action="{{ route('reservations.labProcess', $reservation->id) }}" x-data="{ action: 'aprovada' }">
                        @csrf
                        
                        <div class="mb-4">
                            <x-label for="action" value="{{ __('Ação') }}" />
                            <select id="action" name="action" x-model="action"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                {{-- APROVAR AQUI MUDA O STATUS PARA 'APROVADA' (FINAL) --}}
                                <option value="aprovada">Aprovar (Confirmação Final)</option>
                                <option value="rejeitada">Rejeitar</option>
                            </select>
                        </div>

                        {{-- O feedback de rejeição só aparece se a ação for 'rejeitada' --}}
                        <div class="mb-6" x-show="action === 'rejeitada'">
                            <x-label for="rejection_feedback" value="{{ __('Feedback de Rejeição (Obrigatório)') }}" />
                            <textarea id="rejection_feedback" name="rejection_feedback" rows="4" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" 
                                placeholder="Informe o motivo da rejeição para o professor.">{{ old('rejection_feedback') }}</textarea>
                            <x-input-error for="rejection_feedback" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end">
                            <x-button class="bg-indigo-600 hover:bg-indigo-700">
                                {{ __('Finalizar Aprovação do Laboratório') }}
                            </x-button>
                        </div>
                    </form>
                @else
                    <div class="p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg">
                        Esta reserva não está no status 'Em Andamento' para revisão do laboratório. Status atual: **{{ ucfirst($reservation->status) }}**.
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>