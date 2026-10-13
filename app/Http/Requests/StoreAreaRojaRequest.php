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
        return [
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'fecha' => ['required', 'date'],
            'turno' => ['required', Rule::in(['DIURNO', 'NOCTURNO'])],
            'hora' => ['required', 'date_format:H:i'],
            'persona_recibe_id' => ['required', 'integer', 'exists:usuarios,id'],
            'persona_entrega_id' => ['required', 'integer', 'exists:usuarios,id'],
            'material_id' => [
                'required',
                'integer',
                Rule::exists('materiales', 'id')->where(fn ($query) => $query
                    ->where('servicio_id', $this->input('servicio_id'))
                    ->where('activo', 1)
                ),
            ],
            'cantidad_piezas' => ['required', 'integer', 'min:1'],
            'tipo_lavado' => ['required', Rule::in(['ULTRASONICA', 'MANUAL', 'LAVADORA_DESCONTAMINADORA', 'NO_CORRESPONDE'])],
            'equipo_biomedico_id' => ['required', 'integer', 'exists:equipos_biomedicos,id'],
            'operador_id' => ['required', 'integer', 'exists:usuarios,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'material_id.exists' => 'El material seleccionado no esta activo o no pertenece al servicio elegido.',
            'cantidad_piezas.min' => 'La cantidad de piezas debe ser mayor que cero.',
        ];
    }
}
