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
            'information' => 'required|array',
            'information.*' => 'nullable',
            'style' => 'required|array',
            'style.*' => 'nullable',
            'dishes' => 'required|array',
            'dishes.*' => 'required|array',
            'dishes.*.*' => 'required|array',
            'dishes.*.*.*' => 'nullable',
            'dishes_tags' => 'nullable|array',
            'dishes_tags.*' => 'nullable|array',
            'dishes_tags.*.*' => 'nullable|array',
            'dishes_tags.*.*.*' => 'nullable',
        ];
    }

    public function messages()
    {
        return [];
    }
}
