<?php

use App\Http\Controllers\ContabilidadIntegracionController;
use App\Http\Controllers\AdministradorCmController;
use App\Http\Controllers\FichaPediatriaVacunaController;
use App\Http\Controllers\EnfermeriaTratamientoController;
use App\Http\Controllers\EscritorioEnfermerasController;
use App\Http\Controllers\FichaAtencionOtrosProfController;
use App\Http\Controllers\ficha_atencionController;
use App\Http\Controllers\DetalleRecetaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->get('/Administrador/Integraciones/Contabilidad-Central', [ContabilidadIntegracionController::class, 'index'])
    ->name('integraciones.contabilidad-central');

Route::middleware('auth')
    ->get('/Administrador/Vacunatorio/Mi-Agenda', [AdministradorCmController::class, 'vacunatorio_agenda'])
    ->name('adm_cm.vacunatorio.agenda');

Route::middleware('auth')
    ->post('/ficha/vacuna/eliminar', [FichaPediatriaVacunaController::class, 'eliminarVacuna'])
    ->name('ficha.eliminar.vacuna');

Route::middleware('auth')->group(function (): void {
    Route::post('/enfermeria/administrar-tratamiento', [EnfermeriaTratamientoController::class, 'administrar'])
        ->name('enfermeria.administrar_tratamiento');
    Route::post('/enfermeria/eliminar-tratamiento', [EnfermeriaTratamientoController::class, 'eliminar'])
        ->name('enfermeria.eliminar_tratamiento');
    Route::post('/enfermeria/actualizar-observacion-tratamiento', [EnfermeriaTratamientoController::class, 'actualizarObservacion'])
        ->name('enfermeria.actualizar_observacion_tratamiento');

    Route::post('/enfermeria/ficha/registrar', [FichaAtencionOtrosProfController::class, 'store_enfermeria'])
        ->name('fichaAtencion.registrar_ficha_enfermeria');
    Route::post('/enfermeria/tratamiento-domiciliario/actualizar', [EscritorioEnfermerasController::class, 'actualizar_tratamiento_domiciliario'])
        ->name('enfermeria.actualizar_tratamiento_domiciliario');
    Route::get('/enfermeria/atencion/cargar', [EscritorioEnfermerasController::class, 'cargar_informacion_atencion'])
        ->name('enfermeria.cargar_informacion_atencion');
    Route::get('/enfermeria/curacion', [EscritorioEnfermerasController::class, 'dame_curacion'])
        ->name('enfermeria.dame_curacion');
    Route::post('/enfermeria/curaciones/registro', [EscritorioEnfermerasController::class, 'guardarCuracionRegistro'])
        ->name('enfermeria.guardar_curacion_registro');
    Route::post('/enfermeria/curaciones/listar', [EscritorioEnfermerasController::class, 'obtenerCuracionesRegistro'])
        ->name('enfermeria.obtener_curaciones_registro');
    Route::get('/enfermeria/curaciones/{id}', [EscritorioEnfermerasController::class, 'obtenerCuracionRegistroDetalle'])
        ->name('enfermeria.obtener_curacion_registro_detalle');
    Route::post('/enfermeria/curaciones/eliminar', [EscritorioEnfermerasController::class, 'eliminarCuracionRegistro'])
        ->name('enfermeria.eliminar_curacion_registro');
    Route::post('/enfermeria/tratamiento-domiciliario/eliminar', [EscritorioEnfermerasController::class, 'eliminar_tratamiento_domiciliario'])
        ->name('enfermeria.eliminar_tratamiento_domiciliario');
    Route::post('/enfermeria/tratamiento-inyectable/eliminar', [EscritorioEnfermerasController::class, 'eliminar_tratamiento_inyectable'])
        ->name('enfermeria.eliminar_tratamiento_inyectable');
    Route::post('/enfermeria/control-sueros', [EscritorioEnfermerasController::class, 'guardar_control_sueros'])
        ->name('enfermeria.guardar_control_sueros');
    Route::post('/enfermeria/curacion/pie-diabetico', [EscritorioEnfermerasController::class, 'guardarCuracionPieDiabeticoServicio'])
        ->name('enfermeria.guardar_curacion_pie_diabetico_servicio');
    Route::post('/enfermeria/curacion/procedimiento', [EscritorioEnfermerasController::class, 'guardarCuracionProcedimiento'])
        ->name('enfermeria.guardar_curacion_procedimiento');
    Route::post('/enfermeria/inyectable', [EscritorioEnfermerasController::class, 'guardar_inyectable_im_iv'])
        ->name('enfermeria.guardar_inyectable_im_iv');
    Route::post('/enfermeria/curacion/observaciones', [EscritorioEnfermerasController::class, 'agregar_observaciones_curacion'])
        ->name('enfermeria.guardar_observaciones_curacion');
    Route::post('/enfermeria/curacion/insumos', [EscritorioEnfermerasController::class, 'guardarInsumosCuracion'])
        ->name('enfermeria.guardar_insumos_curacion');
    Route::post('/enfermeria/curacion/estado', [EscritorioEnfermerasController::class, 'actualizarEstadoCuracion'])
        ->name('enfermeria.actualizar_estado_curacion');
    Route::post('/enfermeria/curacion/actualizar-observaciones', [EscritorioEnfermerasController::class, 'actualizarObservacionesCuracion'])
        ->name('enfermeria.actualizar_observaciones_curacion');
    Route::post('/enfermeria/curacion/eliminar-indicada', [EscritorioEnfermerasController::class, 'eliminarCuracion'])
        ->name('enfermeria.eliminar_curacion');
    Route::post('/enfermeria/receta-inyectable', [EscritorioEnfermerasController::class, 'guardar_receta_medica_inyectable'])
        ->name('enfermeria.guardar_receta_medica_inyectable');

    Route::post('/enfermeria/tratamientos/activos', [ficha_atencionController::class, 'obtener_tratamientos_activos_enfermeria'])
        ->name('fichaAtencion.obtener_tratamientos_activos_enfermeria');
    Route::post('/enfermeria/tratamientos/historial', [ficha_atencionController::class, 'historial_administraciones_medicamento_enfermeria'])
        ->name('fichaAtencion.historial_administraciones_medicamento_enfermeria');
    Route::post('/enfermeria/tratamientos/guardar', [ficha_atencionController::class, 'guardar_medicamento_enfermeria'])
        ->name('fichaAtencion.guardar_medicamento_enfermeria');
    Route::post('/enfermeria/receta/eliminar', [DetalleRecetaController::class, 'eliminarRegistroReceta'])
        ->name('detalle_receta.eliminar_registro_receta');
});
