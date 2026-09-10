<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'nullable|string|max:30|unique:clients,code',
            'nom' => 'required|string|max:255',
            'type' => 'required|in:particulier,entreprise',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:255',
            'ninea' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'actif' => 'sometimes|boolean',
        ];
    }
}
