<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeluqueriaController;
Route::middleware('auth')->prefix('Peluqueria')->name('peluqueria.')->group(function(){
    Route::get('/',[PeluqueriaController::class,'index'])->name('home');
    Route::post('/perfil',[PeluqueriaController::class,'perfilGuardar'])->name('perfil');
    Route::post('/clientes',[PeluqueriaController::class,'clienteGuardar'])->name('clientes');
    Route::post('/agenda',[PeluqueriaController::class,'citaGuardar'])->name('agenda');
    Route::post('/agenda/{cita}/estado',[PeluqueriaController::class,'estado'])->whereNumber('cita')->name('estado');
});
