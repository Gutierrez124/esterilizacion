<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EstadisticaService
{
    public function datosExportar(array $f): array
    {
        $registros = $this->exportar($f);

        return [
            'encabezados' => ['Fecha', 'Usuario', 'Responsable', 'Accion', 'Area', 'Proceso', 'Servicio', 'Material', 'Descripcion'],
            'filas' => $registros->map(fn ($registro) => [
                $registro->created_at?->format('d/m/Y H:i') ?? '',
                $registro->usuario?->nombre_completo ?? 'Sistema',
                $registro->responsable_nombre,
                $registro->accion_etiqueta,
                $registro->area_etiqueta,
                $registro->proceso_id ? '#'.$registro->proceso_id : '',
                $registro->proceso?->instrumentos?->pluck('servicio.nombre')->filter()->unique()->implode(', ') ?? '',
                $registro->proceso?->instrumentos?->pluck('material.nombre')->filter()->unique()->implode(', ') ?? '',
                $registro->descripcion,
            ])->all(),
        ];
    }

    private function exportar(array $f): Collection
    {
        return $this->eventos($f)->get();
    }

    private function eventos(array $f): Builder
    {
        return $this->operaciones($f)
            ->when($this->entero($f, 'usuario_id'), fn ($q, $v) => $q->where('auditorias.usuario_id', $v))
            ->when($this->entero($f, 'proceso_id'), fn ($q, $v) => $q->where('auditorias.proceso_id', $v))
            ->with(['usuario', 'responsable', 'proceso.instrumentos.servicio', 'proceso.instrumentos.material'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    private function operaciones(array $f): Builder
    {
        return $this->operacionesBase($f)
            ->when($this->texto($f, 'accion'), fn ($q, $v) => $q->where('auditorias.accion', $v));
    }

    private function operacionesBase(array $f): Builder
    {
        return Auditoria::query()
            ->whereNotIn('auditorias.accion', AuditoriaService::ACCIONES_AUTENTICACION)
            ->when($this->texto($f, 'fecha_desde'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($this->texto($f, 'fecha_hasta'), fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($this->texto($f, 'area'), fn ($q, $v) => $q->where('area', $v))
            ->when($this->entero($f, 'servicio_id'), fn ($q, $v) => $q->whereHas('proceso.instrumentos', fn ($i) => $i->where('servicio_id', $v)));
    }

    private function texto(array $f, string $clave): string
    {
        return trim((string) ($f[$clave] ?? ''));
    }

    private function entero(array $f, string $clave): ?int
    {
        $valor = (int) ($f[$clave] ?? 0);

        return $valor > 0 ? $valor : null;
    }
}
