<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaAzul extends Model
{
    public const EQUIPOS_BIOMEDICOS_AZUL = [
        'PUPINEL' => 'Pupinel',
        'SELLADORA_VAPOR' => 'Selladora de manga para vapor',
        'SELLADORA_PLASMA' => 'Selladora de manga para plasma',
        'INCUBADORA_INDICADOR_BIOLOGICO' => 'Incubadora de indicador biológico',
        'AUTOCLAVE_250_LTR' => 'Autoclave de 250 ltr (autoclave 1)',
        'AUTOCLAVE_160_LTR' => 'Autoclave de 160 ltrs (autoclave 2)',
        'ESTERILIZADOR_PLASMA' => 'Esterilizador de plasma',
        'MAQUINA_CORTADORA_GASA' => 'Máquina cortadora de gasa',
    ];

    protected $table = 'area_azul';

    protected $guarded = [];

    public $timestamps = false;

    public function getEquipoBiomedicoLabelAttribute(): string
    {
        return self::EQUIPOS_BIOMEDICOS_AZUL[$this->equipo_biomedico] ?? (string) $this->equipo_biomedico;
    }

    public function proceso()
    {
        return $this->belongsTo(ProcesoEsterilizacion::class, 'proceso_id');
    }

    public function operador()
    {
        return $this->belongsTo(Usuario::class, 'operador_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por_id');
    }
}
