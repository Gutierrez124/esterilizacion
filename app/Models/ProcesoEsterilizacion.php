<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcesoEsterilizacion extends Model
{
    public const ESTADO_ROJA = 'ROJA';
    public const ESTADO_AZUL = 'AZUL';
    public const ESTADO_VERDE = 'VERDE';

    protected $table = 'proceso_esterilizacion';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'fecha' => 'date',
    ];

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

    public function equipoBiomedico()
    {
        return $this->belongsTo(EquipoBiomedico::class, 'equipo_biomedico_id');
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
