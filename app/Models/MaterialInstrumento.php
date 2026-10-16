<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MaterialInstrumento extends Pivot
{
    protected $table = 'material_instrumentos';

    public $timestamps = false;

    protected $fillable = [
        'material_id',
        'instrumento_id',
        'cantidad_predeterminada',
    ];

    protected $casts = [
        'cantidad_predeterminada' => 'integer',
    ];
}
