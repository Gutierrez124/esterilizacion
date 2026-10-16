<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {

            $table->string('email')->nullable()->unique()->after('apellidos');
            $table->string('password')->nullable()->after('email');
            $table->rememberToken()->after('password');
            $table->string('rol', 20)->default('OPERADOR')->after('remember_token');
            $table->dateTime('actualizado_en')->nullable()->after('rol');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'password', 'remember_token', 'rol', 'actualizado_en']);
        });
    }
};
