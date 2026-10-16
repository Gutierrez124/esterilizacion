<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instrumento extends Model
{
    protected $table = 'instrumentos';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'codigo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function materiales()
    {
        return $this->belongsToMany(Material::class, 'material_instrumentos', 'instrumento_id', 'material_id')
            ->withPivot(['id', 'cantidad_predeterminada'])
            ->withTimestamps();
    }
}
