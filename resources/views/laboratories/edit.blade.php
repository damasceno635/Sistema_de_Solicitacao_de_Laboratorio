<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Laboratório: ') . $laboratory->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <form method="POST" action="{{ route('admin.laboratories.update', $laboratory) }}">
                    @csrf
                    @method('PUT') <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="name" value="{{ __('Nome') }}" />
                        <x-input id="name" type="text" name="name" class="mt-1 block w-full" 
                            value="{{ old('name', $laboratory->name) }}" required autofocus />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="location" value="{{ __('Localização') }}" />
                        <x-input id="location" type="text" name="location" class="mt-1 block w-full" 
                            value="{{ old('location', $laboratory->location) }}" required />
                        <x-input-error for="location" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="capacity" value="{{ __('Capacidade de Ocupação') }}" />
                        <x-input id="capacity" type="number" name="capacity" class="mt-1 block w-full" 
                            value="{{ old('capacity', $laboratory->capacity) }}" required min="1" />
                        <x-input-error for="capacity" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="materials_supplied" value="{{ __('Materiais Fornecidos') }}" />
                        <textarea id="materials_supplied" name="materials_supplied" rows="3" 
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>{{ old('materials_supplied', $laboratory->materials_supplied) }}</textarea>
                        <x-input-error for="materials_supplied" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <label for="is_available" class="flex items-center">
                            <x-checkbox id="is_available" name="is_available" value="1" 
                                checked="{{ old('is_available', $laboratory->is_available) ? 'checked' : '' }}" />
                            <span class="ml-2 text-sm text-gray-600">{{ __('Disponível para Uso') }}</span>
                        </label>
                        <x-input-error for="is_available" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-4 mb-6">
                        <x-label for="observation" value="{{ __('Observação (Opcional)') }}" />
                        <textarea id="observation" name="observation" rows="3" 
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">{{ old('observation', $laboratory->observation) }}</textarea>
                        <x-input-error for="observation" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end">
                        <x-button>
                            {{ __('Atualizar Laboratório') }}
                        </x-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>