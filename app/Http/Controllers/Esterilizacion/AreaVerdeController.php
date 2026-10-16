<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAreaVerdeRequest;
use App\Models\ProcesoEsterilizacion;
use App\Models\Usuario;
use App\Services\EsterilizacionService;

class AreaVerdeController extends Controller
{
    public function index()
    {
$procesos = ProcesoEsterilizacion::query()
            ->with(['servicio', 'material', 'areaAzul'])
            ->where('estado_actual', ProcesoEsterilizacion::ESTADO_AZUL)
            ->whereHas('areaAzul')
            ->whereDoesntHave('areaVerde')
            ->orderBy('fecha')
            ->paginate(10);

        return view('esterilizacion.verde.index', compact('procesos'));
    }

    public function create(ProcesoEsterilizacion $proceso)
    {
        abort_unless($proceso->estado_actual === ProcesoEsterilizacion::ESTADO_AZUL && $proceso->areaAzul()->exists() && ! $proceso->areaVerde()->exists(), 409);
$proceso->load(['servicio', 'material']);

        return view('esterilizacion.verde.create', [
            'proceso' => $proceso,
            'usuarios' => Usuario::query()->where('activo', 1)->orderBy('id')->get(),
        ]);
    }

    public function store(StoreAreaVerdeRequest $request, ProcesoEsterilizacion $proceso, EsterilizacionService $service)
    {
$service->registrarAreaVerde($proceso, $request->validated());

        return redirect()
            ->route('esterilizacion.procesos.show', $proceso)
            ->with('success', 'Area Verde registrada correctamente. El proceso quedo finalizado.');
    }
}
