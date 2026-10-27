# Revisión de agendas por rol

Se unificó la validación de lectura y cambios de estado en `AccesoAgendaVeterinaria`.

| Acción | Profesional | Asistente | Tutor/paciente |
|---|---|---|---|
| Ver una hora | Su agenda | Profesional y lugar vinculados, con permiso | Sus propias horas |
| Reservar | Su agenda y lugar activo | Permiso explícito de reserva | Su tutor y sus mascotas |
| Confirmar o anular | Antes de pasar a atención | Permiso específico y hora pendiente | Solo sus horas pendientes |
| Recepcionar pago | Su agenda | Permiso de caja existente | Sin acceso a caja |
| Espera y ficha clínica | Profesional de la hora | Bloqueado | Bloqueado |

Se protegen también las rutas alternativas de ficha SDI y hospital ambulatorio. Los cobros rechazan horas anuladas, inasistidas o bloqueadas. Un cambio de estado simultáneo no puede ser sobrescrito por una confirmación antigua. La agenda devuelve el estado real, sin convertir una reserva pendiente en confirmada.

Las reservas rechazan horas pasadas, mascotas ajenas y lugares sin vínculo activo; un día sin horario configurado devuelve una respuesta de validación. Los correos de confirmación/anulación toleran dirección o especialidad no configuradas. Las pantallas de asistente ocultan los botones no autorizados y no ofrecen atender. En el tutor se recuperan los botones ante errores de red, manteniendo bloqueados los que ya lo estaban por estado.

## Comprobaciones

- Batería SQLite de permisos, reservas, fichas y reportes: 68 pruebas, 349 aserciones.
- Comprobación local de solo lectura: agenda profesional y escritorio del tutor responden HTTP 200.
- No existe una cuenta local de asistente con permiso de agenda concedido; su matriz de permisos y los rechazos se verifican con datos temporales SQLite. No se concedieron permisos a cuentas reales.
- Antes de esta revisión ya fallaba una comprobación ajena a la agenda en `PerfilTutorMascotaTest`, que espera el campo de dirección `perfil_numero_dir` en el formulario del tutor. No se modificó el formulario de perfil en esta tarea.
