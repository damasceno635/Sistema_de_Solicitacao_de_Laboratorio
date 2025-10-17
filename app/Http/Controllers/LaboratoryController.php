<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class LaboratoryController extends Controller
{
    /**
     * Exibe a lista de laboratórios (Acessível por todos).
     */
    public function index()
    {
        // Puxa todos os laboratórios do banco de dados
        $laboratories = Laboratory::all();
        
        // Passa os dados e a permissão de admin para a view
        return view('laboratories.index', [
            'laboratories' => $laboratories,
            // Verifica se o usuário logado tem permissão para gerenciar (usado no Front-end)
            'can_manage' => Gate::allows('manage-users'), 
        ]);
    }

    /**
     * Mostra o formulário de criação de laboratório (APENAS ADMIN).
     */
    public function create()
    {
        // O middleware de rota 'can:manage-users' já protege isso,
        // mas é bom ter uma checagem aqui também, caso a rota seja chamada de outra forma.
        /*if (Gate::denies('manage-users')) {
            abort(403); 
        }*/

        return view('laboratories.create');
    }

    /**
     * Armazena o novo laboratório no banco de dados.
     */
    public function store(Request $request)
    {
        // Validação dos dados de entrada
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:laboratories'],
            'location' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'materials_supplied' => ['required', 'string'],
            'is_available' => ['sometimes', 'boolean'],
            'observation' => ['nullable', 'string'],
        ]);
        
        // Trata o checkbox 'is_available' para garantir que é booleano (true ou false)
        $validatedData['is_available'] = $request->has('is_available');

        Laboratory::create($validatedData);

        return redirect()->route('laboratories.index')->with('success', 'Laboratório criado com sucesso!');
    }

    // Não precisamos do método show() por enquanto, a listagem já é suficiente.
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Mostra o formulário de edição para um laboratório específico.
     */
    public function edit(Laboratory $laboratory)
    {
        return view('laboratories.edit', compact('laboratory'));
    }

    /**
     * Atualiza o laboratório no banco de dados.
     */
    public function update(Request $request, Laboratory $laboratory)
    {
        // Validação dos dados de entrada
        $validatedData = $request->validate([
            // Regra 'unique' modificada para ignorar o próprio laboratório que está sendo atualizado
            'name' => ['required', 'string', 'max:255', Rule::unique('laboratories')->ignore($laboratory->id)],
            'location' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'materials_supplied' => ['required', 'string'],
            'is_available' => ['sometimes', 'boolean'],
            'observation' => ['nullable', 'string'],
        ]);
        
        // Trata o checkbox 'is_available'
        $validatedData['is_available'] = $request->has('is_available');

        $laboratory->update($validatedData);

        return redirect()->route('laboratories.index')->with('success', 'Laboratório atualizado com sucesso!');
    }

    /**
     * Remove o laboratório do banco de dados.
     */
    public function destroy(Laboratory $laboratory)
    {
        $laboratory->delete();

        return redirect()->route('laboratories.index')->with('success', 'Laboratório excluído com sucesso!');
    }
}
