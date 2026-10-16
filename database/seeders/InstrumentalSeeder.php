<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstrumentalSeeder extends Seeder
{
    public function run(): void
    {

        $catalogo = [
            ['nombre' => 'Pinza Mosquito Curva', 'codigo' => '15-0121'],
            ['nombre' => 'Pinza Mosquito Recta', 'codigo' => '15-0120'],
            ['nombre' => 'Pinza Diseccion C/U', 'codigo' => '14-0623'],
            ['nombre' => 'Pinza Diseccion S/U', 'codigo' => '14-0123'],
            ['nombre' => 'Porta Aguja Mayo Hegar', 'codigo' => '18.1080'],
            ['nombre' => 'Tijera Metzembaum Curva', 'codigo' => '13-0601'],
            ['nombre' => 'Tijera Mayo Recta', 'codigo' => '11-170-14-07'],
            ['nombre' => 'Sonda Acanalada', 'codigo' => '19-0322'],
            ['nombre' => 'Mango Bisturi # 3', 'codigo' => '12-0103'],
            ['nombre' => 'Mango Bisturi # 4', 'codigo' => '12-0104'],
            ['nombre' => 'Estilete', 'codigo' => 'CA-018-18'],
            ['nombre' => 'Separador de Farabeuf', 'codigo' => '17-0600'],
            ['nombre' => 'Cubeta de Acero Qx con Tapa', 'codigo' => null],
        ];

        $instrumentoIds = [];
        foreach ($catalogo as $pieza) {
            $existente = DB::table('instrumentos')->where('nombre', $pieza['nombre'])->first();
            if ($existente) {
                $instrumentoIds[$pieza['nombre']] = $existente->id;
                continue;
            }
            $instrumentoIds[$pieza['nombre']] = DB::table('instrumentos')->insertGetId([
                'nombre' => $pieza['nombre'],
                'codigo' => $pieza['codigo'],
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $servicioId = DB::table('servicios')->where('nombre', 'SOP')->value('id') ?? 1;

        $material = DB::table('materiales')
            ->where('servicio_id', $servicioId)
            ->where('nombre', 'CIRUGIA MENOR I')
            ->first();

        if (! $material) {
            $materialId = DB::table('materiales')->insertGetId([
                'servicio_id' => $servicioId,
                'nombre' => 'CIRUGIA MENOR I',
                'activo' => 1,
                'creado_en' => now(),
            ]);
        } else {
            $materialId = $material->id;
        }

        $piezasConjunto = [
            ['Pinza Mosquito Curva', 3],
            ['Pinza Mosquito Recta', 1],
            ['Pinza Diseccion C/U', 1],
            ['Pinza Diseccion S/U', 1],
            ['Porta Aguja Mayo Hegar', 1],
            ['Tijera Metzembaum Curva', 1],
            ['Tijera Mayo Recta', 1],
            ['Sonda Acanalada', 1],
            ['Mango Bisturi # 3', 1],
            ['Mango Bisturi # 4', 1],
            ['Estilete', 1],
            ['Separador de Farabeuf', 2],
            ['Cubeta de Acero Qx con Tapa', 1],
        ];

        foreach ($piezasConjunto as [$nombre, $cantidad]) {
            $instrumentoId = $instrumentoIds[$nombre] ?? null;
            if (! $instrumentoId) {
                continue;
            }
            $existe = DB::table('material_instrumentos')
                ->where('material_id', $materialId)
                ->where('instrumento_id', $instrumentoId)
                ->exists();
            if ($existe) {
                continue;
            }
            DB::table('material_instrumentos')->insert([
                'material_id' => $materialId,
                'instrumento_id' => $instrumentoId,
                'cantidad_predeterminada' => $cantidad,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
