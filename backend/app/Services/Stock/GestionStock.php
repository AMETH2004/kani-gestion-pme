<?php

namespace App\Services\Stock;

use App\Models\MouvementStock;
use App\Models\Produit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Point d'entrée unique pour toute modification de stock : garantit qu'un
 * mouvement_stock est toujours écrit en même temps que produits.stock_actuel.
 */
class GestionStock
{
    /**
     * @param  'entree'|'sortie'|'ajustement'  $type
     */
    public function enregistrer(
        Produit $produit,
        string $type,
        float $quantite,
        ?string $motif = null,
        ?string $referenceDocument = null,
        ?\DateTimeInterface $date = null,
    ): MouvementStock {
        return DB::transaction(function () use ($produit, $type, $quantite, $motif, $referenceDocument, $date) {
            $produit->refresh();
            $avant = (float) $produit->stock_actuel;

            $apres = match ($type) {
                'entree' => $avant + abs($quantite),
                'sortie' => $avant - abs($quantite),
                'ajustement' => $quantite, // quantite = nouvelle valeur absolue du stock
            };

            $produit->update(['stock_actuel' => $apres]);

            return $produit->mouvements()->create([
                'type' => $type,
                'quantite' => $type === 'ajustement' ? abs($apres - $avant) : abs($quantite),
                'stock_avant' => $avant,
                'stock_apres' => $apres,
                'motif' => $motif,
                'reference_document' => $referenceDocument,
                'user_id' => Auth::id(),
                'date_mouvement' => $date ?? now(),
            ]);
        });
    }
}
