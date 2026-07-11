# Guardar/eliminar/activar sin recargar la página

## Contexto

En `api/admin/index.php`, las acciones de guardar (modal), eliminar y activar logo ya usan `fetch()` (AJAX) para enviar los cambios al servidor. El problema es que, tras la confirmación exitosa, el JS hace `location.reload()` — eso recarga el documento completo, perdiendo la posición de scroll y mostrando un parpadeo, aunque la operación en sí ya fue asíncrona.

## Objetivo

Después de guardar/eliminar/activar, refrescar solo la tabla de la sección afectada (y el contador de su pestaña), sin recargar el navegador.

## Enfoque

En vez de reescribir en JavaScript toda la lógica de renderizado que hoy vive en PHP (miniaturas de imagen, badges Sí/No, swatches de color, botón "Activar" específico de `nav`, truncado de texto largo, etc.), se reutiliza esa misma lógica server-side: se hace un segundo `fetch()` silencioso a la URL actual de la página (`window.location.href`, mismo `admin/index.php`, sin parámetros de acción — una request GET normal, la misma que ya se sirve al cargar), se parsea el HTML de la respuesta con `DOMParser`, y se extrae de ahí:

- El contenido de `#page-<section>` (la tabla o el estado vacío ya renderizados con datos frescos).
- El `<span class="count-badge">` correspondiente al grupo de esa sección, dentro del `<nav>` del sidebar.

Ambos fragmentos se inyectan (`innerHTML`) en los nodos equivalentes del DOM actual, sin tocar el resto de la página (scroll, pestaña activa, etc. quedan intactos porque no se reemplaza el nodo contenedor, solo su contenido).

No se agrega ningún endpoint nuevo: se reutiliza el HTML completo que la página ya sabe generar, evitando mantener dos implementaciones de renderizado (una en PHP y otra en JS) sincronizadas a mano.

## Cambios

En el JS embebido de `api/admin/index.php`:

1. Nueva función:

```js
async function refreshSection(section) {
    try {
        const res = await fetch(window.location.href, { credentials: 'same-origin' });
        const html = await res.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');

        const freshPage = doc.getElementById('page-' + section);
        const currentPage = document.getElementById('page-' + section);
        if (freshPage && currentPage) currentPage.innerHTML = freshPage.innerHTML;

        const freshBadge = doc.querySelector(`.nav-item[data-group="${currentGroup}"] .count-badge`);
        const currentBadge = document.querySelector(`.nav-item[data-group="${currentGroup}"] .count-badge`);
        if (freshBadge && currentBadge) currentBadge.textContent = freshBadge.textContent;
    } catch (e) {
        location.reload();
    }
}
```

Si el `fetch` o el parseo fallan por cualquier motivo, se recurre al `location.reload()` como respaldo — nunca se deja al admin con datos desactualizados en pantalla.

2. En el `submit` de `#modalForm`, `deleteItem()` y `activarLogo()`, se reemplaza:

```js
setTimeout(() => location.reload(), 500);
```

por:

```js
setTimeout(() => refreshSection(currentSection), 500);
```

(en `deleteItem`/`activarLogo`, que reciben `section`/usan la sección de la fila afectada — que siempre es `menu_nav` para `deleteItem` en general, o `nav` para `activarLogo` — se pasa esa misma sección en vez de `currentSection` global cuando corresponda, para no depender de qué pestaña esté abierta en el momento).

## Fuera de alcance

- No se toca la sección `configuraciones` (su guardado ya no recarga la página, solo cierra el modal — sin cambios).
- No se elimina el `<meta>`/estructura de la página ni se convierte el panel en SPA — sigue siendo una página PHP renderizada server-side, solo se evita la navegación completa del navegador tras una mutación.

## Testing

Manual, en al menos dos secciones distintas (una simple como Badges, una con `can_add: false` como Identidad, y Menú Nav que tiene color y activo):

1. Editar un registro → confirmar que la tabla se actualiza con el valor nuevo sin que la página parpadee/recargue (verificar que la posición de scroll no se pierde).
2. Agregar un registro nuevo → confirmar que aparece en la tabla y que el contador de la pestaña sube en 1.
3. Eliminar un registro → confirmar que desaparece de la tabla y el contador baja en 1.
4. En "Logos del Nav", activar un logo distinto → confirmar que la fila activa cambia sin recargar.
5. Simular una falla de red (desconectar) durante un guardado → confirmar que cae al `location.reload()` de respaldo en vez de quedar con datos viejos silenciosamente.
