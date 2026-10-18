<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ReporteUsuarioRequest extends PeriodoReporteRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'usuario_id' => ['required', 'integer', Rule::exists('usuarios', 'id')],
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'usuario_id.required' => 'Selecciona el usuario para generar el reporte.',
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
        ]);
    }
}