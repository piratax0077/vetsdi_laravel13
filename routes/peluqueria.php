<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeluqueriaController;
use App\Http\Controllers\PeluqueriaAgendaController;
Route::middleware('auth')->prefix('Peluqueria')->name('peluqueria.')->group(function(){
    Route::get('/',[PeluqueriaController::class,'index'])->name('home');
    Route::get('/agenda',[PeluqueriaController::class,'index'])->name('agenda.ver');
    // Agenda (modal "Agendar hora" y calendario); la ficha sigue en PeluqueriaController
    Route::get('/agenda/eventos',[PeluqueriaAgendaController::class,'eventos'])->name('agenda.eventos');
    Route::get('/agenda/tutor',[PeluqueriaAgendaController::class,'buscarTutor'])->name('agenda.tutor');
    Route::get('/mascotas',[PeluqueriaController::class,'index'])->name('mascotas');
    Route::get('/historial',[PeluqueriaController::class,'index'])->name('historial');
    Route::get('/configuracion',[PeluqueriaController::class,'configuracion'])->name('configuracion');
    Route::post('/procedimientos',[PeluqueriaController::class,'procedimientoGuardar'])->name('procedimientos.guardar');
    Route::get('/agenda/{cita}/ficha',[PeluqueriaController::class,'ficha'])->whereNumber('cita')->name('ficha');
    Route::post('/agenda/{cita}/ficha',[PeluqueriaController::class,'fichaGuardar'])->whereNumber('cita')->name('ficha.guardar');
    Route::view('/mi-perfil','app.peluqueria.perfil')->name('mi_perfil');
    Route::view('/sucursales','app.peluqueria.sucursales')->name('sucursales');
    Route::view('/mascotas/tutor/{cliente}','app.peluqueria.tutor')->whereNumber('cliente')->name('mascotas.tutor');
    Route::post('/perfil',[PeluqueriaController::class,'perfilGuardar'])->name('perfil');
    Route::post('/clientes',[PeluqueriaController::class,'clienteGuardar'])->name('clientes');
    Route::post('/agenda',[PeluqueriaAgendaController::class,'guardar'])->name('agenda');
    Route::post('/agenda/{cita}/estado',[PeluqueriaController::class,'estado'])->whereNumber('cita')->name('estado');
});
