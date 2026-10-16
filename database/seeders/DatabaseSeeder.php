<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('usuarios')->insert([
            ['nombres' => 'Zoila', 'apellidos' => 'Fonseca'],
            ['nombres' => 'Elizabeth', 'apellidos' => 'Aguirre'],
            ['nombres' => 'Maria', 'apellidos' => 'Rodriguez'],
            ['nombres' => 'Carlos', 'apellidos' => 'Mendoza'],
            ['nombres' => 'Ana', 'apellidos' => 'Torres'],
        ]);

        DB::table('servicios')->insert([
            ['nombre' => 'SOP'],
            ['nombre' => 'CENTRO OBSTETRICO'],
            ['nombre' => 'NEONATOLOGIA'],
            ['nombre' => 'HOSPITALIZACION'],
            ['nombre' => 'GINECO EMERGENCIA'],
        ]);

        DB::table('materiales')->insert([
            ['servicio_id' => 1, 'nombre' => 'CIRUGIA MAYOR'],
            ['servicio_id' => 1, 'nombre' => 'PAQUETES DE GASA 10X10'],
            ['servicio_id' => 1, 'nombre' => 'MANDILONES'],
            ['servicio_id' => 2, 'nombre' => 'EQUIPO DE PARTO'],
            ['servicio_id' => 2, 'nombre' => 'PINZA ROMPE BOLSA'],
            ['servicio_id' => 2, 'nombre' => 'PINZA FOERSTER CURVA'],
            ['servicio_id' => 2, 'nombre' => 'TIJERA DE EPISIOTOMIA CURVA'],
            ['servicio_id' => 3, 'nombre' => 'EQUIPO DE CURACION'],
            ['servicio_id' => 4, 'nombre' => 'FCO DE ASPIRACION'],
            ['servicio_id' => 4, 'nombre' => 'CONEXIONES'],
            ['servicio_id' => 5, 'nombre' => 'RINONERA'],
        ]);

        $this->call(InstrumentalSeeder::class);

        $this->call(AdminSeeder::class);
    }
}
