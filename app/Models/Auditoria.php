<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditorias';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'responsable_id',
        'accion',
        'entidad',
        'entidad_id',
        'proceso_id',
        'area',
        'descripcion',
        'datos_adicionales',
        'created_at',
    ];

    protected $casts = [
        'datos_adicionales' => 'array',
        'created_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'responsable_id');
    }

    public function proceso()
    {
        return $this->belongsTo(ProcesoEsterilizacion::class, 'proceso_id');
    }
}
