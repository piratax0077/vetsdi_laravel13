# Revisión de perfiles de asistente

19 de octubre de 2026.

## Correcciones

- La ruta pública de sitios quedó al final del archivo de rutas: ya no captura `/Asistentes` ni provoca un 404 al entrar al escritorio.
- Las rutas antiguas de datos personales, contacto y residencia delegan en el servicio de perfil propio, incluidos los controladores de asistente de centro y jefatura. No pueden elegir otro asistente mediante el identificador de la solicitud.
- Los cambios de nombre y apellidos se sincronizan con los campos utilizados por el encabezado de la cuenta. El RUT no puede reutilizar el de otra identidad, y el teléfono y correo se sincronizan con la cuenta.
- Registrar un contacto nuevo ya no intenta leer una dirección de un contacto inexistente. Repetir el mismo contacto no duplica la vinculación.
- Los contactos se buscan dentro del perfil propio. No se sobrescribe un contacto ajeno por compartir su RUT. Si un contacto existente está compartido, la edición crea una copia privada y conserva los datos del otro perfil.
- Se conservan las comprobaciones de propiedad para cuentas bancarias, currículum, fotografías y datos de contacto.

## Comprobaciones

- 65 pruebas PHP y 357 comprobaciones, con SQLite y archivos temporales.
- 7 pruebas JavaScript para edición de perfiles y autorizaciones de la interfaz.
- 30 aperturas correctas en MySQL local: escritorio, perfil y lista de contactos para los 10 roles de asistente existentes. La comprobación bloquea escrituras y simula mensajes.
- El rol `AsistenteEnfermeria` no existe actualmente en la base local; las reglas de ese tipo se cubrieron con datos temporales, sin crear roles ni asignar permisos reales.

SMTP y recuperación por celular permanecen pendientes por indicación del usuario.
