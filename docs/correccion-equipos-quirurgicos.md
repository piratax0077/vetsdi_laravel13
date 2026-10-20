# Equipos quirúrgicos

Se corrigió la validación que impedía guardar integrantes sin especialidad o subespecialidad. Esos campos son opcionales y no deben impedir registrar funciones dentro del equipo.

El detalle muestra todos los miembros guardados y su función. Si el profesional ya no existe, informa la referencia ausente y continúa mostrando los demás. Al editar otro equipo se limpia la lista anterior; respuestas antiguas no reemplazan el equipo actual. Los errores de guardado se muestran al usuario.

La edición valida los integrantes antes de reemplazar los existentes. Las operaciones de lectura y modificación se limitan a equipos del profesional autenticado. Se corrigió la relación del modelo a hasMany con la clase real del integrante.

Comprobación local: equipos 29 y 31 sin integrantes; equipo 30 con un integrante. No se modificaron ni completaron datos reales. Los integrantes rechazados por el guardado anterior deben agregarse nuevamente desde Editar.

Validación: cinco pruebas PHP con SQLite y tres pruebas JavaScript de renderizado, lista de edición y respuestas fuera de orden.
