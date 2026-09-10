<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'date_facture' => 'date',
        'date_echeance' => 'date',
        'montant_ht' => 'decimal:2',
        'montant_remise' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'stock_decremente_at' => 'datetime',
    ];

    protected $appends = ['reste_a_payer'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneFacture::class)->orderBy('ordre');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class)->orderBy('date_paiement');
    }

    public function getResteAPayerAttribute(): float
    {
        return max(0, (float) $this->montant_ttc - (float) $this->montant_paye);
    }

    /**
     * Recalcule montant_paye + statut à partir des paiements enregistrés.
     */
    public function rafraichirStatutPaiement(): void
    {
        if (in_array($this->statut, ['brouillon', 'annulee'], true)) {
            return;
        }

        $paye = (float) $this->paiements()->sum('montant');
        $this->montant_paye = $paye;

        if ($paye <= 0) {
            $this->statut = $this->date_echeance && $this->date_echeance->isPast()
                ? 'en_retard'
                : 'envoyee';
        } elseif ($paye + 0.01 < (float) $this->montant_ttc) {
            $this->statut = 'partiellement_payee';
        } else {
            $this->statut = 'payee';
        }

        $this->save();
    }
}
