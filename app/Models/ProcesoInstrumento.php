<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcesoInstrumento extends Model
{
    protected $table = 'proceso_instrumentos';

    public $timestamps = false;

    protected $fillable = [
        'proceso_id',
        'servicio_id',
        'material_id',
        'instrumento_id',
        'nombre_instrumento',
        'codigo_instrumento',
        'cantidad_predeterminada',
        'cantidad_real',
    ];

    protected $casts = [
        'cantidad_predeterminada' => 'integer',
        'cantidad_real' => 'integer',
    ];

    public function proceso()
    {
        return $this->belongsTo(ProcesoEsterilizacion::class, 'proceso_id');
    }

    public function instrumento()
    {
        return $this->belongsTo(Instrumento::class, 'instrumento_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
