<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

class AuditoriaService
{
    public const AREA_AUTENTICACION = 'AUTENTICACION';

    public const ACCIONES_AUTENTICACION = ['LOGIN_EXITOSO', 'LOGIN_FALLIDO', 'LOGOUT'];

    public function registrar(
        string $accion,
        string $area = 'SISTEMA',
        string $descripcion = '',
        ?string $entidad = null,
        ?int $entidadId = null,
        ?int $procesoId = null,
        ?array $datos = null,
        ?Usuario $usuario = null,
        ?int $responsableId = null
    ): Auditoria {
        return Auditoria::create([
            'usuario_id' => $usuario?->getKey() ?? Auth::id(),
            'responsable_id' => $responsableId ?? Auth::id() ?? $usuario?->getKey(),
            'accion' => $accion,
            'area' => $area,
            'descripcion' => $descripcion,
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'proceso_id' => $procesoId,
            'datos_adicionales' => $datos ? $this->limpiarDatos($datos) : null,
            'created_at' => now(),
        ]);
    }

    private function limpiarDatos(array $datos): array
    {
        $prohibidas = ['password', 'contrasena', 'token', 'remember_token', 'csrf', 'secret', '_token', 'cookie'];

        foreach ($datos as $clave => $valor) {
            $claveLower = strtolower((string) $clave);
            foreach ($prohibidas as $palabra) {
                if (str_contains($claveLower, $palabra)) {
                    unset($datos[$clave]);
                    break;
                }
            }
        }

        return $datos;
    }
}
