<?php

use App\Http\Controllers\AsistenteUnificadoController;
use App\Http\Controllers\AutorizacionesAsistentesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:'.implode('|', config('asistentes.roles'))])->prefix('Asistentes')->group(function () {
    Route::get('/', [AsistenteUnificadoController::class, 'home'])->name('asistentes.home');
    Route::post('/contexto', [AsistenteUnificadoController::class, 'contexto'])->name('asistentes.contexto');
    Route::get('/permisos', [AsistenteUnificadoController::class, 'permisos'])->name('asistentes.permisos');
    Route::get('/perfil', [\App\Http\Controllers\EscritorioAsistente::class, 'perfil'])->name('asistentes.perfil');
    Route::post('/perfil/personales', [\App\Http\Controllers\PerfilUsuarioController::class, 'personales'])->name('asistentes.perfil.personales');
    Route::post('/perfil/contacto', [\App\Http\Controllers\PerfilUsuarioController::class, 'contacto'])->name('asistentes.perfil.contacto');
    Route::post('/perfil/direccion', [\App\Http\Controllers\PerfilUsuarioController::class, 'direccion'])->name('asistentes.perfil.direccion');
    Route::get('/perfil/contactos', [\App\Http\Controllers\EscritorioAsistente::class, 'cargar_contacto_emergencia'])->name('asistentes.perfil.contactos');
    Route::get('/perfil/contactos/datos', [\App\Http\Controllers\EscritorioAsistente::class, 'cargar_datos_contacto'])->name('asistentes.perfil.contactos.datos');
    Route::get('/perfil/contactos/buscar', [\App\Http\Controllers\EscritorioAsistente::class, 'buscar_contacto'])->name('asistentes.perfil.contactos.buscar');
    Route::post('/perfil/contactos/crear', [\App\Http\Controllers\EscritorioAsistente::class, 'registrar_contacto_emergencia'])->name('asistentes.perfil.contactos.crear');
    Route::post('/perfil/contactos/editar', [\App\Http\Controllers\EscritorioAsistente::class, 'editar_contacto_emergencia'])->name('asistentes.perfil.contactos.editar');
    Route::post('/perfil/contactos/eliminar', [\App\Http\Controllers\EscritorioAsistente::class, 'eliminar_contacto_asistente'])->name('asistentes.perfil.contactos.eliminar');
    Route::post('/perfil/busqueda', [\App\Http\Controllers\EscritorioAsistente::class, 'editar_configuracion_busqueda'])->name('asistentes.perfil.busqueda');
    Route::post('/perfil/archivo', [\App\Http\Controllers\PerfilUsuarioController::class, 'archivo'])->name('asistentes.perfil.archivo');
    Route::post('/perfil/cuentas/agregar', [\App\Http\Controllers\LiquidacionReciboController::class, 'agregarLiquidacion'])->name('asistentes.perfil.cuentas.agregar');
    Route::post('/perfil/cuentas/modificar', [\App\Http\Controllers\LiquidacionReciboController::class, 'modificarLiquidacion'])->name('asistentes.perfil.cuentas.modificar');
    Route::post('/perfil/cuentas/eliminar', [\App\Http\Controllers\LiquidacionReciboController::class, 'eliminarLiquidacion'])->name('asistentes.perfil.cuentas.eliminar');
    Route::post('/foto', [\App\Http\Controllers\EscritorioAsistente::class, 'actualizarFoto'])->name('asistentes.foto.guardar');
    Route::delete('/foto', [\App\Http\Controllers\EscritorioAsistente::class, 'eliminarFoto'])->name('asistentes.foto.eliminar');
    Route::get('/agenda', [AsistenteUnificadoController::class, 'agenda'])->name('asistentes.agenda');
    Route::get('/pacientes', [AsistenteUnificadoController::class, 'pacientes'])->name('asistentes.pacientes');
    Route::get('/enfermeria', [AsistenteUnificadoController::class, 'enfermeria'])->name('asistentes.enfermeria');
});

Route::post('/Mi-perfil/contrasena', [\App\Http\Controllers\PerfilUsuarioController::class, 'contrasena'])->middleware('auth')->name('perfil.usuario.contrasena');

Route::middleware('auth')->prefix('Autorizaciones/Asistentes')->group(function () {
    Route::get('/', [AutorizacionesAsistentesController::class, 'index'])->name('asistentes.autorizaciones');
    Route::post('/', [AutorizacionesAsistentesController::class, 'guardar'])->name('asistentes.autorizaciones.guardar');
    Route::delete('/politica-centro', [AutorizacionesAsistentesController::class, 'quitarPoliticaCentro'])->name('asistentes.autorizaciones.quitar_politica');
});
