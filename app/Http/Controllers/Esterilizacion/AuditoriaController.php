<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Instrumento;
use App\Services\AuditoriaService;
use App\Support\XlsxWriter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Collection;

class AuditoriaController extends Controller
{
    private const POR_PAGINA = 25;

    private const ENTIDADES_SIN_AREA = ['materiales', 'instrumentos', 'material_instrumentos'];

    public function index(Request $request): View
    {
        $filtros = $this->filtros($request);
        $auditorias = $this->consulta($filtros)
            ->paginate(self::POR_PAGINA)
            ->withQueryString();
        $instrumentos = $this->instrumentosEnAuditorias($auditorias->getCollection());
        $detalles = $auditorias->getCollection()->mapWithKeys(fn (Auditoria $auditoria) => [$auditoria->getKey() => $this->detalle($auditoria, $instrumentos)]);
        $areas = $this->consultaBase()
            ->whereNotIn('entidad', self::ENTIDADES_SIN_AREA)
            ->whereNotNull('area')
            ->select('area')
            ->distinct()
            ->orderBy('area')
            ->pluck('area')
            ->push('—')->unique()->values();

        return view('esterilizacion.auditorias.index', [
            'auditorias' => $auditorias,
            'detalles' => $detalles,
            'acciones' => $this->consultaBase()
                ->select('accion')
                ->distinct()
                ->orderBy('accion')
                ->pluck('accion'),
            'areas' => $areas,
            'filtros' => $filtros,
        ]);
    }

    public function excel(Request $request)
    {
        $filtros = $this->filtros($request);

        $registros = $this->consulta($filtros)->get();
        $instrumentos = $this->instrumentosEnAuditorias($registros);

        if ($registros->isEmpty()) {
            return back()->with('status', 'No hay auditorías para los filtros seleccionados.');
        }

        $pendiente = '—';
        $filtrosAplicados = array_filter([
            $filtros['accion'] ? 'Acción: '.$filtros['accion'] : null,
            $filtros['area'] ? 'Área: '.$filtros['area'] : null,
            $filtros['desde'] ? 'Desde: '.$filtros['desde'] : null,
            $filtros['hasta'] ? 'Hasta: '.$filtros['hasta'] : null,
        ]);

        $reporte = [
            'hoja' => 'Auditorías',
            'titulo' => 'HISTORIAL DE AUDITORÍAS',
            'subtitulos' => [
                'Usuario: '.Auth::user()->nombre_completo,
                'Generado: '.now()->format('d/m/Y H:i'),
                'Filtros aplicados: '.($filtrosAplicados !== [] ? implode(' | ', $filtrosAplicados) : 'Ninguno'),
            ],
            'encabezados' => ['Fecha y hora', 'Usuario', 'Acción', 'Área', 'Responsable', 'Proceso', 'Descripción', 'Detalle'],
            'anchos' => [20, 30, 26, 18, 30, 12, 55, 60],
            'filas' => $registros->map(fn (Auditoria $auditoria) => [
                $auditoria->created_at?->format('d/m/Y H:i') ?? $pendiente,
                $auditoria->usuario?->nombre_completo ?? 'Sistema',
                $auditoria->accion_etiqueta,
                $auditoria->area_etiqueta,
                $auditoria->responsable_nombre,
                $auditoria->proceso_id ? '#'.$auditoria->proceso_id : $pendiente,
                (string) $auditoria->descripcion,
                $this->detalle($auditoria, $instrumentos),
            ])->all(),
        ];

        return XlsxWriter::descargarReporte(
            'auditorias_'.now()->format('Ymd_His').'.xlsx',
            $reporte
        );
    }

    private function filtros(Request $request): array
    {
        return [
            'accion' => $this->texto($request->input('accion')),
            'area' => $this->texto($request->input('area')),
            'desde' => $this->fecha($request->input('desde')),
            'hasta' => $this->fecha($request->input('hasta')),
        ];
    }

    /** Solo muestra las acciones ejecutadas por el usuario autenticado. */
    private function consultaBase(): Builder
    {
        return Auditoria::query()
            ->where('auditorias.usuario_id', Auth::id());
    }

    private function consulta(array $filtros): Builder
    {
        return $this->consultaBase()
            ->with(['usuario', 'responsable'])
            ->when($filtros['accion'], fn ($q, $v) => $q->where('accion', $v))
            ->when($filtros['area'], function (Builder $query, string $area): void {
                if ($area === '—') {
                    $query->where(function (Builder $subquery) {
                        $subquery->whereIn('auditorias.entidad', self::ENTIDADES_SIN_AREA)
                            ->orWhereNull('auditorias.area')
                            ->orWhere('auditorias.area', '—');
                    });

                    return;
                }

                $query->where('auditorias.area', $area)
                    ->whereNotIn('auditorias.entidad', self::ENTIDADES_SIN_AREA);
            })
            ->when($filtros['desde'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filtros['hasta'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    private function instrumentosEnAuditorias(Collection $auditorias): Collection
    {
        $ids = $auditorias
            ->map(fn (Auditoria $auditoria) => $auditoria->datos_adicionales['instrumento_id'] ?? null)
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Instrumento::query()
            ->whereIn('id', $ids)
            ->get(['id', 'nombre', 'codigo'])
            ->keyBy('id');
    }

    private function detalle(Auditoria $auditoria, Collection $instrumentos): string
    {
        $datos = $auditoria->datos_adicionales;

        if (! is_array($datos) || $datos === []) {
            return '—';
        }

        $idInstrumento = (int) ($datos['instrumento_id'] ?? 0);
        $instrumento = $idInstrumento > 0 ? $instrumentos->get($idInstrumento) : null;

        if ($instrumento !== null) {
            $datos['nombre_instrumento'] ??= $instrumento->nombre;
            $datos['codigo_instrumento'] ??= $instrumento->codigo ?: '—';
        }

        $etiquetas = [
            'instrumento_id' => 'ID de instrumento',
            'nombre_instrumento' => 'Instrumento',
            'codigo_instrumento' => 'Código',
            'material_id' => 'ID de conjunto',
            'material_nombre' => 'Conjunto',
        ];

        return collect($datos)
            ->map(fn ($valor, $clave) => ($etiquetas[$clave] ?? $clave).': '.(is_scalar($valor) ? (string) $valor : json_encode($valor, JSON_UNESCAPED_UNICODE)))
            ->implode(' | ');
    }

    private function texto(mixed $valor): string
    {
        return mb_substr(trim((string) $valor), 0, 80);
    }

    private function fecha(mixed $valor): string
    {
        return is_string($valor) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) ? $valor : '';
    }
}
