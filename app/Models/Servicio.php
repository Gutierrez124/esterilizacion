<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'servicios';

    protected $guarded = [];

    public $timestamps = false;

    public function materiales()
    {
        return $this->hasMany(Material::class, 'servicio_id');
    }
}
