<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Models\Instrumento;
use App\Models\Material;
use App\Models\Servicio;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InstrumentalController extends Controller
{
    public function index(Request $request): View
    {
        $conjuntos = Material::query()
            ->with('servicio')
            ->withCount('instrumentos')
            ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', '%'.$request->buscar.'%'))
            ->when($request->filled('servicio_id'), fn ($q) => $q->where('servicio_id', $request->servicio_id))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('esterilizacion.instrumental.index', [
            'conjuntos' => $conjuntos,
            'servicios' => Servicio::query()->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request, AuditoriaService $auditoria): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150', Rule::unique('materiales', 'nombre')->where(fn ($q) => $q->where('servicio_id', $request->input('servicio_id')))],
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
        ], [
            'nombre.unique' => 'Ya existe un conjunto con ese nombre en el servicio seleccionado.',
        ]);

        $conjunto = Material::create($data + ['activo' => true, 'creado_en' => now()]);

        $auditoria->registrar(
            accion: 'INSTRUMENTAL_CREADO',
            area: 'ROJA',
            descripcion: "Conjunto de instrumental creado: {$conjunto->nombre}.",
            entidad: 'materiales',
            entidadId: $conjunto->getKey(),
        );

        return redirect()
            ->route('esterilizacion.instrumental.show', $conjunto)
            ->with('success', 'Conjunto creado correctamente.');
    }

    public function show(Material $material): View
    {
        $material->load(['servicio', 'instrumentos' => fn ($q) => $q->orderBy('nombre')]);

        return view('esterilizacion.instrumental.show', [
            'conjunto' => $material,
            'servicios' => Servicio::query()->orderBy('nombre')->get(),
            'instrumentos' => Instrumento::query()->where('activo', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Material $material, AuditoriaService $auditoria): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150', Rule::unique('materiales', 'nombre')->where(fn ($q) => $q->where('servicio_id', $request->input('servicio_id')))->ignore($material->getKey())],
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
        ]);

        $material->update($data);

        $auditoria->registrar(
            accion: 'INSTRUMENTAL_ACTUALIZADO',
            area: 'ROJA',
            descripcion: "Conjunto de instrumental actualizado: {$material->nombre}.",
            entidad: 'materiales',
            entidadId: $material->getKey(),
        );

        return redirect()
            ->route('esterilizacion.instrumental.show', $material)
            ->with('success', 'Conjunto actualizado correctamente.');
    }

    public function agregarInstrumentos(Request $request, Material $material, AuditoriaService $auditoria): RedirectResponse
    {
        $request->validate([
            'instrumento_id' => ['nullable', 'integer', 'exists:instrumentos,id'],
            'cantidad_predeterminada' => ['required', 'integer', 'min:1'],
            'nuevo_instrumento' => ['nullable', 'string', 'max:150', 'required_without:instrumento_id'],
            'nuevo_codigo' => ['nullable', 'string', 'max:60', Rule::unique('instrumentos', 'codigo')],
        ]);

        DB::transaction(function () use ($request, $material, $auditoria) {
            $instrumentoId = (int) $request->input('instrumento_id');

            if ($request->filled('nuevo_instrumento')) {
                $instrumento = Instrumento::create([
                    'nombre' => $request->input('nuevo_instrumento'),
                    'codigo' => $request->input('nuevo_codigo'),
                    'activo' => true,
                    'created_at' => now(),
                ]);
                $instrumentoId = $instrumento->getKey();
            }

            $material->instrumentos()->syncWithoutDetaching([
                $instrumentoId => ['cantidad_predeterminada' => (int) $request->input('cantidad_predeterminada'), 'created_at' => now()],
            ]);

            $auditoria->registrar(
                accion: 'INSTRUMENTAL_ACTUALIZADO',
                area: 'ROJA',
                descripcion: "Pieza agregada al conjunto {$material->nombre}.",
                entidad: 'material_instrumentos',
                entidadId: $material->getKey(),
                datos: ['instrumento_id' => $instrumentoId],
            );
        });

        return redirect()
            ->route('esterilizacion.instrumental.show', $material)
            ->with('success', 'Pieza agregada al conjunto.');
    }

    public function actualizarPieza(Request $request, Material $material, Instrumento $instrumento, AuditoriaService $auditoria): RedirectResponse
    {
        $data = $request->validate([
            'cantidad_predeterminada' => ['required', 'integer', 'min:1'],
        ]);

        $material->instrumentos()->updateExistingPivot($instrumento->getKey(), [
            'cantidad_predeterminada' => $data['cantidad_predeterminada'],
        ]);

        $auditoria->registrar(
            accion: 'INSTRUMENTAL_ACTUALIZADO',
            area: 'ROJA',
            descripcion: "Cantidad predeterminada actualizada en {$material->nombre}.",
            entidad: 'material_instrumentos',
            entidadId: $material->getKey(),
            datos: ['instrumento_id' => $instrumento->getKey()],
        );

        return redirect()
            ->route('esterilizacion.instrumental.show', $material)
            ->with('success', 'Cantidad actualizada.');
    }

    public function quitarPieza(Material $material, Instrumento $instrumento, AuditoriaService $auditoria): RedirectResponse
    {
        $material->instrumentos()->detach($instrumento->getKey());

        $auditoria->registrar(
            accion: 'INSTRUMENTAL_DESACTIVADO',
            area: 'ROJA',
            descripcion: "Pieza retirada del conjunto {$material->nombre}.",
            entidad: 'material_instrumentos',
            entidadId: $material->getKey(),
            datos: ['instrumento_id' => $instrumento->getKey()],
        );

        return redirect()
            ->route('esterilizacion.instrumental.show', $material)
            ->with('success', 'Pieza retirada del conjunto.');
    }

    public function cambiarEstado(Request $request, Material $material, AuditoriaService $auditoria): RedirectResponse
    {
        $material->update(['activo' => $request->boolean('activo')]);

        $auditoria->registrar(
            accion: 'INSTRUMENTAL_DESACTIVADO',
            area: 'ROJA',
            descripcion: "Estado del conjunto {$material->nombre} cambiado a ".($material->activo ? 'activo' : 'inactivo').'.',
            entidad: 'materiales',
            entidadId: $material->getKey(),
        );

        return redirect()->back()->with('success', 'Estado del conjunto actualizado.');
    }

    public function cambiarEstadoInstrumento(Request $request, Instrumento $instrumento, AuditoriaService $auditoria): RedirectResponse
    {
        $instrumento->update(['activo' => $request->boolean('activo')]);

        $auditoria->registrar(
            accion: 'INSTRUMENTAL_DESACTIVADO',
            area: 'ROJA',
            descripcion: "Estado del instrumento {$instrumento->nombre} cambiado a ".($instrumento->activo ? 'activo' : 'inactivo').'.',
            entidad: 'instrumentos',
            entidadId: $instrumento->getKey(),
        );

        return redirect()->back()->with('success', 'Estado del instrumento actualizado.');
    }
}

