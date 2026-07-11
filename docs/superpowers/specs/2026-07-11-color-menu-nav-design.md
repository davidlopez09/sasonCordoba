# Color por ítem en el Menú de Navegación

## Contexto

El panel de administración (`api/admin/index.php`) ya permite editar la sección "Menú Navegación" (tabla `menu_navegacion`: `etiqueta`, `enlace`, `orden`, `activo`). Actualmente solo existe un color de texto **global** para todo el menú (`color_nav_texto`, guardado en `configuraciones_sitio` y aplicado vía la variable CSS `--nav-link-color` en `css/style.css`). No hay forma de que cada ítem (Inicio, Identidad, El Evento, Invitados, Platillos, Itinerario) tenga su propio color.

## Objetivo

Permitir definir un color de texto individual por cada ítem del menú desde el panel de administración, sin afectar el color de hover (que sigue usando `var(--primary)`).

## Comportamiento

- Cada fila de `menu_navegacion` tiene un campo `color` (hex, ej. `#ffffff`).
- Si el campo está vacío/null, el frontend usa `#ffffff` como valor por defecto.
- El color de hover no cambia: sigue siendo el color primario del sitio (`var(--primary)`), igual para todos los ítems.

## Cambios

### 1. Base de datos (Postgres/Supabase)

Agregar columna a `menu_navegacion`:

```sql
ALTER TABLE public.menu_navegacion
  ADD COLUMN IF NOT EXISTS color VARCHAR(7) NOT NULL DEFAULT '#ffffff';
```

- Se aplica de forma idempotente en `api/database/schema/supabase_schema.sql` y `api/database/schema/nuevas_tablas.sql` (definición de `CREATE TABLE` + `INSERT` de datos de ejemplo, backfilleados con `#ffffff`).
- Como la tabla ya existe en el entorno productivo (Supabase), el `ALTER TABLE` de arriba debe ejecutarse una vez manualmente contra esa base (SQL Editor de Supabase) — los archivos de esquema en el repo no se re-ejecutan automáticamente.

### 2. Panel admin — `api/admin/index.php`

- **Acciones CRUD**: `add_menu_nav` y `edit_menu_nav` agregan `color` a la lista de columnas/parámetros (`INSERT`/`UPDATE`).
- **`fieldConfig.menu_nav`** (JS embebido): agrega `{name:'color', label:'Color del texto', type:'color'}`, siguiendo el mismo patrón que el campo `color_texto` de la sección "Apariencia del Nav" (input HTML `type="color"`).
- **Tabla de listado**: la celda de la columna `color` se renderiza igual que `color_texto` hoy (swatch cuadrado + valor hex), reutilizando la condición existente en el bloque `<?php foreach ($sec['fields'] as $f): ?>` (se agrega `'color'` al `in_array` que ya cubre `color_texto`).

### 3. API pública — `api/index.php`

Sin cambios: el `SELECT * FROM menu_navegacion` ya devuelve la columna nueva dentro de `data['menu_nav']`.

### 4. Frontend — `js/main.js` (`renderNav`)

`renderNav` construye cada link con el color inline del ítem:

```js
`<li><a href="${item.enlace}" style="color:${item.color || '#ffffff'}">${item.etiqueta}</a></li>`
```

El hover (`.nav-links a:hover`) no se toca en `css/style.css`; sigue aplicando `var(--primary)` vía CSS, que tiene prioridad sobre el `color` inline al activarse `:hover` (la regla de hover en la hoja de estilos gana porque el estilo inline no cubre pseudo-clases).

## Fuera de alcance

- Color de fondo / estilo tipo "chip" por ítem.
- Color de hover configurable por ítem.
- Migración automática del `ALTER TABLE` contra Supabase (se documenta el statement, se ejecuta manualmente).

## Testing

Manual, en `api/admin/index.php` y en el sitio público:

1. Editar 2-3 ítems del menú con colores distintos (ej. Inicio en `#ffcc00`, Identidad en `#00c2ff`).
2. Guardar y recargar el sitio público (`index.html`) — verificar que cada link tenga su color correspondiente.
3. Dejar un ítem sin color (o en blanco) — verificar que se vea blanco (`#ffffff`).
4. Pasar el mouse sobre los links — verificar que el hover siga tomando el color primario del sitio, no el color individual.
5. Confirmar que el resto de columnas (`etiqueta`, `enlace`, `orden`, `activo`) siguen funcionando sin regresiones en el CRUD.
