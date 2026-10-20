# Catálogo de aranceles veterinarios

La pantalla Aranceles de veterinaria general incluye **Catálogo de cirugías y procedimientos**, con 97 prestaciones por categoría y búsqueda. **Definir mi arancel** abre la prestación en Procedimientos o Exámenes sin precio predeterminado. El veterinario configura valor, tamaños, duración, anestesia e insumos según corresponda antes de guardar.

El catálogo está en `config/catalogo_prestaciones_veterinarias.php`. Los valores propios se guardan en `aranceles_profesionales`, separados por profesional y con rutas autenticadas. No se modifican aranceles de odontología ni de otros módulos.

Se conserva el respaldo anterior del navegador. Si existe, aparece **Importar mis aranceles del navegador**: requiere confirmar que pertenecen a la cuenta actual y solo se permite si todavía no tiene prestaciones guardadas. La importación es atómica y no borra el respaldo ni reemplaza precios existentes.

Migración aplicada localmente: `2026_10_19_000004_create_aranceles_profesionales_table.php`.

Validación: pruebas PHP aisladas de persistencia, precios por tamaño, códigos únicos, permisos, importación y configuración; pruebas JavaScript de la API; comprobación de solo lectura de pantalla y API locales.
