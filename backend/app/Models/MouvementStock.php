<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementStock extends Model
{
    protected $table = 'mouvements_stock';

    protected $guarded = ['id'];

    protected $casts = [
        'quantite' => 'decimal:3',
        'stock_avant' => 'decimal:3',
        'stock_apres' => 'decimal:3',
        'date_mouvement' => 'datetime',
    ];

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
