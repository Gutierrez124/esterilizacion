<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';

    protected $guarded = [];

    public $timestamps = false;

    public function getNombreCompletoAttribute(): string
    {
        return $this->nombre_completo
            ?? trim(($this->nombres ?? '').' '.($this->apellidos ?? ''))
            ?: ($this->nombre ?? 'Usuario '.$this->getKey());
    }
}
