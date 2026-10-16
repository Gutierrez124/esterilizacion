<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{

    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL/ADMIN_PASSWORD no definidos: se omite la creacion del administrador. Usa: php artisan usuario:crear-admin');

            return;
        }

        if (Usuario::where('email', $email)->exists()) {
            $this->command?->info("El administrador {$email} ya existe. Se omite.");

            return;
        }

        Usuario::create([
            'nombres' => env('ADMIN_NOMBRES', 'Administrador'),
            'apellidos' => env('ADMIN_APELLIDOS', 'del Sistema'),
            'email' => $email,
            'password' => Hash::make($password),
            'rol' => 'ADMIN',
            'activo' => true,
        ]);

        $this->command?->info("Administrador creado: {$email}");
    }
}
