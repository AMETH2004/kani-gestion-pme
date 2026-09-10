<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'prix_achat' => 'decimal:2',
        'prix_vente' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'stock_actuel' => 'decimal:3',
        'stock_alerte' => 'decimal:3',
        'gerer_stock' => 'boolean',
        'actif' => 'boolean',
    ];

    protected $appends = ['stock_bas'];

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementStock::class);
    }

    public function getStockBasAttribute(): bool
    {
        return $this->gerer_stock && $this->stock_actuel <= $this->stock_alerte;
    }

    public function scopeStockBas(Builder $query): Builder
    {
        return $query->where('gerer_stock', true)
            ->whereColumn('stock_actuel', '<=', 'stock_alerte');
    }
}
