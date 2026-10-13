<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaVerde extends Model
{
    protected $table = 'area_verde';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'fecha_entrega' => 'date',
    ];

    public function proceso()
    {
        return $this->belongsTo(ProcesoEsterilizacion::class, 'proceso_id');
    }

    public function personaEntrega()
    {
        return $this->belongsTo(Usuario::class, 'entrega_id');
    }

    public function personaRecibe()
    {
        return $this->belongsTo(Usuario::class, 'recibe_id');
    }
}
