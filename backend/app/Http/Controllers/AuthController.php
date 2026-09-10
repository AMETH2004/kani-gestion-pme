<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Connexion : renvoie l'utilisateur, son rôle principal et un token Sanctum.
     * Réponse alignée sur le format attendu par le frontend Angular
     * ({ status, message, user, token, role }).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        if (! $user->actif) {
            throw ValidationException::withMessages([
                'email' => ['Ce compte est désactivé. Contactez un administrateur.'],
            ]);
        }

        $token = $user->createToken('kani-spa')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Connexion réussie',
            'user' => $user->load('roles'),
            'token' => $token,
            'role' => $user->getRoleNames()->first(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Déconnexion réussie',
        ]);
    }

    /**
     * Utilisateur connecté (rechargé à chaque ouverture de l'app côté Angular).
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles'),
            'role' => $request->user()->getRoleNames()->first(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'sometimes|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profil mis à jour.',
            'data' => $user->fresh()->load('roles'),
        ]);
    }
}
