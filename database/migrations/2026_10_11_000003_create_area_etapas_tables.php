<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_azul', function (Blueprint $table) {
$table->bigIncrements('id');
$table->unsignedBigInteger('proceso_id')->unique();
$table->time('hora')->nullable();
$table->string('empaque', 150)->nullable();
$table->string('tipo_carga', 20);
$table->integer('numero_carga')->nullable();
$table->string('ib', 100)->nullable();
$table->foreignId('operador_id')->constrained('usuarios');
$table->dateTime('creado_en')->useCurrent();
$table->dateTime('actualizado_en')->useCurrent();
$table->foreign('proceso_id')->references('id')->on('proceso_esterilizacion');
        });

        Schema::create('area_verde', function (Blueprint $table) {
$table->bigIncrements('id');
$table->unsignedBigInteger('proceso_id')->unique();
$table->date('fecha_entrega')->nullable();
$table->time('hora_entrega')->nullable();
$table->time('hora_descarga')->nullable();
$table->foreignId('entrega_id')->constrained('usuarios');
$table->foreignId('recibe_id')->constrained('usuarios');
$table->text('observacion')->nullable();
$table->dateTime('creado_en')->useCurrent();
$table->dateTime('actualizado_en')->useCurrent();
$table->foreign('proceso_id')->references('id')->on('proceso_esterilizacion');
        });

        DB::statement("ALTER TABLE area_azul ADD CONSTRAINT CK_azul_tipo_carga CHECK (tipo_carga IN ('INSTRUMENTAL', 'TEXTIL', 'GOMA'))");
        DB::statement('ALTER TABLE area_azul ADD CONSTRAINT CK_azul_numero_carga CHECK (numero_carga IS NULL OR numero_carga > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('area_verde');
        Schema::dropIfExists('area_azul');
    }
};
