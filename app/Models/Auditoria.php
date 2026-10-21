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

    public function getAreaEtiquetaAttribute(): string
    {
        if (in_array($this->entidad, ['materiales', 'instrumentos', 'material_instrumentos'], true)) {
            return '—';
        }

        return $this->area ?: '—';
    }

    public function getResponsableNombreAttribute(): string
    {
        return $this->responsable?->nombre_completo
            ?? $this->usuario?->nombre_completo
            ?? '—';
    }

    public function getAccionEtiquetaAttribute(): string
    {
        if ($this->entidad === 'material_instrumentos'
            && in_array($this->accion, ['INSTRUMENTAL_DESACTIVADO', 'INSTRUMENTAL_ELIMINADO_DEL_CONJUNTO'], true)) {
            return 'PIEZA ELIMINADA DEL CONJUNTO';
        }

        return match ($this->accion) {
            'INSTRUMENTAL_ELIMINADO_DEL_CONJUNTO' => 'PIEZA ELIMINADA DEL CONJUNTO',
            'INSTRUMENTAL_ACTIVADO' => 'INSTRUMENTAL ACTIVADO',
            'INSTRUMENTAL_DESACTIVADO' => 'INSTRUMENTAL DESACTIVADO',
            default => (string) $this->accion,
        };
    }
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
