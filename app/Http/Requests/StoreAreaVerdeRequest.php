<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

            'descarga_id' => ['required', 'integer', Rule::exists('usuarios', 'id')->where(fn ($q) => $q->where('activo', 1))],
            'persona_entrega_id' => ['required', 'integer', Rule::exists('usuarios', 'id')->where(fn ($q) => $q->where('activo', 1))],
            'persona_recibe_id' => ['required', 'integer', Rule::exists('usuarios', 'id')->where(fn ($q) => $q->where('activo', 1))],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'persona_entrega_id.exists' => 'La persona que entrega debe ser un usuario activo del sistema.',
            'persona_recibe_id.exists' => 'La persona que recibe debe ser un usuario activo del sistema.',
        ];
    }
}
