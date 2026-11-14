<?php

use App\Http\Controllers\CentroServiciosMascotasController as Centro;
use Illuminate\Support\Facades\Route;

Route::view('PerfilesServicios', 'app.centros_servicios.perfiles')->middleware(['auth', 'verified'])->name('centros_servicios.perfiles');
Route::post('PerfilesServicios/activar', [Centro::class, 'activar'])->middleware(['auth', 'verified', 'throttle:5,1'])->name('centros_servicios.activar');

foreach (['guarderia_hotel' => 'GuarderiaHotel', 'cementerio_mascotas' => 'CementerioMascotas'] as $tipo => $prefijo) {
    Route::middleware(['auth', 'rol.cuenta:'.$tipo])->prefix($prefijo)->name($tipo.'.')->group(function () use ($tipo) {
        Route::get('/', [Centro::class, 'index'])->name('home');
        Route::get('configuracion', [Centro::class, 'configuracion'])->name('configuracion');
        Route::post('configuracion', [Centro::class, 'guardarConfiguracion'])->name('configuracion.guardar');
        Route::get('mensajes', [App\Http\Controllers\MensajesNegocioController::class, 'index'])->name('mensajes');
        Route::post('mensajes', [App\Http\Controllers\MensajesNegocioController::class, 'enviar'])->middleware('throttle:20,1')->name('mensajes.enviar');
        Route::get('solicitudes/{id}', [Centro::class, 'detalleCentro'])->whereNumber('id')->name('detalle');
        Route::post('solicitudes/{id}', [Centro::class, 'operar'])->whereNumber('id')->name('operar');
        if ($tipo === 'cementerio_mascotas') {
            Route::get('lugares', [Centro::class, 'inventario'])->name('inventario');
            Route::post('lugares/{id?}', [Centro::class, 'guardarLugar'])->whereNumber('id')->name('inventario.guardar');
        }
    });
}

Route::middleware(['auth', 'rol.cuenta:tutor'])->prefix('Paciente/ServiciosMascotas')->name('paciente.centros_servicios.')->group(function () {
    Route::get('/', [Centro::class, 'catalogo'])->name('catalogo');
    Route::get('centro/{id}', [Centro::class, 'formulario'])->whereNumber('id')->name('formulario');
    Route::post('centro/{id}', [Centro::class, 'solicitar'])->whereNumber('id')->middleware('throttle:10,1')->name('solicitar');
    Route::get('solicitud/{id}', [Centro::class, 'detalleTutor'])->whereNumber('id')->name('detalle');
    Route::post('solicitud/{id}/cancelar', [Centro::class, 'cancelarTutor'])->whereNumber('id')->name('cancelar');
});
