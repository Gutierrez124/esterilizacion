<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Models\ProcesoEsterilizacion;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Services\EstadisticaService;
use App\Support\XlsxWriter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{

    public function index(Request $request, EstadisticaService $estadisticas): View
    {
        $filtros = $this->filtros($request);

        return view('esterilizacion.auditorias.index', [
            'filtros' => $filtros,
            'resumen' => $estadisticas->resumen($filtros),
            'estadisticas' => $estadisticas->porUsuario($filtros),
            'usuarios' => Usuario::query()->orderBy('nombres')->orderBy('apellidos')->get(),
            'procesos' => $this->procesosCombo(),
            'servicios' => $this->servicios(),
            'acciones' => $this->accionesDisponibles(),
            'areas' => ['ROJA', 'AZUL', 'VERDE'],
        ]);
    }

    public function usuario(Request $request, Usuario $usuario, EstadisticaService $estadisticas): View
    {
        $filtros = $this->filtros($request, $usuario->getKey());

        return view('esterilizacion.auditorias.usuario', [
            'usuarioDetalle' => $usuario,
            'filtros' => $filtros,
            'resumen' => $estadisticas->resumen($filtros),
            'operaciones' => $estadisticas->operacionesUsuario($usuario, $filtros),
            'servicios' => $this->servicios(),
            'acciones' => $this->accionesDisponibles(),
            'areas' => ['ROJA', 'AZUL', 'VERDE'],
        ]);
    }

    public function exportar(Request $request, EstadisticaService $estadisticas)
    {
        $filtros = $this->filtros($request);
        $registros = $estadisticas->exportar($filtros);

        $encabezados = ['Fecha', 'Usuario', 'Responsable', 'Accion', 'Area', 'Proceso', 'Servicio', 'Material', 'Descripcion'];

        $filas = $registros->map(fn ($registro) => [
            $registro->created_at?->format('d/m/Y H:i') ?? '',
            $registro->usuario?->nombre_completo ?? 'Sistema',
            $registro->responsable?->nombre_completo ?? '',
            $registro->accion,
            $registro->area,
            $registro->proceso_id ? '#'.$registro->proceso_id : '',
            $registro->proceso?->servicio?->nombre ?? '',
            $registro->proceso?->material?->nombre ?? '',
            $registro->descripcion,
        ])->all();

        $nombre = 'auditorias_'.now()->format('Ymd_His').'.xlsx';

        return XlsxWriter::descargar($nombre, $encabezados, $filas);
    }

    private function filtros(Request $request, ?int $usuarioId = null): array
    {
        $accion = $request->query('accion');
        $area = $request->query('area');
        $periodo = $request->query('periodo');
        $periodo = in_array($periodo, ['dia', 'semana', 'mes'], true) ? $periodo : null;

        [$desde, $hasta] = $this->rangoPeriodo(
            $this->fecha($request->query('fecha_desde')),
            $this->fecha($request->query('fecha_hasta')),
            $periodo
        );

        return [
            'periodo' => $periodo,
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'usuario_id' => $usuarioId ?? $this->entero($request->query('usuario_id')),
            'proceso_id' => $this->entero($request->query('proceso_id')),
            'accion' => in_array($accion, $this->accionesDisponibles(), true) ? $accion : null,
            'area' => in_array($area, ['ROJA', 'AZUL', 'VERDE'], true) ? $area : null,
            'servicio_id' => $this->entero($request->query('servicio_id')),
        ];
    }

    private function rangoPeriodo(?string $desde, ?string $hasta, ?string $periodo): array
    {
        if ($desde !== null || $hasta !== null) {
            return [$desde, $hasta];
        }

        $hoy = now();

        return match ($periodo) {
            'dia' => [$hoy->toDateString(), $hoy->toDateString()],
            'semana' => [$hoy->copy()->startOfWeek()->toDateString(), $hoy->toDateString()],
            'mes' => [$hoy->copy()->startOfMonth()->toDateString(), $hoy->toDateString()],
            default => [null, null],
        };
    }

    private function fecha(mixed $valor): ?string
    {
        return is_string($valor) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) ? $valor : null;
    }

    private function entero(mixed $valor): ?int
    {
        $numero = (int) $valor;

        return $numero > 0 ? $numero : null;
    }

    private function servicios()
    {
        return Servicio::query()->orderBy('id')->get();
    }

    private function procesosCombo()
    {
        return ProcesoEsterilizacion::query()
            ->with(['servicio', 'material'])
            ->orderByDesc('id')
            ->limit(300)
            ->get();
    }

    private function accionesDisponibles(): array
    {
        return [
            'PROCESO_CREADO',
            'MATERIAL_RECIBIDO',
            'MATERIAL_ENTREGADO',
            'AREA_AZUL_COMPLETADA',
            'AREA_VERDE_COMPLETADA',
            'PROCESO_ACTUALIZADO',
            'LOGIN_EXITOSO',
            'LOGOUT',
            'LOGIN_FALLIDO',
            'AREA_AZUL_REGISTRADA',
            'AREA_AZUL_ACTUALIZADA',
            'AREA_VERDE_REGISTRADA',
            'AREA_VERDE_ACTUALIZADA',
            'INSTRUMENTAL_CREADO',
            'INSTRUMENTAL_ACTUALIZADO',
            'INSTRUMENTAL_DESACTIVADO',
        ];
    }
}
