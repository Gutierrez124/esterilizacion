<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrumentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);

            $table->string('codigo', 60)->nullable();
            $table->boolean('activo')->default(true);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique('codigo');
            $table->index('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrumentos');
    }
};
