<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UtilizadoresController extends Controller
{
    /**
     * GET /api/admin/utilizadores
     * Lista utilizadores + roles + estado + nif/phone
     */
    public function index()
    {
        $users = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $users->getCollection()->transform(function ($user) {
            return [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'created_at'  => $user->created_at,

                'roles'       => $user->getRoleNames(),

                'is_approved' => (bool) $user->is_approved,
                'approved_at' => $user->approved_at,

                'nif'         => $user->nif,
                'phone'       => $user->phone,
            ];
        });

        return response()->json($users);
    }

    /**
     * GET /api/admin/roles
     * Devolve lista de roles (Spatie)
     */
    public function roles()
    {
        return response()->json(
            Role::query()->orderBy('name')->pluck('name')->values()
        );
    }

    /**
     * GET /api/admin/utilizadores/{user}
     * Mostra um utilizador (inclui roles + nif/phone/is_approved)
     */
    public function show(User $user)
    {
        $user->load('roles');

        return response()->json([
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'created_at'  => $user->created_at,

            'roles'       => $user->getRoleNames(),

            'is_approved' => (bool) $user->is_approved,
            'approved_at' => $user->approved_at,

            'nif'         => $user->nif,
            'phone'       => $user->phone,
        ]);
    }

    /**
     * POST /api/admin/utilizadores
     * Cria utilizador
     * Regra: nif/phone só são guardados se is_approved = true
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|min:6',

            'is_approved' => 'nullable|boolean',

            // podem vir do front, mas só aplicam se is_approved = true
            'nif'         => 'nullable|string|size:9|unique:users,nif',
            'phone'       => 'nullable|string|max:20',

            'roles'       => 'nullable|array',
            'roles.*'     => 'string|exists:roles,name',
        ]);

        $isApproved = (bool) ($data['is_approved'] ?? false);

        // regra de negócio: se não aprovado, limpa
        $nif = $isApproved ? ($data['nif'] ?? null) : null;
        $phone = $isApproved ? ($data['phone'] ?? null) : null;

        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => bcrypt($data['password']),

            'is_approved' => $isApproved ? 1 : 0,
            'approved_at' => $isApproved ? now() : null,

            'nif'         => $nif,
            'phone'       => $phone,
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return response()->json([
            'message' => 'Utilizador criado com sucesso.',
            'user' => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'roles'       => $user->getRoleNames(),
                'is_approved' => (bool) $user->is_approved,
                'approved_at' => $user->approved_at,
                'nif'         => $user->nif,
                'phone'       => $user->phone,
            ],
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/utilizadores/{user}
     * Atualiza utilizador
     * Regra: nif/phone só são guardados se is_approved = true
     * - se desaprovar, limpa nif/phone e approved_at
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'email'       => "sometimes|email|unique:users,email,{$user->id}",
            'password'    => 'sometimes|min:6',

            'is_approved' => 'sometimes|boolean',

            'nif'         => "nullable|string|size:9|unique:users,nif,{$user->id}",
            'phone'       => 'nullable|string|max:20',

            'roles'       => 'nullable|array',
            'roles.*'     => 'string|exists:roles,name',
        ]);

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }

        if (array_key_exists('email', $data)) {
            $user->email = $data['email'];
        }

        if (array_key_exists('password', $data) && $data['password']) {
            $user->password = bcrypt($data['password']);
        }

        // novo estado (se não vier no request, mantém)
        $newApproved = array_key_exists('is_approved', $data)
            ? (bool) $data['is_approved']
            : (bool) $user->is_approved;

        $wasApproved = (bool) $user->is_approved;

        if (array_key_exists('is_approved', $data)) {
            $user->is_approved = $newApproved ? 1 : 0;

            if (!$wasApproved && $newApproved) {
                $user->approved_at = now();
            }

            if ($wasApproved && !$newApproved) {
                $user->approved_at = null;
            }
        }

        // regra: nif/phone só se aprovado
        if ($newApproved) {
            if (array_key_exists('nif', $data)) $user->nif = $data['nif'];
            if (array_key_exists('phone', $data)) $user->phone = $data['phone'];
        } else {
            $user->nif = null;
            $user->phone = null;
        }

        $user->save();

        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles'] ?? []);
        }

        return response()->json([
            'message' => 'Utilizador atualizado com sucesso.',
            'user' => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'roles'       => $user->getRoleNames(),
                'is_approved' => (bool) $user->is_approved,
                'approved_at' => $user->approved_at,
                'nif'         => $user->nif,
                'phone'       => $user->phone,
            ],
        ]);
    }

    /**
     * DELETE /api/admin/utilizadores/{user}
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'Utilizador eliminado.']);
    }
    public function options()
    {
        return response()->json(
            User::select('id', 'name', 'email')
                ->orderBy('name')
                ->get()
        );
    }
}
