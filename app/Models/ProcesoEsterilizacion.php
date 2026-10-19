<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcesoEsterilizacion extends Model
{
    public const ESTADO_ROJA = 'ROJA';
    public const ESTADO_AZUL = 'AZUL';
    public const ESTADO_VERDE = 'VERDE';

    public const EQUIPOS_BIOMEDICOS_ROJA = [
        'LAVADOR_ULTRASONICO' => 'Lavador ultrasónico',
        'BIDESTILADOR' => 'Bidestilador',
        'LAVADORA_TERMODESINFECTADORA' => 'Lavadora termodesinfectadora',
        'PISTOLA_AIRE_COMPRIMIDO' => 'Pistola de aire comprimido',
    ];

    protected $table = 'proceso_esterilizacion';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'fecha' => 'date',
    ];

    public function getEquipoBiomedicoLabelAttribute(): string
    {
        return self::EQUIPOS_BIOMEDICOS_ROJA[$this->equipo_biomedico] ?? (string) $this->equipo_biomedico;
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function personaRecibe()
    {
        return $this->belongsTo(Usuario::class, 'recibe_id');
    }

    public function personaEntrega()
    {
        return $this->belongsTo(Usuario::class, 'entrega_id');
    }

    public function operador()
    {
        return $this->belongsTo(Usuario::class, 'operador_id');
    }
    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por_id');
    }

    public function instrumentos()
    {
        return $this->hasMany(ProcesoInstrumento::class, 'proceso_id');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'proceso_id');
    }

    public function areaAzul()
    {
        return $this->hasOne(AreaAzul::class, 'proceso_id');
    }

    public function areaVerde()
    {
        return $this->hasOne(AreaVerde::class, 'proceso_id');
    }
}
