<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materiales';

    protected $guarded = [];

    public $timestamps = false;

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}
