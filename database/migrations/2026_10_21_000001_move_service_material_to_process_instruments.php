<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $procesosSinDetalle = DB::table('proceso_esterilizacion as p')
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('proceso_instrumentos as pi')->whereColumn('pi.proceso_id', 'p.id'))
            ->count();

        $detallesSinRelacionCatalogo = DB::table('proceso_instrumentos as pi')
            ->join('proceso_esterilizacion as p', 'p.id', '=', 'pi.proceso_id')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('materiales as m')
                    ->join('material_instrumentos as mi', function ($join) {
                        $join->on('mi.material_id', '=', 'm.id')->on('mi.instrumento_id', '=', 'pi.instrumento_id');
                    })
                    ->whereColumn('m.id', 'p.material_id')
                    ->whereColumn('m.servicio_id', 'p.servicio_id');
            })
            ->count();

        if ($procesosSinDetalle > 0 || $detallesSinRelacionCatalogo > 0) {
            throw new \RuntimeException('Migration canceled without changes: historical processes or instruments do not match the catalog relationships. Resolve those records before retrying.');
        }
        Schema::table('proceso_instrumentos', function (Blueprint $table) {
            $table->foreignId('servicio_id')->nullable()->after('proceso_id')->constrained('servicios');
            $table->foreignId('material_id')->nullable()->after('servicio_id')->constrained('materiales');
        });

        // Backfill only when the legacy header, material catalog, and instrument pivot agree.
        DB::table('proceso_instrumentos as pi')
            ->join('proceso_esterilizacion as p', 'p.id', '=', 'pi.proceso_id')
            ->join('materiales as m', function ($join) {
                $join->on('m.id', '=', 'p.material_id')->on('m.servicio_id', '=', 'p.servicio_id');
            })
            ->join('material_instrumentos as mi', function ($join) {
                $join->on('mi.material_id', '=', 'm.id')->on('mi.instrumento_id', '=', 'pi.instrumento_id');
            })
            ->update(['pi.servicio_id' => DB::raw('m.servicio_id'), 'pi.material_id' => DB::raw('m.id')]);

        Schema::table('proceso_esterilizacion', function (Blueprint $table) {
            $table->dropForeign(['servicio_id']);
            $table->dropForeign(['material_id']);
            $table->dropIndex(['servicio_id', 'fecha']);
            $table->dropIndex(['material_id']);
            $table->dropColumn(['servicio_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::table('proceso_esterilizacion', function (Blueprint $table) {
            $table->foreignId('servicio_id')->nullable()->after('id')->constrained('servicios');
            $table->foreignId('material_id')->nullable()->after('entrega_id')->constrained('materiales');
            $table->index(['servicio_id', 'fecha']);
            $table->index('material_id');
        });

        // Restore legacy header values only for processes with one unambiguous service/material pair.
        DB::statement('UPDATE proceso_esterilizacion p
            JOIN (
                SELECT proceso_id, MIN(servicio_id) AS servicio_id, MIN(material_id) AS material_id
                FROM proceso_instrumentos
                WHERE servicio_id IS NOT NULL AND material_id IS NOT NULL
                GROUP BY proceso_id
                HAVING COUNT(DISTINCT servicio_id) = 1 AND COUNT(DISTINCT material_id) = 1
            ) pi ON pi.proceso_id = p.id
            SET p.servicio_id = pi.servicio_id, p.material_id = pi.material_id');

        Schema::table('proceso_instrumentos', function (Blueprint $table) {
            $table->dropForeign(['servicio_id']);
            $table->dropForeign(['material_id']);
            $table->dropColumn(['servicio_id', 'material_id']);
        });
    }
};