<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PeriodoReporteRequest extends FormRequest
{
    private const MESES = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_periodo' => ['required', Rule::in(['anio', 'mes', 'dia'])],
            'anio' => ['required_with:tipo_periodo', 'integer', 'min:2000', 'max:2100'],
            'mes' => ['required_if:tipo_periodo,mes', 'required_if:tipo_periodo,dia', 'nullable', 'integer', 'min:1', 'max:12'],
            'dia' => ['required_if:tipo_periodo,dia', 'nullable', 'integer', 'min:1', 'max:31', $this->diaValido()],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_periodo.required' => 'Selecciona el tipo de periodo: año, mes o día.',
            'tipo_periodo.in' => 'El tipo de periodo debe ser año, mes o día.',
            'anio.required_with' => 'El año es obligatorio para generar el reporte.',
            'anio.min' => 'El año debe estar entre 2000 y 2100.',
            'anio.max' => 'El año debe estar entre 2000 y 2100.',
            'mes.required_if' => 'Selecciona el mes para la consulta por mes o por día.',
            'mes.min' => 'El mes debe estar entre 1 y 12.',
            'mes.max' => 'El mes debe estar entre 1 y 12.',
            'dia.required_if' => 'Selecciona el día para la consulta por día.',
            'dia.min' => 'El día debe estar entre 1 y 31.',
            'dia.max' => 'El día debe estar entre 1 y 31.',
        ];
    }

    /**
     * Rango inclusivo de fechas (Y-m-d) para el periodo solicitado.
     */
    public function rango(): ?array
    {
        $tipo = (string) $this->input('tipo_periodo');

        if (! in_array($tipo, ['anio', 'mes', 'dia'], true)) {
            return null;
        }

        $anio = (int) $this->input('anio');
        $mes = (int) $this->input('mes');
        $dia = (int) $this->input('dia');

        [$desde, $hasta, $etiqueta, $descripcion] = match ($tipo) {
            'mes' => [
                sprintf('%04d-%02d-01', $anio, $mes),
                Carbon::create($anio, $mes, 1)->endOfMonth()->toDateString(),
                sprintf('%04d-%02d', $anio, $mes),
                self::MESES[$mes].' de '.$anio,
            ],
            'dia' => [
                sprintf('%04d-%02d-%02d', $anio, $mes, $dia),
                sprintf('%04d-%02d-%02d', $anio, $mes, $dia),
                sprintf('%04d-%02d-%02d', $anio, $mes, $dia),
                sprintf('Día %02d/%02d/%04d', $dia, $mes, $anio),
            ],
            default => [
                $anio.'-01-01',
                $anio.'-12-31',
                (string) $anio,
                'Año '.$anio,
            ],
        };

        return [
            'tipo' => $tipo,
            'desde' => $desde,
            'hasta' => $hasta,
            'etiqueta' => $etiqueta,
            'descripcion' => $descripcion,
        ];
    }

    private function diaValido(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $anio = (int) $this->input('anio');
            $mes = (int) $this->input('mes');

            if ($anio > 0 && $mes > 0 && $value !== null && $value !== '' && ! checkdate($mes, (int) $value, $anio)) {
                $fail('La fecha seleccionada no existe. Revisa el día, el mes y el año.');
            }
        };
    }
}