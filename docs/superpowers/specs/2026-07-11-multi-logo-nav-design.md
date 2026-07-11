# Múltiples logos del Nav con activación exclusiva

## Contexto

La sección "Apariencia del Nav" del panel admin (`api/admin/index.php`) hoy es una fila sintética de un solo registro, respaldada por dos claves en `configuraciones_sitio` (`logo_nav`, `color_nav_texto`), con un único endpoint especial `save_logo_nav` que sube la imagen a Supabase Storage (bucket `logo_nav`, ver spec [[2026-07-11-logo-nav-supabase-storage-design]]) y guarda la URL.

Se pide poder mantener una **biblioteca de logos** (subir varios, conservarlos), donde solo uno está activo (el que se muestra en el sitio) en todo momento, y activar uno desactiva automáticamente el anterior. También se pide eliminar el color de texto global del menú — ya no aplica desde que cada ítem del menú tiene su propio color ([[2026-07-11-color-menu-nav-design]]).

## Comportamiento

- **Agregar**: subir una imagen nueva crea una fila **inactiva** en la biblioteca (no reemplaza al logo activo automáticamente).
- **Activar**: cada fila inactiva tiene un botón "Activar". Al usarlo, esa fila pasa a activa y la que estaba activa pasa a inactiva, en la misma operación — siempre hay exactamente un logo activo. No existe un botón "Desactivar" independiente.
- **Editar**: permite reemplazar la imagen de una fila existente (subir un archivo nuevo); si no se sube archivo, no se toca el campo `logo`. Editar no cambia el estado `activo`.
- **Eliminar**: bloqueado para la fila actualmente activa (el backend devuelve error). Para eliminarla hay que activar otra fila primero.
- El color de texto global del menú (`color_nav_texto`) se elimina del panel y del sitio — ya no se usa en ningún lado (los colores son por ítem del menú).

## Cambios

### 1. Base de datos

Nueva tabla:

```sql
CREATE TABLE IF NOT EXISTS public.logos_nav (
    id BIGSERIAL PRIMARY KEY,
    logo VARCHAR(500) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);
```

Se agrega a `api/database/schema/supabase_schema.sql` y `api/database/schema/nuevas_tablas.sql` (con `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` no aplica acá porque es tabla nueva completa — para el entorno productivo existente se ejecuta el `CREATE TABLE IF NOT EXISTS` directo, es idempotente).

Se quitan de los `INSERT INTO configuraciones_sitio` de ambos archivos de esquema las filas `logo_nav` y `color_nav_texto` (quedan solo `reservar_url`, `reservar_texto`, `footer_copyright`).

**Migración de datos en producción** (ejecutada una vez, vía script):
- `INSERT INTO logos_nav (logo, activo) VALUES ('{URL actual del logo}', true)` con la URL que ya está en `configuraciones_sitio.logo_nav` (la del logo migrado a Supabase Storage).
- `DELETE FROM configuraciones_sitio WHERE clave IN ('logo_nav', 'color_nav_texto')`.

### 2. `api/admin/index.php` — backend

- **`add_nav`** (reemplaza el uso de `save_logo_nav` para esta sección): requiere archivo (mismas validaciones de tamaño/extensión/mime que ya existían), lo sube a Supabase Storage (bucket `logo_nav`, vía `uploadToSupabaseStorage()`), inserta fila `INSERT INTO logos_nav (logo, activo) VALUES (?, false)`. Si no se envía archivo, error `'Debés subir una imagen'`.
- **`edit_nav`**: si se envía archivo, lo sube y actualiza `logo`; si no, no cambia nada (no hay otros campos editables en el form). No toca `activo`.
- **`delete_nav`**: antes de borrar, consulta si la fila tiene `activo = true`; si sí, responde `jsonResponse(['error' => 'No podés eliminar el logo activo. Activá otro primero.'])` sin borrar.
- **`activar_nav`** (acción nueva): `UPDATE logos_nav SET activo = false; UPDATE logos_nav SET activo = true WHERE id = ?` (dos statements en la misma request, sin necesidad de transacción explícita porque son secuenciales y el segundo solo corre si el primero no lanzó excepción).
- Se quita `'save_logo_nav'` de `$actions`/manejo especial una vez migrado (ya no lo usa ninguna sección) y el bloque `color_texto` del mismo (validación de `#hex`).
- `$sections['nav']`: pasa de la fila sintética actual a `'rows' => $db->query('SELECT * FROM logos_nav ORDER BY id')->fetchAll()`, `'fields' => ['logo','activo']`, `'can_add' => true`.
- Tabla del listado: la columna `activo` ya se renderiza con el badge Sí/No existente (sin cambios). Se agrega, solo para la sección `nav`, un botón "Activar" junto a "Editar"/"Eliminar" quer solo aparece si `!$row['activo']`; si la fila está activa, no se muestra botón de eliminar (el backend igual lo bloquearía, pero se oculta para evitar el click inútil).

### 3. `api/admin/index.php` — frontend (JS embebido)

- `fieldConfig.nav` pasa a: `[{name:'logo', label:'Logo (imagen)', type:'file'}]` (se quita `color_texto`).
- `canAdd.nav` pasa a `true`.
- Se quita `nav:'save_logo_nav'` del mapa `saveActions` del submit handler, así usa el flujo genérico (`add_nav` / `edit_nav`).
- Fix del preview en el modal: `if (val) div.innerHTML += `<img src="../../${val}" ...>`` pasa a anteponer `../../` **solo si `val` no empieza con `http`** (las URLs de Supabase son absolutas; las rutas viejas relativas ya no existen en este flujo, pero el fix es general para cualquier valor absoluto).

### 4. `api/index.php` (API pública)

Después de construir `$data['configuraciones']` desde `configuraciones_sitio`, se agrega:

```php
$logoActivo = $db->query("SELECT logo FROM logos_nav WHERE activo = true LIMIT 1")->fetchColumn();
if ($logoActivo) {
    $data['configuraciones']['logo_nav'] = $logoActivo;
}
```

Esto reemplaza la fuente del valor `logo_nav` sin tocar `js/main.js`, que sigue leyendo `config.logo_nav` igual que hoy.

### 5. `js/main.js`

Se quita el bloque:
```js
if (config?.color_nav_texto) {
    document.documentElement.style.setProperty('--nav-link-color', config.color_nav_texto);
}
```
de `renderNav()` — ya no hay valor `color_nav_texto` que leer.

## Fuera de alcance

- No se borran los objetos del bucket de Supabase Storage cuando se elimina una fila de `logos_nav` — solo se borra el registro de la base de datos (el archivo queda huérfano en el bucket).
- No se agrega un límite de cantidad de logos en la biblioteca.
- La variable CSS `--nav-link-color` en `css/style.css` no se toca (queda como fallback sin uso real, ya estaba efectivamente muerta desde que cada ítem del menú tiene color inline propio).

## Testing

Manual:

1. Recargar el panel admin → confirmar que "Apariencia del Nav" muestra el logo migrado como única fila, activo = Sí, y ya no muestra el campo de color.
2. Agregar un logo nuevo (subir otra imagen) → confirmar que aparece como fila nueva con activo = No, y que el sitio público sigue mostrando el logo anterior (no cambió el activo).
3. Click en "Activar" sobre el logo nuevo → confirmar que pasa a Sí, el anterior pasa a No, y el sitio público (recargando) ya muestra el logo nuevo.
4. Intentar eliminar el logo activo → confirmar que el backend rechaza la operación con el mensaje de error.
5. Activar el logo viejo de nuevo y eliminar el nuevo (ahora inactivo) → confirmar que se borra sin error.
6. Confirmar que ya no aparece ningún control de color en la sección Nav del panel, y que el menú público sigue viéndose igual que antes (colores por ítem, sin depender de `--nav-link-color`).
