<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Devis extends Model
{
    protected $table = 'devis';

    protected $guarded = ['id'];

    protected $casts = [
        'date_devis' => 'date',
        'date_validite' => 'date',
        'montant_ht' => 'decimal:2',
        'montant_remise' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneDevis::class)->orderBy('ordre');
    }

    public function facture(): HasOne
    {
        return $this->hasOne(Facture::class);
    }
}
