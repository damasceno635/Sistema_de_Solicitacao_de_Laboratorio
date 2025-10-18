<?php

// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate; // Importar a facade Gate
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Mostra o formulário de criação de usuário.
     */
    public function create()
    {
        // **USANDO O GATE**
        if (Gate::denies('manage-users')) {
            abort(403, 'Acesso não autorizado. Você não é um administrador.');
        }

        return view('admin.users.create');
    }

    /**
     * Armazena o novo usuário no banco de dados.
     */
    public function store(Request $request)
    {
        // **USANDO O GATE**
        if (Gate::denies('manage-users')) {
            abort(403, 'Acesso não autorizado. Você não é um administrador.');
        }

        // ... o restante da lógica de validação e criação ...

        $rules = [
            // ... (suas regras de validação)
        ];

        // ... (sua lógica de validação condicional)

        $request->validate($rules);

        // REGRAS DE VALIDAÇÃO OBRIGATÓRIAS
        $rules = [
            'role' => 'required|in:coordenador_curso,professor',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'course' => 'required|string|max:255', // OBRIGATÓRIO PARA TODOS
        ];

        // CAMPOS OBRIGATÓRIOS APENAS PARA PROFESSORES
        if ($request->role === 'professor') {
            $rules['discipline'] = 'required|string|max:255';
            $rules['period'] = 'required|string|max:255';
        }

        // VALIDAÇÃO
        $request->validate($rules);

        // 4. Criação do Usuário
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' =>  $request->input('role'),
            'course' => $request->course,
            // Campos que só devem ser preenchidos se o usuário for Professor
            'discipline' => ($request->role === 'professor') ? $request->discipline : null,
            'period' => ($request->role === 'professor') ? $request->period : null,
        ]);

        return redirect()->route('dashboard')->with('success', 'Usuário criado com sucesso!');
    }
}