<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMPAQUE = ['PAPEL CREPADO', 'MANGA', 'PAPEL NO TEJIDO'];

    private const IB = ['VIRO', 'NO VIRO', 'SI VIRO', 'REACCIONO', 'NO REACCIONO'];

    public function up(): void
    {
        $this->normalizarInvalidosANulo('area_azul', 'empaque', self::EMPAQUE);
        $this->normalizarInvalidosANulo('area_azul', 'ib', self::IB);

        DB::statement("ALTER TABLE area_azul MODIFY empaque ENUM('PAPEL CREPADO', 'MANGA', 'PAPEL NO TEJIDO') NULL DEFAULT NULL");
        DB::statement("ALTER TABLE area_azul MODIFY ib ENUM('VIRO', 'NO VIRO', 'SI VIRO', 'REACCIONO', 'NO REACCIONO') NULL DEFAULT NULL");
    }

    public function down(): void
    {

        DB::statement('ALTER TABLE area_azul MODIFY empaque VARCHAR(150) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE area_azul MODIFY ib VARCHAR(100) NULL DEFAULT NULL');
    }

    private function normalizarInvalidosANulo(string $tabla, string $columna, array $permitidos): void
    {
        $consulta = DB::table($tabla)->whereNotNull($columna)->whereNotIn($columna, $permitidos);
        $invalidos = (clone $consulta)->distinct()->pluck($columna);

        if ($invalidos->isEmpty()) {
            return;
        }

        $consulta->update([$columna => null]);

        echo '  [AVISO] '.$tabla.'.'.$columna.': '.count($invalidos)
            .' valor(es) fuera del catalogo normalizados a NULL -> '.$invalidos->implode(' | ').PHP_EOL;
    }
};
