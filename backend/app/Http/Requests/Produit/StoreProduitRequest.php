<?php

namespace App\Http\Requests\Produit;

use Illuminate\Foundation\Http\FormRequest;

class StoreProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference' => 'required|string|max:60|unique:produits,reference',
            'designation' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categorie_id' => 'nullable|exists:categories,id',
            'prix_achat' => 'nullable|numeric|min:0',
            'prix_vente' => 'required|numeric|min:0',
            'taux_tva' => 'nullable|numeric|min:0|max:100',
            'unite' => 'nullable|string|max:20',
            'stock_actuel' => 'nullable|numeric|min:0',
            'stock_alerte' => 'nullable|numeric|min:0',
            'gerer_stock' => 'sometimes|boolean',
            'actif' => 'sometimes|boolean',
        ];
    }
}
