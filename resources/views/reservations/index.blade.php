<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Minhas Solicitações de Reserva') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                @can('create-reservations')
                    <div class="mb-4">
                        <a href="{{ route('reservations.create') }}" 
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Criar Nova Solicitação') }}
                        </a>
                    </div>
                @endcan
                
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
                                    {{-- CORREÇÃO APLICADA AQUI (LINHA QUE DAVA ERRO) --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $reservation->start_time ? $reservation->start_time->format('d/m/Y H:i') : 'Data Indisponível' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $reservation->end_time ? $reservation->end_time->format('H:i') : 'Não definido' }}
                                    </td>
                                    {{-- FIM DA CORREÇÃO NA EXIBIÇÃO DE DATAS/HORAS --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($reservation->status === 'aprovada') bg-green-100 text-green-800
                                            @elseif($reservation->status === 'em andamento') bg-blue-100 text-blue-800
                                            @elseif($reservation->status === 'rejeitada') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ ucfirst($reservation->status) }}
                                        </span>
                                    </td>                                   
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <button 
                                            class="text-blue-600 hover:text-blue-900" 
                                            x-data 
                                            x-on:click="$dispatch('open-modal', 'roteiro-{{ $reservation->id }}')">
                                            Ver Roteiro
                                        </button>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @can('modify-reservation', $reservation)
                                            <a href="{{ route('reservations.edit', $reservation) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Editar</a>
                                            <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta reserva?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                                            </form>
                                        @else
                                            N/A
                                        @endcan
                                    </td>
                                    {{-- CORREÇÃO APLICADA AQUI NA MODAL TAMBÉM --}}
                                    @if($reservation->status === 'rejeitada' && $reservation->rejection_feedback)
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <button class="text-red-600 hover:text-red-900 font-bold" x-data x-on:click="$dispatch('open-modal', 'feedback-{{ $reservation->id }}')">
                                                Ver Feedback
                                            </button>
                                        </td>                                      
                                        <x-plain-modal name="feedback-{{ $reservation->id }}" maxWidth="md">
                                            <div class="p-6">
                                                <h2 class="text-xl font-medium text-red-700">Solicitação Rejeitada</h2>
                                                
                                                <p class="mt-4 font-semibold">Feedback do Coordenador:</p>
                                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $reservation->rejection_feedback }}</p>
                                                
                                                <div class="mt-6 flex justify-end">
                                                    <x-secondary-button x-on:click="$dispatch('close')">
                                                        {{ __('Entendido') }}
                                                    </x-secondary-button>
                                                </div>
                                            </div>
                                        </x-plain-modal>
                                    @endif
                                </tr>
                                
                                <x-plain-modal name="roteiro-{{ $reservation->id }}" maxWidth="lg">
                                    <div class="p-6">
                                        <h2 class="text-lg font-medium text-gray-900">
                                            Roteiro de Aula para {{ $reservation->laboratory->name }}
                                        </h2>

                                        <div class="mt-4">
                                            <p class="font-semibold">Data e Horário:</p>
                                            {{-- CORREÇÃO APLICADA AQUI TAMBÉM --}}
                                            <p class="text-sm text-gray-600">
                                                {{ $reservation->start_time ? $reservation->start_time->format('d/m/Y \à\s H:i') : 'Data Indisponível' }}
                                            </p>
                                        </div>

                                        <div class="mt-4">
                                            <p class="font-semibold">Roteiro Detalhado:</p>
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
                            @if ($reservations->isEmpty())
                                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Nenhuma reserva encontrada.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>