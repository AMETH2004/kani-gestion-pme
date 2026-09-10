<?php

namespace App\Http\Controllers;

use App\Http\Requests\Produit\StoreProduitRequest;
use App\Http\Requests\Produit\UpdateProduitRequest;
use App\Models\Produit;
use App\Services\Stock\GestionStock;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    public function index(Request $request)
    {
        $query = Produit::with('categorie');

        if ($terme = $request->string('recherche')->trim()->value()) {
            $query->where(function ($q) use ($terme) {
                $q->where('designation', 'like', "%{$terme}%")
                    ->orWhere('reference', 'like', "%{$terme}%");
            });
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->integer('categorie_id'));
        }

        if ($request->boolean('stock_bas')) {
            $query->stockBas();
        }

        if ($request->boolean('actifs_seulement')) {
            $query->where('actif', true);
        }

        return response()->json($query->orderBy('designation')->paginate($request->integer('par_page', 20)));
    }

    public function show(Produit $produit)
    {
        return response()->json($produit->load('categorie'));
    }

    public function store(StoreProduitRequest $request, GestionStock $stock)
    {
        $data = $request->validated();
        $stockInitial = (float) ($data['stock_actuel'] ?? 0);
        $data['stock_actuel'] = 0;

        $produit = Produit::create($data);

        if ($stockInitial > 0) {
            $stock->enregistrer($produit, 'entree', $stockInitial, 'Stock initial', $produit->reference);
        }

        return response()->json([
            'message' => 'Produit créé.',
            'data' => $produit->fresh('categorie'),
        ], 201);
    }

    public function update(UpdateProduitRequest $request, Produit $produit)
    {
        // Le stock ne se modifie jamais directement ici : il passe par les mouvements de stock.
        $data = $request->validated();
        unset($data['stock_actuel']);

        $produit->update($data);

        return response()->json([
            'message' => 'Produit mis à jour.',
            'data' => $produit->fresh('categorie'),
        ]);
    }

    public function destroy(Produit $produit)
    {
        $utilise = $produit->mouvements()->exists()
            || \App\Models\LigneFacture::where('produit_id', $produit->id)->exists()
            || \App\Models\LigneDevis::where('produit_id', $produit->id)->exists();

        if ($utilise) {
            $produit->update(['actif' => false]);

            return response()->json([
                'message' => 'Produit déjà utilisé : il a été désactivé plutôt que supprimé.',
            ]);
        }

        $produit->delete();

        return response()->json(['message' => 'Produit supprimé.']);
    }

    public function alertesStock()
    {
        return response()->json(
            Produit::with('categorie')->stockBas()->orderBy('designation')->get()
        );
    }
}
