<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function devis(): HasMany
    {
        return $this->hasMany(Devis::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }

    /**
     * Reste dû cumulé sur toutes les factures non soldées du client.
     */
    public function getSoldeAttribute(): float
    {
        $base = fn () => $this->factures()->whereNotIn('statut', ['annulee', 'brouillon']);

        return (float) ($base()->sum('montant_ttc') - $base()->sum('montant_paye'));
    }
}
