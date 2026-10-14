# Revisión de la Ficha Veterinaria Única

Se corrigieron el historial por mascota (incluido un cambio de tutor), la visualización de recetas y antecedentes guardados, el último peso registrado y el odontograma según especie. Se incorporaron los documentos de laboratorio al resumen, junto con presupuestos y consentimientos.

El tutor puede consultar los registros sanitarios propios, pero no alterarlos. El servidor exige un profesional con una atención vinculada a la mascota para registrar vacunas y desparasitaciones. El formulario general tampoco permite al tutor alterar vacunas, antecedentes crónicos, cirugías o esterilización ya registrados. Los datos básicos y las fotos siguen siendo editables.

Se corrigió la conservación de las marcas crónico/confidencial al editar parcialmente una ficha. Agregar una desparasitación histórica ya no sustituye el resumen de la más reciente. El guardado de registros sanitarios usa transacciones con bloqueo de la mascota para evitar pérdidas por concurrencia.

Validación: 100 pruebas PHP aprobadas, 542 aserciones. La FVU se renderizó correctamente para seis mascotas de la base local en modo solo lectura. Se añadieron comprobaciones de JavaScript específicas de la FVU y de los controles de solo lectura. No se enviaron correos ni se modificaron registros clínicos reales durante estas comprobaciones.

Segunda comprobación: se corrigió la autorización sanitaria para exigir una atención o ficha que coincida también con el tutor actual. Una relación con un tutor anterior no habilita la escritura. La prueba de regresión verifica tanto el rechazo de esa relación como la aceptación de una ficha del tutor actual.

Resultado actualizado: 101 pruebas PHP, 544 aserciones y 26 pruebas JavaScript aprobadas; compilación Blade correcta y seis FVU locales renderizadas sin errores. La auditoría de rutas volvió a ejecutarse y sigue detectando 50 acciones inexistentes.

Actualización posterior: las 50 acciones heredadas fueron resueltas (cuatro recuperadas y 46 retiradas). Véase DEPURACION_RUTAS_2026-10-13.md para el resultado y los respaldos. La comprobación con datos locales cubre el renderizado; no es un recorrido manual autenticado por todas las pantallas. No se declara que todo el sistema esté libre de fallas.
