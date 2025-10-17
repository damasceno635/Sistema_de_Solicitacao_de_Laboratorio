<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Criar Novo Usuário') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                <x-validation-errors class="mb-4" />

                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf

                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="role" value="{{ __('Tipo de Usuário') }}" />
                        <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Selecione a Função</option>
                            <option value="coordenador_curso" {{ old('role') == 'coordenador_curso' ? 'selected' : '' }}>Coordenador de Curso</option>
                            <option value="professor" {{ old('role') == 'professor' ? 'selected' : '' }}>Professor</option>
                        </select>
                    </div>

                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="name" value="{{ __('Nome') }}" />
                        <x-input id="name" type="text" class="mt-1 block w-full" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    </div>

                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="email" value="{{ __('Email') }}" />
                        <x-input id="email" type="email" class="mt-1 block w-full" name="email" :value="old('email')" required autocomplete="username" />
                    </div>

                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="password" value="{{ __('Senha') }}" />
                        <x-input id="password" type="password" class="mt-1 block w-full" name="password" required autocomplete="new-password" />
                    </div>

                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="password_confirmation" value="{{ __('Confirmar Senha') }}" />
                        <x-input id="password_confirmation" type="password" class="mt-1 block w-full" name="password_confirmation" required autocomplete="new-password" />
                    </div>

                    <hr class="my-6 border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Dados Adicionais</h3>

                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="course" value="{{ __('Curso') }}" />
                        <x-input id="course" type="text" class="mt-1 block w-full" name="course" :value="old('course')" />
                        <p class="mt-1 text-sm text-gray-500">Obrigatório para Coordenador de Curso e Professor.</p>
                    </div>

                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="discipline" value="{{ __('Disciplina') }}" />
                        <x-input id="discipline" type="text" class="mt-1 block w-full" name="discipline" :value="old('discipline')" />
                        <p class="mt-1 text-sm text-gray-500">Obrigatório para Professor.</p>
                    </div>

                    <div class="col-span-6 sm:col-span-4 mb-4">
                        <x-label for="period" value="{{ __('Período/Turma') }}" />
                        <x-input id="period" type="text" class="mt-1 block w-full" name="period" :value="old('period')" />
                        <p class="mt-1 text-sm text-gray-500">Obrigatório para Professor.</p>
                    </div>

                    <x-button class="mt-4">
                        {{ __('Criar Usuário') }}
                    </x-button>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>