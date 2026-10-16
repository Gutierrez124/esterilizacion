<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAreaAzulRequest;
use App\Models\ProcesoEsterilizacion;
use App\Models\Usuario;
use App\Services\EsterilizacionService;

class AreaAzulController extends Controller
{
    public function index()
    {
$procesos = ProcesoEsterilizacion::query()
            ->with(['servicio', 'material'])
            ->where('estado_actual', ProcesoEsterilizacion::ESTADO_ROJA)
            ->whereDoesntHave('areaAzul')
            ->orderBy('fecha')
            ->paginate(10);

        return view('esterilizacion.azul.index', compact('procesos'));
    }

    public function create(ProcesoEsterilizacion $proceso)
    {
        abort_unless($proceso->estado_actual === ProcesoEsterilizacion::ESTADO_ROJA && ! $proceso->areaAzul()->exists(), 409);
$proceso->load(['servicio', 'material']);

        return view('esterilizacion.azul.create', [
            'proceso' => $proceso,
            'usuarios' => Usuario::query()->where('activo', 1)->orderBy('id')->get(),
        ]);
    }

    public function store(StoreAreaAzulRequest $request, ProcesoEsterilizacion $proceso, EsterilizacionService $service)
    {
$service->registrarAreaAzul($proceso, $request->validated());

        return redirect()
            ->route('esterilizacion.procesos.show', $proceso)
            ->with('success', 'Area Azul registrada correctamente.');
    }
}
