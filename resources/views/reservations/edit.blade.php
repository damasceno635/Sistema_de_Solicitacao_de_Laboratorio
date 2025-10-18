<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Solicitação de Reserva') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4" role="alert">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <strong class="font-bold text-red-700">Ops! Algo deu errado.</strong>
                        </div>
                        <ul class="mt-2 list-disc list-inside text-red-600 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Mostrar feedback de rejeição se disponível -->
                @if($reservation->status === 'rejeitada' && $reservation->rejection_feedback)
                    <div class="mb-6 bg-gradient-to-r from-red-50 to-orange-50 border-l-4 border-red-500 rounded-lg p-5 shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-lg font-semibold text-red-700 mb-2">Feedback da Rejeição</h3>
                                <div class="bg-white border border-red-100 rounded-lg p-4 mb-3">
                                    <p class="text-gray-700 leading-relaxed whitespace-pre-line break-words">
                                        {!! nl2br(e(trim($reservation->rejection_feedback))) !!}
                                    </p>
                                </div>
                                <div class="flex items-center text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Por favor, revise sua solicitação considerando o feedback acima antes de reenviar.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('reservations.update', $reservation->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <x-label for="laboratory_id" value="{{ __('Laboratório Disponível') }}" class="block text-sm font-medium text-gray-700 mb-1" />
                            <select id="laboratory_id" name="laboratory_id"
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm transition duration-150 ease-in-out"
                                required>
                                <option value="">Selecione um laboratório</option>
                                @foreach ($laboratories as $lab)
                                    <option value="{{ $lab->id }}"
                                        {{ old('laboratory_id', $reservation->laboratory_id) == $lab->id ? 'selected' : '' }}>
                                        {{ $lab->name }} ({{ $lab->location }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error for="laboratory_id" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Data -->
                            <div>
                                <x-label for="reservation_date" value="{{ __('Data da Reserva') }}" class="block text-sm font-medium text-gray-700 mb-1" />
                                <x-input id="reservation_date" type="date" name="reservation_date" class="w-full" 
                                    value="{{ old('reservation_date', $reservation->start_time->format('Y-m-d')) }}" required/>
                                <x-input-error for="reservation_date" class="mt-2" />
                            </div>
                            
                            <!-- Horário de Início (Slot de 1h) -->
                            <div>
                                <x-label for="time_slot" value="{{ __('Horário') }}" class="block text-sm font-medium text-gray-700 mb-1" />
                                <select id="time_slot" name="time_slot" 
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm transition duration-150 ease-in-out" required>
                                    <option value="">Selecione o horário</option>
                                    @php
                                        $currentSlot = $reservation->start_time->format('H:i');
                                    @endphp
                                    @foreach ($timeSlots as $value => $label)
                                        <option value="{{ $value }}" 
                                            {{ old('time_slot', $currentSlot) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error for="time_slot" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <x-label for="lesson_plan" value="{{ __('Roteiro de Aula') }}" class="block text-sm font-medium text-gray-700 mb-2" />
                        <div class="bg-gray-50 rounded-lg p-3 mb-2">
                            <p class="text-sm text-gray-600 flex items-center">
                                <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Detalhe tudo que fará e precisará no laboratório
                            </p>
                        </div>
                        <textarea id="lesson_plan" name="lesson_plan" rows="6"
                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm transition duration-150 ease-in-out"
                            placeholder="Descreva detalhadamente as atividades, materiais necessários, equipamentos que utilizará, etc."
                            required>{{ old('lesson_plan', $reservation->lesson_plan) }}</textarea>
                        <x-input-error for="lesson_plan" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('reservations.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Voltar
                        </a>
                        
                        <x-button class="bg-indigo-600 hover:bg-indigo-700 px-6 py-3 rounded-lg font-semibold">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ __('Atualizar Solicitação') }}
                        </x-button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        // Validação em tempo real do roteiro de aula
        document.addEventListener('DOMContentLoaded', function() {
            const lessonPlan = document.getElementById('lesson_plan');
            const charCount = document.createElement('div');
            charCount.className = 'text-xs text-gray-500 mt-1 text-right';
            lessonPlan.parentNode.appendChild(charCount);

            function updateCharCount() {
                const length = lessonPlan.value.length;
                charCount.textContent = `${length} caracteres${length < 10 ? ' (mínimo 10)' : ''}`;
                
                if (length < 10) {
                    charCount.classList.add('text-red-500');
                    charCount.classList.remove('text-gray-500');
                } else {
                    charCount.classList.remove('text-red-500');
                    charCount.classList.add('text-gray-500');
                }
            }

            lessonPlan.addEventListener('input', updateCharCount);
            updateCharCount(); // Initial call
        });
    </script>
</x-app-layout>