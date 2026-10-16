<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TIPO_CARGA = ['INSTRUMENTAL', 'TEXTIL', 'GOMA'];

    private const TURNO = ['DIURNO', 'NOCTURNO'];

    private const TIPO_LAVADO = ['ULTRASONICA', 'MANUAL', 'LAVADORA_DESCONTAMINADORA', 'NO_CORRESPONDE'];

    private const ESTADOS = ['ROJA', 'AZUL', 'VERDE'];

    public function up(): void
    {

        $this->asegurarSoloValoresPermitidos('area_azul', 'tipo_carga', self::TIPO_CARGA);
        $this->asegurarSoloValoresPermitidos('proceso_esterilizacion', 'turno', self::TURNO);
        $this->asegurarSoloValoresPermitidos('proceso_esterilizacion', 'tipo_lavado', self::TIPO_LAVADO);
        $this->asegurarSoloValoresPermitidos('proceso_esterilizacion', 'estado_actual', self::ESTADOS);

        $this->eliminarCheck('area_azul', 'CK_azul_tipo_carga');
        $this->eliminarCheck('proceso_esterilizacion', 'CK_proceso_turno');
        $this->eliminarCheck('proceso_esterilizacion', 'CK_proceso_tipo_lavado');
        $this->eliminarCheck('proceso_esterilizacion', 'CK_proceso_estado');

        DB::statement("ALTER TABLE area_azul MODIFY tipo_carga ENUM('INSTRUMENTAL', 'TEXTIL', 'GOMA') NOT NULL");
        DB::statement("ALTER TABLE proceso_esterilizacion MODIFY turno ENUM('DIURNO', 'NOCTURNO') NOT NULL");
        DB::statement("ALTER TABLE proceso_esterilizacion MODIFY tipo_lavado ENUM('ULTRASONICA', 'MANUAL', 'LAVADORA_DESCONTAMINADORA', 'NO_CORRESPONDE') NOT NULL");
        DB::statement("ALTER TABLE proceso_esterilizacion MODIFY estado_actual ENUM('ROJA', 'AZUL', 'VERDE') NOT NULL DEFAULT 'ROJA'");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE area_azul MODIFY tipo_carga VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE proceso_esterilizacion MODIFY turno VARCHAR(10) NOT NULL');
        DB::statement('ALTER TABLE proceso_esterilizacion MODIFY tipo_lavado VARCHAR(30) NOT NULL');
        DB::statement("ALTER TABLE proceso_esterilizacion MODIFY estado_actual VARCHAR(10) NOT NULL DEFAULT 'ROJA'");

        $this->crearCheckSiFalta('area_azul', 'CK_azul_tipo_carga', "CHECK (tipo_carga IN ('INSTRUMENTAL', 'TEXTIL', 'GOMA'))");
        $this->crearCheckSiFalta('proceso_esterilizacion', 'CK_proceso_turno', "CHECK (turno IN ('DIURNO', 'NOCTURNO'))");
        $this->crearCheckSiFalta('proceso_esterilizacion', 'CK_proceso_tipo_lavado', "CHECK (tipo_lavado IN ('ULTRASONICA', 'MANUAL', 'LAVADORA_DESCONTAMINADORA', 'NO_CORRESPONDE'))");
        $this->crearCheckSiFalta('proceso_esterilizacion', 'CK_proceso_estado', "CHECK (estado_actual IN ('ROJA', 'AZUL', 'VERDE'))");
    }

    private function asegurarSoloValoresPermitidos(string $tabla, string $columna, array $permitidos): void
    {
        $invalidos = DB::table($tabla)
            ->whereNotNull($columna)
            ->whereNotIn($columna, $permitidos)
            ->distinct()
            ->pluck($columna);

        if ($invalidos->isNotEmpty()) {
            throw new RuntimeException(
                "No se puede convertir {$tabla}.{$columna} a ENUM. Valores no permitidos: ["
                .$invalidos->implode(' | ').
                ']. Corrige esos datos y vuelve a ejecutar la migracion.'
            );
        }
    }

    private function eliminarCheck(string $tabla, string $nombre): void
    {
        $existe = DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$tabla, $nombre, 'CHECK']
        );

        if ((int) $existe->total === 0) {
            return;
        }

        try {

            DB::statement("ALTER TABLE {$tabla} DROP CONSTRAINT {$nombre}");
        } catch (\Throwable $e) {

            DB::statement("ALTER TABLE {$tabla} DROP CHECK {$nombre}");
        }
    }

    private function crearCheckSiFalta(string $tabla, string $nombre, string $condicion): void
    {
        $existe = DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [$tabla, $nombre]
        );

        if ((int) $existe->total === 0) {
            DB::statement("ALTER TABLE {$tabla} ADD CONSTRAINT {$nombre} {$condicion}");
        }
    }
};
