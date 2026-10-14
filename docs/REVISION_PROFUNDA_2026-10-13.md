# Revisión ampliada veterinaria — 13 de octubre de 2026

Proyecto: D:/laragon/www/vet-sdi_v13.

## Correcciones

- URL local corregida a http://vet-sdi_v13.test; el ingreso funciona en el navegador.
- Apertura de consultas ligada a la hora, profesional, mascota, tutor actual y centro. Reutiliza su borrador y evita recuperar la ficha de otra mascota del mismo tutor.
- Lectura de contacto de emergencia sin errores cuando faltan contacto, fecha de nacimiento o región.
- Guardado clínico y odontología rechazan horas o fichas asociadas a un tutor anterior.
- Recetas y exámenes validan permisos e identificadores en el servidor. El tutor puede leer los registros de su mascota, pero no modificarlos. Los otros profesionales tampoco pueden modificar una ficha ajena.
- Registro individual de exámenes corregido: llamaba a un método inexistente de medicamentos.
- Listas de recetas y exámenes validadas antes de borrar registros previos; se rechazan listas incompletas y se utiliza una transacción para el reemplazo.
- Imágenes clínicas: el guardado utilizaba discos inexistentes. Ahora copia desde la carpeta real de subida, comprueba contenido y tamaño, deriva la extensión del contenido y publica el archivo en storage/imagenes/atenciones. Conserva la subida temporal para permitir reintentos. Un fallo posterior puede dejar una copia sin referencia; no se eliminan automáticamente archivos potencialmente referenciados en tablas antiguas.

## Evidencia

- Suite PHP: 118 pruebas, 604 aserciones, todas aprobadas.
- JavaScript: 26 pruebas aprobadas.
- Auditoría: 1.868 rutas, cero acciones inexistentes.
- Cinco perfiles y tres fichas: ocho respuestas HTTP 200 mediante el kernel Laravel, con escrituras SQL bloqueadas y envío de correo/notificaciones desactivado en el comprobador.
- Seis muestras de FVU renderizadas correctamente en lectura.
- Pruebas nuevas de imágenes en las tres especialidades, diagnóstico único obligatorio, reutilización de borrador, rechazo de tutor anterior, permisos sanitarios y rollback de listas en SQLite.
- git diff --check sin errores.

## Pendientes y límites

Los recorridos privados completos en navegador requieren que el usuario complete el inicio de sesión con la cuenta de prueba. El renderizado del servidor no sustituye la interacción completa en pantalla.

La limitación de motores detectada se resolvió después de la autorización explícita del usuario: se convirtieron 10 tablas MyISAM a InnoDB, conservando 5.088 registros con huellas de contenido coincidentes antes y después.

La conversión está fuera de las migraciones automáticas, en scripts/pendientes/2026_10_13_235100_make_veterinary_workflows_transactional.php. Se ejecutó mediante scripts/convertir-motores-autorizados.php con mantenimiento temporal y posterior reapertura. Los respaldos SQL comprimidos y manifiestos están en storage/app/auditoria/motores_resp. Se omitieron las tablas ya InnoDB. La revisión automática había rechazado inicialmente el cambio; el usuario lo autorizó después y la ejecución terminó correctamente.

Tablas convertidas: mascotas_lugares_atencion, mascotas, ficha_veterinaria_general, control_post_quirurgico, detalles_receta, presupuestos_mascota, instituciones, admin_inst_serv, con_consentimientos_pcte y log_users_devices. Tras la conversión se repitieron satisfactoriamente las ocho pantallas y las seis lecturas de FVU. El cambio no modifica los motores de módulos fuera de la lista autorizada.

No se afirma que todo el sistema esté libre de fallos ni que los recorridos privados estén completamente validados.
