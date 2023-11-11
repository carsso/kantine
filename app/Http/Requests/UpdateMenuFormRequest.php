<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'date' => 'required|array',
            'date.*' => 'required|date_format:Y-m-d',
            'event_name' => 'required|array',
            'event_name.*' => 'nullable',
            'starters' => 'required|array',
            'starters.*.*' => 'nullable',
            'mains' => 'required|array',
            'mains.*.*' => 'nullable',
            'sides' => 'required|array',
            'sides.*.*' => 'nullable',
            'cheeses' => 'required|array',
            'cheeses.*.*' => 'nullable',
            'desserts' => 'required|array',
            'desserts.*.*' => 'nullable',
        ];
    }

    public function messages()
    {
        return [];
    }
}
