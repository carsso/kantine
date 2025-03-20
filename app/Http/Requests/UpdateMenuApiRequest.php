<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuApiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'dishes' => 'required|array',
            'dishes.*' => 'required|array',
            'dishes.*.*' => 'required|array',
            'dishes.*.*.*' => 'required|array',
            'dishes.*.*.*.*.name' => 'required|string',
            'dishes.*.*.*.*.tags' => 'nullable|array',
            'dishes.*.*.*.*.tags.*' => 'string',
            'information' => 'nullable|array',
            'information.event_name' => 'nullable|string',
            'information.information' => 'nullable|string',
            'information.style' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'dishes.required' => 'Le champ dishes est requis',
            'dishes.array' => 'Le champ dishes doit être un tableau',
            'dishes.*.required' => 'Chaque type de plat doit être un tableau',
            'dishes.*.array' => 'Chaque type de plat doit être un tableau',
            'dishes.*.*.required' => 'Chaque catégorie racine doit être un tableau',
            'dishes.*.*.array' => 'Chaque catégorie racine doit être un tableau',
            'dishes.*.*.*.required' => 'Chaque sous-catégorie doit être un tableau',
            'dishes.*.*.*.array' => 'Chaque sous-catégorie doit être un tableau',
            'dishes.*.*.*.*.name.required' => 'Le nom du plat est requis',
            'dishes.*.*.*.*.name.string' => 'Le nom du plat doit être une chaîne de caractères',
            'dishes.*.*.*.*.tags.array' => 'Les tags doivent être un tableau',
            'dishes.*.*.*.*.tags.*.string' => 'Chaque tag doit être une chaîne de caractères',
            'information.array' => 'Le champ information doit être un tableau',
            'information.event_name.string' => 'Le nom de l\'événement doit être une chaîne de caractères',
            'information.information.string' => 'L\'information doit être une chaîne de caractères',
            'information.style.string' => 'Le style doit être une chaîne de caractères',
        ];
    }
} 