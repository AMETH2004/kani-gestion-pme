<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Paiement;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaiementController extends Controller
{
    public function __construct(private readonly PaymentManager $paiements) {}

    public function index(Request $request)
    {
        $query = Paiement::with(['facture:id,numero,client_id', 'facture.client:id,nom', 'user:id,name']);

        if ($request->filled('facture_id')) {
            $query->where('facture_id', $request->integer('facture_id'));
        }

        return response()->json($query->latest('date_paiement')->paginate($request->integer('par_page', 30)));
    }

    public function store(Request $request, Facture $facture)
    {
        if (in_array($facture->statut, ['brouillon', 'annulee'], true)) {
            return response()->json([
                'message' => 'Impossible d\'encaisser une facture en brouillon ou annulée.',
            ], 422);
        }

        $data = $request->validate([
            'montant' => 'required|numeric|min:1|max:'.max(1, round($facture->reste_a_payer + 0.5, 2)),
            'mode' => 'required|in:especes,cheque,virement,wave,orange_money,autre',
            'reference' => 'nullable|string|max:255',
            'date_paiement' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // v1 : on enregistre un encaissement DÉJÀ reçu (espèces, virement, ou
        // transfert Wave/Orange Money constaté par le commerçant). Le mode n'est
        // qu'un libellé. L'initiation d'un paiement en ligne (WaveProvider /
        // OrangeMoneyProvider) est un chantier séparé, non branché ici.
        $fournisseur = $this->paiements->fournisseur('manual');
        $resultat = $fournisseur->initier($facture, (float) $data['montant'], $data);

        $paiement = DB::transaction(function () use ($facture, $data, $request, $fournisseur, $resultat) {
            $paiement = $facture->paiements()->create([
                'montant' => $data['montant'],
                'mode' => $data['mode'],
                'reference' => $data['reference'] ?? null,
                'fournisseur_paiement' => $fournisseur->nom(),
                'transaction_id' => $resultat->transactionId,
                'date_paiement' => $data['date_paiement'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
                'user_id' => $request->user()->id,
            ]);

            $facture->rafraichirStatutPaiement();

            return $paiement;
        });

        return response()->json([
            'message' => 'Paiement de '.number_format((float) $data['montant'], 0, ',', ' ').' enregistré.',
            'data' => $paiement,
            'facture' => $facture->fresh(),
        ], 201);
    }

    public function destroy(Facture $facture, Paiement $paiement)
    {
        abort_unless($paiement->facture_id === $facture->id, 404);

        $paiement->delete();
        $facture->rafraichirStatutPaiement();

        return response()->json([
            'message' => 'Paiement supprimé.',
            'facture' => $facture->fresh(),
        ]);
    }
}
