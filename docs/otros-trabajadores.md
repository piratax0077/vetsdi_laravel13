# Personal: Otros trabajadores

La pestaña **Otros** de Personal gestiona mantención, limpieza, contabilidad,
administración, seguridad y cualquier otra categoría no clínica que cree el
establecimiento. Centro y hospital comparten el registro y la presentación de
las tablas de profesionales y asistentes.

- Agregar y editar identidad, RUT, contacto, dirección, información bancaria y
  observaciones. El número de cuenta se conserva como texto.
- Asignar una o varias sucursales propias. La tabla respeta el contexto de
  sucursal y mantiene disponibles los trabajadores desvinculados.
- Crear, renombrar y deshabilitar categorías. Una categoría con trabajadores
  activos debe reasignarse antes de deshabilitarla.
- Consultar información, contratos e historial de mensajes desde Info.
- Desvincular y reactivar conservando los registros. Primero debe cerrarse un
  contrato vigente.
- En **Contratos → Otros**, registrar o editar un contrato indefinido, a plazo,
  de honorarios o de servicio externo, con fechas, horas y remuneración mensual
  ingresada por administración. No se permiten contratos vigentes duplicados ni
  períodos superpuestos. El cierre de un contrato futuro lo anula.
- Enviar un mensaje por correo con historial e identificación del remitente.
  El token de envío evita duplicar el correo cuando se repite una solicitud.
  Si el entorno usa correo de registro (`log` o `array`), se informa como
  **registrado**, no como entregado externamente. Los errores quedan como
  **fallido**. El historial conserva el destinatario de cada envío.

Registrar un trabajador no crea una cuenta de acceso ni concede roles. Registrar
un contrato o un dato bancario no ejecuta pagos. Todas las rutas exigen
autenticación, un rol administrativo y acceso al establecimiento; las escrituras
usan POST y CSRF. Los cambios de personal y contratos se auditan.

## Instalación

```powershell
php artisan migrate --path=database/migrations/2026_10_31_000001_otros_trabajadores_veterinarios.php --force
```

Crea cinco tablas propias y categorías iniciales para los establecimientos
existentes. No modifica los registros de profesionales o asistentes ni depende
de las tablas antiguas de mantención que no están presentes en esta base.

Las pruebas están en `tests/Feature/TrabajadoresCentroVeterinarioTest.php`.
Las pruebas de mensajes usan un transporte simulado y no envían correos reales.
