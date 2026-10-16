<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('area_azul', function (Blueprint $table) {
            $table->foreignId('registrado_por_id')
                ->nullable()
                ->after('operador_id')
                ->constrained('usuarios')
                ->nullOnDelete();
            $table->index('registrado_por_id');
        });

        Schema::table('area_verde', function (Blueprint $table) {
            $table->foreignId('registrado_por_id')
                ->nullable()
                ->after('recibe_id')
                ->constrained('usuarios')
                ->nullOnDelete();
            $table->index('registrado_por_id');
        });

        $this->completarDesdeAuditoria('area_azul', ['AREA_AZUL_REGISTRADA', 'AREA_AZUL_COMPLETADA']);
        $this->completarDesdeAuditoria('area_verde', ['AREA_VERDE_REGISTRADA', 'AREA_VERDE_COMPLETADA']);
    }

    private function completarDesdeAuditoria(string $tabla, array $acciones): void
    {
        $mapa = DB::table('auditorias')
            ->whereIn('accion', $acciones)
            ->where('entidad', $tabla)
            ->whereNotNull('entidad_id')
            ->whereNotNull('usuario_id')
            ->orderBy('created_at')
            ->pluck('usuario_id', 'entidad_id');

        foreach ($mapa as $entidadId => $usuarioId) {
            DB::table($tabla)
                ->where('id', $entidadId)
                ->whereNull('registrado_por_id')
                ->update(['registrado_por_id' => $usuarioId]);
        }
    }

    public function down(): void
    {
        Schema::table('area_azul', function (Blueprint $table) {
            $table->dropForeign(['registrado_por_id']);
            $table->dropColumn('registrado_por_id');
        });

        Schema::table('area_verde', function (Blueprint $table) {
            $table->dropForeign(['registrado_por_id']);
            $table->dropColumn('registrado_por_id');
        });
    }
};
