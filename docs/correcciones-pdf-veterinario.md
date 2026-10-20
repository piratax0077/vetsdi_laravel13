# Correcciones veterinarias del 19 de octubre de 2026

- Agenda profesional, asistente, dental y laboratorio: las validaciones de duplicados distinguen la mascota del tutor. Se conserva la validación de pertenencia al tutor.
- Lugares: el correo y el teléfono pueden compartirse entre consultas. La base local solo tiene índice único en el identificador, no en esos contactos.
- Ficha general: eliminada la dependencia de previsión y sus campos ocultos en las fichas veterinarias; retirada también de los formularios de hospitalización incluidos. El acceso a subespecialidad es opcional.
- Formularios: los catálogos humanos de previsiones no se ofrecen en los selects veterinarios. Se conservan los datos históricos en la base.
- Convenios: en Mis convenios se crean nombres externos con porcentaje de 0 a 100 y ámbito por lugar. La recepción de pagos ofrece esos convenios y calcula el descuento en el servidor. El cobro conserva nombre, porcentaje y monto aplicados aunque el convenio cambie después.
- Reportes: mascotas y fichas finalizadas, inasistencias explícitas, seis meses hasta el mes seleccionado, documentos veterinarios y convenios efectivamente utilizados. Incluye descarga CSV con los mismos filtros.
- Registro y recuperación por correo: el remitente usa la configuración de Laravel y los enlaces de cuenta no se desvían a la casilla de pruebas. El modo log ya no se anuncia como entrega real. Se mantiene una contraseña por identidad/RUT para todos sus perfiles.

## Validación

50 pruebas PHP con 277 comprobaciones y 3 pruebas JavaScript, usando SQLite temporal y envíos simulados. También se verificaron mediante peticiones de solo lectura contra MySQL local las fichas general, cirugía y odontología, agenda con horario, reportes, convenios y lugares, todas con respuesta 200.

La migración `2026_10_19_000001_create_convenios_veterinarios.php` fue aplicada a la base local. Agrega una tabla de convenios y cuatro columnas de historial de descuentos en bonos.

## Pendiente

- Configurar SMTP real en `.env`: MAIL_MAILER=smtp, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS y MAIL_FROM_NAME, según el proveedor. Luego ejecutar `php artisan config:clear`. El transporte actual es log y no dispone de credenciales SMTP; la entrega externa todavía no se ha comprobado.
- Recuperación por celular: pendiente por indicación expresa del usuario. No se activó ningún proveedor ni se enviaron mensajes reales.

## Segunda corrección de pagos y asistentes

- El asistente selecciona convenios externos autorizados para el profesional y lugar de la hora, sin exigir previsión ni bonificación humana. Se retiró también el requisito de previsión al registrar un tutor desde la agenda.
- Los cobros de mascotas utilizan una recepción transaccional común. La hora se bloquea durante la operación y se reutiliza su cobro; repetir un pago confirmado no crea ni altera otro recibo.
- Se conserva el estado de atención en curso o finalizada, incluso al recibir un QR. El porcentaje previamente acordado se conserva aunque el convenio se modifique o desactive.
- La nueva columna `bonos.pago_confirmado_en` distingue una recepción de un pago confirmado después del cierre. Cerrar la ficha o recibir solamente el QR no completa esa columna.
- Los reportes y CSV suman ingresos por fecha de confirmación de pago, incluido el último día completo del mes. Los registros sin confirmación se muestran separados. No se presume que los cobros históricos sin evidencia estén pagados ni se alteran sus montos.
- Se aplicó la migración `2026_10_19_000002_add_pago_confirmado_en_to_bonos.php` en la base local.
