<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAreaRojaRequest;
use App\Models\EquipoBiomedico;
use App\Models\Material;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Services\EsterilizacionService;

class AreaRojaController extends Controller
{
    public function create()
    {
        return view('esterilizacion.roja.create', [
            'servicios' => Servicio::query()->orderBy('id')->get(),
            'usuarios' => Usuario::query()->orderBy('id')->get(),
            'equipos' => EquipoBiomedico::query()->orderBy('id')->get(),
        ]);
    }

    public function store(StoreAreaRojaRequest $request, EsterilizacionService $service)
    {
$proceso = $service->iniciarAreaRoja($request->validated());

        return redirect()
            ->route('esterilizacion.procesos.show', $proceso)
            ->with('success', 'Proceso iniciado correctamente en Area Roja.');
    }

    public function materiales(Servicio $servicio)
    {
        return Material::query()
            ->where('servicio_id', $servicio->getKey())
            ->where('activo', 1)
            ->get()->map(fn ($material) => [
                'id' => $material->getKey(),
                'nombre' => $material->nombre ?? $material->descripcion ?? 'Material '.$material->getKey(),
            ]); 
    }
}
