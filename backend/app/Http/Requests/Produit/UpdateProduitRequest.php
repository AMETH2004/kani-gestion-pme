<?php

namespace App\Http\Requests\Produit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $produitId = $this->route('produit')?->id;

        return [
            'reference' => ['sometimes', 'string', 'max:60', Rule::unique('produits', 'reference')->ignore($produitId)],
            'designation' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'categorie_id' => 'nullable|exists:categories,id',
            'prix_achat' => 'nullable|numeric|min:0',
            'prix_vente' => 'sometimes|required|numeric|min:0',
            'taux_tva' => 'nullable|numeric|min:0|max:100',
            'unite' => 'nullable|string|max:20',
            'stock_alerte' => 'nullable|numeric|min:0',
            'gerer_stock' => 'sometimes|boolean',
            'actif' => 'sometimes|boolean',
        ];
    }
}
