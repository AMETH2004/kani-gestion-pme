<?php

namespace App\Http\Controllers;

use App\Models\MouvementStock;
use App\Models\Produit;
use App\Services\Stock\GestionStock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Historique global des mouvements, filtrable par produit.
     */
    public function index(Request $request)
    {
        $query = MouvementStock::with(['produit:id,reference,designation,unite', 'user:id,name']);

        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->integer('produit_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        return response()->json(
            $query->latest('date_mouvement')->paginate($request->integer('par_page', 30))
        );
    }

    public function store(Request $request, Produit $produit, GestionStock $stock)
    {
        $data = $request->validate([
            'type' => 'required|in:entree,sortie,ajustement',
            'quantite' => 'required|numeric|min:0',
            'motif' => 'nullable|string|max:255',
            'reference_document' => 'nullable|string|max:255',
            'date_mouvement' => 'nullable|date',
        ]);

        if ($data['type'] === 'sortie' && $data['quantite'] > (float) $produit->stock_actuel) {
            return response()->json([
                'message' => 'Stock insuffisant : '.$produit->stock_actuel.' '.$produit->unite.' disponible(s).',
            ], 422);
        }

        $mouvement = $stock->enregistrer(
            $produit,
            $data['type'],
            (float) $data['quantite'],
            $data['motif'] ?? null,
            $data['reference_document'] ?? null,
            isset($data['date_mouvement']) ? new \DateTimeImmutable($data['date_mouvement']) : null,
        );

        return response()->json([
            'message' => 'Mouvement de stock enregistré.',
            'data' => $mouvement->load('produit:id,reference,designation,unite'),
            'produit' => $produit->fresh(),
        ], 201);
    }
}
