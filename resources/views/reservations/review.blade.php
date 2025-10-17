<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Revisar Solicitação de Reserva') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <h3 class="text-2xl font-bold mb-4">Solicitação de: {{ $reservation->user->name }}</h3>
                
                <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                    <p><strong>Laboratório:</strong> {{ $reservation->laboratory->name }}</p>
                    <p><strong>Data/Hora:</strong> {{ $reservation->start_time->format('d/m/Y \\à\\s H:i') }}</p>
                    <p class="mt-3"><strong>Roteiro de Aula:</strong></p>
                    <p class="whitespace-pre-wrap text-sm text-gray-700">{{ $reservation->lesson_plan }}</p>
                </div>
                
                {{-- Verifica se a reserva ainda está pendente, caso contrário, não exibe o formulário de decisão --}}
                @if ($reservation->status == 'pendente')
                    <h4 class="text-xl font-semibold mb-4 text-gray-700">Decisão do Coordenador de Curso</h4>
                    
                    <form method="POST" action="{{ route('reservations.process', $reservation) }}" x-data="{ action: 'em_andamento' }">
                        @csrf
                        
                        <div class="mb-4">
                            <x-label value="{{ __('Aprovação/Rejeição') }}" />
                            {{-- MUDANÇA: Valor da aprovação para 'em_andamento' --}}
                            <select name="action" x-model="action" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                <option value="em_andamento">Aprovar (Status: Em Andamento)</option>
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
                                {{ __('Finalizar Revisão') }}
                            </x-button>
                        </div>
                    </form>
                @else
                    <div class="p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg">
                        Esta reserva já foi revisada e seu status atual é **{{ ucfirst($reservation->status) }}**.
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>