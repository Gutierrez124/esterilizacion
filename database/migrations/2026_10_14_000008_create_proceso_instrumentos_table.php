<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('proceso_instrumentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('proceso_esterilizacion')->cascadeOnDelete();

            $table->foreignId('instrumento_id')->nullable()->constrained('instrumentos')->nullOnDelete();
            $table->string('nombre_instrumento', 150);
            $table->string('codigo_instrumento', 60)->nullable();
            $table->integer('cantidad_predeterminada');
            $table->integer('cantidad_real');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('proceso_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_instrumentos');
    }
};
