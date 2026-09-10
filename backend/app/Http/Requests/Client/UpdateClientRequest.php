<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client')?->id;

        return [
            'code' => ['sometimes', 'string', 'max:30', Rule::unique('clients', 'code')->ignore($clientId)],
            'nom' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:particulier,entreprise',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:255',
            'ninea' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'actif' => 'sometimes|boolean',
        ];
    }
}
