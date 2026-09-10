<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GereLignesDocument;
use App\Models\Devis;
use App\Models\Facture;
use App\Support\Numerotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevisController extends Controller
{
    use GereLignesDocument;

    public function index(Request $request)
    {
        $query = Devis::with('client:id,code,nom')->withCount('lignes');

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        return response()->json($query->latest()->paginate($request->integer('par_page', 20)));
    }

    public function show(Devis $devis)
    {
        return response()->json($devis->load(['client', 'lignes.produit:id,reference,designation', 'user:id,name', 'facture:id,numero']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date_devis' => 'required|date',
            'date_validite' => 'nullable|date|after_or_equal:date_devis',
            'notes' => 'nullable|string',
            ...$this->reglesLignes(),
        ]);

        $devis = DB::transaction(function () use ($data, $request) {
            $devis = Devis::create([
                'numero' => Numerotation::prochain(Devis::class, 'DEV'),
                'client_id' => $data['client_id'],
                'date_devis' => $data['date_devis'],
                'date_validite' => $data['date_validite'] ?? null,
                'notes' => $data['notes'] ?? null,
                'statut' => 'brouillon',
                'user_id' => $request->user()->id,
            ]);

            $this->synchroniserLignes($devis, $data['lignes'], (float) ($data['remise'] ?? 0));

            return $devis;
        });

        return response()->json([
            'message' => 'Devis '.$devis->numero.' créé.',
            'data' => $devis->load(['client', 'lignes']),
        ], 201);
    }

    public function update(Request $request, Devis $devis)
    {
        if (in_array($devis->statut, ['converti', 'accepte'], true)) {
            return response()->json(['message' => 'Ce devis ne peut plus être modifié.'], 422);
        }

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date_devis' => 'required|date',
            'date_validite' => 'nullable|date|after_or_equal:date_devis',
            'statut' => 'sometimes|in:brouillon,envoye,accepte,refuse,expire',
            'notes' => 'nullable|string',
            ...$this->reglesLignes(),
        ]);

        DB::transaction(function () use ($devis, $data) {
            $devis->update([
                'client_id' => $data['client_id'],
                'date_devis' => $data['date_devis'],
                'date_validite' => $data['date_validite'] ?? null,
                'statut' => $data['statut'] ?? $devis->statut,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->synchroniserLignes($devis, $data['lignes'], (float) ($data['remise'] ?? 0));
        });

        return response()->json([
            'message' => 'Devis mis à jour.',
            'data' => $devis->fresh()->load(['client', 'lignes']),
        ]);
    }

    public function destroy(Devis $devis)
    {
        if ($devis->statut === 'converti') {
            return response()->json(['message' => 'Un devis converti en facture ne peut pas être supprimé.'], 422);
        }

        $devis->delete();

        return response()->json(['message' => 'Devis supprimé.']);
    }

    /**
     * Transforme le devis en facture (copie client + lignes + totaux).
     */
    public function convertir(Request $request, Devis $devis)
    {
        if ($devis->statut === 'converti' || $devis->facture()->exists()) {
            return response()->json(['message' => 'Ce devis a déjà été converti en facture.'], 422);
        }

        $facture = DB::transaction(function () use ($devis, $request) {
            $facture = Facture::create([
                'numero' => Numerotation::prochain(Facture::class, 'FAC'),
                'client_id' => $devis->client_id,
                'devis_id' => $devis->id,
                'date_facture' => now()->toDateString(),
                'date_echeance' => now()->addDays(30)->toDateString(),
                'statut' => 'brouillon',
                'montant_ht' => $devis->montant_ht,
                'montant_remise' => $devis->montant_remise,
                'montant_tva' => $devis->montant_tva,
                'montant_ttc' => $devis->montant_ttc,
                'notes' => $devis->notes,
                'user_id' => $request->user()->id,
            ]);

            foreach ($devis->lignes as $ligne) {
                $facture->lignes()->create($ligne->only([
                    'produit_id', 'designation', 'quantite', 'prix_unitaire', 'taux_tva', 'montant_ht', 'ordre',
                ]));
            }

            $devis->update(['statut' => 'converti']);

            return $facture;
        });

        return response()->json([
            'message' => 'Devis converti en facture '.$facture->numero.'.',
            'data' => $facture->load(['client', 'lignes']),
        ], 201);
    }
}
