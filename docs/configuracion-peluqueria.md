# Configuración operativa de peluquería

El panel incluye Procedimientos y precios, Personal y permisos, Configurar agenda, Sucursales y Datos del negocio.

## Personal y permisos

El propietario agrega a personas con una cuenta registrada mediante su correo de acceso. Cada integrante utiliza su propia contraseña; no se crean credenciales compartidas ni se envían correos automáticamente. El acceso aparece en el selector de perfiles aunque el integrante no tenga un negocio propio. Si trabaja en varios negocios puede cambiar de contexto desde el encabezado.

Los permisos permiten consultar agenda, reservar, confirmar, cancelar, atender, consultar/registrar mascotas y tutores, ver historial y configurar procedimientos, agenda, sucursales o negocio. Reservar/atender agrega los permisos de consulta necesarios. La gestión de personal y permisos pertenece al propietario. El acceso puede desactivarse y revoca las operaciones de la sesión existente. Puede limitarse a una sucursal; los permisos se verifican en el servidor y los controles visibles acompañan esas restricciones.

## Agenda y sucursales

Cada sucursal tiene dirección, teléfono, estado y capacidad de atención simultánea. El propietario configura días de atención, apertura/cierre, descanso, anticipación mínima/máxima. Se pueden bloquear períodos por vacaciones, mantenimiento o feriados. Las reservas validan esas reglas y la capacidad real de concurrencia. Un integrante asignado a una reserva no puede atender otra al mismo tiempo. Las sucursales inactivas conservan su historial y rechazan nuevas reservas. La agenda utiliza la zona horaria America/Santiago. Cada reserva ocupa la duración exacta del procedimiento, sin imponer intervalos fijos de inicio. El calendario permite seleccionar minutos y muestra el término estimado en el formulario; los procedimientos admiten duraciones de 1 a 480 minutos. Los intervalos guardados por versiones anteriores se ignoran.

La migración `2026_10_25_000001_configuracion_operativa_peluqueria.php` agrega las tablas de sucursales, personal y bloqueos, y relaciona las citas existentes con una sucursal principal. No modifica horarios de citas, precios ni fichas. El horario de las sucursales existentes empieza sin restricciones adicionales hasta que se guarde la configuración; así se conserva el comportamiento anterior.

## Comprobación

Pruebas con SQLite de permisos, revocación, separación entre negocios y sucursales, configuración, horarios, descansos, bloqueos, capacidad y personal ocupado. Pruebas JavaScript del calendario y reservas. `scripts/verificar-configuracion-peluqueria.php` abre las cinco secciones con datos locales y bloquea todas las escrituras y envíos.

La sesión del navegador de prueba no estaba autenticada; las cinco secciones se verificaron mediante el kernel HTTP local con un usuario autorizado y todas las escrituras bloqueadas.

Resultado final: 70 pruebas PHP de peluquería, selector y perfil profesional aprobadas; 3 pruebas JavaScript aprobadas; cinco secciones locales con HTTP 200. Una ejecución adicional de 110 pruebas detectó una falla fuera de este módulo: `PerfilTutorMascotaTest::test_tutor_form_renders_without_birthdate_or_address_and_without_duplicate_editor_ids` espera `perfil_numero_dir`, ausente en la vista actual del tutor. No se modificó esa vista en este trabajo.

## Agenda profesional compartida

La agenda de peluquería reutiliza `public/css/agenda-profesional.css`, extraído de los estilos existentes de la plantilla profesional usada por odontología. La plantilla profesional carga el mismo archivo, sin duplicar estilos. Peluquería usa su encabezado, selector, botones de día/semana, colores y presentación del calendario.

La configuración permite varios tramos por día (por ejemplo lunes 09:00–13:00 y 15:00–19:00). Se validan solapamientos entre tramos y que cada procedimiento quepa completo en un tramo. La configuración anterior de apertura/cierre y descanso sigue siendo compatible y se presenta como tramos al editar. No se modifican las citas existentes ni se redondean las duraciones de procedimientos.

Validación de esta adaptación: 41 pruebas PHP y 4 JavaScript aprobadas; configuración de peluquería y agenda profesional local responden HTTP 200.

## Acceso a la agenda desde el escritorio

Mi agenda abre un selector de profesional y sucursal/lugar de atención desde el escritorio principal. El calendario muestra ese contexto fijo, sin selector de sucursal dentro. La selección se recuerda por negocio y se vuelve a validar al abrir. Las consultas de eventos y nuevas reservas incluyen el mismo profesional y lugar; no se mezclan agendas del personal. Las reservas históricas sin integrante asignado se muestran en la agenda del titular. La capacidad de la sucursal y la disponibilidad del profesional siguen comprobándose en el servidor.


### Reserva y recepción desde la agenda

El modal comienza únicamente con la búsqueda del RUT del tutor. Después de buscar se muestran sus mascotas (del sistema y de esta peluquería) y la opción de inscribir otra, conservando los datos del tutor encontrado. Cambiar el RUT obliga a repetir la búsqueda.

Las citas se gestionan desde el calendario y desde “Gestionar reserva”, con los estilos compartidos de los modales profesionales. La secuencia es Reservada → Confirmada → Pagada → En espera → En atención → Finalizada. La confirmación guarda su vía; la recepción del pago guarda medio, monto, referencia, fecha y usuario. Es un registro de pago recibido. Los permisos “Recepcionar pagos” y “Registrar llegada y espera” se asignan al personal desde configuración. El servidor valida cada transición y evita registrar pagos dos veces. Las fichas solo se editan durante la atención.
