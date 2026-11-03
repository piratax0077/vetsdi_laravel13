# Exportación de pacientes y atenciones del centro

Acceso: Configuración → Descargar datos. Descarga ZIP con CSV UTF-8 separados por punto y coma y un archivo LEEME con centro, fecha y cantidad de filas.

Solo el propietario y los administradores autorizados por PerfilCentroMedico pueden descargar. El POST exige autenticación, CSRF y una institución autorizada. La exportación se registra en auditoría y la respuesta no permite caché.

Las mascotas se obtienen de las vinculaciones y atenciones del centro. Las horas y fichas se filtran por los lugares de atención de la institución y sus sucursales; si existe institución explícita en la tabla, también se verifica. Reservas e internaciones se filtran por institución; evoluciones y controles dependen exclusivamente de esas internaciones. Los tutores incluyen solo identificación y contacto de las mascotas seleccionadas. No se incluyen tablas de usuarios, personal, contabilidad ni credenciales.

Los CSV se generan por lotes y se archivan temporalmente en storage/app/private/exportaciones. Se eliminan al finalizar la respuesta. Se escapan celdas de texto susceptibles de ejecutarse como fórmulas. No es un volcado SQL ni incluye archivos adjuntos.

## Configuración del profesional

Configuración profesional → Descargar datos genera un ZIP propio. El perfil se obtiene exclusivamente del usuario autenticado, sin aceptar identificadores de otro profesional. Las horas y fichas se filtran por id_profesional; reservas por la relación profesional cuando existe o el usuario que las registró. Hospitalizaciones: solo asignadas y con vínculo activo al centro; sus evoluciones y controles se filtran además por autor. No se exporta el resto de la actividad del establecimiento ni las notas clínicas de otros profesionales.
