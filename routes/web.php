<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Esterilizacion\AreaAzulController;
use App\Http\Controllers\Esterilizacion\AreaRojaController;
use App\Http\Controllers\Esterilizacion\AreaVerdeController;
use App\Http\Controllers\Esterilizacion\InstrumentalController;
use App\Http\Controllers\Esterilizacion\ProcesoController;
use App\Http\Controllers\Esterilizacion\ReporteController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'show'])->name('inicio');
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->prefix('esterilizacion')->name('esterilizacion.')->group(function () {
    Route::get('procesos', [ProcesoController::class, 'index'])->name('procesos.index');
    Route::get('procesos/{proceso}', [ProcesoController::class, 'show'])->name('procesos.show');

    Route::get('area-roja/crear', [AreaRojaController::class, 'create'])->name('roja.create');
    Route::post('area-roja', [AreaRojaController::class, 'store'])->name('roja.store');
    Route::get('servicios/{servicio}/materiales', [AreaRojaController::class, 'materiales'])->name('servicios.materiales');
    Route::get('conjuntos/{material}/piezas', [AreaRojaController::class, 'piezas'])->name('conjuntos.piezas');

    Route::get('area-azul', [AreaAzulController::class, 'index'])->name('azul.index');
    Route::get('area-azul/{proceso}/registrar', [AreaAzulController::class, 'create'])->name('azul.create');
    Route::post('area-azul/{proceso}', [AreaAzulController::class, 'store'])->name('azul.store');

    Route::get('area-verde', [AreaVerdeController::class, 'index'])->name('verde.index');
    Route::get('area-verde/{proceso}/registrar', [AreaVerdeController::class, 'create'])->name('verde.create');
    Route::post('area-verde/{proceso}', [AreaVerdeController::class, 'store'])->name('verde.store');

    Route::get('instrumental', [InstrumentalController::class, 'index'])->name('instrumental.index');
    Route::post('instrumental', [InstrumentalController::class, 'store'])->name('instrumental.store');
    Route::get('instrumental/{material}', [InstrumentalController::class, 'show'])->name('instrumental.show');
    Route::put('instrumental/{material}', [InstrumentalController::class, 'update'])->name('instrumental.update');
    Route::post('instrumental/{material}/instrumentos', [InstrumentalController::class, 'agregarInstrumentos'])->name('instrumental.agregar');
    Route::put('instrumental/{material}/piezas/{instrumento}', [InstrumentalController::class, 'actualizarPieza'])->name('instrumental.actualizar-pieza');
    Route::delete('instrumental/{material}/piezas/{instrumento}', [InstrumentalController::class, 'quitarPieza'])->name('instrumental.quitar-pieza');
    Route::put('instrumental/{material}/estado', [InstrumentalController::class, 'cambiarEstado'])->name('instrumental.estado');
    Route::put('instrumentos/{instrumento}/estado', [InstrumentalController::class, 'cambiarEstadoInstrumento'])->name('instrumentos.estado');

    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('reportes/operativo', [ReporteController::class, 'operativo'])->name('reportes.operativo');
    Route::get('reportes/completo', [ReporteController::class, 'completo'])->name('reportes.completo');
    Route::get('reportes/usuario', [ReporteController::class, 'usuario'])->name('reportes.usuario');
});
