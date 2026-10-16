<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAreaRojaRequest;
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
            'usuarios' => Usuario::query()->where('activo', 1)->orderBy('id')->get(),
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

    public function piezas(Material $material)
    {
        $material->load(['instrumentos' => fn ($q) => $q->where('activo', 1)->orderBy('nombre')]);

        return response()->json([
            'material_id' => $material->getKey(),
            'nombre' => $material->nombre,
            'piezas' => $material->instrumentos->map(fn ($instrumento) => [
                'instrumento_id' => $instrumento->getKey(),
                'nombre' => $instrumento->nombre,
                'codigo' => $instrumento->codigo,
                'cantidad_predeterminada' => (int) $instrumento->pivot->cantidad_predeterminada,
            ])->values(),
        ]);
    }
}
