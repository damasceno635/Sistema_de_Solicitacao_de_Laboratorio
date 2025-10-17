<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reservas de Laboratório do Curso de ') . Auth::user()->course }}
        </h2>
    </x-slot>

    {{-- NOVO: Alerta de Erro/Ação Não Autorizada (O Pop-up de erro) --}}
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
            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Adicionei bg-white e shadow aqui para estilizar o container da tabela --}}
                
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laboratório</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Professor</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Detalhes</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reservations as $reservation)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $reservation->laboratory->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $reservation->user->name }}
                                    <p class="text-xs text-gray-400">{{ $reservation->user->email }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $color = [
                                            'pendente' => 'bg-yellow-100 text-yellow-800',
                                            'aprovada' => 'bg-green-100 text-green-800',
                                            'rejeitada' => 'bg-red-100 text-red-800',
                                            'em andamento' => 'bg-blue-100 text-blue-800',
                                        ][$reservation->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    {{-- Botão Detalhes --}}
                                    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'details-modal-{{ $reservation->id }}')" 
                                        class="text-indigo-600 hover:text-indigo-900 text-xs">
                                        Visualizar
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    {{-- Ações para o Coordenador --}}
                                    @if ($reservation->status === 'pendente' || $reservation->status === 'em andamento')
                                        <a href="{{ route('reservations.review', $reservation->id) }}" class="text-blue-600 hover:text-blue-900 text-xs">
                                            Revisar/Processar
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs">Finalizado</span>
                                    @endif
                                </td>
                            </tr>
                            
                            {{-- Modal de Detalhes --}}
                            <x-plain-modal name="details-modal-{{ $reservation->id }}" :show="false" focusable>
                                <div class="p-6">
                                    <h3 class="text-xl font-bold text-gray-900 mb-4">Detalhes da Reserva</h3>
                                    
                                    <div class="border-b pb-3 mb-3">
                                        <p class="font-semibold">Laboratório:</p>
                                        <p class="text-sm text-gray-600">{{ $reservation->laboratory->name }}</p>
                                    </div>
                                    
                                    <div class="border-b pb-3 mb-3">
                                        <p class="font-semibold">Data e Horário:</p>
                                        <p class="text-sm text-gray-600">
                                            {{ $reservation->start_time ? $reservation->start_time->format('d/m/Y \à\s H:i') : 'Data Indisponível' }}
                                            @if ($reservation->end_time)
                                                <span class="text-xs text-gray-400"> (até {{ $reservation->end_time->format('H:i') }})</span>
                                            @endif
                                        </p>
                                    </div>
                                    
                                    <div class="border-b pb-3 mb-3">
                                        <p class="font-semibold">Status:</p>
                                        <p class="text-sm text-gray-600">Status: {{ ucfirst($reservation->status) }}</p>
                                        @if ($reservation->rejection_feedback)
                                            <p class="text-xs text-red-500 mt-2">Motivo da Rejeição: {{ $reservation->rejection_feedback }}</p>
                                        @endif
                                    </div>

                                    <div class="border-b pb-3 mb-3">
                                        <p class="font-semibold">Professor Solicitante:</p>
                                        <p class="text-sm text-gray-600">{{ $reservation->user->name }}</p>
                                        <p class="text-sm text-gray-600">E-mail: {{ $reservation->user->email }}</p>
                                    </div>

                                    <div>
                                        <p class="font-semibold">Roteiro de Aula:</p>
                                        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $reservation->lesson_plan }}</p>
                                    </div>
                                    
                                    <div class="mt-6 flex justify-end">
                                        <x-secondary-button x-on:click="$dispatch('close')">
                                            {{ __('Fechar') }}
                                        </x-secondary-button>
                                    </div>
                                </div>
                            </x-plain-modal>
                        @endforeach
                    </tbody>
                </table>
            
            </div>
        </div>
    </div>
</x-app-layout>
