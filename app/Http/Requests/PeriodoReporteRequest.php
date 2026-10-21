<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class PeriodoReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_desde' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31'],
            'fecha_hasta' => ['required', 'date_format:Y-m-d', 'after_or_equal:fecha_desde', 'before_or_equal:2100-12-31'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_desde.required' => 'Selecciona o escribe la fecha de inicio.',
            'fecha_desde.date_format' => 'La fecha de inicio no es válida.',
            'fecha_desde.after_or_equal' => 'La fecha de inicio debe ser desde el año 2000.',
            'fecha_desde.before_or_equal' => 'La fecha de inicio debe ser hasta el año 2100.',
            'fecha_hasta.required' => 'Selecciona o escribe la fecha de fin.',
            'fecha_hasta.date_format' => 'La fecha de fin no es válida.',
            'fecha_hasta.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'fecha_hasta.before_or_equal' => 'La fecha de fin debe ser hasta el año 2100.',
        ];
    }

    /**
     * Rango inclusivo de fechas (Y-m-d) elegido en el formulario.
     */
    public function rango(): array
    {
        $desde = Carbon::createFromFormat('!Y-m-d', (string) $this->validated('fecha_desde'))->toDateString();
        $hasta = Carbon::createFromFormat('!Y-m-d', (string) $this->validated('fecha_hasta'))->toDateString();

        return [
            'tipo' => 'rango',
            'desde' => $desde,
            'hasta' => $hasta,
            'etiqueta' => $desde.'_a_'.$hasta,
            'descripcion' => Carbon::parse($desde)->format('d/m/Y').' al '.Carbon::parse($hasta)->format('d/m/Y'),
        ];
    }
}