<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instrumentos', function (Blueprint $table) {
            $table->dropUnique(['codigo']);
            $table->index('codigo');
        });
    }

    public function down(): void
    {
        Schema::table('instrumentos', function (Blueprint $table) {
            $table->dropIndex(['codigo']);
            $table->unique('codigo');
        });
    }
};
