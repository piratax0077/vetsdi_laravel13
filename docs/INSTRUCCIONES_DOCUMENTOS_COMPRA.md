# Documentos de compra del inventario: lo que falta programar

Pantalla: `http://vetsdi.local/Veterinaria/Inventario`, pestaña **Documentos de compras**.

La parte visual ya está terminada y aprobada por la diseñadora. Este trabajo es **solo de back-end**: base de datos, rutas, controlador y lectura del archivo. La pantalla ya sabe usar todo lo que se describe aquí; basta con crear las rutas con los nombres exactos y enviar los datos con los campos indicados.

Antes de empezar, lee completo `AGENTS.md` en la raíz del proyecto. Sus reglas de diseño mandan sobre cualquier otra decisión.

---

## 1. Lo que NO se toca

Estos archivos son el diseño aprobado. **No se modifican**, ni siquiera para "ordenar" o "mejorar":

| Archivo | Qué contiene |
|---|---|
| `resources/views/app/veterinaria/inventario/index.blade.php` | Pantalla con sus pestañas |
| `resources/views/app/veterinaria/inventario/partials/facturas.blade.php` | Indicadores, filtros y tabla de documentos |
| `resources/views/app/veterinaria/inventario/partials/facturas_modales.blade.php` | Modales de agregar, detalle y eliminar |
| `public/js/facturas_compra.js` | Cálculo de IVA, RUT, adjuntador y lectura del XML |
| `public/css/style.css` | Estilos de todo el sistema |
| `resources/views/template/**` | Plantillas, headers y menús |

Tampoco se cambian:

- Los `id`, `name`, clases ni atributos `data-*` de los modales y de la tabla: el JavaScript depende de ellos.
- Los textos visibles, botones, colores, tipografías, íconos ni el orden de los campos.
- Los nombres de las rutas listadas en el punto 3: la vista los busca con `Route::has()`.

Si para que algo funcione **parece necesario** cambiar uno de estos archivos, detente y explica qué cambiarías y por qué, sin hacerlo.

Lo que sí se puede crear o modificar:

- Una migración nueva.
- `app/Http/Controllers/InventarioVeterinarioController.php` (agregar métodos y completar `index()`), o un controlador nuevo si queda más ordenado.
- Las rutas dentro del grupo `Veterinaria/Inventario` de `routes/web.php`.
- Clases nuevas de apoyo (validación del RUT, lector de documentos) y pruebas.

---

## 2. Base de datos

Crear una migración nueva con la tabla `vet_inventario_documentos_compra` (motor **InnoDB**):

| Columna | Tipo | Notas |
|---|---|---|
| `id` | id | |
| `titular` | string(60), índice | Mismo valor que usan los lotes: `profesional:ID` o `centro:ID` (sale de `contexto()`) |
| `tipo_documento` | string(2) | `33`, `34`, `61` o `56` |
| `folio` | unsignedBigInteger | |
| `fecha_emision` | date | |
| `proveedor` | string(150) | Razón social |
| `rut_proveedor` | string(12) | Guardado con puntos y guion: `76.123.456-0` |
| `documento_referencia_id` | foreignId nullable | Solo notas 61 y 56; apunta a esta misma tabla, `nullOnDelete()` |
| `neto` | bigInteger (con signo) | Pesos, sin decimales |
| `exento` | bigInteger (con signo) | |
| `iva` | bigInteger (con signo) | |
| `total` | bigInteger (con signo) | |
| `estado_pago` | string(15) | `Pendiente`, `Pagada` o `Aplicada` |
| `observaciones` | string(250) nullable | |
| `archivo_ruta` | string nullable | Ruta dentro del disco `local` |
| `archivo_nombre` | string nullable | Nombre original del archivo |
| `archivo_mime` | string(100) nullable | |
| `archivo_peso` | unsignedInteger nullable | Bytes |
| `usuario_id` | unsignedBigInteger | Quién lo registró |
| timestamps | | |

Índice único: `(titular, rut_proveedor, tipo_documento, folio)`. Así no se registra dos veces el mismo documento de un proveedor.

Los montos de la **nota de crédito (61)** se guardan en negativo (neto, exento, IVA y total).

---

## 3. Rutas

Dentro del grupo existente en `routes/web.php`:

```php
Route::middleware('auth')->prefix('Veterinaria/Inventario')->name('vet.inventario.')->controller(\App\Http\Controllers\InventarioVeterinarioController::class)->group(function () {
    // ... rutas actuales ...
});
```

Agregar estas, con **estos nombres exactos**:

| Método | URL | Nombre | Para qué |
|---|---|---|---|
| POST | `documentos-compra` | `facturas.guardar` | Guardar el documento y su archivo |
| POST | `documentos-compra/leer` | `facturas.leer` | Leer un PDF o imagen y devolver sus datos (punto 6) |
| GET | `documentos-compra/exportar` | `facturas.exportar` | Descargar `facturas.csv` |
| GET | `documentos-compra/{factura}/archivo` | `facturas.archivo` | Ver el archivo adjunto |
| POST | `documentos-compra/{factura}/pago` | `facturas.pago` | Cambiar entre Pendiente y Pagada |
| DELETE | `documentos-compra/{factura}` | `facturas.eliminar` | Eliminar el documento |

Dejar `leer` y `exportar` **antes** de las rutas con `{factura}`, y restringir `{factura}` a números (`whereNumber`).

Todas las rutas usan `$this->contexto($r)` para saber el titular, igual que los métodos actuales. La pantalla ya les manda `id_institucion` cuando se entra desde un centro. Cada documento se busca siempre **filtrando por titular** (nadie puede ver ni tocar documentos de otro).

---

## 4. Lo que espera la vista

En `index()` agregar la variable `$facturas` al `compact(...)`. Debe ser una colección con un objeto por documento, con **estos nombres de campo**:

| Campo | Ejemplo | Notas |
|---|---|---|
| `id` | `12` | |
| `tipo_documento` | `'33'` | Como texto |
| `folio` | `'10482'` | |
| `fecha_emision` | `'2026-11-12'` | Formato `Y-m-d` |
| `proveedor` | `'Distribuidora Agrovet'` | |
| `rut_proveedor` | `'76.123.456-0'` | |
| `neto`, `exento`, `iva`, `total` | `363000` | Enteros; negativos en la nota de crédito |
| `estado_pago` | `'Pendiente'` | |
| `documento_referencia` | `'Factura N° 10482'` | Texto; vacío si no es nota |
| `url_respaldo` | `route('vet.inventario.facturas.archivo', ...)` | Vacío si no tiene archivo |
| `observaciones` | `'...'` | |

- Ordenar por `fecha_emision` descendente y luego `id` descendente.
- **No paginar**: los filtros de la tabla trabajan sobre las filas cargadas. Si se quiere un tope, usar `limit(500)`.
- Los indicadores (documentos, pendientes, monto por pagar, compras del mes) se calculan solos en la vista. Si se prefiere calcularlos en el servidor, se puede enviar `$resumenFacturas` con las claves `documentos`, `pendientes`, `por_pagar` y `compras_mes`.

---

## 5. Guardar (`facturas.guardar`)

Campos que envía el formulario (`multipart/form-data`):

`tipo_documento`, `folio`, `fecha_emision`, `proveedor`, `rut_proveedor`, `documento_referencia_id`, `neto`, `exento`, `iva`, `total`, `observaciones`, `archivo_factura` y `_formulario` (siempre `factura`).

Validación en el servidor (aunque la pantalla ya valide, **no confiar en el navegador**):

- `tipo_documento`: obligatorio, uno de `33,34,61,56`.
- `folio`: obligatorio, solo dígitos, máximo 10.
- `fecha_emision`: obligatoria, `date_format:Y-m-d`, no posterior a hoy.
- `proveedor`: obligatorio, máximo 150.
- `rut_proveedor`: obligatorio y **válido con módulo 11**. Normalizar a `76.123.456-0` (mayúscula si termina en K) antes de guardar y de revisar duplicados.
- `documento_referencia_id`: obligatorio si el tipo es 61 o 56. Debe existir, ser del mismo titular y ser una factura (33 o 34).
- `neto`, `exento`, `iva`: enteros, mínimo 0. La pantalla los envía siempre en positivo.
- Factura exenta (34): `neto` e `iva` se guardan en 0.
- Neto o exento debe ser mayor que 0.
- `archivo_factura`: opcional, `file|mimes:xml,pdf,jpg,jpeg,png|max:10240`.
- Duplicado (mismo titular, RUT, tipo y folio): error en `folio` con el texto *"Ya registraste un documento de este tipo con ese folio para el proveedor."*

Cálculo en el servidor (ignorar el `total` que llega):

- `total = neto + exento + iva`.
- Nota de crédito (61): guardar `neto`, `exento`, `iva` y `total` en negativo, con `estado_pago = 'Aplicada'`.
- Los demás tipos parten con `estado_pago = 'Pendiente'`.

Archivo:

- Guardarlo en el disco **`local`** (privado, no `public`), en `inventario/documentos_compra/{titular}/` con un nombre generado (`store()`), nunca con el nombre original.
- Guardar `archivo_nombre`, `archivo_mime` y `archivo_peso`.
- Guardar el registro y el archivo de forma que, si algo falla, no quede un archivo huérfano.

Respuestas:

- **Con error:** `back()->withErrors(...)->withInput()`. La pantalla vuelve a abrir sola la pestaña y el modal con lo escrito, porque el formulario trae `_formulario=factura`. El archivo hay que volver a elegirlo (los navegadores no permiten devolverlo).
- **Bien:** redirigir a `route('vet.inventario.index', ['vista' => 'facturas'] + $r->only('id_institucion'))` con `->with('success', 'Documento de compra registrado.')`. El parámetro `vista=facturas` deja abierta la pestaña Documentos de compras.

---

## 6. Leer el archivo (`facturas.leer`)

Sirve para que, al adjuntar un **PDF o una imagen**, los campos se completen solos. El **XML del SII ya se lee en el navegador**, así que esta ruta solo recibe PDF, JPG y PNG.

- Recibe `archivo_factura` por POST (con `_token`). Validar `file|mimes:pdf,jpg,jpeg,png|max:10240`.
- **No guarda nada**: solo lee y responde.
- Responde JSON con los datos que haya podido reconocer, todos opcionales:

```json
{
  "tipo_documento": "33",
  "folio": "10482",
  "fecha_emision": "2026-11-12",
  "proveedor": "Distribuidora Agrovet SpA",
  "rut_proveedor": "76123456-0",
  "neto": 363000,
  "exento": 0,
  "iva": 68970,
  "folio_referencia": "8831"
}
```

- Si no reconoce nada, responder `{}` con estado 200. La pantalla avisa que se complete a mano.
- Solo responder error (422) si el archivo no es válido.

Cómo leerlo:

- **PDF:** extraer el texto (por ejemplo con `smalot/pdfparser`) y buscar los datos del formato impreso del SII: "FACTURA ELECTRÓNICA" o "NOTA DE CRÉDITO", "N°" del folio, el RUT del emisor, la fecha y los montos NETO, EXENTO, IVA y TOTAL.
- **Imagen:** requiere reconocimiento de texto (OCR) en el servidor. Si no está disponible, responder `{}`.
- **Antes de instalar cualquier paquete de Composer o programa externo, pedir autorización.**

---

## 7. Las demás acciones

- **`facturas.archivo`**: entrega el archivo del disco `local` con `response()->file()` (para verlo en el navegador) solo si el documento es del titular. Este es el enlace que va en `url_respaldo`.
- **`facturas.pago`**: alterna entre `Pendiente` y `Pagada`. La nota de crédito (61) no cambia de estado. Volver con `vista=facturas` y un mensaje `success`.
- **`facturas.eliminar`**: borra el registro y su archivo. Las notas que lo referenciaban quedan sin referencia (`nullOnDelete`). Volver con `vista=facturas` y un mensaje `success`.
- **`facturas.exportar`**: descarga `facturas.csv` con separador `;`, BOM UTF-8 y la misma protección contra fórmulas que usa `exportar()` del inventario. Columnas en este orden:

  `Folio; Fecha emisión; Proveedor; Total; Tipo de documento; Estado de pago; RUT proveedor; Neto; Exento; IVA 19%; Enlace a PDF/XML; Dif. vs detalle; Observaciones`

  Fecha en `d-m-Y`. "Dif. vs detalle" va vacía (los productos de cada documento se programarán más adelante).

---

## 8. Al terminar

1. `php artisan migrate` y `php artisan route:list --name=vet.inventario.facturas` (deben aparecer las 6 rutas).
2. Pruebas de las reglas importantes: RUT inválido, folio duplicado, nota sin referencia, exenta con neto, nota de crédito en negativo y acceso a documentos de otro titular (debe dar 404).
3. Probar en la pantalla, como profesional y como centro (`?id_institucion=`):
   - Agregar un documento con XML, con PDF y sin archivo.
   - Ver el detalle y abrir el archivo.
   - Marcar como pagada y volver a pendiente.
   - Eliminar.
   - Descargar el CSV.
4. Confirmar que **no cambió nada visual**: `git diff --stat` no debe mostrar los archivos del punto 1.
5. Informar qué archivos se crearon o modificaron y por qué.
