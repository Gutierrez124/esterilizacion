<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('area_verde', function (Blueprint $table) {
            $table->foreignId('descarga_id')->after('recibe_id')->constrained('usuarios');
            $table->dropColumn('hora_descarga');
        });
    }

    public function down(): void
    {
        Schema::table('area_verde', function (Blueprint $table) {
            $table->time('hora_descarga')->nullable()->after('hora_entrega');
            $table->dropConstrainedForeignId('descarga_id');
        });
    }
};
