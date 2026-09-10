<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Génère des numéros de documents séquentiels par année : FAC-2026-0001, DEV-2026-0001…
 * Le compteur repart à 1 chaque année civile.
 */
class Numerotation
{
    public static function prochain(string $modelClass, string $prefixe, string $colonne = 'numero'): string
    {
        /** @var Model $modelClass */
        $annee = now()->year;
        $debut = sprintf('%s-%d-', $prefixe, $annee);

        $dernier = $modelClass::query()
            ->where($colonne, 'like', $debut.'%')
            ->orderByDesc($colonne)
            ->value($colonne);

        $sequence = $dernier
            ? ((int) substr($dernier, strlen($debut))) + 1
            : 1;

        return sprintf('%s%04d', $debut, $sequence);
    }
}
