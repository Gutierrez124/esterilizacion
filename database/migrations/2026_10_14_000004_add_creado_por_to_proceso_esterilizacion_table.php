<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proceso_esterilizacion', function (Blueprint $table) {

            $table->foreignId('creado_por_id')->nullable()->after('operador_id')
                ->constrained('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('proceso_esterilizacion', function (Blueprint $table) {
            $table->dropForeign(['creado_por_id']);
            $table->dropColumn('creado_por_id');
        });
    }
};
