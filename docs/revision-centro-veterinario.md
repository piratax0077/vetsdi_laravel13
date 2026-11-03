# Revisión del centro veterinario

Los formularios del centro y sus áreas usan `floating-label-activo-sm`. El escritorio carga `centro-formularios.js` para presentar los RUT con puntos, guion y dígito verificador en mayúscula, incluidos los campos agregados al abrir modales. El formato no reemplaza la validación del dígito verificador en el servidor.

## Integración comercial

Los accesos del centro son al panel comercial de ventas. Sus destinos se configuran por separado con `CENTRO_ALIMENTOS_URL`, `CENTRO_FARMACIA_URL` y `CENTRO_ACCESORIOS_URL`. Por defecto conservan las rutas de Veterfood definidas a partir de `VETERFOOD_URL`. Si un destino está vacío o no es una URL HTTP(S) válida, el acceso aparece como no disponible.

Estos enlaces no crean una sesión comercial ni sincronizan productos, compras, inventario o pagos. Esa integración requiere acordar y probar el contrato con los sistemas receptores. El SSO existente para tutores usa `SDI_SSO_KEY`, `ALIMENTOS_SSO_URL` y `VETERFARMA_URL`; no se utiliza para dar permisos de venta al administrador del centro.

## Verificación

Las pruebas funcionales usan SQLite en memoria y comprueban, entre otros casos, separación por centro, permisos, hospitalización, gestión de áreas, exportaciones y perfiles. Las pruebas de JavaScript comprueban envío de formularios, errores y presentación del RUT. La revisión de plantillas compila las vistas del administrador y de las áreas antes de validar su sintaxis PHP.

Las pruebas locales no certifican la disponibilidad ni el comportamiento de los sistemas externos de Alimentos y Farmacia.

Verificación realizada: 404 pruebas funcionales y 94 pruebas de JavaScript aprobadas; 153 plantillas compiladas sin errores. Después de conectar los indicadores del escritorio al reporte real se repitieron las 87 pruebas de centro, perfiles, reportes, gestión de trabajadores y exportaciones, todas aprobadas. Se renderizaron el escritorio de Centrovet y las seis áreas con su gestión en la base local. Los tres destinos comerciales respondieron HTTP 302 a solicitudes HEAD sin credenciales; falta probar la sesión comercial y la sincronización con los sistemas receptores.
