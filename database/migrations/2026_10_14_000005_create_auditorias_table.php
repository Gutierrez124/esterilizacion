<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('accion', 80);
            $table->string('entidad', 80)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->unsignedBigInteger('proceso_id')->nullable();

            $table->string('area', 20)->default('SISTEMA');
            $table->text('descripcion');

            $table->json('datos_adicionales')->nullable();
            $table->dateTime('created_at')->nullable();

            $table->foreign('proceso_id')->references('id')->on('proceso_esterilizacion')->nullOnDelete();

            $table->index('usuario_id');
            $table->index('proceso_id');
            $table->index('accion');
            $table->index('area');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
