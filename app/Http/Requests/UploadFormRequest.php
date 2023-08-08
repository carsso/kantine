<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'files' => 'required',
            'files.*' => 'required|mimes:pdf|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'files.required' => "Le fichier est obligatoire.",
            'files.mimes' => "Le fichier doit être un PDF.",
            'files.max' => "Le fichier est trop gros.",
            'files.*.required' => "Le fichier est obligatoire.",
            'files.*.mimes' => "Le fichier doit être un PDF.",
            'files.*.max' => "Le fichier est trop gros.",
        ];
    }
}
