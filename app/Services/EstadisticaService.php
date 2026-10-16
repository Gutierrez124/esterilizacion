<?php

namespace App\Services;

use App\Models\AreaAzul;
use App\Models\AreaVerde;
use App\Models\Auditoria;
use App\Models\ProcesoEsterilizacion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class EstadisticaService
{
    public const ACCION_RECEPCION = 'MATERIAL_RECIBIDO';
    public const ACCION_ENTREGA = 'MATERIAL_ENTREGADO';

    public function resumen(array $f): array
    {
        $uid = $this->entero($f, 'usuario_id');
        $procesos = $this->procesos($f)->whereNotNull('creado_por_id');

        return [
            'procesos_iniciados' => (int) $this->conUsuario($procesos, 'creado_por_id', $uid)->count(),
            'recepciones' => (int) $this->conUsuario($this->operacion($f, self::ACCION_RECEPCION), 'usuario_id', $uid)->count(),
            'entregas' => (int) $this->conUsuario($this->operacion($f, self::ACCION_ENTREGA), 'usuario_id', $uid)->count(),
            'area_azul' => (int) $this->conUsuario($this->etapas(AreaAzul::class, 'AZUL', $f), 'registrado_por_id', $uid)->count(),
            'area_verde' => (int) $this->conUsuario($this->etapas(AreaVerde::class, 'VERDE', $f), 'registrado_por_id', $uid)->count(),
            'piezas_recibidas' => (int) $this->conUsuario($procesos, 'creado_por_id', $uid)->sum('cantidad_piezas'),
        ];
    }

    public function porUsuario(array $f): LengthAwarePaginator
    {
        $uid = $this->entero($f, 'usuario_id');

        $columnas = [
            'procesos_iniciados' => $this->agrupar($this->conUsuario($this->procesos($f)->whereNotNull('creado_por_id'), 'creado_por_id', $uid), 'creado_por_id'),
            'recepciones_registradas' => $this->agrupar($this->conUsuario($this->operacion($f, self::ACCION_RECEPCION), 'usuario_id', $uid), 'usuario_id'),
            'recepciones_responsable' => $this->agrupar($this->conUsuario($this->procesos($f), 'recibe_id', $uid), 'recibe_id'),
            'entregas_responsable' => $this->agrupar($this->conUsuario($this->procesos($f), 'entrega_id', $uid), 'entrega_id'),
            'area_azul' => $this->agrupar($this->conUsuario($this->etapas(AreaAzul::class, 'AZUL', $f), 'registrado_por_id', $uid), 'registrado_por_id'),
            'area_verde' => $this->agrupar($this->conUsuario($this->etapas(AreaVerde::class, 'VERDE', $f), 'registrado_por_id', $uid), 'registrado_por_id'),
            'total_operaciones' => $this->agrupar($this->conUsuario($this->operaciones($f), 'usuario_id', $uid), 'usuario_id'),
        ];

        $ids = collect($columnas)
            ->flatMap(fn (Collection $mapa) => $mapa->keys())
            ->unique()
            ->filter()
            ->values();

        if ($uid) {
            $ids = collect([$uid]);
        }

        $usuarios = Usuario::query()->whereIn('id', $ids)->get()->keyBy('id');

        $filas = $ids
            ->map(function ($id) use ($columnas, $usuarios) {
                $total = fn (string $clave): int => (int) ($columnas[$clave][(int) $id] ?? 0);

                return [
                    'usuario_id' => (int) $id,
                    'usuario' => $usuarios[$id]?->nombre_completo ?? 'Usuario '.$id,
                    'procesos_iniciados' => $total('procesos_iniciados'),
                    'recepciones_registradas' => $total('recepciones_registradas'),
                    'recepciones_responsable' => $total('recepciones_responsable'),
                    'entregas_responsable' => $total('entregas_responsable'),
                    'area_azul' => $total('area_azul'),
                    'area_verde' => $total('area_verde'),
                    'total_operaciones' => $total('total_operaciones'),
                ];
            })
            ->sortByDesc('total_operaciones')
            ->sortByDesc('procesos_iniciados')
            ->sortBy('usuario')
            ->values();

        $pagina = Paginator::resolveCurrentPage();
        $porPagina = 15;

        return (new LengthAwarePaginator(
            $filas->forPage($pagina, $porPagina)->values(),
            $filas->count(),
            $porPagina,
            $pagina,
            ['path' => Paginator::resolveCurrentPath()]
        ))->withQueryString();
    }

    public function operacionesUsuario(Usuario $usuario, array $f): LengthAwarePaginator
    {
        return $this->eventos($f)
            ->where('auditorias.usuario_id', $usuario->getKey())
            ->paginate(15)
            ->withQueryString();
    }

    public function exportar(array $f): Collection
    {
        return $this->eventos($f)->get();
    }

    private function eventos(array $f): Builder
    {
        return $this->operaciones($f)
            ->when($this->entero($f, 'usuario_id'), fn ($q, $v) => $q->where('auditorias.usuario_id', $v))
            ->when($this->entero($f, 'proceso_id'), fn ($q, $v) => $q->where('auditorias.proceso_id', $v))
            ->with(['usuario', 'responsable', 'proceso.servicio', 'proceso.material'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    private function procesos(array $f): Builder
    {
        return ProcesoEsterilizacion::query()
            ->when($this->texto($f, 'fecha_desde'), fn ($q, $v) => $q->whereDate('fecha', '>=', $v))
            ->when($this->texto($f, 'fecha_hasta'), fn ($q, $v) => $q->whereDate('fecha', '<=', $v))
            ->when($this->entero($f, 'servicio_id'), fn ($q, $v) => $q->where('servicio_id', $v))
            ->when($this->entero($f, 'proceso_id'), fn ($q, $v) => $q->where('id', $v))

            ->when($this->texto($f, 'area'), function ($q) use ($f) {
                $q->whereExists(function ($sub) use ($f) {
                    $sub->selectRaw(1)
                        ->from('auditorias')
                        ->whereColumn('auditorias.proceso_id', 'proceso_esterilizacion.id')
                        ->where('auditorias.area', $this->texto($f, 'area'))
                        ->when($this->texto($f, 'fecha_desde'), fn ($x, $v) => $x->whereDate('auditorias.created_at', '>=', $v))
                        ->when($this->texto($f, 'fecha_hasta'), fn ($x, $v) => $x->whereDate('auditorias.created_at', '<=', $v));
                });
            });
    }

    private function operaciones(array $f): Builder
    {
        return $this->operacionesBase($f)
            ->when($this->texto($f, 'accion'), fn ($q, $v) => $q->where('auditorias.accion', $v));
    }

    private function operacionesBase(array $f): Builder
    {
        return Auditoria::query()
            ->when($this->texto($f, 'fecha_desde'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($this->texto($f, 'fecha_hasta'), fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($this->texto($f, 'area'), fn ($q, $v) => $q->where('area', $v))
            ->when($this->entero($f, 'servicio_id'), fn ($q, $v) => $q->whereHas('proceso', fn ($p) => $p->where('servicio_id', $v)));
    }
    private function operacion(array $f, string $accion): Builder
    {
        $filtro = $this->texto($f, 'accion');

        if ($filtro !== '' && $filtro !== $accion) {
            return Auditoria::query()->whereRaw('1 = 0');
        }

        return $this->operacionesBase($f)->where('accion', $accion);
    }

    private function etapas(string $modelo, string $area, array $f): Builder
    {
        if ($this->texto($f, 'area') !== '' && $this->texto($f, 'area') !== $area) {
            return $modelo::query()->whereRaw('1 = 0');
        }

        return $modelo::query()
            ->when($this->texto($f, 'fecha_desde'), fn ($q, $v) => $q->whereDate('creado_en', '>=', $v))
            ->when($this->texto($f, 'fecha_hasta'), fn ($q, $v) => $q->whereDate('creado_en', '<=', $v))
            ->when($this->entero($f, 'servicio_id'), fn ($q, $v) => $q->whereHas('proceso', fn ($p) => $p->where('servicio_id', $v)))
            ->when($this->entero($f, 'proceso_id'), fn ($q, $v) => $q->where('proceso_id', $v));
    }

    private function agrupar(Builder $consulta, string $campo): Collection
    {
        return $consulta
            ->selectRaw($campo.' as llave, COUNT(*) as total')
            ->whereNotNull($campo)
            ->groupBy($campo)
            ->get()
            ->mapWithKeys(fn ($fila) => [(int) $fila->llave => (int) $fila->total]);
    }

    private function conUsuario(Builder $consulta, string $campo, ?int $usuarioId): Builder
    {
        return $usuarioId ? (clone $consulta)->where($campo, $usuarioId) : $consulta;
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
