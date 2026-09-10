<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categorie\StoreCategorieRequest;
use App\Http\Requests\Categorie\UpdateCategorieRequest;
use App\Models\Categorie;

class CategorieController extends Controller
{
    public function index()
    {
        return response()->json(
            Categorie::withCount('produits')->orderBy('nom')->get()
        );
    }

    public function show(Categorie $categorie)
    {
        return response()->json($categorie->loadCount('produits'));
    }

    public function store(StoreCategorieRequest $request)
    {
        $categorie = Categorie::create($request->validated());

        return response()->json([
            'message' => 'Catégorie créée.',
            'data' => $categorie,
        ], 201);
    }

    public function update(UpdateCategorieRequest $request, Categorie $categorie)
    {
        $categorie->update($request->validated());

        return response()->json([
            'message' => 'Catégorie mise à jour.',
            'data' => $categorie,
        ]);
    }

    public function destroy(Categorie $categorie)
    {
        if ($categorie->produits()->exists()) {
            return response()->json([
                'message' => 'Impossible : des produits sont rattachés à cette catégorie.',
            ], 422);
        }

        $categorie->delete();

        return response()->json(['message' => 'Catégorie supprimée.']);
    }
}
