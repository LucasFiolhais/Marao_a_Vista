<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UtilizadoresController extends Controller
{
    /**
     * LISTAR UTILIZADORES (GET)
     * Devolve utilizadores + roles do Spatie
     */
    public function index()
    {
        $users = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Transformar para enviar apenas o necessário (incluindo nomes dos roles)
        $users->getCollection()->transform(function ($user) {
            return [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'created_at' => $user->created_at,
                // Collection de strings: ['admin', 'cliente', ...]
                'roles'      => $user->getRoleNames(),
            ];
        });

        return response()->json($users);
    }

    /**
     * CRIAR UTILIZADOR (POST)
     * Cria user e atribui roles do Spatie
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            // agora é array de roles (nomes Spatie)
            'roles'    => 'nullable|array',
            'roles.*'  => 'string|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        if (!empty($data['roles'])) {
            // Atribui/sincroniza roles Spatie
            $user->syncRoles($data['roles']);
        }

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'created_at' => $user->created_at,
            'roles'      => $user->getRoleNames(),
        ], 201);
    }

    /**
     * MOSTRAR UM UTILIZADOR ESPECÍFICO (GET /id)
     * Inclui roles do Spatie
     */
    public function show(User $user)
    {
        $user->load('roles');

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'created_at' => $user->created_at,
            'roles'      => $user->getRoleNames(),
        ]);
    }

    /**
     * ATUALIZAR UTILIZADOR (PUT/PATCH /id)
     * Atualiza dados + roles do Spatie
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => "sometimes|email|unique:users,email,{$user->id}",
            'password' => 'sometimes|min:6',
            'roles'    => 'nullable|array',
            'roles.*'  => 'string|exists:roles,name',
        ]);

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }

        if (array_key_exists('email', $data)) {
            $user->email = $data['email'];
        }

        if (array_key_exists('password', $data)) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();

        // Se o front enviar roles, sincronizamos
        if (array_key_exists('roles', $data)) {
            // Se vier [] limpa os roles, se vierem alguns, atualiza
            $user->syncRoles($data['roles'] ?? []);
        }

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'created_at' => $user->created_at,
            'roles'      => $user->getRoleNames(),
        ]);
    }

    /**
     * APAGAR (DELETE /id)
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'Utilizador eliminado.']);
    }
}
