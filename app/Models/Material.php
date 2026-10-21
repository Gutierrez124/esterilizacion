<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materiales';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function setNombreAttribute($value): void
    {
        $this->attributes['nombre'] = mb_strtoupper(trim((string) $value), 'UTF-8');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function instrumentos()
    {
        return $this->belongsToMany(Instrumento::class, 'material_instrumentos', 'material_id', 'instrumento_id')
            ->withPivot(['id', 'cantidad_predeterminada'])
            ->withTimestamps();
    }
}

