<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Facture;
use App\Models\Produit;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $debutMois = now()->startOfMonth();

        $caMois = (float) Facture::whereNotIn('statut', ['brouillon', 'annulee'])
            ->where('date_facture', '>=', $debutMois)
            ->sum('montant_ttc');

        $encaisseMois = (float) DB::table('paiements')
            ->where('date_paiement', '>=', $debutMois)
            ->sum('montant');

        $impayes = (float) Facture::whereIn('statut', ['envoyee', 'partiellement_payee', 'en_retard'])
            ->sum(DB::raw('montant_ttc - montant_paye'));

        return response()->json([
            'ca_mois' => $caMois,
            'encaisse_mois' => $encaisseMois,
            'impayes_total' => $impayes,
            'nb_clients' => Client::count(),
            'nb_produits' => Produit::where('actif', true)->count(),
            'nb_factures_impayees' => Facture::whereIn('statut', ['envoyee', 'partiellement_payee', 'en_retard'])->count(),
            'produits_stock_bas' => Produit::stockBas()->count(),
            'dernieres_factures' => Facture::with('client:id,nom')
                ->latest()->limit(5)->get(['id', 'numero', 'client_id', 'date_facture', 'montant_ttc', 'statut']),
            'ca_6_mois' => $this->caParMois(),
        ]);
    }

    private function caParMois(): array
    {
        $resultats = [];

        for ($i = 5; $i >= 0; $i--) {
            $mois = now()->subMonths($i);
            $montant = (float) Facture::whereNotIn('statut', ['brouillon', 'annulee'])
                ->whereYear('date_facture', $mois->year)
                ->whereMonth('date_facture', $mois->month)
                ->sum('montant_ttc');

            $resultats[] = [
                'mois' => $mois->translatedFormat('M Y'),
                'montant' => $montant,
            ];
        }

        return $resultats;
    }
}
