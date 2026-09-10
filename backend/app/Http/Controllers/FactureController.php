<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GereLignesDocument;
use App\Models\Facture;
use App\Services\Stock\GestionStock;
use App\Support\Numerotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
{
    use GereLignesDocument;

    public function index(Request $request)
    {
        $query = Facture::with('client:id,code,nom')->withCount('lignes');

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->boolean('impayees')) {
            $query->whereIn('statut', ['envoyee', 'partiellement_payee', 'en_retard']);
        }

        return response()->json($query->latest()->paginate($request->integer('par_page', 20)));
    }

    public function show(Facture $facture)
    {
        return response()->json(
            $facture->load(['client', 'lignes.produit:id,reference,designation', 'paiements.user:id,name', 'user:id,name', 'devis:id,numero'])
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date_facture' => 'required|date',
            'date_echeance' => 'nullable|date|after_or_equal:date_facture',
            'notes' => 'nullable|string',
            ...$this->reglesLignes(),
        ]);

        $facture = DB::transaction(function () use ($data, $request) {
            $facture = Facture::create([
                'numero' => Numerotation::prochain(Facture::class, 'FAC'),
                'client_id' => $data['client_id'],
                'date_facture' => $data['date_facture'],
                'date_echeance' => $data['date_echeance'] ?? null,
                'notes' => $data['notes'] ?? null,
                'statut' => 'brouillon',
                'user_id' => $request->user()->id,
            ]);

            $this->synchroniserLignes($facture, $data['lignes'], (float) ($data['remise'] ?? 0));

            return $facture;
        });

        return response()->json([
            'message' => 'Facture '.$facture->numero.' créée.',
            'data' => $facture->load(['client', 'lignes']),
        ], 201);
    }

    public function update(Request $request, Facture $facture)
    {
        if ($facture->statut !== 'brouillon') {
            return response()->json([
                'message' => 'Seule une facture au statut brouillon peut être modifiée.',
            ], 422);
        }

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date_facture' => 'required|date',
            'date_echeance' => 'nullable|date|after_or_equal:date_facture',
            'notes' => 'nullable|string',
            ...$this->reglesLignes(),
        ]);

        DB::transaction(function () use ($facture, $data) {
            $facture->update([
                'client_id' => $data['client_id'],
                'date_facture' => $data['date_facture'],
                'date_echeance' => $data['date_echeance'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->synchroniserLignes($facture, $data['lignes'], (float) ($data['remise'] ?? 0));
        });

        return response()->json([
            'message' => 'Facture mise à jour.',
            'data' => $facture->fresh()->load(['client', 'lignes']),
        ]);
    }

    public function destroy(Facture $facture)
    {
        if ($facture->statut !== 'brouillon') {
            return response()->json([
                'message' => 'Une facture validée ne se supprime pas : annulez-la à la place.',
            ], 422);
        }

        $facture->delete();

        return response()->json(['message' => 'Facture supprimée.']);
    }

    /**
     * Valide la facture (brouillon → envoyee) et décrémente le stock des produits.
     */
    public function valider(Request $request, Facture $facture, GestionStock $stock)
    {
        if ($facture->statut !== 'brouillon') {
            return response()->json(['message' => 'Cette facture est déjà validée.'], 422);
        }

        DB::transaction(function () use ($facture, $stock) {
            foreach ($facture->lignes as $ligne) {
                if (! $ligne->produit_id) {
                    continue;
                }

                $produit = $ligne->produit;
                if (! $produit || ! $produit->gerer_stock) {
                    continue;
                }

                $stock->enregistrer(
                    $produit,
                    'sortie',
                    (float) $ligne->quantite,
                    'Facture '.$facture->numero,
                    $facture->numero,
                );
            }

            $facture->update([
                'statut' => 'envoyee',
                'stock_decremente_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Facture '.$facture->numero.' validée. Stock mis à jour.',
            'data' => $facture->fresh()->load(['client', 'lignes']),
        ]);
    }

    public function annuler(Facture $facture)
    {
        if ($facture->statut === 'payee') {
            return response()->json(['message' => 'Une facture soldée ne peut pas être annulée.'], 422);
        }

        $facture->update(['statut' => 'annulee']);

        return response()->json([
            'message' => 'Facture annulée.',
            'data' => $facture->fresh(),
        ]);
    }
}
