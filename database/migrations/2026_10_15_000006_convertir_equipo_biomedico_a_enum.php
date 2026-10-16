<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE proceso_esterilizacion ADD equipo_biomedico ENUM('AUTOCLAVE') NULL AFTER tipo_lavado");
        DB::statement("UPDATE proceso_esterilizacion SET equipo_biomedico = 'AUTOCLAVE'");
        DB::statement("ALTER TABLE proceso_esterilizacion MODIFY equipo_biomedico ENUM('AUTOCLAVE') NOT NULL");

        Schema::table('proceso_esterilizacion', function (Blueprint $table) {
            $table->dropForeign(['equipo_biomedico_id']);
            $table->dropColumn('equipo_biomedico_id');
        });

        Schema::dropIfExists('equipos_biomedicos');
    }

    public function down(): void
    {
        Schema::create('equipos_biomedicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->boolean('activo')->default(true);
            $table->dateTime('creado_en')->useCurrent();
        });

        DB::table('equipos_biomedicos')->insert(['nombre' => 'AUTOCLAVE']);

        Schema::table('proceso_esterilizacion', function (Blueprint $table) {
            $table->foreignId('equipo_biomedico_id')->nullable()->after('tipo_lavado')->constrained('equipos_biomedicos');
        });

        DB::statement('UPDATE proceso_esterilizacion SET equipo_biomedico_id = 1');
        DB::statement('ALTER TABLE proceso_esterilizacion MODIFY equipo_biomedico_id BIGINT UNSIGNED NOT NULL');

        Schema::table('proceso_esterilizacion', function (Blueprint $table) {
            $table->dropColumn('equipo_biomedico');
        });
    }
};
