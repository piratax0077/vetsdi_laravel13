# Centros y hospitales veterinarios

Los dos escritorios comparten la operación veterinaria y los estilos existentes
de Vet SDI. El tipo de establecimiento se configura desde **Configuración →
Configurar establecimiento**. Cambiar entre centro y hospital conserva sus datos.

## Funciones

- Hospitalización: plazas (jaula, canil, sala, aislamiento u observación), ingreso
  de mascotas, cambio de plaza, indicaciones, evoluciones con peso y temperatura,
  y egreso por alta, traslado, fallecimiento o anulación. Las internaciones
  cerradas y sus evoluciones conservan su historia.
- Servicios: catálogo por establecimiento para peluquería, laboratorio,
  imagenología, vacunatorio y dental; precio en CLP, duración, habilitación,
  reservas, confirmación, atención, resultado y cancelación.
- Asistentes: vacantes internas, requisitos, jornada, cierre, candidatos y etapas
  de selección. Seleccionar un candidato no crea una cuenta ni un contrato;
  el alta administrativa sigue en Personal.
- Impuestos: obligaciones ingresadas por administración, período, monto,
  vencimiento, estado, fecha de pago y referencia de comprobante. Registrar un
  pago no ejecuta una transferencia ni una declaración tributaria.
- Estadísticas y avisos calculados desde los registros del establecimiento.
- Auditoría de altas y modificaciones, incluido el usuario y valores anteriores
  y posteriores. La operación y su auditoría se guardan en una transacción.

Las mascotas disponibles son las que tienen horas registradas en un lugar de
atención del establecimiento o sus sucursales, o las vinculadas desde **Pacientes
del establecimiento**. La vinculación busca por RUT exacto del tutor y permite
recibir urgencias sin una hora previa. No duplica la ficha ni crea horas ficticias.

Las plazas ocupadas no se pueden deshabilitar. La hospitalización no se puede
deshabilitar con internaciones activas. La agenda rechaza superposiciones de una
mascota o del mismo responsable dentro del establecimiento, incluso entre áreas.
El precio y duración de las reservas existentes se conservan cuando cambia el
catálogo, salvo que se reprograme el horario o se elija otro servicio.

La zona horaria del módulo es `America/Santiago`, configurable con
`VET_OPERACION_TIMEZONE`. El middleware restaura la zona del resto del sistema
al terminar la petición.

## Instalación

```powershell
php artisan migrate --path=database/migrations/2026_10_28_000001_operacion_establecimientos_veterinarios.php --force
php artisan migrate --path=database/migrations/2026_10_28_000002_pacientes_establecimientos_veterinarios.php --force
```

Las migraciones crean once tablas `vet_*` sin modificar las tablas clínicas
heredadas. Sus referencias internas tienen claves foráneas; los identificadores
heredados se validan desde el servicio para respetar los tipos de claves ya
existentes. No necesita un seeder ni datos de demostración.

Las rutas están en `routes/operacion_veterinaria.php`, cargadas desde
`routes/integraciones.php`. Exigen autenticación, un rol administrativo admitido
y acceso al establecimiento. Toda escritura usa POST y CSRF.

## Verificación

`tests/Feature/OperacionVeterinariaTest.php` comprueba migración reversible,
ambos tipos, ocupación, egreso, evoluciones, agenda, precios, selección de
candidatos, pagos, aislamiento entre centros, rollback de auditoría, formularios
con datos, autenticación y tarjetas conectadas.

`tests/Unit/operacion-veterinaria.test.cjs` comprueba el bloqueo de envíos repetidos.

## Estadísticas y reporte CSV

`Administrador/Reporte_estadisticas` calcula los indicadores con
`ReporteCentroVeterinario`. Permite elegir mes, año y sucursal y descargar los
mismos resultados en CSV compatible con Excel. El historial mensual siempre
contiene seis meses consecutivos, incluso al consultar desde un día 31.

- Recaudación: bonos de consulta con pago confirmado dentro del período de pago,
  excluyendo devoluciones. El precio de una reserva no se interpreta como un pago.
- Atenciones: fichas veterinarias finalizadas y servicios completados, por fecha
  registrada de atención. Al filtrar por sucursal se excluyen los servicios que
  no registran un lugar de atención.
- Pacientes nuevos: mascotas distintas cuya primera vinculación o actividad en
  el ámbito seleccionado está dentro del período. Una mascota ya registrada en
  meses anteriores no se vuelve a contar como nueva.
- Asistencia: se distinguen asistencias, inasistencias explícitas, cancelaciones
  de tutor, cancelaciones del centro, cancelaciones sin origen y pendientes.
  Una cita pasada sin cierre no se convierte automáticamente en inasistencia.
  Las tasas usan solamente asistencias e inasistencias registradas.
- Documentos: se cuentan por fecha de emisión y se limitan a fichas de mascotas
  del centro o sucursal. Las recetas se agrupan por ficha, no por medicamento.
- Hospitalización, plazas, reservas de hoy, obligaciones y convocatorias muestran
  el estado actual. Las obligaciones y convocatorias son del establecimiento
  completo; las plazas y hospitalizaciones no tienen desglose por sucursal.

Las fuentes ausentes se avisan en pantalla y en el CSV; los indicadores afectados
no se sustituyen silenciosamente por cero. El reporte solo lee registros y no
requiere nuevas migraciones. Sus pruebas están en
`tests/Feature/ReporteCentroVeterinarioTest.php`.
