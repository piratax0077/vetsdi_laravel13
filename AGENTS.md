# AGENTS.md — Protección del diseño y lógica visual del proyecto

Este documento establece las reglas obligatorias para cualquier asistente de IA o programador que trabaje en este proyecto, incluyendo Codex y Claude Code.

Su objetivo es permitir que el sistema evolucione funcionalmente sin perder, alterar ni reinterpretar arbitrariamente los diseños establecidos por la diseñadora.

---

## 1. PRINCIPIO FUNDAMENTAL

**El diseño existente es la referencia y autoridad visual del proyecto.**

La persona responsable del diseño define la identidad visual, la experiencia de usuario, la estructura de las vistas y los patrones de interacción.

El trabajo de desarrollo debe respetar tanto la apariencia como la lógica del diseño existente. No basta con conservar los colores: también deben mantenerse la organización, jerarquía, navegación, comportamiento y consistencia de los componentes.

La autorización para desarrollar una funcionalidad NO significa autorización para rediseñar la interfaz.

En caso de duda, conserva la solución existente y consulta antes de realizar cambios visuales.

## 2. ELEMENTOS VISUALES QUE DEBEN RESPETARSE

No modificar, eliminar, reemplazar ni reorganizar sin autorización explícita:

- Headers, encabezados, barras superiores y sus variantes correspondientes a cada sección o módulo.
- Menús, navegación, breadcrumbs y elementos de orientación.
- Botones, incluyendo su texto, ubicación, jerarquía, tamaño, estilo y comportamiento.
- Botones **Volver**, **Cancelar**, **Guardar**, **Continuar** y demás acciones existentes.
- Tipografías, familias, pesos, tamaños, alturas de línea y jerarquías de texto.
- Colores institucionales, paletas, gradientes y colores de estados.
- Márgenes, paddings, espacios, alineaciones, anchos, alturas y proporciones.
- Tarjetas, tablas, listados, formularios, campos, etiquetas e indicadores.
- Modales, ventanas emergentes, confirmaciones, alertas y sus encabezados, pies y acciones.
- Iconos, ilustraciones, bordes, radios, sombras y fondos.
- Distribución de contenidos y orden de los elementos.
- Adaptaciones responsive y comportamiento en distintos tamaños de pantalla.

No sustituyas un componente existente por otro visualmente distinto solo por preferencia técnica o estética.

## 3. RESPETAR LA LÓGICA DEL DISEÑO

Antes de implementar o modificar una vista, analiza cómo funciona el diseño existente:

1. Identifica el header y la variante que corresponde a esa sección.
2. Revisa cómo se organiza el contenido y cuál es su jerarquía.
3. Identifica los patrones de navegación y las acciones disponibles.
4. Revisa cómo se presentan los botones y sus estados.
5. Observa cómo se abren, presentan y cierran los modales.
6. Identifica los componentes reutilizables y las convenciones visuales.
7. Revisa cómo se comporta la vista en escritorio y móvil.

No interpretes cada pantalla como una pieza aislada. Las vistas forman parte de un mismo producto y deben conservar una experiencia coherente.

## 4. CREACIÓN DE VISTAS NUEVAS

Cuando se solicite una vista, módulo, pantalla o funcionalidad que todavía no existe, Codex o el programador tiene libertad para diseñar la nueva interfaz, pero debe hacerlo **siguiendo la lógica de diseño del proyecto**.

Antes de crearla:

- Examina vistas existentes del mismo módulo o de módulos similares.
- Identifica el header correcto que debe utilizar.
- Reutiliza componentes y patrones existentes cuando corresponda.
- Respeta tipografías, colores, botones, modales, espacios, bordes, iconografía y jerarquías.
- Mantén los patrones de navegación y los comportamientos habituales.
- Conserva la identidad visual del producto.

La nueva vista debe sentirse como una parte nativa del sistema, no como una aplicación diferente ni como una plantilla genérica.

No copies componentes sin criterio: comprende su función y aplica el patrón adecuado a la nueva necesidad. Puedes crear componentes nuevos cuando sea necesario, siempre que sean coherentes con el sistema visual existente.

Si no encuentras un patrón suficiente para resolver una nueva necesidad, propón una solución consistente con el diseño actual. Si la propuesta implica establecer una nueva regla visual o modificar un patrón compartido, solicita aprobación antes de incorporarla.

## 5. HEADERS Y NAVEGACIÓN

Los headers no son intercambiables por defecto.

- Conserva el header que corresponde a cada módulo, sección, rol o flujo.
- No elimines encabezados para ganar espacio.
- No reemplaces un header específico por uno genérico.
- No cambies títulos, subtítulos, acciones, navegación o distribución sin autorización.
- Al crear una vista nueva, identifica primero qué header utilizan las vistas equivalentes.
- Mantén los botones de volver y la navegación contextual según el flujo existente.

Si técnicamente una vista necesita una variante distinta, revisa primero si esa variante ya existe. No inventes una nueva sin justificarla.

## 6. BOTONES, ACCIONES Y MODALES

- Mantén el diseño, texto, jerarquía, ubicación y estilo de los botones existentes.
- Conecta las acciones solicitadas sin rediseñar el botón.
- Respeta los estados normal, hover, focus, disabled, loading y active que utilice el proyecto.
- Conserva los botones Volver y Cancelar y su comportamiento esperado.
- Mantén la estructura visual de los modales: encabezado, contenido, acciones, cierre y jerarquía.
- No conviertas un modal en una página, alerta o panel diferente sin autorización.
- No cambies el comportamiento de cierre, confirmación o cancelación sin revisar el flujo existente.

## 7. TIPOGRAFÍA, COLORES Y COMPONENTES

- Utiliza las fuentes y familias tipográficas ya definidas.
- No introduzcas nuevas tipografías por preferencia personal.
- Usa los colores y variables del proyecto; no inventes tonos alternativos.
- Reutiliza tokens, variables CSS y componentes existentes.
- Respeta los tamaños, pesos, radios, sombras y espaciados establecidos.
- No agregues estilos globales que puedan cambiar otras vistas.
- Evita duplicar componentes cuando ya exista uno que resuelva la necesidad.

Si necesitas un estilo que todavía no existe, sigue la escala y los criterios visuales ya utilizados. No cambies la identidad del producto.

## 8. CAMBIOS FUNCIONALES SIN ALTERAR EL DISEÑO

Siempre que sea posible, implementa la funcionalidad dentro de la estructura visual existente.

Está permitido modificar lógica PHP/Laravel, consultas, controladores, rutas, modelos, validaciones, JavaScript y conexiones necesarias para que la interfaz funcione.

Sin embargo:

- No reemplaces bloques completos de HTML o Blade si basta con modificar una parte.
- No sobrescribas archivos CSS completos para resolver un detalle local.
- No elimines clases existentes sin comprobar dónde se utilizan.
- No cambies estilos globales para corregir una sola pantalla.
- No elimines elementos visuales porque parezcan innecesarios.
- No cambies el orden o la estructura del contenido por conveniencia de implementación.

Separa los cambios funcionales de los cambios visuales y minimiza el alcance de cada modificación.

## 9. AUTORIZACIÓN PARA CAMBIOS VISUALES

Debes solicitar autorización antes de:

- Rediseñar una vista existente.
- Cambiar la estructura o distribución de una pantalla.
- Modificar un header o su variante.
- Cambiar botones, tipografías, colores o estilos.
- Alterar la estructura o apariencia de un modal.
- Cambiar patrones de navegación.
- Modificar componentes compartidos que afecten otras vistas.
- Introducir una nueva regla visual que afecte al resto del sistema.

Antes de solicitar autorización, informa de manera concreta:

1. Qué necesitas cambiar.
2. Por qué es técnicamente necesario.
3. Qué archivos y vistas serán afectados.
4. Qué elementos visuales cambiarían.
5. Qué alternativa existe para conservar el diseño actual.

No ejecutes el cambio visual hasta recibir aprobación explícita.

## 10. FLUJO OBLIGATORIO ANTES DE PROGRAMAR

Antes de comenzar una tarea:

1. Lee este archivo completo.
2. Analiza la vista y los componentes existentes relacionados con la tarea.
3. Identifica qué partes son diseño establecido.
4. Determina qué cambios son funcionales y cuáles son visuales.
5. Identifica los archivos que necesitas modificar.
6. Comprueba si los componentes ya existen y pueden reutilizarse.
7. Advierte si la tarea puede afectar otras vistas.

Si el usuario solicita expresamente implementar una nueva vista, puedes diseñarla siguiendo las reglas de este documento. No necesitas pedir autorización para cada decisión menor de diseño coherente con los patrones existentes.

## 11. VERIFICACIÓN OBLIGATORIA DESPUÉS DE PROGRAMAR

Al terminar:

- Comprueba que la funcionalidad solicitada funciona.
- Verifica que se conserva el header correcto.
- Comprueba que los botones, especialmente Volver y Cancelar, siguen presentes y funcionan.
- Revisa tipografías, colores, tamaños, espacios y alineaciones.
- Comprueba que los modales mantienen su estructura y comportamiento.
- Revisa que no se hayan alterado otras vistas por cambios globales.
- Comprueba el comportamiento responsive.
- Informa qué archivos modificaste y por qué.
- Declara cualquier diferencia visual introducida.

Si no puedes comprobar visualmente una pantalla, indícalo expresamente. No afirmes que el diseño quedó intacto sin haberlo verificado.

## 12. REGLA FINAL

**Desarrolla siguiendo la lógica del diseño establecido.**

No impongas una estética personal ni reemplaces decisiones visuales de la diseñadora. Conserva las vistas aprobadas y, cuando debas crear algo nuevo, diseña una solución que respete los patrones, componentes, jerarquías y experiencia del sistema.

El objetivo es que todo el producto se perciba como un sistema único, coherente y diseñado bajo una misma dirección visual.

**La funcionalidad puede evolucionar; la identidad y la lógica visual deben mantenerse.**
