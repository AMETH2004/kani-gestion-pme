<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneDevis extends Model
{
    protected $table = 'lignes_devis';

    protected $guarded = ['id'];

    protected $casts = [
        'quantite' => 'decimal:3',
        'prix_unitaire' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'montant_ht' => 'decimal:2',
    ];

    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }
}
