<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {

            $table->foreignId('responsable_id')
                ->nullable()
                ->after('usuario_id')
                ->constrained('usuarios')
                ->nullOnDelete();
        });

        Schema::table('auditorias', function (Blueprint $table) {

            $table->index(['usuario_id', 'created_at'], 'auditorias_usuario_fecha_idx');
            $table->index(['accion', 'created_at'], 'auditorias_accion_fecha_idx');
            $table->index(['area', 'created_at'], 'auditorias_area_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {
            $table->dropIndex('auditorias_usuario_fecha_idx');
            $table->dropIndex('auditorias_accion_fecha_idx');
            $table->dropIndex('auditorias_area_fecha_idx');
            $table->dropForeign(['responsable_id']);
            $table->dropColumn('responsable_id');
        });
    }
};
