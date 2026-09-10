<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(
            User::with('roles:id,name')->orderBy('name')->get()
        );
    }

    public function roles()
    {
        return response()->json(Role::orderBy('name')->pluck('name'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'telephone' => 'nullable|string|max:30',
            'poste' => 'nullable|string|max:100',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::exists('roles', 'name')],
            'actif' => 'sometimes|boolean',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'telephone' => $data['telephone'] ?? null,
            'poste' => $data['poste'] ?? null,
            'password' => Hash::make($data['password']),
            'actif' => $data['actif'] ?? true,
        ]);

        $user->syncRoles([$data['role']]);

        return response()->json([
            'message' => 'Utilisateur créé.',
            'data' => $user->load('roles:id,name'),
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'telephone' => 'nullable|string|max:30',
            'poste' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6',
            'role' => ['sometimes', Rule::exists('roles', 'name')],
            'actif' => 'sometimes|boolean',
        ]);

        $user->fill(collect($data)->except(['password', 'role'])->toArray());

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return response()->json([
            'message' => 'Utilisateur mis à jour.',
            'data' => $user->fresh()->load('roles:id,name'),
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }
}
