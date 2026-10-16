<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use function Laravel\Prompts\password as promptPassword;
use function Laravel\Prompts\text;

class CrearAdministrador extends Command
{
    protected $signature = 'usuario:crear-admin
                            {--nombres= : Nombres del administrador}
                            {--apellidos= : Apellidos del administrador}
                            {--email= : Correo electronico unico}
                            {--password= : Contrasena (si se omite, se solicita de forma segura)}';

    protected $description = 'Crea o actualiza un usuario administrador de forma controlada.';

    public function handle(): int
    {
        $nombres = $this->option('nombres') ?? text(label: 'Nombres', required: true);
        $apellidos = $this->option('apellidos') ?? text(label: 'Apellidos', required: true);
        $email = $this->option('email') ?? text(
            label: 'Correo electronico',
            placeholder: 'admin@hospital.local',
            required: true,
            validate: fn (string $value) => ! filter_var($value, FILTER_VALIDATE_EMAIL)
                ? 'El correo no es valido.'
                : (Usuario::where('email', $value)->exists() ? 'El correo ya esta registrado.' : null)
        );

        $password = $this->option('password') ?? promptPassword(label: 'Contrasena', required: true);

        if (strlen($password) < 8) {
            $this->error('La contrasena debe tener al menos 8 caracteres.');

            return self::FAILURE;
        }

        $usuario = Usuario::create([
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'email' => $email,
            'password' => Hash::make($password),
            'rol' => 'ADMIN',
            'activo' => true,
        ]);

        $this->info("Administrador creado: {$usuario->email} (ID {$usuario->getKey()}).");

        return self::SUCCESS;
    }
}
