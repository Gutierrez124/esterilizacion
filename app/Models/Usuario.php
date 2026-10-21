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

    public function setNombresAttribute($value): void
    {
        $this->attributes['nombres'] = mb_strtoupper(trim((string) $value), 'UTF-8');
    }

    public function setApellidosAttribute($value): void
    {
        $this->attributes['apellidos'] = mb_strtoupper(trim((string) $value), 'UTF-8');
    }

    public function setDniAttribute($value): void
    {
        $this->attributes['dni'] = $value === null || trim((string) $value) === '' ? null : mb_strtoupper(trim((string) $value), 'UTF-8');
    }

    public function setCodigoAttribute($value): void
    {
        $this->attributes['codigo'] = $value === null || trim((string) $value) === '' ? null : mb_strtoupper(trim((string) $value), 'UTF-8');
    }
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

