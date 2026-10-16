<?php

namespace App\Services;

use App\Models\AreaAzul;
use App\Models\AreaVerde;
use App\Models\Material;
use App\Models\ProcesoEsterilizacion;
use App\Models\ProcesoInstrumento;
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

            $material = Material::query()
                ->with(['instrumentos' => fn ($q) => $q->where('activo', 1)])
                ->findOrFail($data['material_id']);

            $predeterminadas = $material->instrumentos
                ->mapWithKeys(fn ($instrumento) => [
                    $instrumento->getKey() => [
                        'nombre' => $instrumento->nombre,
                        'codigo' => $instrumento->codigo,
                        'cantidad_predeterminada' => (int) $instrumento->pivot->cantidad_predeterminada,
                    ],
                ]);

            $reales = collect($data['piezas'] ?? [])
                ->keyBy('instrumento_id')
                ->map(fn ($pieza) => (int) $pieza['cantidad_real']);

            $detalle = [];
            $totalReal = 0;

            foreach ($reales as $instrumentoId => $cantidadReal) {
                if (! $predeterminadas->has($instrumentoId)) {
                    throw ValidationException::withMessages([
                        'piezas' => 'El conjunto contiene piezas que no pertenecen a su catalogo.',
                    ]);
                }

                $info = $predeterminadas[$instrumentoId];
                $detalle[] = [
                    'instrumento_id' => (int) $instrumentoId,
                    'nombre_instrumento' => $info['nombre'],
                    'codigo_instrumento' => $info['codigo'],
                    'cantidad_predeterminada' => $info['cantidad_predeterminada'],
                    'cantidad_real' => $cantidadReal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $totalReal += $cantidadReal;
            }

            if ($totalReal < 1) {
                throw ValidationException::withMessages([
                    'piezas' => 'El total de piezas recibidas debe ser al menos 1.',
                ]);
            }

            $proceso = ProcesoEsterilizacion::create([
                'servicio_id' => $data['servicio_id'],
                'fecha' => $data['fecha'],
                'turno' => $data['turno'],
                'hora' => $data['hora'],
                'recibe_id' => $data['persona_recibe_id'],
                'entrega_id' => $data['persona_entrega_id'],
                'material_id' => $data['material_id'],
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
                $pieza['proceso_id'] = $proceso->getKey();
                ProcesoInstrumento::create($pieza);
            }

            $this->auditoria->registrar(
                accion: 'PROCESO_CREADO',
                area: 'ROJA',
                descripcion: "Proceso #{$proceso->getKey()} iniciado en Area Roja con {$totalReal} piezas.",
                entidad: 'proceso_esterilizacion',
                entidadId: $proceso->getKey(),
                procesoId: $proceso->getKey(),
                datos: ['material_id' => $proceso->material_id, 'total_piezas' => $totalReal],
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

