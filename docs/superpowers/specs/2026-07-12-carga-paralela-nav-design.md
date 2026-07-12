# Carga en paralelo del logo y menú del nav

## Contexto

`index.html` carga `js/main.js`, que en `DOMContentLoaded` dispara un único `fetch('api/site')`. Ese endpoint (`api/index.php`) ejecuta 15 consultas SQL secuenciales contra Postgres (Supabase remoto) y arma un solo JSON que recién se envía cuando termina la última. El logo real (`config.logo_nav`) y el menú (`data.menu_nav`) — que dependen nada más de 2 de esas consultas — no se pintan hasta que resuelven las 15, aunque el resto de la página (hero, chefs, platillos, etc.) tampoco necesita esperar al navbar. El usuario percibe esto como "la página carga pero el logo y el menú aparecen después de un rato".

## Comportamiento

- Al entrar a la página, el navbar (logo + menú + botones de acción, ver spec [[2026-07-12-botones-nav-editable-design]]) se pinta apenas resuelve una consulta liviana e independiente, sin esperar al resto del contenido.
- El resto de las secciones (hero, identidad, about, chefs, platillos, itinerario, faq, footer, patrocinadores) se siguen cargando con el `fetch('api/site')` existente, sin cambios de comportamiento.
- Ambos fetches se disparan al mismo tiempo (en paralelo), no uno después del otro.

## Cambios

### 1. `api/index.php`

Se agrega una rama de ruteo nueva para `GET api/site?route=nav` (o `path` terminando en `/api/site/nav`, siguiendo el mismo patrón de detección que ya usa `$isSiteRoute`), que ejecuta solo:

```php
$data['menu_nav'] = $db->query('SELECT * FROM menu_navegacion WHERE activo = true ORDER BY orden')->fetchAll();
$data['botones_nav'] = $db->query('SELECT * FROM botones_nav WHERE activo = true ORDER BY orden')->fetchAll();
$data['configuraciones'] = []; // reservar_url/reservar_texto ya no se usan (ver spec de botones)
$logoActivo = $db->query('SELECT logo FROM logos_nav WHERE activo = true LIMIT 1')->fetchColumn();
if ($logoActivo) $data['configuraciones']['logo_nav'] = $logoActivo;
jsonResponse($data);
```

La ruta `api/site` (sin `route=nav`) sigue haciendo las 15 consultas actuales tal cual, sin quitar nada (aunque ahora sea redundante para `menu_nav`/`logo_nav`/`botones_nav`, se dejan para no romper nada que dependa del payload completo).

### 2. `js/main.js`

`loadSiteData()` se separa en dos funciones independientes, ambas llamadas desde `DOMContentLoaded` sin `await` entre sí (no una bloqueando a la otra):

```js
loadNavData();   // fetch('api/site?route=nav') -> renderNav(...)
loadSiteData();  // fetch('api/site') -> el resto de los render*, sin tocar renderNav
```

`renderNav()` deja de ser llamada desde `loadSiteData()` — solo la llama `loadNavData()`. El resto de `loadSiteData()` (renderHero, renderIdentity, etc.) no cambia.

Manejo de error: si `loadNavData()` falla, se aplica el mismo fallback que hoy (mostrar el logo con `classList.add('loaded')` aunque no haya `src` nuevo). Si `loadSiteData()` falla, no afecta al navbar.

## Fuera de alcance

- No se agrega caché (APCu, archivo, etc.) al backend — la mejora es solo paralelizar, no acelerar cada consulta individual.
- No se reordenan ni combinan las 15 consultas de la ruta `api/site` completa.

## Testing

Manual:
1. Con DevTools → Network abierto, recargar el sitio y confirmar que se disparan dos requests a `api/site` (uno con `?route=nav`) que arrancan casi al mismo tiempo (no uno esperando al otro).
2. Confirmar que el logo y el menú aparecen apenas resuelve la request liviana, sin esperar a que carguen las imágenes de chefs/platillos/hero.
3. Simular error en `api/site?route=nav` (ej. cortar la red brevemente) y confirmar que el logo igual queda visible (fallback) y el resto de la página sigue cargando normalmente.
