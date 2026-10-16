<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaAzul extends Model
{
    protected $table = 'area_azul';

    protected $guarded = [];

    public $timestamps = false;

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
