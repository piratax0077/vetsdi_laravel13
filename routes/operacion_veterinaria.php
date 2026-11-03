<?php

use App\Http\Controllers\OperacionVeterinariaController;
use App\Services\OperacionVeterinaria;
use Illuminate\Support\Facades\Route;

$accesoOperacion = ['auth','role:Admin|Institucion|AsistenteAdm|Adm_Comercial|Adm_Institucion|AdministradorLaboratorio|Contador',\App\Http\Middleware\HorarioOperacionVeterinaria::class];

Route::middleware(['auth',\App\Http\Middleware\HorarioOperacionVeterinaria::class])->group(function(){
    Route::get('Veterinaria/Hospitalizacion/Servicio',[\App\Http\Controllers\ServicioHospitalizacionVeterinariaController::class,'index'])->name('vet.hospital.servicio');
    Route::post('Veterinaria/Hospitalizacion/Enfermeria',[\App\Http\Controllers\ServicioHospitalizacionVeterinariaController::class,'guardar'])->name('vet.hospital.enfermeria.guardar');
});
Route::middleware($accesoOperacion)->group(function(){
    Route::get('Veterinaria/Hospitalizacion/Equipo',[\App\Http\Controllers\ServicioHospitalizacionVeterinariaController::class,'equipo'])->name('vet.hospital.equipo');
    Route::post('Veterinaria/Hospitalizacion/Equipo',[\App\Http\Controllers\ServicioHospitalizacionVeterinariaController::class,'guardarEquipo'])->name('vet.hospital.equipo.guardar');
});

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

Route::middleware($accesoOperacion)->get('Veterinaria/Areas/{area}/Gestion',[\App\Http\Controllers\GestionAreaVeterinariaController::class,'index'])
    ->whereIn('area',array_keys(OperacionVeterinaria::AREAS))->name('vet.areas.gestion');
Route::middleware($accesoOperacion)->post('Veterinaria/Areas/{area}/Gestion',[\App\Http\Controllers\GestionAreaVeterinariaController::class,'guardar'])
    ->whereIn('area',array_keys(OperacionVeterinaria::AREAS))->name('vet.areas.gestion.guardar');

Route::middleware(['auth',\App\Http\Middleware\HorarioOperacionVeterinaria::class])->group(function () {
    Route::get('Veterinaria/Profesional/Hospitalizados',[\App\Http\Controllers\AtencionHospitalariaVeterinariaController::class,'index'])->name('vet.hospital.profesional');
    Route::get('Veterinaria/Profesional/Hospitalizados/examen/{documento}',[\App\Http\Controllers\AtencionHospitalariaVeterinariaController::class,'examen'])->whereNumber('documento')->name('vet.hospital.examen.ver');
    Route::post('Veterinaria/Profesional/Hospitalizados/registro',[\App\Http\Controllers\AtencionHospitalariaVeterinariaController::class,'guardar'])->name('vet.hospital.profesional.guardar');
});

Route::middleware($accesoOperacion)->group(function () {
    Route::get('Veterinaria/Hospitalizacion/Asignaciones',[\App\Http\Controllers\AtencionHospitalariaVeterinariaController::class,'asignaciones'])->name('vet.hospital.asignaciones');
    Route::post('Veterinaria/Hospitalizacion/Asignaciones',[\App\Http\Controllers\AtencionHospitalariaVeterinariaController::class,'asignar'])->name('vet.hospital.asignar');
});
Route::middleware(['auth',\App\Http\Middleware\HorarioOperacionVeterinaria::class])->group(function () {
    Route::post('Veterinaria/Profesional/Hospitalizados/alta',[\App\Http\Controllers\AtencionHospitalariaVeterinariaController::class,'alta'])->name('vet.hospital.profesional.alta');
    Route::post('Veterinaria/Profesional/Hospitalizados/control',[\App\Http\Controllers\AtencionHospitalariaVeterinariaController::class,'control'])->name('vet.hospital.profesional.control');
    Route::get('Veterinaria/Profesional/Hospitalizados/carne',[\App\Http\Controllers\AtencionHospitalariaVeterinariaController::class,'carne'])->name('vet.hospital.profesional.carne');
});
Route::middleware(['auth',\App\Http\Middleware\HorarioOperacionVeterinaria::class])->post('Veterinaria/Profesional/Hospitalizados/ingreso',[\App\Http\Controllers\AtencionHospitalariaVeterinariaController::class,'ingreso'])->name('vet.hospital.profesional.ingreso');
Route::middleware(['auth',\App\Http\Middleware\HorarioOperacionVeterinaria::class])->post('Veterinaria/Centro/ExportarPacientes',[\App\Http\Controllers\ExportacionPacientesCentroController::class,'descargar'])->name('vet.centro.exportar_pacientes');
Route::middleware(['auth',\App\Http\Middleware\HorarioOperacionVeterinaria::class])->post('Veterinaria/Profesional/ExportarPacientes',[\App\Http\Controllers\ExportacionPacientesProfesionalController::class,'descargar'])->name('vet.profesional.exportar_pacientes');
