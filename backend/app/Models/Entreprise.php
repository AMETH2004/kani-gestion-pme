<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'taux_tva_defaut' => 'decimal:2',
    ];

    /**
     * L'entreprise courante (application mono-entreprise pour la v1).
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'raison_sociale' => 'Mon entreprise',
        ]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }
}
