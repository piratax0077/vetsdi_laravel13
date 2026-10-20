# Perfil de peluquería

Disponible en el registro de cuentas como **Peluquería**. El registro utiliza la misma identidad, verificación de correo y selección de perfiles que el resto del sistema. Se completa nombre del negocio o profesional, teléfono y dirección. Entrada: `/Peluqueria`.

Incluye registro privado de tutores y mascotas (contacto, especie, raza y cuidados comunicados), agenda diaria, reservas de Baño, Belleza o Peluquería con duración y valor, confirmación, inicio de atención, cancelación y registro obligatorio del trabajo antes de finalizar. El historial conserva los servicios finalizados con fecha y valor. No concede permisos de ficha clínica.

Cada peluquería trabaja con una sola agenda simultánea. Las reservas canceladas liberan el horario; se admiten reservas consecutivas. Los registros de mascotas de este módulo son propios del negocio y no modifican las fichas clínicas ni asignan tutores de otras cuentas.

Migración: `2026_10_19_000003_create_peluqueria_module.php` (aplicada localmente). Agrega el tipo de cuenta y rol `Peluqueria`, y las tablas `peluquerias`, `peluqueria_clientes`, `peluqueria_citas`. No asigna el nuevo perfil a usuarios existentes.

Pruebas aisladas: `tests/Feature/PeluqueriaTest.php` cubre completar perfil, acceso web, formularios, reservas superpuestas/consecutivas, registro obligatorio, cancelación y aislamiento entre negocios. La verificación de correo depende del SMTP pendiente ya documentado para el sistema.
