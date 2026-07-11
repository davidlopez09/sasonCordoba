# Auto-numeración del campo "Orden"

## Contexto

Once secciones del panel admin (`api/admin/index.php`) tienen un campo `orden` numérico que hoy se escribe a mano en el formulario, sin ninguna validación de colisión ni renumeración al borrar. Esto ya causó dos veces datos rotos en "Menú Navegación" (valores duplicados como `1,1,2,3,4,5` o huecos como `1,2,4,5,6,7`), corregidos manualmente vía script directo a la base de datos.

## Alcance

Las 11 secciones con `orden`: `slide` (banner_principal), `estadistica` (estadisticas_principales), `caracteristica` (caracteristicas_about), `exponente` (exponentes), `platillo` (platillos_destacados), `itinerario` (itinerario_items), `patrocinador` (patrocinadores), `badges` (badges_identidad), `menu_nav` (menu_navegacion), `faq` (preguntas_frecuentes), `footer` (pie_pagina — este además agrupa por `columna`, cada columna se numera independiente).

## Comportamiento

- **Agregar**: el campo "Orden" del formulario viene precargado con `count_actual + 1` (se agrega al final por defecto). Sigue siendo editable — si se escribe un número que ya existe, se corren +1 todos los que estén en esa posición o después (dentro del mismo grupo, para Footer).
- **Editar** (cambio de `orden`, y opcionalmente de `columna` en Footer): se recalculan las posiciones intermedias entre la posición vieja y la nueva para que la secuencia quede siempre `1..N` sin huecos ni duplicados. Si en Footer también cambia la `columna`, se cierra el hueco en la columna vieja y se abre lugar en la columna nueva.
- **Eliminar**: los registros con `orden` mayor al eliminado se corren -1 (dentro del mismo grupo).
- La numeración de cada sección (y de cada columna en Footer) siempre es contigua, empezando en 1.

## Implementación

En `api/admin/index.php`, antes del despacho genérico de `$actions` (que hoy solo hace `INSERT`/`UPDATE`/`DELETE` con los valores tal cual vienen de `$_POST`), se agrega:

1. Una tabla de configuración por sección:

```php
$orderTables = [
    'slide' => ['table' => 'banner_principal', 'group' => null],
    'estadistica' => ['table' => 'estadisticas_principales', 'group' => null],
    'caracteristica' => ['table' => 'caracteristicas_about', 'group' => null],
    'exponente' => ['table' => 'exponentes', 'group' => null],
    'platillo' => ['table' => 'platillos_destacados', 'group' => null],
    'itinerario' => ['table' => 'itinerario_items', 'group' => null],
    'patrocinador' => ['table' => 'patrocinadores', 'group' => null],
    'badges' => ['table' => 'badges_identidad', 'group' => null],
    'menu_nav' => ['table' => 'menu_navegacion', 'group' => null],
    'faq' => ['table' => 'preguntas_frecuentes', 'group' => null],
    'footer' => ['table' => 'pie_pagina', 'group' => 'columna'],
];
```

2. Funciones auxiliares (agregadas antes del bloque `try`, junto a `jsonResponse`):

```php
function ordenCount(PDO $db, string $table, ?string $groupCol, $groupVal): int {
    if ($groupCol) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE $groupCol = ?");
        $stmt->execute([$groupVal]);
    } else {
        $stmt = $db->query("SELECT COUNT(*) FROM $table");
    }
    return (int) $stmt->fetchColumn();
}

function ordenClamp(int $requested, int $min, int $max): int {
    return max($min, min($max, $requested));
}

function ordenShiftForInsert(PDO $db, string $table, ?string $groupCol, $groupVal, int $orden): void {
    if ($groupCol) {
        $stmt = $db->prepare("UPDATE $table SET orden = orden + 1 WHERE orden >= ? AND $groupCol = ?");
        $stmt->execute([$orden, $groupVal]);
    } else {
        $stmt = $db->prepare("UPDATE $table SET orden = orden + 1 WHERE orden >= ?");
        $stmt->execute([$orden]);
    }
}

function ordenShiftForDelete(PDO $db, string $table, ?string $groupCol, $groupVal, int $orden): void {
    if ($groupCol) {
        $stmt = $db->prepare("UPDATE $table SET orden = orden - 1 WHERE orden > ? AND $groupCol = ?");
        $stmt->execute([$orden, $groupVal]);
    } else {
        $stmt = $db->prepare("UPDATE $table SET orden = orden - 1 WHERE orden > ?");
        $stmt->execute([$orden]);
    }
}

function ordenShiftForMove(PDO $db, string $table, ?string $groupCol, $oldGroupVal, $newGroupVal, int $oldOrden, int $newOrden): void {
    if ($groupCol && $oldGroupVal !== $newGroupVal) {
        $stmt = $db->prepare("UPDATE $table SET orden = orden - 1 WHERE orden > ? AND $groupCol = ?");
        $stmt->execute([$oldOrden, $oldGroupVal]);
        $stmt = $db->prepare("UPDATE $table SET orden = orden + 1 WHERE orden >= ? AND $groupCol = ?");
        $stmt->execute([$newOrden, $newGroupVal]);
        return;
    }
    if ($newOrden === $oldOrden) return;
    if ($newOrden < $oldOrden) {
        $sql = "UPDATE $table SET orden = orden + 1 WHERE orden >= ? AND orden < ?" . ($groupCol ? " AND $groupCol = ?" : "");
        $params = $groupCol ? [$newOrden, $oldOrden, $newGroupVal] : [$newOrden, $oldOrden];
    } else {
        $sql = "UPDATE $table SET orden = orden - 1 WHERE orden > ? AND orden <= ?" . ($groupCol ? " AND $groupCol = ?" : "");
        $params = $groupCol ? [$oldOrden, $newOrden, $newGroupVal] : [$oldOrden, $newOrden];
    }
    $db->prepare($sql)->execute($params);
}
```

3. Antes de construir `$actions` y ejecutar el dispatch genérico, se detecta si `$action` corresponde a `add_<key>`, `edit_<key>` o `delete_<key>` de alguna entrada de `$orderTables`, y:
   - **add**: calcula `$groupVal` (si aplica), cuenta filas, clampa `$_POST['orden']` a `[1, count+1]` (default `count+1` si no vino), corre `ordenShiftForInsert`, y sobrescribe `$_POST['orden']` con el valor final — así el `INSERT` genérico que ya existe en `$actions` toma ese valor sin más cambios.
   - **edit**: lee `orden` (y `columna` si aplica) actual del registro por `id`, calcula el máximo válido para el grupo destino (`count` si el grupo no cambia — ya incluye a la fila misma —, o `count+1` si cambia de grupo), clampa el valor pedido, corre `ordenShiftForMove`, sobrescribe `$_POST['orden']`.
   - **delete**: lee `orden` (y `columna`) del registro antes de que el `DELETE` genérico corra, y guarda esos datos en una variable local `$pendingOrdenDelete`. Justo después de que el `$stmt->execute($vals)` del dispatch genérico corre (antes del `jsonResponse(['ok'=>true])` final), si `$pendingOrdenDelete` está seteado, se llama `ordenShiftForDelete`.

4. En el JS embebido, `openModal(section, mode, data)`: cuando `mode === 'add'` y el campo es `orden`, el valor precargado pasa de `''` a `document.querySelectorAll('#page-' + section + ' tbody tr').length + 1`.

## Fuera de alcance

- No se agregan botones de reordenar (subir/bajar) — el reordenamiento sigue siendo editando el número de "Orden" a mano, ahora con corrimiento automático.
- El valor precargado en "Agregar" para Footer usa el conteo total de filas de esa sección (todas las columnas juntas), no el conteo exacto de la columna que se vaya a elegir después en el formulario — es un valor de partida razonable, sigue siendo editable.

## Testing

Manual, sobre "Menú Navegación" (ya con datos 1-7 corregidos) y al menos una sección con grupo (Footer):

1. Agregar un ítem nuevo sin tocar "Orden" → confirmar que entra al final (orden 8).
2. Agregar otro ítem forzando `orden = 3` → confirmar que el que estaba en 3 y los siguientes se corrieron +1, sin duplicados.
3. Editar un ítem del final para moverlo a la posición 1 → confirmar que los demás se corrieron +1 y la secuencia sigue 1..N sin huecos.
4. Eliminar un ítem del medio → confirmar que los siguientes se corrieron -1.
5. En Footer, mover un ítem de la columna 1 a la columna 2 con un `orden` específico → confirmar que ambas columnas quedan con numeración contigua.
