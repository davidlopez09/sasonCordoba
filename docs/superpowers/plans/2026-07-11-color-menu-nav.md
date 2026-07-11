# Color por ítem en el Menú de Navegación — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Permitir definir un color de texto individual por cada ítem del menú de navegación (Inicio, Identidad, El Evento, etc.) desde el panel de administración, con `#ffffff` como valor por defecto.

**Architecture:** Se agrega una columna `color` a la tabla `menu_navegacion` (Postgres/Supabase). El CRUD genérico del panel admin (`api/admin/index.php`) ya soporta agregar campos declarando una fila en el array `$actions` (PHP) y una entrada en `fieldConfig` (JS embebido) — no hace falta lógica nueva, solo declarar el campo. El frontend (`js/main.js`) aplica el color vía `style` inline en cada `<a>`, dejando el hover intacto (CSS ya define `.nav-links a:hover { color: var(--primary) }`, que tiene prioridad sobre el inline al activarse el pseudo-selector).

**Tech Stack:** PHP 8 + PDO (pgsql), Postgres/Supabase, HTML/CSS, JS vanilla (sin build step, sin framework de testing).

## Global Constraints

- Color por defecto cuando el campo está vacío: `#ffffff` (spec: `docs/superpowers/specs/2026-07-11-color-menu-nav-design.md`).
- El hover de los links NO cambia — sigue usando `var(--primary)` global, no se hace configurable por ítem.
- No se toca la sección "Apariencia del Nav" (`color_nav_texto` global existente) — queda como está, sin relación con este cambio.
- No hay framework de tests automatizados en este proyecto (sin `phpunit.xml`, `package.json`, ni carpeta `tests/`) — la verificación de cada tarea es manual, ejecutando el flujo real (panel admin y sitio público en el navegador).

---

## File Structure

- Modify: `api/database/schema/supabase_schema.sql` — agrega columna `color` a `CREATE TABLE menu_navegacion` + `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` + backfill en el `INSERT` de ejemplo.
- Modify: `api/database/schema/nuevas_tablas.sql` — mismo cambio que arriba (este archivo duplica el esquema de `menu_navegacion` para setups nuevos).
- Modify: `api/admin/index.php` — agrega `color` a las acciones `add_menu_nav`/`edit_menu_nav`, a `fieldConfig.menu_nav` (JS embebido), y al render de la celda de tabla (swatch).
- Modify: `js/main.js` — `renderNav()` aplica `item.color` como color inline de cada link.

No se crean archivos nuevos: el proyecto no tiene infraestructura de tests ni módulos separados para esto: es una tabla más del mismo CRUD genérico ya existente.

---

### Task 1: Migración de base de datos (columna `color`)

**Files:**
- Modify: `api/database/schema/supabase_schema.sql:194-202` (CREATE TABLE) y `:251-258` (INSERT de ejemplo)
- Modify: `api/database/schema/nuevas_tablas.sql:25-33` (CREATE TABLE) y `:85-92` (INSERT de ejemplo)

**Interfaces:**
- Produces: columna `menu_navegacion.color VARCHAR(7) NOT NULL DEFAULT '#ffffff'`, consumida por Task 2 (backend admin) y por la API pública (`api/index.php`, sin cambios porque usa `SELECT *`).

- [ ] **Step 1: Agregar la columna en `supabase_schema.sql`**

En `api/database/schema/supabase_schema.sql`, reemplazar:

```sql
-- 13. TABLA: Menú de Navegación
CREATE TABLE IF NOT EXISTS public.menu_navegacion (
    id BIGSERIAL PRIMARY KEY,
    etiqueta VARCHAR(255) NOT NULL,
    enlace VARCHAR(255) NOT NULL,
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);
```

por:

```sql
-- 13. TABLA: Menú de Navegación
CREATE TABLE IF NOT EXISTS public.menu_navegacion (
    id BIGSERIAL PRIMARY KEY,
    etiqueta VARCHAR(255) NOT NULL,
    enlace VARCHAR(255) NOT NULL,
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    color VARCHAR(7) NOT NULL DEFAULT '#ffffff',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde la tabla se creó sin la columna `color`
ALTER TABLE public.menu_navegacion
    ADD COLUMN IF NOT EXISTS color VARCHAR(7) NOT NULL DEFAULT '#ffffff';
```

- [ ] **Step 2: Backfillear el INSERT de ejemplo en `supabase_schema.sql`**

Reemplazar:

```sql
INSERT INTO public.menu_navegacion (etiqueta, enlace, orden, activo) VALUES
('Inicio', '#home', 0, true),
('Identidad', '#identity', 1, true),
('El Evento', '#about', 2, true),
('Invitados', '#chefs', 3, true),
('Platillos', '#dishes', 4, true),
('Itinerario', '#itinerary', 5, true)
ON CONFLICT DO NOTHING;
```

por:

```sql
INSERT INTO public.menu_navegacion (etiqueta, enlace, orden, activo, color) VALUES
('Inicio', '#home', 0, true, '#ffffff'),
('Identidad', '#identity', 1, true, '#ffffff'),
('El Evento', '#about', 2, true, '#ffffff'),
('Invitados', '#chefs', 3, true, '#ffffff'),
('Platillos', '#dishes', 4, true, '#ffffff'),
('Itinerario', '#itinerary', 5, true, '#ffffff')
ON CONFLICT DO NOTHING;
```

- [ ] **Step 3: Repetir el mismo cambio en `nuevas_tablas.sql`**

En `api/database/schema/nuevas_tablas.sql`, reemplazar:

```sql
-- 3. TABLA: Menú de Navegación
CREATE TABLE IF NOT EXISTS public.menu_navegacion (
    id BIGSERIAL PRIMARY KEY,
    etiqueta VARCHAR(255) NOT NULL,
    enlace VARCHAR(255) NOT NULL,
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);
```

por:

```sql
-- 3. TABLA: Menú de Navegación
CREATE TABLE IF NOT EXISTS public.menu_navegacion (
    id BIGSERIAL PRIMARY KEY,
    etiqueta VARCHAR(255) NOT NULL,
    enlace VARCHAR(255) NOT NULL,
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    color VARCHAR(7) NOT NULL DEFAULT '#ffffff',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde la tabla se creó sin la columna `color`
ALTER TABLE public.menu_navegacion
    ADD COLUMN IF NOT EXISTS color VARCHAR(7) NOT NULL DEFAULT '#ffffff';
```

Y reemplazar:

```sql
INSERT INTO public.menu_navegacion (etiqueta, enlace, orden, activo) VALUES
('Inicio', '#home', 0, true),
('Identidad', '#identity', 1, true),
('El Evento', '#about', 2, true),
('Invitados', '#chefs', 3, true),
('Platillos', '#dishes', 4, true),
('Itinerario', '#itinerary', 5, true)
ON CONFLICT DO NOTHING;
```

por:

```sql
INSERT INTO public.menu_navegacion (etiqueta, enlace, orden, activo, color) VALUES
('Inicio', '#home', 0, true, '#ffffff'),
('Identidad', '#identity', 1, true, '#ffffff'),
('El Evento', '#about', 2, true, '#ffffff'),
('Invitados', '#chefs', 3, true, '#ffffff'),
('Platillos', '#dishes', 4, true, '#ffffff'),
('Itinerario', '#itinerary', 5, true, '#ffffff')
ON CONFLICT DO NOTHING;
```

- [ ] **Step 4: Ejecutar el ALTER TABLE contra la base real de Supabase**

Esta tabla ya existe en producción (el `CREATE TABLE IF NOT EXISTS` de los archivos de esquema no se re-ejecuta solo). Copiar y correr manualmente en el SQL Editor de Supabase:

```sql
ALTER TABLE public.menu_navegacion
    ADD COLUMN IF NOT EXISTS color VARCHAR(7) NOT NULL DEFAULT '#ffffff';
```

Verificar: correr `SELECT id, etiqueta, color FROM public.menu_navegacion ORDER BY orden;` en el mismo SQL Editor y confirmar que las 6 filas existentes muestran `color = '#ffffff'`.

- [ ] **Step 5: Commit**

```bash
git add api/database/schema/supabase_schema.sql api/database/schema/nuevas_tablas.sql
git commit -m "feat: agrega columna color a menu_navegacion"
```

---

### Task 2: CRUD del panel admin (`api/admin/index.php`)

**Files:**
- Modify: `api/admin/index.php:88-90` (acciones `add_menu_nav`/`edit_menu_nav`)
- Modify: `api/admin/index.php:524-541` (render de celdas de tabla, agregar swatch para `color`)
- Modify: `api/admin/index.php:641-646` (`fieldConfig.menu_nav`, JS embebido)

**Interfaces:**
- Consumes: columna `menu_navegacion.color` (Task 1).
- Produces: el formulario modal de "Menú Navegación" acepta y guarda `color`; el listado lo muestra. Este `color` es consumido por Task 3 vía `data.menu_nav[].color` (respuesta de `api/index.php`, que ya hace `SELECT *` sin cambios).

- [ ] **Step 1: Agregar `color` a las acciones SQL**

En `api/admin/index.php`, reemplazar (líneas 88-90):

```php
            // Menu Nav
            'add_menu_nav' => ['INSERT INTO menu_navegacion (etiqueta, enlace, orden, activo) VALUES (?,?,?,?)', ['etiqueta','enlace','orden','activo']],
            'edit_menu_nav' => ['UPDATE menu_navegacion SET etiqueta=?, enlace=?, orden=?, activo=? WHERE id=?', ['etiqueta','enlace','orden','activo','id']],
            'delete_menu_nav' => ['DELETE FROM menu_navegacion WHERE id=?', ['id']],
```

por:

```php
            // Menu Nav
            'add_menu_nav' => ['INSERT INTO menu_navegacion (etiqueta, enlace, orden, activo, color) VALUES (?,?,?,?,?)', ['etiqueta','enlace','orden','activo','color']],
            'edit_menu_nav' => ['UPDATE menu_navegacion SET etiqueta=?, enlace=?, orden=?, activo=?, color=? WHERE id=?', ['etiqueta','enlace','orden','activo','color','id']],
            'delete_menu_nav' => ['DELETE FROM menu_navegacion WHERE id=?', ['id']],
```

- [ ] **Step 2: Agregar el swatch de color al listado de la tabla**

En `api/admin/index.php`, reemplazar (línea 528):

```php
                                <?php elseif ($f === 'color_texto'): ?>
```

por:

```php
                                <?php elseif (in_array($f, ['color_texto', 'color'])): ?>
```

(la rama ya existente pinta el swatch cuadrado + hex reutilizando `$row[$f]`, así que no hace falta duplicar el bloque).

- [ ] **Step 3: Agregar el campo `color` al formulario modal**

En `api/admin/index.php`, reemplazar (líneas 641-646):

```javascript
    menu_nav: [
        {name:'etiqueta', label:'Etiqueta', type:'text'},
        {name:'enlace', label:'Enlace', type:'text'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
    ],
```

por:

```javascript
    menu_nav: [
        {name:'etiqueta', label:'Etiqueta', type:'text'},
        {name:'enlace', label:'Enlace', type:'text'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
        {name:'color', label:'Color del texto', type:'color'},
    ],
```

- [ ] **Step 4: Verificación manual en el panel**

Levantar el sitio en XAMPP (Apache local ya sirve `C:\xampp\htdocs\sasonCordoba`), entrar a `http://localhost/sasonCordoba/api/admin/index.php`, ir a la sección "Menú Nav":

1. Confirmar que la tabla ahora muestra una columna extra con swatch + hex `#ffffff` en las 6 filas existentes.
2. Click en "Editar" sobre "Inicio", cambiar el color a `#ffcc00`, guardar. Confirmar que el modal cierra sin error y la tabla refleja el nuevo swatch amarillo.
3. Repetir con "Identidad" usando `#00c2ff`.

Expected: sin errores en la consola del navegador ni el `jsonResponse(['error' => ...])` del backend; los cambios persisten al recargar la página del panel.

- [ ] **Step 5: Commit**

```bash
git add api/admin/index.php
git commit -m "feat: agrega campo color al CRUD de menu de navegacion"
```

---

### Task 3: Aplicar el color en el sitio público (`js/main.js`)

**Files:**
- Modify: `js/main.js:24-30` (función `renderNav`)

**Interfaces:**
- Consumes: `data.menu_nav[].color` (string hex o `null`/vacío), disponible en la respuesta de `api/index.php` desde Task 1 (columna ya incluida por `SELECT *`).

- [ ] **Step 1: Aplicar `item.color` como estilo inline en cada link**

En `js/main.js`, reemplazar (líneas 24-30):

```javascript
function renderNav(menu, config) {
    const ul = document.getElementById('nav-links');
    if (ul) {
        ul.innerHTML = (menu || []).map(item =>
            `<li><a href="${item.enlace}">${item.etiqueta}</a></li>`
        ).join('');
    }
```

por:

```javascript
function renderNav(menu, config) {
    const ul = document.getElementById('nav-links');
    if (ul) {
        ul.innerHTML = (menu || []).map(item =>
            `<li><a href="${item.enlace}" style="color:${item.color || '#ffffff'}">${item.etiqueta}</a></li>`
        ).join('');
    }
```

- [ ] **Step 2: Verificación manual en el sitio público**

Abrir `http://localhost/sasonCordoba/index.html` (con los colores seteados en Task 2 Step 4):

1. Confirmar que el link "Inicio" se ve amarillo (`#ffcc00`) y "Identidad" celeste (`#00c2ff`).
2. Confirmar que el resto de los links (sin color seteado) se ven blancos.
3. Pasar el mouse sobre cualquier link y confirmar que el color cambia al color primario del sitio (hover), no al color individual — coherente con `.nav-links a:hover { color: var(--primary) }` en `css/style.css:204-206`, que no se modificó.
4. Revisar en las herramientas de desarrollador (Elements) que cada `<a>` tiene el atributo `style="color:#..."` correspondiente.

Expected: sin errores en consola; comportamiento visual descrito en la spec (`docs/superpowers/specs/2026-07-11-color-menu-nav-design.md`, sección "Testing").

- [ ] **Step 3: Commit**

```bash
git add js/main.js
git commit -m "feat: aplica color individual por item en el menu de navegacion publico"
```

---

## Self-Review Notes

- **Spec coverage:** columna `color` con default `#ffffff` (Task 1), campo editable en panel admin (Task 2), listado con swatch (Task 2 Step 2), aplicación en frontend con fallback blanco (Task 3), hover sin cambios (verificado en Task 3 Step 2) — todos los puntos de la spec tienen tarea.
- **Placeholder scan:** sin TBD/TODO; todos los pasos de código muestran el diff completo antes/después.
- **Type consistency:** el nombre de campo `color` es el mismo en SQL (Task 1), en las acciones PHP y `fieldConfig` (Task 2), y en `item.color` (Task 3) — no hay variantes de nombre.
