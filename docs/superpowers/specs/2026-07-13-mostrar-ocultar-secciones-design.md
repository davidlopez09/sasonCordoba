# Mostrar/Ocultar secciones del sitio desde el panel

## Contexto

Hoy no hay forma de ocultar una sección completa del sitio público (Identidad, Sobre Nosotros, Exponentes, Platillos, Itinerario, Patrocinadores, FAQ) sin editar código — vaciar los datos de una sección no la oculta, solo la deja con el título y el contenedor vacíos. Se pide un interruptor Sí/No por sección, gestionable desde el panel, para poder activar/desactivar cualquiera de esas 7 secciones cuando se necesite. Nav, Hero y Footer quedan siempre visibles (no tienen este interruptor).

## Comportamiento

- Nueva sección en el panel, **"Secciones del Sitio"** (grupo General), con una fila por cada una de las 7 secciones: Identidad, Sobre Nosotros, Exponentes, Platillos, Itinerario, Patrocinadores, FAQ.
- Cada fila tiene un campo Sí/No ("Mostrar sección").
- Si está en "No", esa sección desaparece por completo del sitio público (el `<section>` entero, no solo su contenido).
- Volver a ponerla en "Sí" la muestra de nuevo tal cual estaba, sin perder ningún dato (las filas/contenido de esa sección no se tocan).

## Cambios

### 1. Base de datos

Se reutiliza `configuraciones_sitio` (mismo patrón que `color_nav_fondo`), agregando 7 claves nuevas:

```
mostrar_identidad   = '1'
mostrar_about       = '1'
mostrar_chefs       = '1'
mostrar_platillos   = '1'
mostrar_itinerario  = '1'
mostrar_sponsors    = '1'
mostrar_faq         = '1'
```

Todas arrancan en `'1'` (mostrada) para no cambiar el comportamiento actual del sitio.

### 2. `api/index.php`

Sin cambios — `configuraciones` ya se arma con un loop genérico `SELECT clave, valor FROM configuraciones_sitio`, así que las 7 claves nuevas ya viajan solas en `data.configuraciones`.

### 3. `api/admin/index.php`

- Se agregan las 7 filas a `configuraciones_sitio` si no existen (mismo patrón que `$navFondo`/`$footerFondo`).
- Sección nueva `secciones_visibilidad`: `rows` = las 7 filas, `fields` = `['clave','valor']`, `can_add` = false (mismo patrón que `nav_apariencia`/`footer_apariencia`, pero con 7 filas en vez de 1-2).
- `fieldConfig.secciones_visibilidad`: `clave` oculto, `valor` como `select` Sí/No (no `color_text`, porque acá el valor es booleano, no un color).
- Reutiliza la acción genérica `save_config` (ya existe) — no hace falta acción nueva.
- Se agrega `'secciones_visibilidad'` al grupo `general` (junto a `subtitulos`, `configuraciones`) y a la lista de exclusión del botón Eliminar.

### 4. `js/main.js`

Al principio de `loadSiteData()`, después de tener `data.configuraciones`, se aplica un helper:

```js
function toggleSection(id, visible) {
    const el = document.getElementById(id);
    if (el) el.style.display = visible === '0' ? 'none' : '';
}
```

Llamado para cada una de las 7 secciones con su `id` real de `index.html` (`identity`, `about`, `chefs`, `dishes`, `itinerary`, `sponsors`, `faq`), usando `data.configuraciones.mostrar_X`. Se ejecuta independientemente de si la sección tiene datos o no — si está en "No", ni siquiera se llama a su función de render (para no gastar el fetch/trabajo de armar el contenido de una sección que no se va a ver).

## Fuera de alcance

- Nav, Hero y Footer no tienen interruptor — siempre visibles.
- No se agrega reordenar secciones (solo mostrar/ocultar).
- No afecta el menú del nav — si ocultás "Platillos" pero el link `#dishes` sigue activo en Menú Nav, el link va a apuntar a una sección oculta (queda a criterio del usuario desactivar también el link del menú si corresponde).

## Testing

Manual:
1. Panel → Secciones del Sitio → poner "Platillos" en No → recargar el sitio → confirmar que la sección Platillos no aparece en absoluto (ni título, ni contenedor vacío).
2. Volver a poner "Sí" → recargar → confirmar que reaparece con los mismos platillos de antes.
3. Confirmar que Nav, Hero y Footer no tienen este control y siempre se muestran.
