<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;

class EntrepriseController extends Controller
{
    public function show()
    {
        return response()->json(Entreprise::current());
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'raison_sociale' => 'required|string|max:255',
            'ninea' => 'nullable|string|max:50',
            'rccm' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'site_web' => 'nullable|string|max:255',
            'devise' => 'required|string|max:10',
            'taux_tva_defaut' => 'required|numeric|min:0|max:100',
            'pied_de_page' => 'nullable|string',
        ]);

        $entreprise = Entreprise::current();
        $entreprise->update($data);

        return response()->json([
            'message' => 'Paramètres de l\'entreprise enregistrés.',
            'data' => $entreprise,
        ]);
    }
}
