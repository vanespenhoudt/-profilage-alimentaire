<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'prenom'        => ['required', 'string', 'max:255'],
            'nom'           => ['required', 'string', 'max:255'],
            'tel'           => ['required', 'string', 'max:30'],
            'email'         => ['nullable', 'email', 'max:255'],
            'adresse'       => ['nullable', 'string'],
            'bt'            => ['nullable', 'string'],
            'rgpd'          => ['required', 'accepted'],
            'notes'         => ['nullable', 'string'],
            'conseiller_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'prenom.required'  => 'Le prénom est obligatoire.',
            'nom.required'     => 'Le nom est obligatoire.',
            'tel.required'     => 'Le téléphone est obligatoire.',
            'email.email'      => 'L\'adresse email n\'est pas valide.',
            'rgpd.required'    => 'Le consentement RGPD est obligatoire.',
            'rgpd.accepted'    => 'Le client doit accepter le consentement RGPD.',
        ];
    }
}
