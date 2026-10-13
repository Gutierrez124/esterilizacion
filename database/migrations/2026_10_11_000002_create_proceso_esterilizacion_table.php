<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proceso_esterilizacion', function (Blueprint $table) {
$table->bigIncrements('id');
$table->foreignId('servicio_id')->constrained('servicios');
$table->date('fecha');
$table->string('turno', 10);
$table->time('hora')->nullable();
$table->foreignId('recibe_id')->constrained('usuarios');
$table->foreignId('entrega_id')->constrained('usuarios');
$table->foreignId('material_id')->constrained('materiales');
$table->integer('cantidad_piezas');
$table->string('tipo_lavado', 30);
$table->foreignId('equipo_biomedico_id')->constrained('equipos_biomedicos');
$table->foreignId('operador_id')->constrained('usuarios');
$table->string('estado_actual', 10)->default('ROJA');
$table->dateTime('creado_en')->useCurrent();
$table->dateTime('actualizado_en')->useCurrent();
$table->index('fecha');
$table->index(['servicio_id', 'fecha']);
$table->index(['estado_actual', 'fecha']);
$table->index('material_id');
        });

        DB::statement("ALTER TABLE proceso_esterilizacion ADD CONSTRAINT CK_proceso_turno CHECK (turno IN ('DIURNO', 'NOCTURNO'))");
        DB::statement("ALTER TABLE proceso_esterilizacion ADD CONSTRAINT CK_proceso_tipo_lavado CHECK (tipo_lavado IN ('ULTRASONICA', 'MANUAL', 'LAVADORA_DESCONTAMINADORA', 'NO_CORRESPONDE'))");
        DB::statement('ALTER TABLE proceso_esterilizacion ADD CONSTRAINT CK_proceso_cantidad CHECK (cantidad_piezas > 0)');
        DB::statement("ALTER TABLE proceso_esterilizacion ADD CONSTRAINT CK_proceso_estado CHECK (estado_actual IN ('ROJA', 'AZUL', 'VERDE'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_esterilizacion');
    }
};
