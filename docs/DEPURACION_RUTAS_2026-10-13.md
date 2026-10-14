# Depuración de las 50 rutas pendientes

Proyecto: D:/laragon/www/vet-sdi_v13.

Resultado: 1.868 rutas registradas, cero controladores o métodos inexistentes. Se retiraron 46 acciones heredadas y se recuperaron cuatro: reporte de fallas, registro de revocación autorizada, formulario de revocación y confirmación del tutor.

Las rutas retiradas pertenecían a funciones sin implementación de hospitalización/enfermería humana, venta de audífonos, formularios dentales anteriores, integraciones públicas antiguas y endpoints sin uso local. Los antecedentes, recetas, presupuestos, pagos y odontogramas de las mascotas conservan los flujos veterinarios existentes. No se eliminaron tablas ni registros. Se creó la tabla de reportes de soporte que faltaba.

Se retiraron las referencias a estas rutas desde las plantillas y el botón de cobro simulado que mostraba éxito sin efectuar ninguna operación. Los originales están en storage/app/auditoria/depuracion-rutas/resp, conservando su estructura de carpetas. El plan de retiro está en storage/app/auditoria/depuracion-rutas/plan.json. No se debe volver a aplicar el script de depuración; tiene un bloqueo para no sobrescribir ese respaldo.

La revocación valida el consentimiento, mascota, tutor, profesional, destinatario, tipo de autorización, vencimiento y uso único del token de confirmación. El tutor puede aceptar o rechazar una autorización, pero no editar los registros sanitarios. Guardar observaciones devuelve confirmación de persistencia, sin afirmar que se haya enviado un correo.

## Validación

- Suite PHP completa: 108 pruebas y 566 aserciones aprobadas. Dos casos adicionales aprobados después: formulario con token de otro consentimiento y profesional ajeno; la clase ampliada suma nueve pruebas y 27 aserciones.
- 26 pruebas JavaScript aprobadas.
- 243 bloques JavaScript comparados con sus originales sin nuevos errores de sintaxis; 12 bloques con sintaxis Blade o errores previos no pudieron evaluarse con este análisis estático.
- Compilación Blade correcta; seis FVU reales renderizadas solo en lectura.
- Auditoría de rutas sin excepciones ni exclusiones añadidas: cero fallas.
- No se enviaron correos ni se escribieron datos clínicos reales en estas pruebas.

## Límite de la prueba en navegador

Resuelto en la revisión posterior: el virtual host existente funciona en el puerto 80. Se corrigió APP_URL a http://vet-sdi_v13.test y se comprobó el ingreso HTTP 200 en navegador. Sigue pendiente completar los recorridos privados con una sesión de prueba autenticada. Los resultados ampliados están en REVISION_PROFUNDA_2026-10-13.md.

## Rutas retiradas

| Nombre o URI | Acción anterior |
| --- | --- |
| laboratorio.profesional.enviar_seguimiento_cotizacion | CotizacionController@enviarSeguimiento |
| paciente.archivo.eliminar | CargaImagenController@eliminarArchivoTemp |
| paciente.laboratorio.registrar | EscritorioPaciente@registrar_nuevo_paciente_laboratorio |
| profesional.agregar_antecedente_quirurgico_paciente | EscritorioProfesional@agregar_antecedente_quirurgico_paciente |
| flujo_caja.profesional.iniciar_cobro | FlujoCajaController@iniciar_cobro |
| profesional.registrar_presupuesto_dental | EscritorioProfesional@registrar_presupuesto_dental |
| detalle_receta.registro_manual_receta | DetalleRecetaController@registroRecetaManualInterna |
| asistentecm.rendicion_carga_bonos_fecha | FlujoCajaController@cargaBonosAsistenteFecha |
| enfermeria.guardar_informacion_ingreso | EscritorioGeneral@guardarIngresoUrgencia |
| enfermeria.guardar_antecedentes_ingreso | EscritorioGeneral@guardarAntecedentesUrgencia |
| laboratorio.editar_datos_perfil_responsable_medico | LaboratorioController@editarDatosPerfilResponsableMedico |
| laboratorio.cargar_archivo_campania | CargaImagenController@cargarArchivoCampania |
| laboratorio.bodega.guardarItemFactura | ComprasController@guardarItemFacturaLaboratorio |
| laboratorio.registrar_personal | ManejoContratoController@registrarPersonalLaboratorio |
| laboratorio.empleado_horario_lugar_atencion | ManejoContratoController@verHorarioEmpleadoLaboratorio |
| ficha_medica.registrar_certificado_defuncion | ficha_atencionController@registrar_defuncion |
| ficha_medica.profesional_provisorio | ficha_atencionController@index2 |
| crear.interconsulta | ficha_atencionController@interconsulta |
| crear.reposo | ficha_atencionController@reposo |
| crear.informe_medico | ficha_atencionController@informe_medico |
| crear.eno | ficha_atencionController@eno |
| adm_hospital.guardar_salas_servicio | AdministradorHospitalController@guardar_salas_servicio |
| adm_hospital.agregar_observaciones_tratamiento | AdministradorHospitalController@agregar_observaciones_tratamiento |
| adm_hospital.cambiar_estado_tratamiento | AdministradorHospitalController@cambiar_estado_tratamiento |
| profesional.finalizar_atencion | AdministradorHospitalController@finalizarAtencion |
| urgencia.adminstrativo.mis.enfermeras | UrgenciaController@MisEnfermerasAdministrativo |
| bodegas.editar_bodega | BodegasController@editarBodega |
| dental.generar_pdf_protocolo_period | DentalController@generar_pdf_prot_period |
| paciente.pdf.dieta | EscritorioPaciente@generarPdfDietaNutricional |
| fichaAtencion.obtener_tratamientos_activos_enfermeria | ficha_atencionController@obtener_tratamientos_activos_enfermeria |
| fichaAtencion.historial_administraciones_medicamento_enfermeria | ficha_atencionController@historial_administraciones_medicamento_enfermeria |
| fichaAtencion.guardar_medicamento_enfermeria | ficha_atencionController@guardar_medicamento_enfermeria |
| detalle_receta.eliminar_registro_receta | DetalleRecetaController@eliminarRegistroReceta |
| api/profesional_provisorio/modificar | ProfesionalProvisorioController@modificar_profesional_provisorio |
| api/documento/ver_examen_pdf/{id} | DocumentoController@verExamenPDF |
| api/documento/ver_resultado_octavo_par_pdf/{id} | DocumentoController@verOctavoParPDF |
| api/documento/ver_licencia_pdf/{id} | DocumentoController@verLicenciaPDF |
| api/documento/ver_certificado_pdf/{id} | DocumentoController@verCertificadoPDF |
| api.obtener_datos_paciente_por_rut_agenda | CentroMedicoController@obtenerDatosPacientePorRut |
| api.confirmar_reserva | CentroMedicoController@confirmarReserva |
| api.agendar_hora_paciente_publico | CentroMedicoController@agendarHoraPacientePublico |
| api.buscar_profesionales_examen | CentroMedicoController@buscarProfesionalesExamen |
| api.horas_examen_profesional_lugar_atencion | CentroMedicoController@horasExamenProfesionalLugarAtencion |
| api.confirmar_reserva_examen | CentroMedicoController@confirmarReservaExamen |
| laboratorio.aprobar-rendicion-email | LaboratorioController@aprobarRendicionEmail |
| laboratorio.rechazar-rendicion-email | LaboratorioController@rechazarRendicionEmail |
