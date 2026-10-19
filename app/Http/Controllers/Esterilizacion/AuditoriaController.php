<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Services\AuditoriaService;
use App\Support\XlsxWriter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    private const POR_PAGINA = 25;

    public function index(Request $request): View
    {
        $filtros = $this->filtros($request);

        return view('esterilizacion.auditorias.index', [
            'auditorias' => $this->consulta($filtros)
                ->paginate(self::POR_PAGINA)
                ->withQueryString(),
            'acciones' => $this->consultaBase()
                ->select('accion')
                ->distinct()
                ->orderBy('accion')
                ->pluck('accion'),
            'areas' => $this->consultaBase()
                ->select('area')
                ->distinct()
                ->orderBy('area')
                ->pluck('area'),
            'filtros' => $filtros,
        ]);
    }

    public function excel(Request $request)
    {
        $filtros = $this->filtros($request);

        $registros = $this->consulta($filtros)
            ->with(['usuario', 'responsable'])
            ->get();

        if ($registros->isEmpty()) {
            return back()->with('status', 'No hay auditorias para los filtros seleccionados.');
        }

        $pendiente = '—';

        return XlsxWriter::descargar(
            'auditorias_'.now()->format('Ymd_His').'.xlsx',
            ['Fecha y hora', 'Usuario', 'Accion', 'Area', 'Responsable', 'Proceso', 'Descripcion', 'Detalle'],
            $registros->map(fn (Auditoria $auditoria) => [
                $auditoria->created_at?->format('d/m/Y H:i') ?? $pendiente,
                $auditoria->usuario?->nombre_completo ?? 'Sistema',
                (string) $auditoria->accion,
                (string) ($auditoria->area ?? $pendiente),
                $auditoria->responsable?->nombre_completo ?? $pendiente,
                $auditoria->proceso_id ? '#'.$auditoria->proceso_id : $pendiente,
                (string) $auditoria->descripcion,
                $this->detalle($auditoria),
            ])
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

    /**
     * Solo eventos del usuario autenticado: aquellos donde fue el responsable
     * (el actor) y los de sesion/proceso que le pertenecen (sin responsable).
     */
    private function consultaBase(): Builder
    {
        return Auditoria::query()
            ->where(function (Builder $query) {
                $query->where('auditorias.responsable_id', Auth::id())
                    ->orWhere(function (Builder $query) {
                        $query->whereNull('auditorias.responsable_id')
                            ->where('auditorias.usuario_id', Auth::id());
                    });
            });
    }

    private function consulta(array $filtros): Builder
    {
        return $this->consultaBase()
            ->when($filtros['accion'], fn ($q, $v) => $q->where('accion', $v))
            ->when($filtros['area'], fn ($q, $v) => $q->where('area', $v))
            ->when($filtros['desde'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filtros['hasta'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    private function detalle(Auditoria $auditoria): string
    {
        $datos = $auditoria->datos_adicionales;

        if (! is_array($datos) || $datos === []) {
            return '—';
        }

        return collect($datos)
            ->map(fn ($valor, $clave) => $clave.': '.(is_scalar($valor) ? (string) $valor : json_encode($valor, JSON_UNESCAPED_UNICODE)))
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
