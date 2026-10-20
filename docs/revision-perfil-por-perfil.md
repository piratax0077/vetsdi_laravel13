# Revisión de perfiles — 19 de octubre de 2026

- Tutor: edición, dirección, contactos, mascotas, propiedad de datos y acceso a dependientes comprobados.
- Profesional: edición de identidad, contacto, dirección y especialidad comprobada. Se corrigió el registro inicial sin especialidad opcional y se rechaza una especialidad de otra profesión.
- Asistente: perfiles, contactos y controles de permisos comprobados; 30 aperturas HTTP 200 de los 10 roles existentes. AsistenteEnfermeria sigue sin rol en la base local.
- Clínica: se corrigió la edición de direcciones compartidas y la sincronización del nombre estructurado, apellidos y teléfono del responsable con su cuenta; el correo se normaliza antes de validar.
- Peluquería: registro inicial, agenda, estados, historial y separación entre negocios comprobados con datos aislados. Incluye Baño, Belleza y Peluquería.

Las ediciones de dirección de tutor, profesional, asistente y clínica ahora usan una comprobación común para copiar registros compartidos con otros perfiles, contactos o lugares antes de modificarlos. El registro inicial de tutor y clínica comprueba que la comuna corresponda a la región.

Validación: pruebas PHP aisladas en SQLite y pruebas JavaScript de los formularios; apertura de pantallas locales con escritura y envíos bloqueados. La revisión de peluquería utiliza cuentas temporales en SQLite.

Pendientes conservados: SMTP, recuperación por SMS y comprobación con cuenta real del rol de enfermería, que todavía no existe localmente. No se crearon usuarios ni se asignaron roles a cuentas existentes durante esta revisión.
