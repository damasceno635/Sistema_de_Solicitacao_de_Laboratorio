<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laboratórios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                @can('manage-users')
                    <div class="mb-4">
                        <a href="{{ route('admin.laboratories.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Criar Novo Laboratório') }}
                        </a>
                    </div>
                @endcan

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacidade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Detalhes</th>
                                @can('manage-users')
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($laboratories as $laboratory)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $laboratory->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $laboratory->location }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $laboratory->capacity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $laboratory->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $laboratory->is_available ? 'Disponível' : 'Ocupado/Manutenção' }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <button 
                                            class="text-blue-600 hover:text-blue-900" 
                                            x-data 
                                            x-on:click="$dispatch('open-modal', 'details-{{ $laboratory->id }}')">
                                            Visualizar
                                        </button>
                                    </td>
                                    
                                    @can('manage-users')
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.laboratories.edit', $laboratory) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Editar</a>
                                            <form action="{{ route('admin.laboratories.destroy', $laboratory) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                                            </form>
                                        </td>
                                    @endcan
                                </tr>
                                
                                <x-plain-modal name="details-{{ $laboratory->id }}">
                                    <div class="p-6">
                                        <h2 class="text-lg font-medium text-gray-900">
                                            Detalhes do Laboratório: {{ $laboratory->name }}
                                        </h2>

                                        <div class="mt-4">
                                            <p class="font-semibold">Materiais Fornecidos:</p>
                                            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $laboratory->materials_supplied }}</p>
                                        </div>

                                        <div class="mt-4">
                                            <p class="font-semibold">Observação:</p>
                                            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $laboratory->observation ?? 'Nenhuma observação registrada.' }}</p>
                                        </div>
                                        
                                        <div class="mt-6 flex justify-end">
                                            <x-secondary-button x-on:click="$dispatch('close')">
                                                {{ __('Fechar') }}
                                            </x-secondary-button>
                                        </div>
                                    </div>
                                </x-plain-modal>
                            @endforeach
                            @if ($laboratories->isEmpty())
                                <tr><td colspan="{{ Gate::allows('manage-users') ? '6' : '5' }}" class="px-6 py-4 text-center text-gray-500">Nenhum laboratório cadastrado.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <p class="mt-4 text-sm text-gray-600">
                    * Todos os Professores e Coordenadores de Curso podem visualizar todas as informações, incluindo os detalhes.
                </p>

            </div>
        </div>
    </div>
</x-app-layout>