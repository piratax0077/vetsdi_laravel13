<?php

use App\Http\Controllers\OperacionVeterinariaController;
use App\Services\OperacionVeterinaria;
use Illuminate\Support\Facades\Route;

$accesoOperacion = ['auth','role:Admin|Institucion|AsistenteAdm|Adm_Comercial|Adm_Institucion|AdministradorLaboratorio|Contador',\App\Http\Middleware\HorarioOperacionVeterinaria::class];

Route::middleware($accesoOperacion)
    ->prefix('Veterinaria/Operacion')->name('vet.operacion.')->group(function () {
        Route::post('guardar/{accion}',[OperacionVeterinariaController::class,'guardar'])
            ->whereIn('accion',['configuracion','vincular','plaza','internacion','evolucion','servicio','reserva','convocatoria','postulacion','obligacion'])->name('guardar');
        Route::get('{seccion?}',[OperacionVeterinariaController::class,'index'])
            ->whereIn('seccion',array_keys(OperacionVeterinaria::SECCIONES + OperacionVeterinaria::SERVICIOS))->name('index');
    });

Route::middleware($accesoOperacion)->get('Veterinaria/Historial',[OperacionVeterinariaController::class,'historial'])->name('vet.historial.index');

Route::middleware($accesoOperacion)->get('Veterinaria/Areas/{area}',[OperacionVeterinariaController::class,'area'])
    ->whereIn('area',array_keys(OperacionVeterinaria::AREAS))->name('vet.areas.index');
