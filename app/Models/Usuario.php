<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    public $timestamps = false;

    protected $fillable = [
        'nombres',
        'apellidos',
        'dni',
        'codigo',
        'foto',
        'email',
        'password',
        'rol',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'password' => 'hashed',
    ];

    public function getNombreCompletoAttribute(): string
    {
        return trim(($this->nombres ?? '').' '.($this->apellidos ?? ''))
            ?: ($this->email ?? 'Usuario '.$this->getKey());
    }

    public function getUrlFotoAttribute(): ?string
    {
        return $this->foto ? Storage::url($this->foto) : null;
    }

    public function procesosCreados()
    {
        return $this->hasMany(ProcesoEsterilizacion::class, 'creado_por_id');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'usuario_id');
    }
}

