<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArchivoPdfVeterinarioController as Documentos;
Route::get('Veterinaria/PDF/compartido/{documento}',[Documentos::class,'compartido'])->whereUuid('documento')->name('vet.pdf.compartido');
Route::middleware('auth')->prefix('Veterinaria/PDF')->name('vet.pdf.')->group(function(){
    Route::get('/',[Documentos::class,'index'])->name('index');
    Route::get('historico/{tipo}/{origen}',[Documentos::class,'historico'])->whereNumber('origen')->name('historico');
    Route::get('{documento}',[Documentos::class,'detalle'])->whereUuid('documento')->name('detalle');
    Route::get('{documento}/ver',[Documentos::class,'ver'])->whereUuid('documento')->name('ver');
    Route::post('{documento}/preparar',[Documentos::class,'preparar'])->whereUuid('documento')->name('preparar');
    Route::post('{documento}/correo',[Documentos::class,'enviar'])->whereUuid('documento')->name('enviar');
});
