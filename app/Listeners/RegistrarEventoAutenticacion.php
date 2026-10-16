<?php

namespace App\Listeners;

use App\Services\AuditoriaService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class RegistrarEventoAutenticacion
{
    public function __construct(private AuditoriaService $auditoria)
    {
    }

    public function handleLogin(Login $event): void
    {
        $this->auditoria->registrar(
            accion: 'LOGIN_EXITOSO',
            area: 'AUTENTICACION',
            descripcion: 'Inicio de sesion exitoso.',
            entidad: 'usuarios',
            entidadId: $event->user->getKey(),
            usuario: $event->user,
            datos: ['email' => $event->user->email],
        );
    }

    public function handleLogout(Logout $event): void
    {
        $usuario = $event->user;

        $this->auditoria->registrar(
            accion: 'LOGOUT',
            area: 'AUTENTICACION',
            descripcion: 'Cierre de sesion.',
            entidad: 'usuarios',
            entidadId: $usuario?->getKey(),
            usuario: $usuario,
        );
    }

    public function handleFailed(Failed $event): void
    {
        $this->auditoria->registrar(
            accion: 'LOGIN_FALLIDO',
            area: 'AUTENTICACION',
            descripcion: 'Intento de inicio de sesion fallido.',
            entidad: 'usuarios',
            usuario: null,
            datos: ['email' => $event->credentials['email'] ?? null],
        );
    }
}
