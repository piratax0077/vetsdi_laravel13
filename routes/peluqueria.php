<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeluqueriaController;
Route::middleware('auth')->prefix('Peluqueria')->name('peluqueria.')->group(function(){
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
    Route::post('/clientes',[PeluqueriaController::class,'clienteGuardar'])->name('clientes');
    Route::post('/agenda',[PeluqueriaController::class,'citaGuardar'])->name('agenda');
    Route::post('/agenda/{cita}/estado',[PeluqueriaController::class,'estado'])->whereNumber('cita')->name('estado');
});
