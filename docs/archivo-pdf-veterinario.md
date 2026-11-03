# Archivo de PDF veterinarios

Los PDF nuevos generados por PdfController, las respuestas PDF directas y las rutas locales de PDF devueltas en JSON se archivan en el disco privado `local`, tabla `vet_documentos_pdf`, cuando existe una mascota o un centro identificable y autorizado. No se asignan por aproximación a un centro si el usuario administra varios. No se descargan URL externas.

Accesos: «Documentos PDF» en el escritorio de la mascota, la ficha y la atención hospitalaria; «Documentos PDF del centro» en reportes del centro. El reporte estadístico admite generar PDF conservando los filtros.

El historial permite preparar documentos clínicos anteriores de informes, certificados, interconsultas, recetas, órdenes y resultados de exámenes, además de PDF subidos por laboratorio. No se regenera ni envía todo el historial automáticamente. Los generadores anteriores conservan sus comprobaciones de identidad.

Los documentos de mascotas pueden enviarse al correo registrado del tutor actual como adjunto, mediante la configuración de correo de Laravel. «Preparar WhatsApp» crea un mensaje con enlace firmado de 48 horas y abre el compositor de WhatsApp; la persona confirma allí el envío. El enlace se invalida al cambiar el tutor. Los reportes internos del centro no ofrecen envío a pacientes.

Aplicar la migración `2026_11_01_000006_archivo_pdf_veterinario.php` en otros entornos. Se conserva el comportamiento y las ubicaciones públicas de generadores antiguos para compatibilidad; el archivo nuevo utiliza copias privadas. Los generadores sin identificación de mascota o centro no se archivan hasta proporcionar ese contexto.

Validación: pruebas de permisos, separación entre mascotas y centros, adjunto por correo (Mail::fake), enlaces firmados/expirados/traspasos, respuestas PDF y JSON con varios archivos, históricos de laboratorio y reporte PDF del centro. Se generó y renderizó un reporte real de una página A4 para revisar su presentación. No se hicieron envíos reales durante la revisión.
