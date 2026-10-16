<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Models\ProcesoEsterilizacion;
use App\Models\Servicio;
use Illuminate\Http\Request;

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
}
