<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeriodoReporteRequest;
use App\Http\Requests\ReporteUsuarioRequest;
use App\Models\Auditoria;
use App\Models\ProcesoEsterilizacion;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use App\Services\EstadisticaService;
use App\Support\XlsxWriter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReporteController extends Controller
{
    private const PENDIENTE = '—';

    public function index(): View
    {
        return view('esterilizacion.reportes.index', [
            'usuarios' => Usuario::query()
                ->where('activo', 1)
                ->orderBy('nombres')
                ->orderBy('apellidos')
                ->get(),
        ]);
    }

    public function operativo(PeriodoReporteRequest $request)
    {
        $rango = $request->rango();

        $procesos = ProcesoEsterilizacion::query()
            ->with([
                'instrumentos.servicio',
                'instrumentos.material',
                'personaRecibe',
                'personaEntrega',
                'operador',
                'areaAzul.operador',
                'areaVerde.personaEntrega',
                'areaVerde.personaRecibe',
                'areaVerde.personaDescarga',
            ])
            ->whereBetween('fecha', [$rango['desde'], $rango['hasta']])
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        if ($procesos->isEmpty()) {
            return back()->withInput($request->query())->with('status', 'No hay procesos registrados en el periodo '.$rango['descripcion'].'.');
        }

        $reporte = [
            'hoja' => 'Reporte operativo',
            'titulo' => 'REPORTE OPERATIVO POR ÁREAS',
            'subtitulos' => [
                'Periodo: '.$rango['descripcion'],
                'Generado: '.now()->format('d/m/Y H:i'),
                'Procesos incluidos: '.$procesos->count(),
            ],
            'grupos' => [
                ['titulo' => 'PROCESO', 'columnas' => 3, 'estilo' => 'proceso'],
                ['titulo' => 'ÁREA ROJA', 'columnas' => 10, 'estilo' => 'roja'],
                ['titulo' => 'ÁREA AZUL', 'columnas' => 7, 'estilo' => 'azul'],
                ['titulo' => 'ÁREA VERDE', 'columnas' => 6, 'estilo' => 'verde'],
                ['titulo' => 'OBSERVACIÓN', 'columnas' => 1, 'estilo' => 'observacion'],
            ],
            'encabezados' => [
                'Nro.',
                'Servicio',
                'Estado',
                'Fecha',
                'Turno',
                'Hora',
                'Recibe',
                'Entrega',
                'Material/Instrumental',
                'Cantidad de piezas',
                'Tipo de lavado',
                'Equipo biomédico',
                'OP',
                'OP',
                'Hora',
                'Empaque',
                'Tipo de carga',
                'Nro. de carga',
                'IB',
                'Equipo biomédico',
                'OP',
                'Entrega',
                'Descarga',
                'Fecha',
                'Hora',
                'Recibe',
                'Observación',
            ],
            'anchos' => [8, 24, 10, 12, 12, 8, 24, 24, 28, 12, 24, 16, 24, 24, 8, 18, 16, 12, 16, 24, 24, 24, 24, 12, 8, 24, 45],
            'filas' => $procesos->map(fn (ProcesoEsterilizacion $proceso) => $this->filaOperativa($proceso))->all(),
        ];

        return XlsxWriter::descargarReporte('reporte_operativo_'.$rango['etiqueta'].'.xlsx', $reporte);
    }

    public function completo(PeriodoReporteRequest $request, EstadisticaService $estadisticas)
    {
        // Mismas columnas y datos de la descarga completa de auditorias (reporte 2).
        $filtros = $this->filtros($request);
        $rango = $request->rango();

        if ($rango !== null) {
            $filtros['fecha_desde'] = $rango['desde'];
            $filtros['fecha_hasta'] = $rango['hasta'];
            $filtros['periodo'] = null;
        }

        $datos = $estadisticas->datosExportar($filtros);

        if ($datos['filas'] === []) {
            return back()->withInput($request->query())->with(
                'status',
                'No hay registros para descargar'.($rango ? ' en el periodo '.$rango['descripcion'] : '').'.'
            );
        }

        $nombre = $rango !== null
            ? 'reporte_completo_'.$rango['etiqueta'].'.xlsx'
            : 'reporte_completo_'.now()->format('Ymd_His').'.xlsx';

        $criterios = array_filter([
            $filtros['accion'] ? 'Acción: '.$filtros['accion'] : null,
            $filtros['area'] ? 'Área: '.$filtros['area'] : null,
            $filtros['servicio_id'] ? 'Servicio ID: '.$filtros['servicio_id'] : null,
        ]);

        $reporte = [
            'hoja' => 'Reporte completo',
            'titulo' => 'REPORTE COMPLETO DE AUDITORÍAS',
            'subtitulos' => [
                'Periodo: '.$rango['descripcion'],
                'Generado: '.now()->format('d/m/Y H:i'),
                'Registros incluidos: '.count($datos['filas']),
                'Filtros adicionales: '.($criterios !== [] ? implode(' | ', $criterios) : 'Ninguno'),
            ],
            'encabezados' => ['Fecha', 'Usuario', 'Responsable', 'Acción', 'Área', 'Proceso', 'Servicio', 'Material', 'Descripción'],
            'anchos' => [20, 30, 30, 28, 18, 12, 30, 30, 60],
            'filas' => array_map(
                fn (array $fila) => array_map(fn ($valor) => ['v' => (string) $valor, 's' => 10], $fila),
                $datos['filas']
            ),
        ];

        return XlsxWriter::descargarReporte($nombre, $reporte);
    }

    private function filtros(PeriodoReporteRequest $request): array
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
            'usuario_id' => null,
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

    private function accionesDisponibles(): array
    {
        return [
            'PROCESO_CREADO',
            'PROCESO_ACTUALIZADO',
            'PROCESO_ELIMINADO',
            'MATERIAL_RECIBIDO',
            'MATERIAL_ENTREGADO',
            'AREA_AZUL_COMPLETADA',
            'AREA_VERDE_COMPLETADA',
            'AREA_AZUL_REGISTRADA',
            'AREA_AZUL_ACTUALIZADA',
            'AREA_VERDE_REGISTRADA',
            'AREA_VERDE_ACTUALIZADA',
            'INSTRUMENTAL_CREADO',
            'INSTRUMENTAL_ACTIVADO',
            'INSTRUMENTAL_ACTUALIZADO',
            'INSTRUMENTAL_DESACTIVADO',
            'INSTRUMENTAL_ELIMINADO_DEL_CONJUNTO',
        ];
    }

    public function usuario(ReporteUsuarioRequest $request)
    {
        $usuario = Usuario::query()->findOrFail($request->integer('usuario_id'));
        $rango = $request->rango();

       $registros = Auditoria::query()
            ->with(['proceso.instrumentos.servicio', 'proceso.instrumentos.material'])
            ->whereNotIn('accion', AuditoriaService::ACCIONES_AUTENTICACION)
            ->where(function ($consulta) use ($usuario) {
                $consulta
                    ->where('usuario_id', $usuario->getKey())
                    ->orWhere('responsable_id', $usuario->getKey());
            })
            ->whereDate('created_at', '>=', $rango['desde'])
            ->whereDate('created_at', '<=', $rango['hasta'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        if ($registros->isEmpty()) {
            return back()->withInput($request->query())->with(
                'status',
                'El usuario '.$usuario->nombre_completo.' no tiene actividades registradas en el periodo '.$rango['descripcion'].'.'
            );
        }

        $pendiente = self::PENDIENTE;

        $reporte = [
            'hoja' => 'Reporte por usuario',
            'titulo' => 'REPORTE DE ACTIVIDAD POR USUARIO',
            'subtitulos' => [
                'Usuario: '.$usuario->nombre_completo,
                'Periodo: '.$rango['descripcion'],
                'Generado: '.now()->format('d/m/Y H:i'),
                'Registros incluidos: '.$registros->count(),
            ],
            'encabezados' => [
                'Fecha',
                'Acción',
                'Área',
                'Proceso',
                'Servicio',
                'Material',
                'Descripción',
            ],
            'anchos' => [18, 26, 14, 12, 26, 30, 60],
            'filas' => $registros->map(fn (Auditoria $auditoria) => [
                ['v' => $auditoria->created_at?->format('d/m/Y H:i') ?? $pendiente, 's' => 11],
                ['v' => $auditoria->accion_etiqueta, 's' => 11],
                ['v' => $auditoria->area_etiqueta, 's' => 11],
                ['v' => $auditoria->proceso_id ? '#'.$auditoria->proceso_id : $pendiente, 's' => 11],
                $auditoria->proceso?->instrumentos?->pluck('servicio.nombre')->filter()->unique()->implode(', ') ?: $pendiente,
                $auditoria->proceso?->instrumentos?->pluck('material.nombre')->filter()->unique()->implode(', ') ?: $pendiente,
                (string) $auditoria->descripcion,
            ])->all(),
        ];

        return XlsxWriter::descargarReporte(
            'reporte_usuario_'.$this->slugUsuario($usuario).'_'.$rango['etiqueta'].'.xlsx',
            $reporte
        );
    }

    private function filaOperativa(ProcesoEsterilizacion $proceso): array
    {
        $azul = $proceso->areaAzul;
        $verde = $proceso->areaVerde;
        $pendiente = self::PENDIENTE;

        return [
            ['v' => '#'.$proceso->getKey(), 's' => 11],
            $proceso->instrumentos->pluck('servicio.nombre')->filter()->unique()->implode(', '),
            ['v' => (string) $proceso->estado_actual, 's' => 11],
            ['v' => $proceso->fecha?->format('d/m/Y') ?? '', 's' => 11],
            ['v' => (string) $proceso->turno, 's' => 11],
            ['v' => $this->hora($proceso->hora), 's' => 11],
            $proceso->personaRecibe?->nombre_completo ?? '',
            $proceso->personaEntrega?->nombre_completo ?? '',
            $proceso->instrumentos->pluck('material.nombre')->filter()->unique()->implode(', '),
            ['v' => (int) $proceso->cantidad_piezas, 's' => 11],
            ['v' => (string) $proceso->tipo_lavado, 's' => 11],
            ['v' => $proceso->equipo_biomedico_label ?: $pendiente, 's' => 11],
            $proceso->operador?->nombre_completo ?? '',

            $azul?->operador?->nombre_completo ?? $pendiente,
            ['v' => $azul ? $this->hora($azul->hora) : $pendiente, 's' => 11],
            $azul?->empaque ?? $pendiente,
            $azul?->tipo_carga ?? $pendiente,
            ['v' => $azul?->numero_carga ?? $pendiente, 's' => 11],
            ['v' => $azul?->ib ?? $pendiente, 's' => 11],
            ['v' => $azul?->equipo_biomedico_label ?: $pendiente, 's' => 11],

            $verde ? ($proceso->operador?->nombre_completo ?? $pendiente) : $pendiente,
            $verde?->personaEntrega?->nombre_completo ?? $pendiente,
            $verde?->personaDescarga?->nombre_completo ?? $pendiente,
            ['v' => $verde?->fecha_entrega?->format('d/m/Y') ?? $pendiente, 's' => 11],
            ['v' => $verde ? $this->hora($verde->hora_entrega) : $pendiente, 's' => 11],
            $verde?->personaRecibe?->nombre_completo ?? $pendiente,

            $verde?->observacion ?? '',
        ];
    }

    private function hora(mixed $valor): string
    {
        return $valor ? substr((string) $valor, 0, 5) : '';
    }

    private function slugUsuario(Usuario $usuario): string
    {
        return Str::slug($usuario->nombre_completo) ?: 'usuario-'.$usuario->getKey();
    }
}