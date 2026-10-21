<?php

namespace App\Http\Requests;

use App\Models\ProcesoEsterilizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAreaRojaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'turno' => ['required', Rule::in(['DIURNO', 'NOCTURNO'])],
            'hora' => ['required', 'date_format:H:i'],
            'persona_recibe_id' => ['required', 'integer', Rule::exists('usuarios', 'id')->where(fn ($q) => $q->where('activo', 1))],
            'persona_entrega_id' => ['required', 'integer', Rule::exists('usuarios', 'id')->where(fn ($q) => $q->where('activo', 1))],
            'tipo_lavado' => ['required', Rule::in(['ULTRASONICA', 'MANUAL', 'LAVADORA_DESCONTAMINADORA', 'NO_CORRESPONDE'])],
            'equipo_biomedico' => ['required', Rule::in(array_keys(ProcesoEsterilizacion::EQUIPOS_BIOMEDICOS_ROJA))],
            'operador_id' => ['required', 'integer', Rule::exists('usuarios', 'id')->where(fn ($q) => $q->where('activo', 1))],

            'materiales_confirmados' => ['required', 'array', 'min:1'],
            'materiales_confirmados.*.servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'materiales_confirmados.*.material_id' => ['required', 'integer', 'exists:materiales,id'],
            'materiales_confirmados.*.piezas' => ['required', 'array', 'min:1'],
            'materiales_confirmados.*.piezas.*.instrumento_id' => ['required', 'integer', 'exists:instrumentos,id'],
            'materiales_confirmados.*.piezas.*.cantidad_real' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'persona_recibe_id.exists' => 'La persona que recibe debe ser un usuario activo del sistema.',
            'persona_entrega_id.exists' => 'La persona que entrega debe ser un usuario activo del sistema.',
            'operador_id.exists' => 'El operador debe ser un usuario activo del sistema.',
            'equipo_biomedico.in' => 'El equipo biomedico debe ser uno de los equipos habilitados para el Area Roja.',
            'materiales_confirmados.required' => 'Debes confirmar al menos un material y sus piezas.',
            'materiales_confirmados.*.piezas.*.cantidad_real.min' => 'Las cantidades no pueden ser negativas.',
        ];
    }
}
