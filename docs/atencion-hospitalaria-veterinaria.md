# Atención hospitalaria veterinaria

La vista administrativa de Hospitalización ya no muestra la leyenda Atrás ni el botón Agregar registro. La configuración conserva plazas y consulta de hospitalizaciones.

## Acceso y asignación

- Cambiar perfil → **Hospitalización veterinaria** abre el servicio: administración accede al área del centro, profesionales a sus pacientes asignados y enfermería al escritorio de controles y cuidados.
- Administración → Hospitalización → **Equipo del servicio** permite dar o desactivar accesos a profesionales vinculados y asistentes con contrato activo del centro. También está disponible desde Gestión del área.
- El servicio es una función de la cuenta existente y no requiere otra cuenta ni contraseña. El acceso se calcula a partir del vínculo activo; no es una opción de registro público para autoasignarse permisos clínicos.
- Enfermería consulta las indicaciones del veterinario y registra peso, temperatura, frecuencias cardíaca y respiratoria, valoración de dolor y cuidados realizados. No puede registrar ingresos, cirugía, altas ni controles médicos posteriores. La baja del contrato o del acceso impide continuar operando en el servicio.
- La estructura de trabajo de atención hospitalaria se adapta a la mascota, indicaciones, evolución y controles; los formularios humanos de pediatría, maternidad y licencias no se incorporan a la atención veterinaria.

- Escritorio profesional → Pacientes hospitalizados (`vet.hospital.profesional`).
- Configuración → Especialidades y áreas → Hospitalización → Asignar pacientes a profesionales.
- Solo profesionales con vínculo activo al establecimiento acceden a la vista clínica. Cada paciente debe estar asignado al usuario; los ingresos nuevos realizados por un profesional se asignan automáticamente a ese profesional.
- Las asignaciones se pueden modificar sin borrar las atenciones históricas. Todas las escrituras verifican el centro y la asignación del paciente.

## Registros

Se conservan las internaciones y evoluciones existentes. Se agregan tipos de registro: atención veterinaria, enfermería y cirugía (con procedimiento obligatorio y detalle clínico). Los registros conservan autor y fecha; no se reescriben las evoluciones históricas.

El alta requiere fecha, resumen e indicaciones; cierra la hospitalización y libera la plaza. El carné imprimible incluye indicaciones, cirugías y controles programados. Los controles permiten registrar realizado, cancelado o no asistió; el resultado es obligatorio al realizar y no se pueden completar anticipadamente.

Las citaciones se registran e imprimen en el carné; no se envían mensajes automáticos ni se bloquea la agenda de consultas con este módulo.

Migraciones: `2026_11_01_000001`, `000002`, `000003` y `000005` (equipo y controles de enfermería). No se generan pacientes o registros clínicos ficticios en la base local.
