<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nova Solicitação de Reserva') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Ops! Algo deu errado.</strong>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('reservations.store') }}">
                    @csrf

                    <!-- Laboratório disponível -->
                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="laboratory_id" value="{{ __('Laboratório Disponível') }}" />
                        <select id="laboratory_id" name="laboratory_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                            <option value="">Selecione um laboratório</option>
                            @foreach ($laboratories as $lab)
                                <option value="{{ $lab->id }}" {{ old('laboratory_id') == $lab->id ? 'selected' : '' }}>
                                    {{ $lab->name }} ({{ $lab->location }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="laboratory_id" class="mt-2" />
                        
                        @if ($laboratories->isEmpty())
                            <p class="mt-2 text-sm text-red-600">Nenhum laboratório cadastrado. Contate o administrador.</p>
                        @endif
                    </div>

                    <!-- Data e Horário (Novo formato com slots de 1h) -->
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 mb-4">
                        <!-- Data -->
                        <div class="w-full sm:w-1/2">
                            <x-label for="date" value="{{ __('Data da Reserva') }}" />
                            <x-input id="date" type="date" name="date" class="mt-1 block w-full" 
                                     value="{{ old('date') }}" min="{{ now()->format('Y-m-d') }}" required/>
                            <x-input-error for="date" class="mt-2" />
                        </div>
                        
                        <!-- Horário de Início (Slot de 1h) -->
                        <div class="w-full sm:w-1/2">
                            <x-label for="time_slot" value="{{ __('Horário (Intervalo de 1 hora)') }}" />
                            <select id="time_slot" name="time_slot" 
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                                <option value="">Selecione o horário de início</option>
                                {{-- A variável $timeSlots é injetada pelo ReservationController --}}
                                @foreach ($timeSlots as $value => $label)
                                    <option value="{{ $value }}" {{ old('time_slot') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error for="time_slot" class="mt-2" />
                        </div>
                    </div>

                    <!-- Roteiro de aula -->
                    <div class="col-span-6 sm:col-span-4 mb-6">
                        <x-label for="lesson_plan" value="{{ __('Roteiro de Aula (Detalhe tudo que fará/precisará)') }}" />
                        <textarea id="lesson_plan" name="lesson_plan" rows="5"
                                  class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>{{ old('lesson_plan') }}</textarea>
                        <x-input-error for="lesson_plan" class="mt-2" />
                    </div>

                    <!-- Botão de envio -->
                    <div class="flex items-center justify-end">
                        <x-button>
                            {{ __('Enviar Solicitação de Reserva') }}
                        </x-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
