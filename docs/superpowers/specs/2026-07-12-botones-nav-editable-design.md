# Botones del nav editables (texto, colores, agregar/eliminar)

## Contexto

Hoy el navbar público (`index.html`) tiene dos botones fijos en el HTML: "Reservar Ahora" (`.btn-primary`, degradado naranja) e "Ingresar" (`.btn-outline`, fondo transparente con borde), enlazando respectivamente a `#itinerary`/config y a `api/admin/login.php`. Solo el texto y el link de "Reservar Ahora" son editables hoy (vía `configuraciones_sitio.reservar_texto`/`reservar_url`, sección "Config" del panel). No hay forma de cambiar colores, ni de agregar o quitar botones.

Además, `.nav-container` usa `justify-content: space-between` con 5 hijos directos (logo, menú, botón Reservar, botón Ingresar, botón hamburguesa) — eso separa mucho los dos botones entre sí porque el espacio se reparte a lo ancho de toda la barra, no solo entre ellos.

## Comportamiento

- El panel admin tiene una sección nueva "Botones Nav" (grupo "Nav", junto a "Logos del Nav" y "Menú Nav") con el mismo patrón CRUD que el resto de las secciones: agregar, editar, eliminar, con orden.
- Cada botón tiene: texto, enlace (URL o ancla `#seccion`), color de fondo, color de texto, orden, activo (para ocultar sin borrar).
- El color de fondo acepta cualquier valor CSS válido como texto libre (hex `#ff6b00` o `transparent`), para poder reproducir el look "outline" actual del botón Ingresar sin necesitar un campo de estilo aparte.
- El sitio público muestra los botones activos, en su orden, con sus colores — pueden ser 0, 1, 2 o más.
- Los dos botones quedan agrupados en un solo contenedor flex con gap chico entre ellos, separado del resto de la barra (logo/menú/hamburguesa).

## Cambios

### 1. Base de datos

Nueva tabla, agregada a `api/database/schema/supabase_schema.sql` y `api/database/schema/nuevas_tablas.sql`:

```sql
CREATE TABLE IF NOT EXISTS public.botones_nav (
    id BIGSERIAL PRIMARY KEY,
    texto VARCHAR(100) NOT NULL,
    enlace VARCHAR(255) NOT NULL,
    color_fondo VARCHAR(20) NOT NULL DEFAULT '#ff6b00',
    color_texto VARCHAR(20) NOT NULL DEFAULT '#ffffff',
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

INSERT INTO public.botones_nav (texto, enlace, color_fondo, color_texto, orden, activo) VALUES
('Reservar Ahora', '#itinerary', '#ff6b00', '#ffffff', 0, true),
('Ingresar', 'api/admin/login.php', 'transparent', '#ffffff', 1, true)
ON CONFLICT DO NOTHING;
```

Se quitan de los `INSERT INTO configuraciones_sitio` de ambos esquemas las filas `reservar_url` y `reservar_texto` (quedan sin uso; se reemplazan por la tabla nueva). En producción, migración de datos vía script: crear las dos filas iniciales con los valores actuales de `configuraciones_sitio.reservar_texto`/`reservar_url` (en vez de los defaults de arriba) y luego `DELETE FROM configuraciones_sitio WHERE clave IN ('reservar_url', 'reservar_texto')`.

### 2. `api/admin/index.php` — backend

- Se agrega `'boton_nav' => ['table' => 'botones_nav', 'group' => null]` a `$orderTables` (reutiliza toda la lógica existente de `ordenShiftForInsert/Delete/Move`).
- Se agregan a `$actions`:
  - `add_boton_nav`: `INSERT INTO botones_nav (texto, enlace, color_fondo, color_texto, orden, activo) VALUES (?,?,?,?,?,?)`.
  - `edit_boton_nav`: `UPDATE botones_nav SET texto=?, enlace=?, color_fondo=?, color_texto=?, orden=?, activo=? WHERE id=?`.
  - `delete_boton_nav`: `DELETE FROM botones_nav WHERE id=?`.
- Se agrega la consulta `$botones_nav = $db->query('SELECT * FROM botones_nav ORDER BY orden')->fetchAll();`.
- Se agrega a `$sections`: `'botones_nav' => ['label' => 'Botones Nav', 'icon' => 'ph-cursor-click', 'rows' => $botones_nav, 'fields' => ['texto','enlace','color_fondo','color_texto','orden','activo'], 'can_add' => true]`.
- Se agrega `'botones_nav'` a `$groups['nav']['children']` (queda `['nav', 'menu_nav', 'botones_nav']`).
- Preview de tabla: la condición que ya dibuja el swatch de color (`in_array($f, ['color_texto', 'color'])`) se extiende a `'color_fondo'` también.

### 3. `api/admin/index.php` — frontend (JS embebido)

- `fieldConfig.botones_nav`:
  ```js
  botones_nav: [
      {name:'texto', label:'Texto', type:'text'},
      {name:'enlace', label:'Enlace (URL o #ancla)', type:'text'},
      {name:'color_fondo', label:'Color de fondo (hex o "transparent")', type:'text'},
      {name:'color_texto', label:'Color de texto', type:'color'},
      {name:'orden', label:'Orden', type:'number'},
      {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
  ],
  ```
- `pageTitles.botones_nav = 'Botones Nav'`, `canAdd.botones_nav = true`.
- `groups.nav.children` (mapa JS) pasa a `['nav', 'menu_nav', 'botones_nav']`.

### 4. `api/index.php` (API pública)

Se agrega, junto a la consulta de `menu_nav`:
```php
$data['botones_nav'] = $db->query('SELECT * FROM botones_nav WHERE activo = true ORDER BY orden')->fetchAll();
```
(y en la ruta liviana `route=nav` de la spec [[2026-07-12-carga-paralela-nav-design]], igual).

### 5. `index.html`

Se reemplazan los dos `<a>` fijos por un contenedor vacío:
```html
<div class="nav-actions" id="nav-actions"></div>
```
ubicado en el mismo lugar (entre `.nav-links` y `.mobile-menu-btn`).

### 6. `js/main.js`

En `renderNav(menu, config, botones)`:
- Se quita el bloque que setea `btn-reservar` (ya no existe ese `id` fijo).
- Se agrega el render de `#nav-actions`:
  ```js
  const actions = document.getElementById('nav-actions');
  if (actions) {
      actions.innerHTML = (botones || []).map(b =>
          `<a href="${b.enlace}" class="nav-btn" style="background:${b.color_fondo}; color:${b.color_texto}">${b.texto}</a>`
      ).join('');
  }
  ```
- La llamada a `renderNav(...)` pasa también `data.botones_nav` (o `data.botones_nav` de la ruta liviana, según la spec de carga paralela).

### 7. `css/style.css`

```css
.nav-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.nav-btn {
    display: inline-flex;
    align-items: center;
    padding: 10px 22px;
    border-radius: 50px;
    font-weight: 500;
    font-size: 0.95rem;
    border: 1px solid transparent;
    transition: var(--transition);
}

.nav-btn:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
}
```

`.nav-container` no cambia (sigue `space-between`), pero ahora reparte entre 4 hijos (logo, menú, `.nav-actions`, hamburguesa) en vez de 5, y el gap chico queda solo entre los botones.

En el media query `@media (max-width: 768px)`, el selector `.navbar .btn` que hoy oculta los botones fijos pasa a `.navbar .btn, .nav-actions` para seguir ocultándolos en mobile (se muestran dentro del menú hamburguesa como hoy no lo hacían — sin cambios de comportamiento mobile).

## Fuera de alcance

- No se agrega selector de estilo "sólido/outline" — el color de fondo como texto libre ya cubre el caso outline (`transparent`).
- No se agrega opción de abrir en nueva pestaña (`target="_blank"`).
- No se migra el botón "Ver Menú"/"Descubre Más" del hero — quedan hardcodeados como están hoy.
- No se borra `configuraciones_sitio.footer_copyright` ni otras claves — solo se quitan `reservar_url`/`reservar_texto`.

## Testing

Manual:
1. Recargar el panel admin → confirmar que aparece "Botones Nav" bajo el grupo "Nav", con las 2 filas migradas (Reservar Ahora, Ingresar) y sus colores correctos en el preview.
2. Editar el color de fondo de "Ingresar" a un hex sólido → confirmar que en el sitio público el botón deja de verse transparente y toma ese color.
3. Agregar un tercer botón (ej. "WhatsApp") → confirmar que aparece en el navbar del sitio junto a los otros dos, con gap chico entre los tres.
4. Eliminar "Ingresar" → confirmar que en el sitio solo queda "Reservar Ahora" (o el que quede), sin espacio vacío raro.
5. Eliminar los dos → confirmar que `.nav-actions` queda vacío sin romper el layout del navbar (logo y menú se acomodan igual con `space-between`).
6. Ver en mobile (< 768px) que los botones no aparecen sueltos en la barra (mismo comportamiento que hoy).
