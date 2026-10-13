<?php

use App\Http\Controllers\Esterilizacion\AreaAzulController;
use App\Http\Controllers\Esterilizacion\AreaRojaController;
use App\Http\Controllers\Esterilizacion\AreaVerdeController;
use App\Http\Controllers\Esterilizacion\ProcesoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/esterilizacion/procesos');

Route::prefix('esterilizacion')->name('esterilizacion.')->group(function () {
    Route::get('procesos', [ProcesoController::class, 'index'])->name('procesos.index');
    Route::get('procesos/{proceso}', [ProcesoController::class, 'show'])->name('procesos.show');

    Route::get('area-roja/crear', [AreaRojaController::class, 'create'])->name('roja.create');
    Route::post('area-roja', [AreaRojaController::class, 'store'])->name('roja.store');
    Route::get('servicios/{servicio}/materiales', [AreaRojaController::class, 'materiales'])->name('servicios.materiales');

    Route::get('area-azul', [AreaAzulController::class, 'index'])->name('azul.index');
    Route::get('area-azul/{proceso}/registrar', [AreaAzulController::class, 'create'])->name('azul.create');
    Route::post('area-azul/{proceso}', [AreaAzulController::class, 'store'])->name('azul.store');

    Route::get('area-verde', [AreaVerdeController::class, 'index'])->name('verde.index');
    Route::get('area-verde/{proceso}/registrar', [AreaVerdeController::class, 'create'])->name('verde.create');
    Route::post('area-verde/{proceso}', [AreaVerdeController::class, 'store'])->name('verde.store');
});
