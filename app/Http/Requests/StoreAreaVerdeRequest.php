<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAreaVerdeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_entrega' => ['required', 'date'],
            'hora_entrega' => ['required', 'date_format:H:i'],
            'hora_descarga' => ['required', 'date_format:H:i'],
            'persona_entrega_id' => ['required', 'integer', 'exists:usuarios,id'],
            'persona_recibe_id' => ['required', 'integer', 'exists:usuarios,id'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
