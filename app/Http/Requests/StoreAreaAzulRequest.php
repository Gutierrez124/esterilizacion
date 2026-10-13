<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAreaAzulRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hora' => ['required', 'date_format:H:i'],
            'empaque' => ['required', 'string', 'max:100'],
            'tipo_carga' => ['required', Rule::in(['INSTRUMENTAL', 'TEXTIL', 'GOMA'])],
            'numero_carga' => ['required', 'integer', 'min:1'],
            'ib' => ['required', 'string', 'max:50'],
            'operador_id' => ['required', 'integer', 'exists:usuarios,id'],
        ];
    }
}
