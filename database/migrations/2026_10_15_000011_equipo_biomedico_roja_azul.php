<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Area Roja: eliminar el valor AUTOCLAVE y dejar solo los 4 equipos habilitados.
        // Puente por VARCHAR para no perder filas en modo strict (el valor viejo no existe en el enum nuevo).
        DB::statement('ALTER TABLE proceso_esterilizacion MODIFY equipo_biomedico VARCHAR(50) NULL');
        DB::statement("UPDATE proceso_esterilizacion SET equipo_biomedico = NULL WHERE equipo_biomedico = 'AUTOCLAVE'");
        DB::statement("ALTER TABLE proceso_esterilizacion MODIFY equipo_biomedico ENUM('LAVADOR_ULTRASONICO', 'BIDESTILADOR', 'LAVADORA_TERMODESINFECTADORA', 'PISTOLA_AIRE_COMPRIMIDO') NULL DEFAULT NULL AFTER tipo_lavado");

        // Area Azul: nuevo campo equipo_biomedico con sus 8 equipos habilitados.
        DB::statement("ALTER TABLE area_azul ADD equipo_biomedico ENUM('PUPINEL', 'SELLADORA_VAPOR', 'SELLADORA_PLASMA', 'INCUBADORA_INDICADOR_BIOLOGICO', 'AUTOCLAVE_250_LTR', 'AUTOCLAVE_160_LTR', 'ESTERILIZADOR_PLASMA', 'MAQUINA_CORTADORA_GASA') NULL AFTER tipo_carga");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE proceso_esterilizacion MODIFY equipo_biomedico VARCHAR(50) NULL');
        DB::statement("UPDATE proceso_esterilizacion SET equipo_biomedico = 'AUTOCLAVE'");
        DB::statement("ALTER TABLE proceso_esterilizacion MODIFY equipo_biomedico ENUM('AUTOCLAVE') NOT NULL AFTER tipo_lavado");

        DB::statement('ALTER TABLE area_azul DROP COLUMN equipo_biomedico');
    }
};
