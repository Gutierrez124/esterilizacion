<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Elimina las auditorias de autenticacion (LOGIN_EXITOSO, LOGIN_FALLIDO, LOGOUT).
     * Son datos viejos que ya no se muestran en el modulo de reportes Excel
     * y que tampoco vuelven a registrarse (el listener de autenticacion fue retirado).
     */
    public function up(): void
    {
        DB::table('auditorias')
            ->whereIn('accion', ['LOGIN_EXITOSO', 'LOGIN_FALLIDO', 'LOGOUT'])
            ->delete();
    }

    public function down(): void
    {
        // Los registros eliminados no se pueden recuperar.
    }
};