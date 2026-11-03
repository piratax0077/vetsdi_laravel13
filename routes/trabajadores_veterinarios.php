<?php

use App\Http\Controllers\TrabajadoresCentroVeterinarioController as Trabajadores;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','role:Admin|Institucion|AsistenteAdm|Adm_Comercial|Adm_Institucion|Contador',\App\Http\Middleware\HorarioOperacionVeterinaria::class])
    ->prefix('Veterinaria/Personal')->name('vet.trabajadores.')->group(function(){
        Route::post('guardar',[Trabajadores::class,'guardar'])->name('guardar');
        Route::post('categorias',[Trabajadores::class,'categoria'])->name('categoria');
        Route::post('contratos',[Trabajadores::class,'contrato'])->name('contrato');
        Route::post('contratos/{id}/terminar',[Trabajadores::class,'terminar'])->whereNumber('id')->name('terminar');
        Route::get('{id}',[Trabajadores::class,'detalle'])->whereNumber('id')->name('detalle');
        Route::post('{id}/estado',[Trabajadores::class,'estado'])->whereNumber('id')->name('estado');
        Route::post('{id}/mensaje',[Trabajadores::class,'mensaje'])->whereNumber('id')->name('mensaje');
    });
