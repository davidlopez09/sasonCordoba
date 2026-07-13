# Secciones dinámicas (constructor de secciones por bloques)

## Contexto

Hoy cada sección del sitio (Hero, Identidad, Platillos, etc.) es código fijo: una tabla propia en la BD, una consulta propia en `api/index.php`, un bloque de HTML propio en `index.html`, una función de render propia en `js/main.js`, y una sección propia en el panel. Agregar una sección nueva significa escribir todo eso a mano.

Se pide poder **crear secciones nuevas desde el panel**, sin que cada una necesite código nuevo, con la flexibilidad de armarlas combinando bloques (texto, imagen, tarjetas, botón) — incluyendo la posibilidad de poner dos bloques lado a lado — y elegir después de cuál sección existente aparecen.

No se justifica un editor visual de posicionamiento libre (arrastrar y soltar, grillas arbitrarias) — es un proyecto mucho más grande. La solución es un sistema de **bloques apilados**, con un modo especial para poner dos bloques uno al lado del otro.

## Comportamiento

- En el panel, sección nueva **"Secciones Dinámicas"**: lista de las secciones creadas, con nombre, después de cuál sección fija aparece, y activo/inactivo (reutiliza el mismo mecanismo Sí/No de la Parte 1 — [[2026-07-13-mostrar-ocultar-secciones-design]]).
- Al crear una: nombre (interno), elegís de un dropdown después de cuál sección fija va (Hero, Identidad, Sobre Nosotros, Exponentes, Platillos, Itinerario, FAQ, Patrocinadores), y si arranca activa.
- Cada sección dinámica tiene un botón **"Bloques"** que lleva a una vista con la lista de bloques de esa sección: agregar bloque (elegís el tipo: Texto, Imagen, Tarjetas o Botón), editar, eliminar, y reordenar (subir/bajar, reutilizando el mismo mecanismo de `orden` que ya existe en el resto del panel).
- Para poner dos bloques lado a lado: al agregar/editar un bloque, un campo **"Posición"** con tres opciones — Completo (ancho completo, default), Izquierda, Derecha. Si dos bloques consecutivos (por orden) quedan marcados Izquierda y Derecha, se renderizan juntos en una fila de 2 columnas. Si un bloque queda marcado Izquierda/Derecha sin su pareja inmediata, se muestra completo igual (no rompe el layout).
- En el sitio: la sección dinámica se inserta en el DOM justo después de la sección fija elegida, en el orden en que se crearon (si hay más de una apuntando al mismo lugar).

## Modelo de datos

Dos tablas nuevas, genéricas — no una tabla por tipo de sección:

```sql
CREATE TABLE IF NOT EXISTS public.secciones_dinamicas (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    insertar_despues VARCHAR(50) NOT NULL, -- hero|identity|about|chefs|dishes|itinerary|faq|sponsors
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.bloques_dinamicos (
    id BIGSERIAL PRIMARY KEY,
    seccion_id BIGINT NOT NULL REFERENCES secciones_dinamicas(id) ON DELETE CASCADE,
    tipo VARCHAR(30) NOT NULL, -- texto|imagen|tarjetas|boton
    posicion VARCHAR(20) NOT NULL DEFAULT 'completo', -- completo|izquierda|derecha
    contenido TEXT NOT NULL, -- JSON, forma según `tipo` (ver abajo)
    orden INTEGER DEFAULT 0,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);
```

**Forma del JSON en `contenido` según `tipo`:**
- `texto`: `{titulo, texto, color}`
- `imagen`: `{url}` (subida a un bucket nuevo `secciones_dinamicas`, mismo mecanismo que Exponentes/Platillos — reemplaza el archivo viejo del bucket al editar)
- `boton`: `{texto, enlace, color_fondo, color_texto, color_borde}` (mismos campos que botones_nav/botones_hero)
- `tarjetas`: `{items: [{imagen, titulo, descripcion}, ...]}` — **para esta v1, el formulario de este tipo de bloque es un textarea de JSON crudo** (el admin edita el array a mano). Un editor con "+ agregar tarjeta" fila por fila queda para una iteración posterior si hace falta — no es parte de este alcance para no inflar el trabajo de esta primera versión.

Guardar `contenido` como JSON (en vez de una columna por campo) es lo que permite que cada tipo de bloque tenga campos distintos sin necesitar una tabla nueva por tipo, y que agregar un tipo de bloque nuevo en el futuro (Mapa, Formulario, etc.) sea solo: una entrada nueva en el "menú" de tipos + su forma de JSON + su función de render — no una migración de base de datos.

## Cambios

### 1. `api/index.php`

Se agrega, en la ruta `/api/site` completa:

```php
$data['secciones_dinamicas'] = $db->query("
    SELECT s.*, json_agg(b.* ORDER BY b.orden) AS bloques
    FROM secciones_dinamicas s
    LEFT JOIN bloques_dinamicos b ON b.seccion_id = s.id
    WHERE s.activo = true
    ORDER BY s.orden
    GROUP BY s.id
")->fetchAll();
```

(Postgres permite `json_agg` para traer sección + bloques en una sola consulta; se parsea el JSON de `bloques` en PHP con `json_decode` antes de mandarlo en la respuesta.)

### 2. `api/admin/index.php`

- **Bucket nuevo** `secciones_dinamicas` en Supabase Storage (público), mismo patrón que `image_nosotros`/`exponentes_fotos`.
- Sección **`secciones_dinamicas`** (grupo General o uno nuevo "Secciones Dinámicas"): CRUD estándar (add/edit/delete, reutiliza `$orderTables` para el `orden`), campos: nombre, insertar_despues (select con las 8 opciones), activo.
- Por cada sección dinámica existente, se genera server-side (foreach en PHP) un `page-content` adicional `bloques_dinamica_{id}` con la tabla de sus bloques — mismo patrón que las demás secciones del panel — y un botón **"Bloques"** en cada fila de `secciones_dinamicas` que llama a `showSection('bloques_dinamica_' + id)` (reutiliza el mecanismo de mostrar/ocultar `page-content` que ya existe, sin necesidad de recargar la página).
- Acciones nuevas: `add_seccion_dinamica`/`edit_seccion_dinamica`/`delete_seccion_dinamica` (secciones_dinamicas) y `add_bloque_dinamico`/`edit_bloque_dinamico`/`delete_bloque_dinamico` (bloques_dinamicos, requiere `seccion_id` en el form).
- El formulario de "agregar/editar bloque" cambia sus campos según el `tipo` elegido (mismo patrón que ya usamos para mostrar campos distintos según selección — se arma en JS con un mapa `tipo → campos`, análogo a `fieldConfig`). Antes de guardar, el JS arma el objeto `contenido` a partir de esos campos y lo manda como un string JSON en un campo oculto; el backend lo guarda tal cual en la columna `contenido` (no valida su forma más allá de que sea JSON válido).
- El campo `imagen` del bloque tipo Imagen usa `handleImageUpload()`/`deleteFromSupabaseStorage()` igual que Exponentes/Platillos.

### 3. `js/main.js`

- Nuevas funciones de render por tipo de bloque: `renderBloqueTexto(contenido)`, `renderBloqueImagen(contenido)`, `renderBloqueBoton(contenido)`, `renderBloqueTarjetas(contenido)` — cada una devuelve un string de HTML.
- `renderSeccionDinamica(seccion)`: agrupa los bloques por posición (detecta pares izquierda/derecha consecutivos, arma el HTML de cada bloque con la función correspondiente a su tipo, envuelve los pares en `<div class="dyn-row-2col">`), arma un `<section class="section dyn-section" id="dyn-{id}">` con todo adentro.
- Después del render de las secciones fijas, por cada sección dinámica activa: `document.getElementById(seccion.insertar_despues)?.insertAdjacentElement('afterend', elementoGenerado)` — si hay más de una apuntando al mismo lugar, se insertan en orden, cada una "empujando" a la siguiente (insertar en orden ascendente de `orden` logra el resultado correcto sin lógica extra).

### 4. `css/style.css`

Estilos genéricos para `.dyn-section`, `.dyn-row-2col` (flex, 2 columnas, responsive a 1 columna en mobile como el resto del sitio), y los bloques individuales (`.dyn-bloque-texto`, `.dyn-bloque-imagen`, `.dyn-bloque-boton`, `.dyn-bloque-tarjetas`) — reutilizando variables ya existentes (`--text-main`, `--bg-card`, etc.) para que combinen visualmente con el resto del sitio sin definir un montón de estilos nuevos.

## Fuera de alcance

- Bloques de Mapa y Formulario de contacto — quedan para cuando se necesiten, mismo sistema, se agregan como un tipo más.
- Editor visual de tarjetas fila por fila (v1 usa un textarea de JSON crudo para ese tipo de bloque).
- Reordenar secciones dinámicas entre sí más allá del campo `orden` manual (no hay drag-and-drop).
- Posicionamiento libre más allá de "completo / izquierda / derecha" (no hay grillas de 3+ columnas ni tamaños custom).
- Migrar las secciones fijas actuales (Hero, Platillos, etc.) a este sistema — quedan como están, este sistema es solo para secciones nuevas.

## Testing

Manual:
1. Crear una sección dinámica "Contacto" después de "Itinerario" → confirmar que aparece justo después de esa sección en el sitio, con el título/orden esperado.
2. Agregarle un bloque Texto y un bloque Imagen (completo) → confirmar que se ven apilados en ese orden.
3. Agregar dos bloques marcados Izquierda/Derecha → confirmar que se ven lado a lado (y en una columna en mobile).
4. Desactivar la sección (Sí/No) → confirmar que desaparece del sitio sin perder los bloques.
5. Eliminar un bloque → confirmar que el resto se reacomoda bien.
6. Subir una imagen nueva a un bloque tipo Imagen que ya tenía una → confirmar que la vieja se borra del bucket (mismo comportamiento que Exponentes/Platillos).
