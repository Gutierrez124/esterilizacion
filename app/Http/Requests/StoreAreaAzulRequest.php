<?php

namespace App\Http\Requests;

use App\Models\AreaAzul;
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

            'empaque' => ['required', Rule::in(['PAPEL CREPADO', 'MANGA', 'PAPEL NO TEJIDO'])],
            'tipo_carga' => ['required', Rule::in(['INSTRUMENTAL', 'TEXTIL', 'GOMA'])],
            'numero_carga' => ['required', 'integer', 'min:1'],
            'ib' => ['required', Rule::in(['VIRO', 'NO VIRO', 'SI VIRO', 'REACCIONO', 'NO REACCIONO'])],
            'equipo_biomedico' => ['required', Rule::in(array_keys(AreaAzul::EQUIPOS_BIOMEDICOS_AZUL))],

            'operador_id' => ['required', 'integer', Rule::exists('usuarios', 'id')->where(fn ($q) => $q->where('activo', 1))],
        ];
    }

    public function messages(): array
    {
        return [
            'empaque.in' => 'El empaque debe ser PAPEL CREPADO, MANGA o PAPEL NO TEJIDO.',
            'ib.in' => 'El IB debe ser VIRO, NO VIRO, SI VIRO, REACCIONO o NO REACCIONO.',
            'equipo_biomedico.in' => 'El equipo biomedico debe ser uno de los equipos habilitados para el Area Azul.',
            'operador_id.exists' => 'El operador debe ser un usuario activo del sistema.',
        ];
    }
}
