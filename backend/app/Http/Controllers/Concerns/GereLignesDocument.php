<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Produit;
use App\Support\CalculDocument;
use Illuminate\Database\Eloquent\Model;

/**
 * Logique commune aux devis et aux factures : (re)construction des lignes,
 * calcul des totaux HT / remise / TVA / TTC, et report sur le document parent.
 */
trait GereLignesDocument
{
    /**
     * @param  Model  $document  Devis ou Facture (doit avoir une relation lignes())
     * @param  array<int, array>  $lignesEntrantes
     */
    protected function synchroniserLignes(Model $document, array $lignesEntrantes, float $remise = 0): void
    {
        $lignesNormalisees = [];

        foreach (array_values($lignesEntrantes) as $index => $ligne) {
            $produit = ! empty($ligne['produit_id']) ? Produit::find($ligne['produit_id']) : null;

            $lignesNormalisees[] = [
                'produit_id' => $produit?->id,
                'designation' => $ligne['designation'] ?? $produit?->designation ?? 'Article',
                'quantite' => (float) ($ligne['quantite'] ?? 1),
                'prix_unitaire' => (float) ($ligne['prix_unitaire'] ?? $produit?->prix_vente ?? 0),
                'taux_tva' => (float) ($ligne['taux_tva'] ?? $produit?->taux_tva ?? 0),
                'ordre' => $index,
            ];
        }

        $calcul = CalculDocument::totaux($lignesNormalisees, $remise);

        $document->lignes()->delete();
        $document->lignes()->createMany($calcul['lignes']);

        $document->forceFill([
            'montant_ht' => $calcul['montant_ht'],
            'montant_remise' => $calcul['montant_remise'],
            'montant_tva' => $calcul['montant_tva'],
            'montant_ttc' => $calcul['montant_ttc'],
        ])->save();
    }

    /**
     * Règles de validation partagées pour le tableau `lignes`.
     *
     * @return array<string, string>
     */
    protected function reglesLignes(): array
    {
        return [
            'lignes' => 'required|array|min:1',
            'lignes.*.produit_id' => 'nullable|exists:produits,id',
            'lignes.*.designation' => 'required_without:lignes.*.produit_id|nullable|string|max:255',
            'lignes.*.quantite' => 'required|numeric|min:0.001',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva' => 'nullable|numeric|min:0|max:100',
            'remise' => 'nullable|numeric|min:0',
        ];
    }
}
