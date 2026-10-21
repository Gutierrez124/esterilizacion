<?php

namespace App\Services;

use App\Models\AreaAzul;
use App\Models\AreaVerde;
use App\Models\Material;
use App\Models\ProcesoEsterilizacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EsterilizacionService
{
    public function __construct(private AuditoriaService $auditoria)
    {
    }

    public function iniciarAreaRoja(array $data): ProcesoEsterilizacion
    {
        return DB::transaction(function () use ($data) {
            $usuario = Auth::user();

            $creadoPorId = $usuario?->getKey();

            $detalle = [];
            $totalReal = 0;
            $confirmados = collect($data['materiales_confirmados'] ?? []);
            $llavesMaterial = [];
            foreach ($confirmados as $indice => $grupo) {
                $material = Material::query()->with(['instrumentos' => fn ($q) => $q->where('activo', 1)])
                    ->where('activo', 1)->find($grupo['material_id']);
                if (! $material || (int) $material->servicio_id !== (int) $grupo['servicio_id']) {
                    throw ValidationException::withMessages(["materiales_confirmados.{$indice}.material_id" => 'El material no pertenece al servicio indicado o está inactivo.']);
                }
                if (isset($llavesMaterial[$material->getKey()])) {
                    throw ValidationException::withMessages(["materiales_confirmados.{$indice}.material_id" => 'No puedes agregar el mismo material más de una vez.']);
                }
                $llavesMaterial[$material->getKey()] = true;
                $catalogo = $material->instrumentos->keyBy(fn ($i) => (int) $i->getKey());
                $ids = [];
                foreach ($grupo['piezas'] as $pieza) {
                    $id = (int) $pieza['instrumento_id'];
                    if (isset($ids[$id]) || ! $catalogo->has($id)) {
                        throw ValidationException::withMessages(["materiales_confirmados.{$indice}.piezas" => 'Hay instrumentos repetidos o que no pertenecen al material confirmado.']);
                    }
                    $ids[$id] = true;
                    $instrumento = $catalogo[$id];
                    $cantidad = (int) $pieza['cantidad_real'];
                    $detalle[] = [
                        'servicio_id' => (int) $material->servicio_id,
                        'material_id' => (int) $material->getKey(),
                        'instrumento_id' => $id,
                        'nombre_instrumento' => $instrumento->nombre,
                        'codigo_instrumento' => $instrumento->codigo,
                        'cantidad_predeterminada' => (int) $instrumento->pivot->cantidad_predeterminada,
                        'cantidad_real' => $cantidad,
                        'created_at' => now(), 'updated_at' => now(),
                    ];
                    $totalReal += $cantidad;
                }
            }
            if ($totalReal < 1) {
                throw ValidationException::withMessages([
                    'piezas' => 'El total de piezas recibidas debe ser al menos 1.',
                ]);
            }

            $proceso = ProcesoEsterilizacion::create([
                'fecha' => $data['fecha'],
                'turno' => $data['turno'],
                'hora' => $data['hora'],
                'recibe_id' => $data['persona_recibe_id'],
                'entrega_id' => $data['persona_entrega_id'],
                'cantidad_piezas' => $totalReal,
                'tipo_lavado' => $data['tipo_lavado'],
                'equipo_biomedico' => $data['equipo_biomedico'],
                'operador_id' => $data['operador_id'],
                'estado_actual' => ProcesoEsterilizacion::ESTADO_ROJA,
                'creado_por_id' => $creadoPorId,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);

            foreach ($detalle as $pieza) {
                $proceso->instrumentos()->create($pieza);
            }

            $this->auditoria->registrar(
                accion: 'PROCESO_CREADO',
                area: 'ROJA',
                descripcion: "Proceso #{$proceso->getKey()} iniciado en Area Roja con {$totalReal} piezas.",
                entidad: 'proceso_esterilizacion',
                entidadId: $proceso->getKey(),
                procesoId: $proceso->getKey(),
                datos: ['materiales' => count($llavesMaterial), 'total_piezas' => $totalReal],
            );

            $this->auditoria->registrar(
                accion: 'MATERIAL_RECIBIDO',
                area: 'ROJA',
                descripcion: "Material recibido para el proceso #{$proceso->getKey()}.",
                entidad: 'proceso_esterilizacion',
                entidadId: $proceso->getKey(),
                procesoId: $proceso->getKey(),
                datos: ['total_piezas' => $totalReal],
                responsableId: (int) $data['persona_recibe_id'],
            );

            $this->auditoria->registrar(
                accion: 'MATERIAL_ENTREGADO',
                area: 'ROJA',
                descripcion: "Material entregado al proceso #{$proceso->getKey()}.",
                entidad: 'proceso_esterilizacion',
                entidadId: $proceso->getKey(),
                procesoId: $proceso->getKey(),
                responsableId: (int) $data['persona_entrega_id'],
            );

            return $proceso;
        });
    }

    public function registrarAreaAzul(ProcesoEsterilizacion $proceso, array $data): AreaAzul
    {
        return DB::transaction(function () use ($proceso, $data) {
            $proceso = ProcesoEsterilizacion::query()
                ->with('areaAzul')
                ->lockForUpdate()
                ->findOrFail($proceso->getKey());

            if ($proceso->estado_actual !== ProcesoEsterilizacion::ESTADO_ROJA) {
                throw ValidationException::withMessages([
                    'proceso' => 'Solo se puede registrar el Area Azul cuando el proceso esta en Area Roja.',
                ]);
            }

            if ($proceso->areaAzul()->exists()) {
                throw ValidationException::withMessages([
                    'proceso' => 'Este proceso ya tiene informacion registrada en el Area Azul.',
                ]);
            }

            $areaAzul = $proceso->areaAzul()->create($data + ['registrado_por_id' => Auth::id()]);
            $proceso->update(['estado_actual' => ProcesoEsterilizacion::ESTADO_AZUL]);

            $this->auditoria->registrar(
                accion: 'AREA_AZUL_COMPLETADA',
                area: 'AZUL',
                descripcion: "Area Azul registrada para el proceso #{$proceso->getKey()}.",
                entidad: 'area_azul',
                entidadId: $areaAzul->getKey(),
                procesoId: $proceso->getKey(),
            );

            return $areaAzul;
        });
    }

    public function registrarAreaVerde(ProcesoEsterilizacion $proceso, array $data): AreaVerde
    {
        return DB::transaction(function () use ($proceso, $data) {
            $proceso = ProcesoEsterilizacion::query()
                ->with(['areaAzul', 'areaVerde'])
                ->lockForUpdate()
                ->findOrFail($proceso->getKey());

            if ($proceso->estado_actual !== ProcesoEsterilizacion::ESTADO_AZUL || ! $proceso->areaAzul()->exists()) {
                throw ValidationException::withMessages([
                    'proceso' => 'Solo se puede registrar el Area Verde cuando el Area Azul ya fue completada.',
                ]);
            }

            if ($proceso->areaVerde()->exists()) {
                throw ValidationException::withMessages([
                    'proceso' => 'Este proceso ya tiene informacion registrada en el Area Verde.',
                ]);
            }

            $areaVerde = $proceso->areaVerde()->create([
                'fecha_entrega' => $data['fecha_entrega'],
                'hora_entrega' => $data['hora_entrega'],
                'descarga_id' => $data['descarga_id'],
                'entrega_id' => $data['persona_entrega_id'],
                'recibe_id' => $data['persona_recibe_id'],
                'observacion' => $data['observacion'] ?? null,
                'registrado_por_id' => Auth::id(),
            ]);
            $proceso->update(['estado_actual' => ProcesoEsterilizacion::ESTADO_VERDE]);

            $this->auditoria->registrar(
                accion: 'AREA_VERDE_COMPLETADA',
                area: 'VERDE',
                descripcion: "Area Verde registrada para el proceso #{$proceso->getKey()}. Proceso finalizado.",
                entidad: 'area_verde',
                entidadId: $areaVerde->getKey(),
                procesoId: $proceso->getKey(),
            );

            return $areaVerde;
        });
    }
}

