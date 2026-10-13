<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
$table->id();
$table->string('nombres', 100);
$table->string('apellidos', 100);
$table->boolean('activo')->default(true);
$table->dateTime('creado_en')->useCurrent();
        });

        Schema::create('servicios', function (Blueprint $table) {
$table->id();
$table->string('nombre', 100)->unique();
$table->boolean('activo')->default(true);
$table->dateTime('creado_en')->useCurrent();
        });

        Schema::create('materiales', function (Blueprint $table) {
$table->id();
$table->foreignId('servicio_id')->constrained('servicios');
$table->string('nombre', 150);
$table->boolean('activo')->default(true);
$table->dateTime('creado_en')->useCurrent();
$table->unique(['servicio_id', 'nombre']);
        });

        Schema::create('equipos_biomedicos', function (Blueprint $table) {
$table->id();
$table->string('nombre', 100)->unique();
$table->boolean('activo')->default(true);
$table->dateTime('creado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos_biomedicos');
        Schema::dropIfExists('materiales');
        Schema::dropIfExists('servicios');
        Schema::dropIfExists('usuarios');
    }
};
