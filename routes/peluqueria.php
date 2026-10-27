<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeluqueriaController;
foreach(['peluqueria'=>'Peluqueria','centro_estetico'=>'CentroEstetico'] as $rol=>$prefijo){
Route::middleware('auth')->prefix($prefijo)->name($rol.'.')->group(function(){
    Route::post('/contexto',[App\Http\Controllers\PeluqueriaConfiguracionController::class,'contexto'])->name('contexto');
    Route::post('/configuracion/personal',[App\Http\Controllers\PeluqueriaConfiguracionController::class,'personal'])->name('configuracion.personal');
    Route::post('/configuracion/sucursales',[App\Http\Controllers\PeluqueriaConfiguracionController::class,'sucursal'])->name('configuracion.sucursal');
    Route::post('/configuracion/agenda',[App\Http\Controllers\PeluqueriaConfiguracionController::class,'agenda'])->name('configuracion.agenda');
    Route::post('/configuracion/bloqueos',[App\Http\Controllers\PeluqueriaConfiguracionController::class,'bloqueo'])->name('configuracion.bloqueo');
    Route::post('/configuracion/bloqueos/{id}/quitar',[App\Http\Controllers\PeluqueriaConfiguracionController::class,'quitarBloqueo'])->whereNumber('id')->name('configuracion.bloqueo.quitar');

    Route::get('/',[PeluqueriaController::class,'index'])->name('home');
    Route::get('/agenda',[PeluqueriaController::class,'index'])->name('agenda.ver');
    Route::get('/agenda/eventos',[PeluqueriaController::class,'eventos'])->name('agenda.eventos');
    Route::get('/mascotas',[PeluqueriaController::class,'index'])->name('mascotas');
    Route::get('/historial',[PeluqueriaController::class,'index'])->name('historial');
    Route::get('/configuracion',[PeluqueriaController::class,'configuracion'])->name('configuracion');
    Route::post('/procedimientos',[PeluqueriaController::class,'procedimientoGuardar'])->name('procedimientos.guardar');
    Route::get('/agenda/{cita}/ficha',[PeluqueriaController::class,'ficha'])->whereNumber('cita')->name('ficha');
    Route::post('/agenda/{cita}/ficha',[PeluqueriaController::class,'fichaGuardar'])->whereNumber('cita')->name('ficha.guardar');
    Route::post('/perfil',[PeluqueriaController::class,'perfilGuardar'])->name('perfil');
    Route::get('/tutores/buscar',[PeluqueriaController::class,'buscarTutor'])->name('tutores.buscar');
    Route::post('/clientes',[PeluqueriaController::class,'clienteGuardar'])->name('clientes');
    Route::post('/agenda',[PeluqueriaController::class,'citaGuardar'])->name('agenda');
    Route::post('/agenda/{cita}/estado',[PeluqueriaController::class,'estado'])->whereNumber('cita')->name('estado');
});

}
