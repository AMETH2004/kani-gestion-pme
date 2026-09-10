<?php

namespace App\Support;

/**
 * Calcule les totaux HT / remise / TVA / TTC d'un devis ou d'une facture
 * à partir de ses lignes et d'une remise globale (montant fixe en devise).
 */
class CalculDocument
{
    /**
     * @param  array<int, array{quantite: float|int, prix_unitaire: float|int, taux_tva: float|int}>  $lignes
     * @return array{montant_ht: float, montant_remise: float, montant_tva: float, montant_ttc: float, lignes: array<int, array>}
     */
    public static function totaux(array $lignes, float $remise = 0): array
    {
        $montantHt = 0.0;
        $montantTva = 0.0;
        $lignesCalculees = [];

        foreach ($lignes as $ligne) {
            $ht = round((float) $ligne['quantite'] * (float) $ligne['prix_unitaire'], 2);
            $montantHt += $ht;
            $lignesCalculees[] = array_merge($ligne, ['montant_ht' => $ht]);
        }

        $remise = min(max($remise, 0), $montantHt);
        $ratioApresRemise = $montantHt > 0 ? ($montantHt - $remise) / $montantHt : 0;

        foreach ($lignesCalculees as $ligne) {
            $baseTva = $ligne['montant_ht'] * $ratioApresRemise;
            $montantTva += round($baseTva * (float) $ligne['taux_tva'] / 100, 2);
        }

        $montantHtApresRemise = round($montantHt - $remise, 2);
        $montantTva = round($montantTva, 2);

        return [
            'montant_ht' => round($montantHt, 2),
            'montant_remise' => round($remise, 2),
            'montant_tva' => $montantTva,
            'montant_ttc' => round($montantHtApresRemise + $montantTva, 2),
            'lignes' => $lignesCalculees,
        ];
    }
}
