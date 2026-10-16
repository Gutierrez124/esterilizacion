<?php

namespace App\Http\Requests;

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
        $materialId = $this->input('material_id');

        return [
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'fecha' => ['required', 'date'],
            'turno' => ['required', Rule::in(['DIURNO', 'NOCTURNO'])],
            'hora' => ['required', 'date_format:H:i'],
            'persona_recibe_id' => ['required', 'integer', Rule::exists('usuarios', 'id')->where(fn ($q) => $q->where('activo', 1))],
            'persona_entrega_id' => ['required', 'integer', Rule::exists('usuarios', 'id')->where(fn ($q) => $q->where('activo', 1))],
            'material_id' => [
                'required',
                'integer',
                Rule::exists('materiales', 'id')->where(fn ($query) => $query
                    ->where('servicio_id', $this->input('servicio_id'))
                    ->where('activo', 1)
                ),
            ],
            'tipo_lavado' => ['required', Rule::in(['ULTRASONICA', 'MANUAL', 'LAVADORA_DESCONTAMINADORA', 'NO_CORRESPONDE'])],
            'equipo_biomedico' => ['required', Rule::in(['AUTOCLAVE'])],
            'operador_id' => ['required', 'integer', Rule::exists('usuarios', 'id')->where(fn ($q) => $q->where('activo', 1))],

            'piezas' => ['required', 'array', 'min:1'],
            'piezas.*.instrumento_id' => [
                'required',
                'integer',

                Rule::exists('material_instrumentos', 'instrumento_id')->where(fn ($query) => $query->where('material_id', $materialId)),
            ],
            'piezas.*.cantidad_real' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'material_id.exists' => 'El material seleccionado no esta activo o no pertenece al servicio elegido.',
            'persona_recibe_id.exists' => 'La persona que recibe debe ser un usuario activo del sistema.',
            'persona_entrega_id.exists' => 'La persona que entrega debe ser un usuario activo del sistema.',
            'operador_id.exists' => 'El operador debe ser un usuario activo del sistema.',
            'piezas.required' => 'Debes confirmar las piezas del conjunto.',
            'piezas.*.instrumento_id.exists' => 'Una de las piezas no pertenece al conjunto seleccionado.',
            'piezas.*.cantidad_real.min' => 'Las cantidades no pueden ser negativas.',
        ];
    }
}
