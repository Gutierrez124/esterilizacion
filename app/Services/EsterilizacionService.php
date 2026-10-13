<?php

namespace App\Services;

use App\Models\AreaAzul;
use App\Models\AreaVerde;
use App\Models\ProcesoEsterilizacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EsterilizacionService
{
    public function iniciarAreaRoja(array $data): ProcesoEsterilizacion
    {
        return DB::transaction(function () use ($data) {
$data['estado_actual'] = ProcesoEsterilizacion::ESTADO_ROJA;
$data['recibe_id'] = $data['persona_recibe_id'];
$data['entrega_id'] = $data['persona_entrega_id'];
            unset($data['persona_recibe_id'], $data['persona_entrega_id']);

            return ProcesoEsterilizacion::create($data);
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
$areaAzul = $proceso->areaAzul()->create($data);
$proceso->update(['estado_actual' => ProcesoEsterilizacion::ESTADO_AZUL]);

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
                'hora_descarga' => $data['hora_descarga'],
                'entrega_id' => $data['persona_entrega_id'],
                'recibe_id' => $data['persona_recibe_id'],
                'observacion' => $data['observacion'] ?? null,
            ]);
$proceso->update(['estado_actual' => ProcesoEsterilizacion::ESTADO_VERDE]);

            return $areaVerde;
        });
    }
}
