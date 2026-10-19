<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // El catalogo de materiales/instrumentos/piezas ya esta cargado en la BD.
        // MaterialesSeeder.php se conserva SOLO como respaldo para reconstruirlo
        // en una instalacion nueva; no se ejecuta aqui por defecto.
        $this->call(AdminSeeder::class);
    }
}
