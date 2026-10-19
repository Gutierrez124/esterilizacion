<?php

namespace App\Listeners;

use App\Services\AuditoriaService;
use App\Models\Usuario;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class RegistrarEventoAutenticacion
{
    public function __construct(private readonly AuditoriaService $auditorias)
    {
    }

    public function handleLogin(Login $event): void
    {
        $this->auditorias->registrar(
            accion: 'LOGIN_EXITOSO',
            area: AuditoriaService::AREA_AUTENTICACION,
            descripcion: 'Inicio de sesion exitoso.',
            usuario: $event->user,
            datos: ['email' => $event->user?->email],
        );
    }

    public function handleLogout(Logout $event): void
    {
        $this->auditorias->registrar(
            accion: 'LOGOUT',
            area: AuditoriaService::AREA_AUTENTICACION,
            descripcion: 'Cierre de sesion.',
            usuario: $event->user,
            datos: ['email' => $event->user?->email],
        );
    }

    public function handleFailed(Failed $event): void
    {
        $email = (string) ($event->credentials['email'] ?? '');
        $usuario = Usuario::query()->where('email', $email)->first();

        $this->auditorias->registrar(
            accion: 'LOGIN_FALLIDO',
            area: AuditoriaService::AREA_AUTENTICACION,
            descripcion: 'Intento de inicio de sesion fallido.',
            usuario: $usuario,
            datos: [
                'email' => $email,
                'ip' => request()->ip(),
            ],
        );
    }
}
