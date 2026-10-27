# Centro estético y profesional de estética

Se incorpora `centro_estetico` como rol institucional con su propio escritorio `/CentroEstetico`. El rol existente `peluqueria` se presenta como **Profesional de estética** y conserva `/Peluqueria` para no romper los accesos anteriores. El rol veterinario `profesional` continúa separado.

El centro administra sus datos de empresa (RUT, razón social, giro y correo), personal y permisos, sucursales, procedimientos, precios y agendas. Su perfil no habilita atender ni editar fichas de atención. El profesional de estética trabaja en su agenda y registra atenciones; para gestionar personal o sucursales debe cambiar al perfil institucional autorizado.

Los profesionales tienen una identidad propia en `profesionales_estetica`. Los centros usan el modelo `CentroEstetico` sobre los establecimientos existentes: no se duplican ni se trasladan las reservas, clientes, fichas, aranceles o sucursales. La actualización habilita el nuevo rol institucional a los propietarios actuales sin quitarles su perfil profesional anterior.

El personal conserva su cuenta de acceso. La membresía activa permite entrar al centro con los permisos concedidos; solo quien tiene permiso de atender obtiene además el acceso profesional de estética. Desactivar la membresía retira esos accesos derivados. Los profesionales vinculados ven su propia agenda dentro del centro.

El alta de un centro no crea un veterinario ni un profesional de estética automáticamente. Si la misma persona necesita atender y administrar, utiliza los dos perfiles separados desde **Cambiar de perfil**.

## Verificación

75 pruebas de peluquería, selección de cuenta y registro aprobadas. El script `scripts/verificar-centro-estetico.php` revisa ambos escritorios y la denegación de configuración de personal desde el perfil profesional con escrituras y notificaciones bloqueadas.
