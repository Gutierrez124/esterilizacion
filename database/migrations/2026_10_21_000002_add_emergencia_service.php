<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('servicios')->insertOrIgnore([
            'nombre' => 'EMERGENCIA',
            'activo' => true,
            'creado_en' => now(),
        ]);
    }

    public function down(): void
    {
        // Keep the catalog entry so a rollback cannot orphan materials or processes.
    }
};
