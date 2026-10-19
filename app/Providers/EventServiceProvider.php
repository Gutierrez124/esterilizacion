<?php

namespace App\Providers;

use App\Listeners\RegistrarEventoAutenticacion;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            RegistrarEventoAutenticacion::class.'@handleLogin',
        ],
        Logout::class => [
            RegistrarEventoAutenticacion::class.'@handleLogout',
        ],
        Failed::class => [
            RegistrarEventoAutenticacion::class.'@handleFailed',
        ],
    ];

    public function boot(): void
    {

    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
