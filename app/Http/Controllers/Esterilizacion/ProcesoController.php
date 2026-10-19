<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Models\ProcesoEsterilizacion;
use App\Models\Servicio;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcesoController extends Controller
{
    public function index(Request $request)
    {
$procesos = ProcesoEsterilizacion::query()
            ->with(['servicio', 'material', 'personaRecibe', 'personaEntrega', 'operador', 'areaAzul.operador', 'areaVerde.personaEntrega', 'areaVerde.personaRecibe'])
            ->when($request->filled('fecha_desde'), fn ($query) => $query->whereDate('fecha', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($query) => $query->whereDate('fecha', '<=', $request->fecha_hasta))
            ->when($request->filled('servicio_id'), fn ($query) => $query->where('servicio_id', $request->servicio_id))
            ->when($request->filled('turno'), fn ($query) => $query->where('turno', $request->turno))
            ->when($request->filled('estado_actual'), fn ($query) => $query->where('estado_actual', $request->estado_actual))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('esterilizacion.index', [
            'procesos' => $procesos,
            'servicios' => Servicio::query()->orderBy('id')->get(),
            'estados' => [
                ProcesoEsterilizacion::ESTADO_ROJA,
                ProcesoEsterilizacion::ESTADO_AZUL,
                ProcesoEsterilizacion::ESTADO_VERDE,
            ],
        ]);
    }

    public function show(ProcesoEsterilizacion $proceso)
    {
$proceso->load([
            'servicio',
            'material',
            'personaRecibe',
            'personaEntrega',
            'operador',
            'areaAzul.operador',
            'areaVerde.personaEntrega',
            'areaVerde.personaRecibe',
            'creadoPor',
            'instrumentos',

        ]);

        return view('esterilizacion.show', compact('proceso'));
    }

    public function destroy(ProcesoEsterilizacion $proceso, AuditoriaService $auditoria)
    {
        $identificador = $proceso->getKey();

        DB::transaction(function () use ($proceso, $auditoria, $identificador) {
            $proceso->areaVerde()->delete();
            $proceso->areaAzul()->delete();
            $proceso->delete();

            $auditoria->registrar(
                accion: 'PROCESO_ELIMINADO',
                area: 'ROJA',
                descripcion: "Proceso #{$identificador} eliminado del sistema.",
                entidad: 'proceso_esterilizacion',
                entidadId: $identificador,
                datos: [
                    'servicio_id' => $proceso->servicio_id,
                    'material_id' => $proceso->material_id,
                    'estado_final' => $proceso->estado_actual,
                    'cantidad_piezas' => $proceso->cantidad_piezas,
                ],
            );
        });

        return redirect()
            ->route('esterilizacion.procesos.index')
            ->with('success', "Proceso #{$identificador} eliminado correctamente.");
    }
}
