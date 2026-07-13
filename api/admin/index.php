<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../db.php';
require __DIR__ . '/../supabase_storage.php';
$db = getDB();

function jsonResponse($data) { echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }

function resolveImgSrc(?string $path): string {
    if (!$path) return '';
    return preg_match('#^https?://#', $path) ? $path : "../../$path";
}

function handleImageUpload(string $fieldName, string $bucket, string $prefix): ?string {
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(['error' => 'Error al subir el archivo']);
    }
    $file = $_FILES[$fieldName];
    if ($file['size'] > 2 * 1024 * 1024) {
        jsonResponse(['error' => 'La imagen no debe superar 2MB']);
    }
    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'svg' => 'image/svg+xml'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$ext])) {
        jsonResponse(['error' => 'Formato no permitido. Usa JPG, PNG, WEBP o SVG']);
    }
    $mime = mime_content_type($file['tmp_name']);
    if ($ext !== 'svg' && $mime !== $allowed[$ext]) {
        jsonResponse(['error' => 'El archivo no es una imagen válida']);
    }
    $filename = $prefix . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    try {
        return uploadToSupabaseStorage($bucket, $filename, $file['tmp_name'], $allowed[$ext]);
    } catch (Exception $e) {
        jsonResponse(['error' => 'No se pudo subir el archivo: ' . $e->getMessage()]);
    }
}

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

$orderTables = [
    'slides' => ['table' => 'banner_principal', 'group' => null],
    'estadisticas' => ['table' => 'estadisticas_principales', 'group' => null],
    'caracteristicas' => ['table' => 'caracteristicas_about', 'group' => null],
    'exponentes' => ['table' => 'exponentes', 'group' => null],
    'platillos' => ['table' => 'platillos_destacados', 'group' => null],
    'itinerario' => ['table' => 'itinerario_items', 'group' => null],
    'patrocinadores' => ['table' => 'patrocinadores', 'group' => null],
    'badges' => ['table' => 'badges_identidad', 'group' => null],
    'menu_nav' => ['table' => 'menu_navegacion', 'group' => null],
    'botones_nav' => ['table' => 'botones_nav', 'group' => null],
    'botones_hero' => ['table' => 'botones_hero', 'group' => null],
    'faq' => ['table' => 'preguntas_frecuentes', 'group' => null],
    'footer' => ['table' => 'pie_pagina', 'group' => 'columna'],
    'secciones_dinamicas' => ['table' => 'secciones_dinamicas', 'group' => null],
    'bloques_dinamicos' => ['table' => 'bloques_dinamicos', 'group' => 'seccion_id'],
];

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    header('Content-Type: application/json');
    $pendingOrdenDelete = null;
    try {
        foreach ($orderTables as $key => $cfg) {
            $table = $cfg['table'];
            $groupCol = $cfg['group'];

            if ($action === "add_$key") {
                $groupVal = $groupCol ? ($_POST[$groupCol] ?? null) : null;
                $count = ordenCount($db, $table, $groupCol, $groupVal);
                $requested = (int) ($_POST['orden'] ?? ($count + 1));
                $orden = ordenClamp($requested, 1, $count + 1);
                ordenShiftForInsert($db, $table, $groupCol, $groupVal, $orden);
                $_POST['orden'] = $orden;
            } elseif ($action === "edit_$key") {
                $current = $db->prepare('SELECT orden' . ($groupCol ? ", $groupCol" : '') . " FROM $table WHERE id = ?");
                $current->execute([$_POST['id'] ?? null]);
                $row = $current->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $oldOrden = (int) $row['orden'];
                    $oldGroupVal = $groupCol ? $row[$groupCol] : null;
                    $newGroupVal = $groupCol ? ($_POST[$groupCol] ?? $oldGroupVal) : null;
                    $sameGroup = !$groupCol || $oldGroupVal === $newGroupVal;
                    $countDestGroup = ordenCount($db, $table, $groupCol, $newGroupVal);
                    $maxOrden = $sameGroup ? $countDestGroup : $countDestGroup + 1;
                    $requested = (int) ($_POST['orden'] ?? $oldOrden);
                    $orden = ordenClamp($requested, 1, max(1, $maxOrden));
                    ordenShiftForMove($db, $table, $groupCol, $oldGroupVal, $newGroupVal, $oldOrden, $orden);
                    $_POST['orden'] = $orden;
                }
            } elseif ($action === "delete_$key") {
                $current = $db->prepare('SELECT orden' . ($groupCol ? ", $groupCol" : '') . " FROM $table WHERE id = ?");
                $current->execute([$_POST['id'] ?? null]);
                $row = $current->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $pendingOrdenDelete = [
                        'table' => $table,
                        'group' => $groupCol,
                        'groupVal' => $groupCol ? $row[$groupCol] : null,
                        'orden' => (int) $row['orden'],
                    ];
                }
            }
        }

        if ($action === 'add_exponentes' || $action === 'edit_exponentes') {
            $fotoUrl = handleImageUpload('foto', 'exponentes_fotos', 'exponente');
            $instagram = $_POST['instagram_url'] !== '' ? $_POST['instagram_url'] : null;
            $twitter = $_POST['twitter_url'] !== '' ? $_POST['twitter_url'] : null;

            if ($action === 'edit_exponentes') {
                $current = $db->prepare('SELECT foto FROM exponentes WHERE id = ?');
                $current->execute([$_POST['id']]);
                $oldFoto = $current->fetchColumn();

                if ($fotoUrl && $oldFoto) {
                    deleteFromSupabaseStorage('exponentes_fotos', $oldFoto);
                }

                $stmt = $db->prepare('UPDATE exponentes SET nombre=?, especialidad=?, foto=?, instagram_url=?, twitter_url=?, orden=?, color=? WHERE id=?');
                $stmt->execute([$_POST['nombre'], $_POST['especialidad'], $fotoUrl ?: $oldFoto, $instagram, $twitter, $_POST['orden'], $_POST['color'] ?? '#1a1a1a', $_POST['id']]);
            } else {
                if (!$fotoUrl) {
                    jsonResponse(['error' => 'Debés subir una foto']);
                }
                $stmt = $db->prepare('INSERT INTO exponentes (nombre, especialidad, foto, instagram_url, twitter_url, orden, color) VALUES (?,?,?,?,?,?,?)');
                $stmt->execute([$_POST['nombre'], $_POST['especialidad'], $fotoUrl, $instagram, $twitter, $_POST['orden'], $_POST['color'] ?? '#1a1a1a']);
            }
            jsonResponse(['ok' => true]);
        }

        if ($action === 'add_bloques_dinamicos' || $action === 'edit_bloques_dinamicos') {
            $tipo = $_POST['tipo'] ?? '';
            $decoded = json_decode($_POST['contenido'] ?? '{}', true) ?: [];

            if ($tipo === 'imagen') {
                $imgUrl = handleImageUpload('imagen_file', 'secciones_dinamicas', 'bloque');
                $oldUrl = null;
                if ($action === 'edit_bloques_dinamicos') {
                    $current = $db->prepare('SELECT contenido FROM bloques_dinamicos WHERE id = ?');
                    $current->execute([$_POST['id']]);
                    $oldContenido = json_decode($current->fetchColumn() ?: '{}', true);
                    $oldUrl = $oldContenido['url'] ?? null;
                }
                if ($imgUrl) {
                    if ($oldUrl) {
                        deleteFromSupabaseStorage('secciones_dinamicas', $oldUrl);
                    }
                    $decoded['url'] = $imgUrl;
                } elseif ($oldUrl) {
                    $decoded['url'] = $oldUrl;
                }
            }

            $contenidoJson = json_encode($decoded, JSON_UNESCAPED_UNICODE);
            $posicion = $_POST['posicion'] ?? 'completo';

            if ($action === 'add_bloques_dinamicos') {
                $stmt = $db->prepare('INSERT INTO bloques_dinamicos (seccion_id, tipo, posicion, contenido, orden) VALUES (?,?,?,?,?)');
                $stmt->execute([$_POST['seccion_id'], $tipo, $posicion, $contenidoJson, $_POST['orden']]);
            } else {
                $stmt = $db->prepare('UPDATE bloques_dinamicos SET seccion_id=?, tipo=?, posicion=?, contenido=?, orden=? WHERE id=?');
                $stmt->execute([$_POST['seccion_id'], $tipo, $posicion, $contenidoJson, $_POST['orden'], $_POST['id']]);
            }
            jsonResponse(['ok' => true]);
        }

        if ($action === 'add_platillos' || $action === 'edit_platillos') {
            $imagenUrl = handleImageUpload('imagen', 'platillos_fotos', 'platillo');

            if ($action === 'edit_platillos') {
                $current = $db->prepare('SELECT imagen FROM platillos_destacados WHERE id = ?');
                $current->execute([$_POST['id']]);
                $oldImagen = $current->fetchColumn();

                if ($imagenUrl && $oldImagen) {
                    deleteFromSupabaseStorage('platillos_fotos', $oldImagen);
                }

                $stmt = $db->prepare('UPDATE platillos_destacados SET nombre=?, descripcion=?, imagen=?, orden=?, color=? WHERE id=?');
                $stmt->execute([$_POST['nombre'], $_POST['descripcion'], $imagenUrl ?: $oldImagen, $_POST['orden'], $_POST['color'] ?? '#ffffff', $_POST['id']]);
            } else {
                if (!$imagenUrl) {
                    jsonResponse(['error' => 'Debés subir una imagen']);
                }
                $stmt = $db->prepare('INSERT INTO platillos_destacados (nombre, descripcion, imagen, orden, color) VALUES (?,?,?,?,?)');
                $stmt->execute([$_POST['nombre'], $_POST['descripcion'], $imagenUrl, $_POST['orden'], $_POST['color'] ?? '#ffffff']);
            }
            jsonResponse(['ok' => true]);
        }

        if ($action === 'add_patrocinadores' || $action === 'edit_patrocinadores') {
            $logoUrl = handleImageUpload('logo', 'logo_patrocinador', 'patrocinador');

            if ($action === 'edit_patrocinadores') {
                $current = $db->prepare('SELECT logo FROM patrocinadores WHERE id = ?');
                $current->execute([$_POST['id']]);
                $oldLogo = $current->fetchColumn();

                if ($logoUrl && $oldLogo) {
                    deleteFromSupabaseStorage('logo_patrocinador', $oldLogo);
                }

                $stmt = $db->prepare('UPDATE patrocinadores SET nombre=?, logo=?, url=?, orden=? WHERE id=?');
                $stmt->execute([$_POST['nombre'], $logoUrl ?: $oldLogo, $_POST['url'] ?: null, $_POST['orden'], $_POST['id']]);
            } else {
                if (!$logoUrl) {
                    jsonResponse(['error' => 'Debés subir un logo']);
                }
                $stmt = $db->prepare('INSERT INTO patrocinadores (nombre, logo, url, orden) VALUES (?,?,?,?)');
                $stmt->execute([$_POST['nombre'], $logoUrl, $_POST['url'] ?: null, $_POST['orden']]);
            }
            jsonResponse(['ok' => true]);
        }

        if ($action === 'save_about') {
            $imagenUrl = handleImageUpload('imagen', 'image_nosotros', 'about');
            if ($imagenUrl) {
                $stmt = $db->prepare('UPDATE secciones_about SET imagen=?, titulo=?, descripcion=?, color=? WHERE id=?');
                $stmt->execute([$imagenUrl, $_POST['titulo'] ?? '', $_POST['descripcion'] ?? '', $_POST['color'] ?? '#1a1a1a', $_POST['id']]);
            } else {
                $stmt = $db->prepare('UPDATE secciones_about SET titulo=?, descripcion=?, color=? WHERE id=?');
                $stmt->execute([$_POST['titulo'] ?? '', $_POST['descripcion'] ?? '', $_POST['color'] ?? '#1a1a1a', $_POST['id']]);
            }
            jsonResponse(['ok' => true]);
        }

        if ($action === 'add_nav' || $action === 'edit_nav') {
            $logoUrl = handleImageUpload('logo', 'logo_nav', 'nav_logo');

            if ($action === 'add_nav') {
                if (!$logoUrl) {
                    jsonResponse(['error' => 'Debés subir una imagen']);
                }
                $stmt = $db->prepare('INSERT INTO logos_nav (logo, activo) VALUES (?, false)');
                $stmt->execute([$logoUrl]);
            } else {
                if ($logoUrl) {
                    $stmt = $db->prepare('UPDATE logos_nav SET logo = ? WHERE id = ?');
                    $stmt->execute([$logoUrl, $_POST['id']]);
                }
            }
            jsonResponse(['ok' => true]);
        }

        if ($action === 'delete_nav') {
            $activo = $db->prepare('SELECT activo FROM logos_nav WHERE id = ?');
            $activo->execute([$_POST['id']]);
            if ($activo->fetchColumn()) {
                jsonResponse(['error' => 'No podés eliminar el logo activo. Activá otro primero.']);
            }
            $stmt = $db->prepare('DELETE FROM logos_nav WHERE id = ?');
            $stmt->execute([$_POST['id']]);
            jsonResponse(['ok' => true]);
        }

        if ($action === 'activar_nav') {
            $db->exec('UPDATE logos_nav SET activo = false');
            $stmt = $db->prepare('UPDATE logos_nav SET activo = true WHERE id = ?');
            $stmt->execute([$_POST['id']]);
            jsonResponse(['ok' => true]);
        }

        $actions = [
            'add_slides' => ['INSERT INTO banner_principal (imagen, activo, orden) VALUES (?,?,?)', ['imagen','activo','orden']],
            'edit_slides' => ['UPDATE banner_principal SET imagen=?, activo=?, orden=? WHERE id=?', ['imagen','activo','orden','id']],
            'delete_slides' => ['DELETE FROM banner_principal WHERE id=?', ['id']],
            'add_estadisticas' => ['INSERT INTO estadisticas_principales (numero, etiqueta, icono, orden, color) VALUES (?,?,?,?,?)', ['numero','etiqueta','icono','orden','color']],
            'edit_estadisticas' => ['UPDATE estadisticas_principales SET numero=?, etiqueta=?, icono=?, orden=?, color=? WHERE id=?', ['numero','etiqueta','icono','orden','color','id']],
            'delete_estadisticas' => ['DELETE FROM estadisticas_principales WHERE id=?', ['id']],
            'save_hero_texto' => ['UPDATE hero_texto SET texto_badge=?, titulo=?, subtitulo=?, color=? WHERE id=?', ['texto_badge','titulo','subtitulo','color','id']],
            'add_caracteristicas' => ['INSERT INTO caracteristicas_about (icono, titulo, descripcion, orden, color) VALUES (?,?,?,?,?)', ['icono','titulo','descripcion','orden','color']],
            'edit_caracteristicas' => ['UPDATE caracteristicas_about SET icono=?, titulo=?, descripcion=?, orden=?, color=? WHERE id=?', ['icono','titulo','descripcion','orden','color','id']],
            'delete_caracteristicas' => ['DELETE FROM caracteristicas_about WHERE id=?', ['id']],
            'delete_exponentes' => ['DELETE FROM exponentes WHERE id=?', ['id']],
            'delete_platillos' => ['DELETE FROM platillos_destacados WHERE id=?', ['id']],
            'add_itinerario' => ['INSERT INTO itinerario_items (hora, dia, titulo, nombre_chef, descripcion, orden, color, color_fondo, color_borde) VALUES (?,?,?,?,?,?,?,?,?)', ['hora','dia','titulo','nombre_chef','descripcion','orden','color','color_fondo','color_borde']],
            'edit_itinerario' => ['UPDATE itinerario_items SET hora=?, dia=?, titulo=?, nombre_chef=?, descripcion=?, orden=?, color=?, color_fondo=?, color_borde=? WHERE id=?', ['hora','dia','titulo','nombre_chef','descripcion','orden','color','color_fondo','color_borde','id']],
            'delete_itinerario' => ['DELETE FROM itinerario_items WHERE id=?', ['id']],
            'delete_patrocinadores' => ['DELETE FROM patrocinadores WHERE id=?', ['id']],
            // Identidad
            'save_identidad' => ['UPDATE seccion_identidad SET titulo=?, descripcion=?, activo=?, color=? WHERE id=?', ['titulo','descripcion','activo','color','id']],
            // Badges
            'add_badges' => ['INSERT INTO badges_identidad (texto, orden, color, color_fondo) VALUES (?,?,?,?)', ['texto','orden','color','color_fondo']],
            'edit_badges' => ['UPDATE badges_identidad SET texto=?, orden=?, color=?, color_fondo=? WHERE id=?', ['texto','orden','color','color_fondo','id']],
            'delete_badges' => ['DELETE FROM badges_identidad WHERE id=?', ['id']],
            // Menu Nav
            'add_menu_nav' => ['INSERT INTO menu_navegacion (etiqueta, enlace, orden, activo, color) VALUES (?,?,?,?,?)', ['etiqueta','enlace','orden','activo','color']],
            'edit_menu_nav' => ['UPDATE menu_navegacion SET etiqueta=?, enlace=?, orden=?, activo=?, color=? WHERE id=?', ['etiqueta','enlace','orden','activo','color','id']],
            'delete_menu_nav' => ['DELETE FROM menu_navegacion WHERE id=?', ['id']],
            // Botones Nav
            'add_botones_nav' => ['INSERT INTO botones_nav (texto, enlace, color_fondo, color_texto, color_borde, orden, activo) VALUES (?,?,?,?,?,?,?)', ['texto','enlace','color_fondo','color_texto','color_borde','orden','activo']],
            'edit_botones_nav' => ['UPDATE botones_nav SET texto=?, enlace=?, color_fondo=?, color_texto=?, color_borde=?, orden=?, activo=? WHERE id=?', ['texto','enlace','color_fondo','color_texto','color_borde','orden','activo','id']],
            'delete_botones_nav' => ['DELETE FROM botones_nav WHERE id=?', ['id']],
            // Botones Hero
            'add_botones_hero' => ['INSERT INTO botones_hero (texto, enlace, color_fondo, color_texto, color_borde, orden, activo) VALUES (?,?,?,?,?,?,?)', ['texto','enlace','color_fondo','color_texto','color_borde','orden','activo']],
            'edit_botones_hero' => ['UPDATE botones_hero SET texto=?, enlace=?, color_fondo=?, color_texto=?, color_borde=?, orden=?, activo=? WHERE id=?', ['texto','enlace','color_fondo','color_texto','color_borde','orden','activo','id']],
            'delete_botones_hero' => ['DELETE FROM botones_hero WHERE id=?', ['id']],
            // FAQ
            'add_faq' => ['INSERT INTO preguntas_frecuentes (pregunta, respuesta, orden, activo, color) VALUES (?,?,?,?,?)', ['pregunta','respuesta','orden','activo','color']],
            'edit_faq' => ['UPDATE preguntas_frecuentes SET pregunta=?, respuesta=?, orden=?, activo=?, color=? WHERE id=?', ['pregunta','respuesta','orden','activo','color','id']],
            'delete_faq' => ['DELETE FROM preguntas_frecuentes WHERE id=?', ['id']],
            // Footer
            'add_footer' => ['INSERT INTO pie_pagina (tipo, titulo, contenido, url, icono, columna, orden, color) VALUES (?,?,?,?,?,?,?,?)', ['tipo','titulo','contenido','url','icono','columna','orden','color']],
            'edit_footer' => ['UPDATE pie_pagina SET tipo=?, titulo=?, contenido=?, url=?, icono=?, columna=?, orden=?, color=? WHERE id=?', ['tipo','titulo','contenido','url','icono','columna','orden','color','id']],
            'delete_footer' => ['DELETE FROM pie_pagina WHERE id=?', ['id']],
            // Subtítulos
            'save_subtitulo' => ['UPDATE secciones_subtitulos SET titulo=?, subtitulo=?, color=? WHERE id=?', ['titulo','subtitulo','color','id']],
            // Configuraciones
            'save_config' => ['UPDATE configuraciones_sitio SET valor=? WHERE clave=?', ['valor','clave']],
            // Secciones Dinámicas
            'add_secciones_dinamicas' => ['INSERT INTO secciones_dinamicas (nombre, insertar_despues, orden, activo) VALUES (?,?,?,?)', ['nombre','insertar_despues','orden','activo']],
            'edit_secciones_dinamicas' => ['UPDATE secciones_dinamicas SET nombre=?, insertar_despues=?, orden=?, activo=? WHERE id=?', ['nombre','insertar_despues','orden','activo','id']],
            'delete_secciones_dinamicas' => ['DELETE FROM secciones_dinamicas WHERE id=?', ['id']],
            'delete_bloques_dinamicos' => ['DELETE FROM bloques_dinamicos WHERE id=?', ['id']],
        ];

        if (isset($actions[$action])) {
            [$sql, $params] = $actions[$action];
            $vals = array_map(fn($p) => $_POST[$p] ?? null, $params);
            $stmt = $db->prepare($sql);
            $stmt->execute($vals);
            if ($pendingOrdenDelete) {
                ordenShiftForDelete($db, $pendingOrdenDelete['table'], $pendingOrdenDelete['group'], $pendingOrdenDelete['groupVal'], $pendingOrdenDelete['orden']);
            }
            jsonResponse(['ok' => true]);
        }
        jsonResponse(['error' => 'Acción no válida']);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()]);
    }
}

$slides = $db->query('SELECT * FROM banner_principal ORDER BY orden')->fetchAll();
$estadisticas = $db->query('SELECT * FROM estadisticas_principales ORDER BY orden')->fetchAll();
$about = $db->query('SELECT * FROM secciones_about LIMIT 1')->fetch();
$caracteristicas = $db->query('SELECT * FROM caracteristicas_about ORDER BY orden')->fetchAll();
$exponentes = $db->query('SELECT * FROM exponentes ORDER BY orden')->fetchAll();
$platillos = $db->query('SELECT * FROM platillos_destacados ORDER BY orden')->fetchAll();
$itinerario = $db->query('SELECT * FROM itinerario_items ORDER BY orden')->fetchAll();
$patrocinadores = $db->query('SELECT * FROM patrocinadores ORDER BY orden')->fetchAll();

if (!$about) {
    $db->query("INSERT INTO secciones_about (titulo, descripcion) VALUES ('Una Experiencia Inolvidable', 'Descripción del evento.')");
    $about = $db->query('SELECT * FROM secciones_about LIMIT 1')->fetch();
}

$hero_texto = $db->query('SELECT * FROM hero_texto LIMIT 1')->fetch();
if (!$hero_texto) {
    $db->query("INSERT INTO hero_texto (texto_badge, titulo, subtitulo) VALUES ('Edición 2026', 'El Sabor que Enciende a Montería', 'Descubre el evento culinario más prestigioso de la región.')");
    $hero_texto = $db->query('SELECT * FROM hero_texto LIMIT 1')->fetch();
}

// Nuevas secciones
$identidad = $db->query('SELECT * FROM seccion_identidad LIMIT 1')->fetch();
if (!$identidad) {
    $db->query("INSERT INTO seccion_identidad (titulo, descripcion) VALUES ('Identidad', 'Descripción')");
    $identidad = $db->query('SELECT * FROM seccion_identidad LIMIT 1')->fetch();
}
$badges = $db->query('SELECT * FROM badges_identidad ORDER BY orden')->fetchAll();
$logos_nav = $db->query('SELECT * FROM logos_nav ORDER BY id')->fetchAll();
$menu_nav = $db->query('SELECT * FROM menu_navegacion ORDER BY orden')->fetchAll();
$botones_nav = $db->query('SELECT * FROM botones_nav ORDER BY orden')->fetchAll();
$botones_hero = $db->query('SELECT * FROM botones_hero ORDER BY orden')->fetchAll();
$faq_items = $db->query('SELECT * FROM preguntas_frecuentes ORDER BY orden')->fetchAll();
$footer_items = $db->query('SELECT * FROM pie_pagina ORDER BY columna, orden')->fetchAll();
$subtitulos = $db->query('SELECT * FROM secciones_subtitulos ORDER BY seccion')->fetchAll();
$configs = $db->query('SELECT id, clave, valor FROM configuraciones_sitio ORDER BY clave')->fetchAll();

$secciones_dinamicas = $db->query('SELECT * FROM secciones_dinamicas ORDER BY orden')->fetchAll();
foreach ($secciones_dinamicas as &$sd) {
    $stmtB = $db->prepare('SELECT * FROM bloques_dinamicos WHERE seccion_id = ? ORDER BY orden');
    $stmtB->execute([$sd['id']]);
    $sd['bloques'] = $stmtB->fetchAll();
}
unset($sd);
$configMap = [];
foreach ($configs as $c) { $configMap[$c['clave']] = $c['valor']; }

$navFondo = null;
foreach ($configs as $c) { if ($c['clave'] === 'color_nav_fondo') { $navFondo = $c; break; } }
if (!$navFondo) {
    $db->prepare("INSERT INTO configuraciones_sitio (clave, valor) VALUES ('color_nav_fondo', '#000000')")->execute();
    $navFondo = $db->query("SELECT id, clave, valor FROM configuraciones_sitio WHERE clave = 'color_nav_fondo'")->fetch();
}

$footerFondo = null;
$footerTexto = null;
foreach ($configs as $c) {
    if ($c['clave'] === 'color_footer_fondo') { $footerFondo = $c; }
    if ($c['clave'] === 'color_footer_texto') { $footerTexto = $c; }
}
if (!$footerFondo) {
    $db->prepare("INSERT INTO configuraciones_sitio (clave, valor) VALUES ('color_footer_fondo', '#020202')")->execute();
    $footerFondo = $db->query("SELECT id, clave, valor FROM configuraciones_sitio WHERE clave = 'color_footer_fondo'")->fetch();
}
if (!$footerTexto) {
    $db->prepare("INSERT INTO configuraciones_sitio (clave, valor) VALUES ('color_footer_texto', '')")->execute();
    $footerTexto = $db->query("SELECT id, clave, valor FROM configuraciones_sitio WHERE clave = 'color_footer_texto'")->fetch();
}

$seccionesVisibilidadClaves = ['mostrar_identidad', 'mostrar_about', 'mostrar_chefs', 'mostrar_platillos', 'mostrar_itinerario', 'mostrar_sponsors', 'mostrar_faq'];
$seccionesVisibilidad = [];
foreach ($seccionesVisibilidadClaves as $clave) {
    $found = null;
    foreach ($configs as $c) { if ($c['clave'] === $clave) { $found = $c; break; } }
    if (!$found) {
        $db->prepare("INSERT INTO configuraciones_sitio (clave, valor) VALUES (?, '1')")->execute([$clave]);
        $stmt = $db->prepare("SELECT id, clave, valor FROM configuraciones_sitio WHERE clave = ?");
        $stmt->execute([$clave]);
        $found = $stmt->fetch();
    }
    $seccionesVisibilidad[] = $found;
}

$sections = [
    'nav' => ['label' => 'Logos del Nav', 'icon' => 'ph-image', 'rows' => $logos_nav, 'fields' => ['logo', 'activo'], 'can_add' => true],
    'nav_apariencia' => ['label' => 'Apariencia del Nav', 'icon' => 'ph-palette', 'rows' => [$navFondo], 'fields' => ['valor'], 'can_add' => false],
    'slides' => ['label' => 'Hero Slides', 'icon' => 'ph-images', 'rows' => $slides, 'fields' => ['imagen','activo','orden'], 'can_add' => true],
    'hero_texto' => ['label' => 'Texto del Hero', 'icon' => 'ph-text-aa', 'rows' => [$hero_texto], 'fields' => ['texto_badge','titulo','subtitulo','color'], 'can_add' => false],
    'botones_hero' => ['label' => 'Botones Hero', 'icon' => 'ph-cursor-click', 'rows' => $botones_hero, 'fields' => ['texto','enlace','color_fondo','color_texto','color_borde','orden','activo'], 'can_add' => true],
    'estadisticas' => ['label' => 'Estadísticas', 'icon' => 'ph-chart-bar', 'rows' => $estadisticas, 'fields' => ['numero','etiqueta','icono','orden','color'], 'can_add' => true],
    'about' => ['label' => 'Sección About', 'icon' => 'ph-info', 'rows' => [$about], 'fields' => ['imagen','titulo','descripcion','color'], 'can_add' => false],
    'caracteristicas' => ['label' => 'Características', 'icon' => 'ph-list-checks', 'rows' => $caracteristicas, 'fields' => ['icono','titulo','descripcion','orden','color'], 'can_add' => true],
    'exponentes' => ['label' => 'Exponentes', 'icon' => 'ph-users-three', 'rows' => $exponentes, 'fields' => ['nombre','especialidad','foto','instagram_url','twitter_url','orden','color'], 'can_add' => true],
    'platillos' => ['label' => 'Platillos', 'icon' => 'ph-fork-knife', 'rows' => $platillos, 'fields' => ['nombre','descripcion','imagen','orden','color'], 'can_add' => true],
    'itinerario' => ['label' => 'Itinerario', 'icon' => 'ph-calendar', 'rows' => $itinerario, 'fields' => ['hora','dia','titulo','nombre_chef','descripcion','orden','color','color_fondo','color_borde'], 'can_add' => true],
    'patrocinadores' => ['label' => 'Patrocinadores', 'icon' => 'ph-handshake', 'rows' => $patrocinadores, 'fields' => ['nombre','logo','url','orden'], 'can_add' => true],
    'identidad' => ['label' => 'Identidad', 'icon' => 'ph-seal-check', 'rows' => [$identidad], 'fields' => ['titulo','descripcion','activo','color'], 'can_add' => false],
    'badges' => ['label' => 'Badges Identidad', 'icon' => 'ph-tags', 'rows' => $badges, 'fields' => ['texto','orden','color','color_fondo'], 'can_add' => true],
    'menu_nav' => ['label' => 'Menú Nav', 'icon' => 'ph-list', 'rows' => $menu_nav, 'fields' => ['etiqueta','enlace','orden','activo','color'], 'can_add' => true],
    'botones_nav' => ['label' => 'Botones Nav', 'icon' => 'ph-cursor-click', 'rows' => $botones_nav, 'fields' => ['texto','enlace','color_fondo','color_texto','color_borde','orden','activo'], 'can_add' => true],
    'faq' => ['label' => 'FAQ', 'icon' => 'ph-question', 'rows' => $faq_items, 'fields' => ['pregunta','respuesta','orden','activo','color'], 'can_add' => true],
    'footer_apariencia' => ['label' => 'Apariencia del Footer', 'icon' => 'ph-palette', 'rows' => [$footerFondo, $footerTexto], 'fields' => ['clave','valor'], 'can_add' => false],
    'footer' => ['label' => 'Footer', 'icon' => 'ph-article', 'rows' => $footer_items, 'fields' => ['tipo','titulo','contenido','url','icono','columna','orden','color'], 'can_add' => true],
    'subtitulos' => ['label' => 'Subtítulos', 'icon' => 'ph-text-aa', 'rows' => $subtitulos, 'fields' => ['seccion','titulo','subtitulo','color'], 'can_add' => false],
    'secciones_visibilidad' => ['label' => 'Secciones del Sitio', 'icon' => 'ph-eye', 'rows' => $seccionesVisibilidad, 'fields' => ['clave','valor'], 'can_add' => false],
    'configuraciones' => ['label' => 'Config', 'icon' => 'ph-gear', 'rows' => $configs, 'fields' => ['clave','valor'], 'can_add' => false],
    'secciones_dinamicas' => ['label' => 'Secciones Dinámicas', 'icon' => 'ph-squares-four', 'rows' => $secciones_dinamicas, 'fields' => ['nombre','insertar_despues','orden','activo'], 'can_add' => true],
];

foreach ($secciones_dinamicas as $sd) {
    $sections['bloques_' . $sd['id']] = [
        'label' => 'Bloques: ' . $sd['nombre'],
        'icon' => 'ph-stack',
        'rows' => $sd['bloques'],
        'fields' => ['tipo', 'posicion', 'orden'],
        'can_add' => true,
    ];
}

$groups = [
    'nav' => ['label' => 'Nav', 'icon' => 'ph-list', 'children' => ['nav', 'nav_apariencia', 'menu_nav', 'botones_nav']],
    'hero' => ['label' => 'Hero', 'icon' => 'ph-images', 'children' => ['slides', 'hero_texto', 'botones_hero', 'estadisticas']],
    'identidad' => ['label' => 'Identidad', 'icon' => 'ph-seal-check', 'children' => ['identidad', 'badges']],
    'nosotros' => ['label' => 'Sobre Nosotros', 'icon' => 'ph-info', 'children' => ['about', 'caracteristicas']],
    'exponentes' => ['label' => 'Exponentes', 'icon' => 'ph-users-three', 'children' => ['exponentes']],
    'platillos' => ['label' => 'Platillos', 'icon' => 'ph-fork-knife', 'children' => ['platillos']],
    'itinerario' => ['label' => 'Itinerario', 'icon' => 'ph-calendar', 'children' => ['itinerario']],
    'patrocinadores' => ['label' => 'Patrocinadores', 'icon' => 'ph-handshake', 'children' => ['patrocinadores']],
    'faq' => ['label' => 'FAQ', 'icon' => 'ph-question', 'children' => ['faq']],
    'footer' => ['label' => 'Footer', 'icon' => 'ph-article', 'children' => ['footer_apariencia', 'footer']],
    'general' => ['label' => 'General', 'icon' => 'ph-gear', 'children' => ['subtitulos', 'secciones_visibilidad', 'configuraciones']],
    'dinamicas' => ['label' => 'Secciones Dinámicas', 'icon' => 'ph-squares-four', 'children' => ['secciones_dinamicas']],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Sazón Córdoba</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f6f8;
            color: #1a1a1a;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: #ffffff;
            border-right: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 100;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-brand img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }
        .sidebar-brand h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            color: #1a1a1a;
        }
        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .sidebar-nav .nav-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #adb5bd;
            padding: 8px 12px 4px;
            font-weight: 600;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: #5a6066;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 2px;
            text-decoration: none;
        }
        .nav-item:hover { background: #f8f9fa; color: #1a1a1a; }
        .nav-item.active { background: rgba(255,107,0,0.1); color: #ff6b00; }
        .nav-item i { font-size: 1.2rem; }
        .nav-item .count-badge {
            margin-left: auto;
            background: #e9ecef;
            padding: 2px 10px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .nav-item.active .count-badge { background: rgba(255,107,0,0.2); color: #ff6b00; }
        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid #e9ecef;
        }
        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            color: #5a6066;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .sidebar-footer a:hover { background: #f8f9fa; color: #1a1a1a; }

        /* MAIN */
        .main {
            margin-left: 260px;
            flex: 1;
            padding: 24px 32px;
            max-width: calc(100vw - 260px);
        }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }
        .main-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem;
        }
        .main-header h1 span { color: #adb5bd; font-weight: 400; font-size: 0.95rem; }
        .page-content { display: none; }
        .page-content.active { display: block; }
        .tab-bar {
            display: none;
            gap: 6px;
            margin-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 0;
        }
        .tab-btn {
            background: none;
            border: none;
            padding: 10px 18px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            color: #5a6066;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
            margin-bottom: -1px;
        }
        .tab-btn:hover { color: #1a1a1a; }
        .tab-btn.active { color: #ff6b00; border-bottom-color: #ff6b00; font-weight: 600; }

        /* CARDS */
        .section-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            overflow: hidden;
        }
        .section-card-header {
            padding: 18px 24px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .section-card-header h2 { font-family: 'Outfit', sans-serif; font-size: 1.05rem; }
        .section-card-header .count { color: #adb5bd; font-size: 0.85rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.88rem;
        }
        th {
            color: #adb5bd;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td.actions { white-space: nowrap; text-align: right; width: 130px; }
        .img-preview { width: 60px; height: 35px; object-fit: cover; border-radius: 6px; }
        .empty-state {
            padding: 48px 24px;
            text-align: center;
            color: #6c757d;
        }
        .empty-state i { font-size: 2.5rem; margin-bottom: 12px; display: block; }
        .toggle-yes { color: #2ecc71; } .toggle-no { color: #e74c3c; }

        /* BUTTONS */
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 18px; border-radius: 50px; border: none;
            font-size: 0.82rem; font-weight: 600;
            font-family: 'Outfit', sans-serif;
            cursor: pointer; transition: all 0.25s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #ff6b00, #ffb703);
            color: #fff;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(255,107,0,0.35); }
        .btn-sm { padding: 6px 14px; font-size: 0.78rem; }
        .btn-ghost { background: #f8f9fa; color: #5a6066; border: 1px solid #e9ecef; }
        .btn-ghost:hover { background: #e9ecef; color: #1a1a1a; }
        .btn-danger { background: rgba(255,0,0,0.15); color: #ff6b6b; }
        .btn-danger:hover { background: rgba(255,0,0,0.25); }

        /* MODAL */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 1000;
            align-items: center; justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #ffffff; border-radius: 20px; padding: 32px;
            width: 90%; max-width: 560px; max-height: 85vh; overflow-y: auto;
            border: 1px solid #e9ecef;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .modal h3 { font-family: 'Outfit', sans-serif; margin-bottom: 20px; font-size: 1.2rem; }
        .modal .form-group { margin-bottom: 14px; }
        .modal label { display: block; font-size: 0.8rem; font-weight: 600; color: #5a6066; margin-bottom: 4px; }
        .modal input, .modal textarea, .modal select {
            width: 100%; padding: 10px 14px; border-radius: 10px;
            border: 1px solid #e9ecef;
            background: #f8f9fa; color: #1a1a1a;
            font-size: 0.9rem; outline: none; font-family: 'Inter', sans-serif;
        }
        .modal input:focus, .modal textarea:focus { border-color: #ff6b00; }
        .modal textarea { resize: vertical; min-height: 80px; }
        .modal .btn-row { display: flex; gap: 12px; margin-top: 20px; }
        .modal .btn-row .btn-cancel {
            flex: 1; padding: 12px; border-radius: 50px;
            border: 1px solid #e9ecef;
            background: #f8f9fa; color: #5a6066; cursor: pointer; font-weight: 600;
        }
        .modal .btn-row .btn-cancel:hover { background: #e9ecef; color: #1a1a1a; }
        .modal .btn-row .btn-save { flex: 2; }

        .form-row { display: flex; gap: 12px; }
        .form-row > * { flex: 1; }

        /* LOADER */
        .loader-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .loader { width: 50px; aspect-ratio: 1; display: grid; }
        .loader::before, .loader::after {
            content: ""; grid-area: 1/1;
            --c: no-repeat radial-gradient(farthest-side,#ff6b00 92%,#0000);
            background: var(--c) 50% 0, var(--c) 50% 100%, var(--c) 100% 50%, var(--c) 0 50%;
            background-size: 12px 12px;
            animation: l12 1s infinite;
        }
        .loader::before { margin: 4px; filter: hue-rotate(45deg); background-size: 8px 8px; animation-timing-function: linear; }
        @keyframes l12 { 100% { transform: rotate(.5turn); } }

        /* SWEETALERT LIGHT THEME */
        .swal2-popup { background: #ffffff !important; color: #1a1a1a !important; border-radius: 20px !important; border: 1px solid #e9ecef !important; }
        .swal2-title { color: #1a1a1a !important; font-family: 'Outfit', sans-serif !important; }
        .swal2-html-container { color: #5a6066 !important; }
        .swal2-icon.swal2-success { border-color: #2ecc71 !important; color: #2ecc71 !important; }
        .swal2-icon.swal2-success [class^=swal2-success-line] { background-color: #2ecc71 !important; }
        .swal2-icon.swal2-success .swal2-success-ring { border-color: rgba(46,204,113,0.3) !important; }
        .swal2-icon.swal2-error { border-color: #e74c3c !important; color: #e74c3c !important; }
        .swal2-icon.swal2-warning { border-color: #f1c40f !important; color: #f1c40f !important; }
        .swal2-confirm { background: linear-gradient(135deg, #ff6b00, #ffb703) !important; border-radius: 50px !important; font-family: 'Outfit', sans-serif !important; font-weight: 600 !important; }
        .swal2-cancel { border-radius: 50px !important; font-family: 'Outfit', sans-serif !important; font-weight: 600 !important; }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .sidebar { width: 60px; }
            .sidebar-brand h2, .sidebar-brand span, .nav-label, .nav-item span, .count-badge, .sidebar-footer a span { display: none; }
            .sidebar-brand { justify-content: center; padding: 16px; }
            .sidebar-brand img { width: 30px; height: 30px; }
            .nav-item { justify-content: center; padding: 12px; }
            .main { margin-left: 60px; padding: 16px; max-width: calc(100vw - 60px); }
            table { font-size: 0.8rem; }
            th, td { padding: 8px 10px; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="../../img/logos/logosason.jpg" alt="Sazón Córdoba">
        <h2>Sazón Córdoba</h2>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Contenido</div>
        <?php foreach ($groups as $gkey => $g): ?>
            <?php $count = array_sum(array_map(fn($c) => count($sections[$c]['rows']), $g['children'])); ?>
            <div class="nav-item" data-group="<?= $gkey ?>" onclick="showGroup('<?= $gkey ?>')">
                <i class="<?= $g['icon'] ?>"></i>
                <span><?= $g['label'] ?></span>
                <span class="count-badge"><?= $count ?></span>
            </div>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php"><i class="ph ph-sign-out"></i> <span>Cerrar Sesión</span></a>
    </div>
</aside>

<!-- LOADER -->
<div class="loader-overlay" id="globalLoader">
    <div class="loader"></div>
</div>

<!-- MAIN -->
<main class="main">
    <div class="main-header">
        <h1 id="pageTitle">Hero Slides <span id="pageCount"></span></h1>
        <button class="btn btn-primary" id="btnAdd" onclick="openModal(currentSection,'add')">+ Agregar</button>
    </div>

    <div class="tab-bar" id="tabBar"></div>

    <?php foreach ($sections as $key => $sec): ?>
    <div class="page-content" id="page-<?= $key ?>">
        <div class="section-card">
            <?php if (empty($sec['rows'])): ?>
                <div class="empty-state">
                    <i class="<?= $sec['icon'] ?>"></i>
                    <p>No hay registros en esta sección</p>
                </div>
            <?php else: ?>
            <table>
                <thead><tr>
                    <?php foreach ($sec['fields'] as $f): ?>
                        <th><?= ucfirst(str_replace('_', ' ', $f)) ?></th>
                    <?php endforeach; ?>
                    <th>Acciones</th>
                </tr></thead>
                <tbody>
                <?php foreach ($sec['rows'] as $row): ?>
                    <tr>
                        <?php foreach ($sec['fields'] as $f): ?>
                            <td>
                                <?php if (in_array($f, ['imagen','foto','logo'])): ?>
                                    <?php if ($row[$f]): ?><img src="<?= htmlspecialchars(resolveImgSrc($row[$f])) ?>" class="img-preview"><?php else: ?>—<?php endif; ?>
                                <?php elseif (in_array($f, ['color_texto', 'color', 'color_fondo', 'color_borde'])): ?>
                                    <span style="display:inline-flex; align-items:center; gap:8px;">
                                        <span style="display:inline-block; width:18px; height:18px; border-radius:4px; background:<?= htmlspecialchars($row[$f]) ?>; border:1px solid #e9ecef;"></span>
                                        <?= htmlspecialchars($row[$f]) ?>
                                    </span>
                                <?php elseif ($f === 'activo'): ?>
                                    <span class="<?= $row[$f] ? 'toggle-yes' : 'toggle-no' ?>"><?= $row[$f] ? 'Sí' : 'No' ?></span>
                                <?php elseif (in_array($f, ['subtitulo','descripcion'])): ?>
                                    <?= htmlspecialchars(mb_substr($row[$f] ?? '', 0, 50)) ?><?= mb_strlen($row[$f] ?? '') > 50 ? '…' : '' ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($row[$f] ?? '—') ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="actions">
                            <button class="btn btn-ghost btn-sm" onclick="openModal('<?= $key ?>','edit',<?= htmlspecialchars(json_encode($row)) ?>)">Editar</button>
                            <?php if ($key === 'nav'): ?>
                                <?php if (!$row['activo']): ?>
                                    <button class="btn btn-primary btn-sm" onclick="activarLogo(<?= $row['id'] ?>)">Activar</button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteItem('nav',<?= $row['id'] ?>)">Eliminar</button>
                                <?php endif; ?>
                            <?php elseif ($key === 'secciones_dinamicas'): ?>
                                <button class="btn btn-ghost btn-sm" onclick="showSection('bloques_<?= $row['id'] ?>')">Bloques</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteItem('secciones_dinamicas',<?= $row['id'] ?>)">Eliminar</button>
                            <?php elseif (!in_array($key, ['about','identidad','subtitulos','configuraciones','hero_texto','nav_apariencia','footer_apariencia','secciones_visibilidad'])): ?>
                                <button class="btn btn-danger btn-sm" onclick="deleteItem('<?= $key ?>',<?= $row['id'] ?>)">Eliminar</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</main>

<!-- MODAL -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <h3 id="modalTitle">Agregar</h3>
        <form id="modalForm">
            <input type="hidden" name="id" id="formId">
            <div id="formFields"></div>
            <div class="btn-row">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-save">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
const fieldConfig = {
    nav: [
        {name:'logo', label:'Logo (imagen)', type:'file'},
    ],
    nav_apariencia: [
        {name:'clave', label:'', type:'hidden'},
        {name:'valor', label:'Color de fondo del Nav', type:'color_text'},
    ],
    slides: [
        {name:'imagen', label:'URL Imagen', type:'url'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
        {name:'orden', label:'Orden', type:'number'},
    ],
    hero_texto: [
        {name:'texto_badge', label:'Texto Badge', type:'text'},
        {name:'titulo', label:'Título', type:'text'},
        {name:'subtitulo', label:'Subtítulo', type:'textarea'},
        {name:'color', label:'Color del texto', type:'color'},
    ],
    estadisticas: [
        {name:'numero', label:'Número (ej: 15+)', type:'text'},
        {name:'etiqueta', label:'Etiqueta', type:'text'},
        {name:'icono', label:'Icono (clase Phospor)', type:'text'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'color', label:'Color del texto', type:'color'},
    ],
    about: [
        {name:'imagen', label:'Imagen', type:'file'},
        {name:'titulo', label:'Título', type:'text'},
        {name:'descripcion', label:'Descripción', type:'textarea'},
        {name:'color', label:'Color del texto', type:'color'},
    ],
    caracteristicas: [
        {name:'icono', label:'Icono (clase Phospor)', type:'text'},
        {name:'titulo', label:'Título', type:'text'},
        {name:'descripcion', label:'Descripción', type:'textarea'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'color', label:'Color del texto', type:'color'},
    ],
    exponentes: [
        {name:'nombre', label:'Nombre', type:'text'},
        {name:'especialidad', label:'Especialidad', type:'text'},
        {name:'foto', label:'Foto', type:'file'},
        {name:'instagram_url', label:'Instagram URL (opcional)', type:'url'},
        {name:'twitter_url', label:'Twitter URL (opcional)', type:'url'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'color', label:'Color del texto', type:'color'},
    ],
    platillos: [
        {name:'nombre', label:'Nombre', type:'text'},
        {name:'descripcion', label:'Descripción', type:'textarea'},
        {name:'imagen', label:'Imagen', type:'file'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'color', label:'Color del texto', type:'color'},
    ],
    itinerario: [
        {name:'hora', label:'Hora (ej: 12:00 PM)', type:'text'},
        {name:'dia', label:'Día', type:'text'},
        {name:'titulo', label:'Título', type:'text'},
        {name:'nombre_chef', label:'Chef', type:'text'},
        {name:'descripcion', label:'Descripción', type:'textarea'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'color', label:'Color del texto', type:'color'},
        {name:'color_fondo', label:'Color de fondo de la card', type:'color_text'},
        {name:'color_borde', label:'Color de borde de la card', type:'color_text'},
    ],
    patrocinadores: [
        {name:'nombre', label:'Nombre', type:'text'},
        {name:'logo', label:'Logo', type:'file'},
        {name:'url', label:'Sitio Web (opcional)', type:'url'},
        {name:'orden', label:'Orden', type:'number'},
    ],
    identidad: [
        {name:'titulo', label:'Título', type:'text'},
        {name:'descripcion', label:'Descripción', type:'textarea'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
        {name:'color', label:'Color del texto', type:'color'},
    ],
    badges: [
        {name:'texto', label:'Texto', type:'text'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'color', label:'Color del texto', type:'color'},
        {name:'color_fondo', label:'Color de fondo', type:'color_text'},
    ],
    menu_nav: [
        {name:'etiqueta', label:'Etiqueta', type:'text'},
        {name:'enlace', label:'Enlace', type:'text'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
        {name:'color', label:'Color del texto', type:'color'},
    ],
    botones_nav: [
        {name:'texto', label:'Texto', type:'text'},
        {name:'enlace', label:'Enlace (URL o #ancla)', type:'text'},
        {name:'color_fondo', label:'Color de fondo', type:'color_text'},
        {name:'color_texto', label:'Color de texto', type:'color'},
        {name:'color_borde', label:'Color de borde', type:'color_text'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
    ],
    botones_hero: [
        {name:'texto', label:'Texto', type:'text'},
        {name:'enlace', label:'Enlace (URL o #ancla)', type:'text'},
        {name:'color_fondo', label:'Color de fondo', type:'color_text'},
        {name:'color_texto', label:'Color de texto', type:'color'},
        {name:'color_borde', label:'Color de borde', type:'color_text'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
    ],
    faq: [
        {name:'pregunta', label:'Pregunta', type:'textarea'},
        {name:'respuesta', label:'Respuesta', type:'textarea'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
        {name:'color', label:'Color del texto', type:'color'},
    ],
    footer_apariencia: [
        {name:'clave', label:'', type:'hidden'},
        {name:'valor', label:'Color', type:'color_text'},
    ],
    footer: [
        {name:'tipo', label:'Tipo', type:'select', options:[{v:'texto',l:'Texto'},{v:'red_social',l:'Red Social'},{v:'enlace',l:'Enlace'}]},
        {name:'titulo', label:'Título / Columna', type:'text'},
        {name:'contenido', label:'Contenido', type:'text'},
        {name:'url', label:'URL', type:'url'},
        {name:'icono', label:'Icono (clase Phospor)', type:'text'},
        {name:'columna', label:'Columna', type:'select', options:[{v:'1',l:'Col 1'},{v:'2',l:'Col 2'},{v:'3',l:'Col 3'}]},
        {name:'orden', label:'Orden', type:'number'},
        {name:'color', label:'Color del texto (vacío = usa el color general del footer)', type:'color_text'},
    ],
    subtitulos: [
        {name:'seccion', label:'Sección (clave)', type:'text'},
        {name:'titulo', label:'Título', type:'text'},
        {name:'subtitulo', label:'Subtítulo', type:'textarea'},
        {name:'color', label:'Color (vacío = usa el estilo por defecto)', type:'color_text'},
    ],
    secciones_visibilidad: [
        {name:'clave', label:'', type:'hidden'},
        {name:'valor', label:'Mostrar sección', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
    ],
    configuraciones: [
        {name:'clave', label:'Clave', type:'text'},
        {name:'valor', label:'Valor', type:'text'},
    ],
    secciones_dinamicas: [
        {name:'nombre', label:'Nombre', type:'text'},
        {name:'insertar_despues', label:'Insertar después de', type:'select', options:[
            {v:'home', l:'Hero'}, {v:'identity', l:'Identidad'}, {v:'about', l:'Sobre Nosotros'},
            {v:'chefs', l:'Exponentes'}, {v:'dishes', l:'Platillos'}, {v:'itinerary', l:'Itinerario'},
            {v:'faq', l:'FAQ'}, {v:'sponsors', l:'Patrocinadores'},
        ]},
        {name:'orden', label:'Orden', type:'number'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
    ],
};

const pageTitles = {
    nav: 'Logos del Nav', nav_apariencia: 'Apariencia del Nav', slides: 'Hero Slides', hero_texto: 'Texto del Hero', botones_hero: 'Botones Hero', estadisticas: 'Estadísticas del Hero', about: 'Sección About',
    caracteristicas: 'Características About', exponentes: 'Exponentes (Chefs)',
    platillos: 'Platillos Destacados', itinerario: 'Itinerario', patrocinadores: 'Patrocinadores',
    identidad: 'Sección Identidad', badges: 'Badges Identidad', menu_nav: 'Menú Navegación',
    botones_nav: 'Botones Nav',
    faq: 'Preguntas Frecuentes', footer_apariencia: 'Apariencia del Footer', footer: 'Configuración Footer',
    subtitulos: 'Subtítulos de Secciones', secciones_visibilidad: 'Secciones del Sitio', configuraciones: 'Configuraciones del Sitio',
    secciones_dinamicas: 'Secciones Dinámicas',
    <?php foreach ($secciones_dinamicas as $sd): ?>
    bloques_<?= $sd['id'] ?>: 'Bloques: <?= addslashes($sd['nombre']) ?>',
    <?php endforeach; ?>
};

const canAdd = {
    nav:true, nav_apariencia:false, slides:true, hero_texto:false, estadisticas:true, about:false, caracteristicas:true,
    exponentes:true, platillos:true, itinerario:true, patrocinadores:true,
    identidad:false, badges:true, menu_nav:true, botones_nav:true, botones_hero:true, faq:true, footer_apariencia:false, footer:true,
    subtitulos:false, secciones_visibilidad:false, configuraciones:false,
    secciones_dinamicas:true,
    <?php foreach ($secciones_dinamicas as $sd): ?>
    bloques_<?= $sd['id'] ?>: true,
    <?php endforeach; ?>
};

const groups = {
    nav: { label: 'Nav', children: ['nav', 'nav_apariencia', 'menu_nav', 'botones_nav'] },
    hero: { label: 'Hero', children: ['slides', 'hero_texto', 'botones_hero', 'estadisticas'] },
    identidad: { label: 'Identidad', children: ['identidad', 'badges'] },
    nosotros: { label: 'Sobre Nosotros', children: ['about', 'caracteristicas'] },
    exponentes: { label: 'Exponentes', children: ['exponentes'] },
    platillos: { label: 'Platillos', children: ['platillos'] },
    itinerario: { label: 'Itinerario', children: ['itinerario'] },
    patrocinadores: { label: 'Patrocinadores', children: ['patrocinadores'] },
    faq: { label: 'FAQ', children: ['faq'] },
    footer: { label: 'Footer', children: ['footer_apariencia', 'footer'] },
    general: { label: 'General', children: ['subtitulos', 'secciones_visibilidad', 'configuraciones'] },
    dinamicas: { label: 'Secciones Dinámicas', children: ['secciones_dinamicas'] },
};

let currentSection = 'nav';
let currentGroup = 'nav';
let currentMode = '';

function showGroup(group) {
    currentGroup = group;
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    document.querySelector(`.nav-item[data-group="${group}"]`).classList.add('active');
    renderTabs(group);
    showSection(groups[group].children[0]);
}

function renderTabs(group) {
    const children = groups[group].children;
    const tabBar = document.getElementById('tabBar');
    if (children.length > 1) {
        tabBar.style.display = 'flex';
        tabBar.innerHTML = children.map((c, i) =>
            `<button class="tab-btn${i === 0 ? ' active' : ''}" data-tab="${c}" onclick="switchTab('${c}', this)">${pageTitles[c] || c}</button>`
        ).join('');
    } else {
        tabBar.style.display = 'none';
        tabBar.innerHTML = '';
    }
}

function switchTab(section, btn) {
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');
    showSection(section);
}

function showSection(section) {
    const pageEl = document.getElementById('page-' + section);
    if (!pageEl) {
        // La página no existe todavía en el DOM (ej. sección dinámica creada/borrada
        // sin recargar) — recargamos para que el servidor la regenere.
        location.reload();
        return;
    }
    currentSection = section;
    document.querySelectorAll('.page-content').forEach(el => el.classList.remove('active'));
    pageEl.classList.add('active');
    document.getElementById('pageTitle').textContent = pageTitles[section] || section;
    document.getElementById('btnAdd').style.display = canAdd[section] ? 'inline-flex' : 'none';
}

const BLOQUE_TIPOS = {
    texto: [
        {name:'contenido_titulo', label:'Título', type:'text', key:'titulo'},
        {name:'contenido_texto', label:'Texto', type:'textarea', key:'texto'},
        {name:'contenido_color', label:'Color del texto', type:'color', key:'color'},
    ],
    imagen: [
        {name:'imagen_file', label:'Imagen', type:'file', key:'url'},
    ],
    boton: [
        {name:'contenido_texto', label:'Texto del botón', type:'text', key:'texto'},
        {name:'contenido_enlace', label:'Enlace', type:'text', key:'enlace'},
        {name:'contenido_color_fondo', label:'Color de fondo', type:'color_text', key:'color_fondo'},
        {name:'contenido_color_texto', label:'Color de texto', type:'color', key:'color_texto'},
        {name:'contenido_color_borde', label:'Color de borde', type:'color_text', key:'color_borde'},
    ],
    tarjetas: [
        {name:'contenido_items', label:'Tarjetas (JSON: [{"imagen":"URL","titulo":"...","descripcion":"..."}])', type:'textarea', key:'items', isJson:true},
    ],
};

function renderBloqueSubFields(tipo, contenido) {
    const container = document.getElementById('bloqueSubFields');
    container.innerHTML = '';
    (BLOQUE_TIPOS[tipo] || []).forEach(f => {
        let val = '';
        if (contenido && f.key) {
            val = f.isJson ? JSON.stringify(contenido[f.key] || [], null, 2) : (contenido[f.key] ?? '');
        }
        const div = document.createElement('div');
        div.className = 'form-group';
        div.innerHTML = `<label>${f.label}</label>`;
        if (f.type === 'textarea') {
            div.innerHTML += `<textarea name="${f.name}">${val}</textarea>`;
        } else if (f.type === 'file') {
            if (val) div.innerHTML += `<img src="${val}" class="img-preview" style="display:block; margin-bottom:8px;">`;
            div.innerHTML += `<input type="file" name="${f.name}" accept="image/*">`;
        } else if (f.type === 'color_text') {
            const hexVal = /^#[0-9a-fA-F]{6}$/.test(val) ? val : '#000000';
            div.innerHTML += `<div style="display:flex; gap:8px; align-items:center;">
                <input type="color" value="${hexVal}" oninput="this.nextElementSibling.value=this.value">
                <input type="text" name="${f.name}" value="${val}" placeholder='hex o "transparent"' style="flex:1;" oninput="const p=this.previousElementSibling; if(/^#[0-9a-fA-F]{6}$/.test(this.value)) p.value=this.value;">
            </div>`;
        } else {
            div.innerHTML += `<input type="${f.type}" name="${f.name}" value="${val}">`;
        }
        container.appendChild(div);
    });
}

function openBloqueModal(section, mode, data) {
    currentSection = section;
    currentMode = mode;
    document.getElementById('modalOverlay').classList.add('active');
    document.getElementById('modalTitle').textContent = mode === 'add' ? 'Agregar bloque' : 'Editar bloque';

    const seccionId = section.replace('bloques_', '');
    const contenido = data?.contenido ? JSON.parse(data.contenido) : null;
    const tipoActual = data?.tipo || 'texto';

    const container = document.getElementById('formFields');
    container.innerHTML = `
        <input type="hidden" name="seccion_id" value="${seccionId}">
        <div class="form-group">
            <label>Tipo de bloque</label>
            <select name="tipo" id="bloqueTipoSelect" onchange="renderBloqueSubFields(this.value, null)">
                ${Object.keys(BLOQUE_TIPOS).map(t => `<option value="${t}"${t===tipoActual?' selected':''}>${t.charAt(0).toUpperCase()+t.slice(1)}</option>`).join('')}
            </select>
        </div>
        <div class="form-group">
            <label>Posición</label>
            <select name="posicion">
                <option value="completo"${(data?.posicion||'completo')==='completo'?' selected':''}>Completo (ancho completo)</option>
                <option value="izquierda"${data?.posicion==='izquierda'?' selected':''}>Izquierda</option>
                <option value="derecha"${data?.posicion==='derecha'?' selected':''}>Derecha</option>
            </select>
        </div>
        <div class="form-group">
            <label>Orden</label>
            <input type="number" name="orden" value="${data?.orden ?? (document.querySelectorAll('#page-' + section + ' tbody tr').length + 1)}">
        </div>
        <div id="bloqueSubFields"></div>
    `;
    renderBloqueSubFields(tipoActual, contenido);
    document.getElementById('formId').value = data?.id ?? '';
}

function openModal(section, mode, data = null) {
    if (section.startsWith('bloques_')) { openBloqueModal(section, mode, data); return; }
    currentSection = section;
    currentMode = mode;
    document.getElementById('modalOverlay').classList.add('active');
    document.getElementById('modalTitle').textContent = mode === 'add' ? `Agregar - ${pageTitles[section]}` : `Editar - ${pageTitles[section]}`;

    const container = document.getElementById('formFields');
    container.innerHTML = '';
    (fieldConfig[section] || []).forEach(f => {
        let val = data ? (data[f.name] ?? '') : '';
        if (!data && f.name === 'orden') {
            val = document.querySelectorAll('#page-' + section + ' tbody tr').length + 1;
        }
        const div = document.createElement('div');
        div.className = 'form-group';
        div.innerHTML = `<label>${f.label}</label>`;
        if (f.type === 'textarea') {
            div.innerHTML += `<textarea name="${f.name}">${val}</textarea>`;
        } else if (f.type === 'select') {
            div.innerHTML += `<select name="${f.name}">${f.options.map(o => `<option value="${o.v}"${val == o.v ? ' selected' : ''}>${o.l}</option>`).join('')}</select>`;
        } else if (f.type === 'file') {
            if (val) {
                const previewSrc = /^https?:\/\//.test(val) ? val : `../../${val}`;
                div.innerHTML += `<img src="${previewSrc}" class="img-preview" style="display:block; margin-bottom:8px;">`;
            }
            div.innerHTML += `<input type="file" name="${f.name}" accept="image/*">`;
        } else if (f.type === 'color_text') {
            const hexVal = /^#[0-9a-fA-F]{6}$/.test(val) ? val : '#000000';
            div.innerHTML += `<div style="display:flex; gap:8px; align-items:center;">
                <input type="color" value="${hexVal}" oninput="this.nextElementSibling.value=this.value">
                <input type="text" name="${f.name}" value="${val}" placeholder='hex o "transparent"' style="flex:1;" oninput="const p=this.previousElementSibling; if(/^#[0-9a-fA-F]{6}$/.test(this.value)) p.value=this.value;">
            </div>`;
        } else {
            div.innerHTML += `<input type="${f.type}" name="${f.name}" value="${val}">`;
        }
        container.appendChild(div);
    });
    document.getElementById('formId').value = data?.id ?? '';
}

function closeModal() { document.getElementById('modalOverlay').classList.remove('active'); }

document.getElementById('modalForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    showLoader();
    const formData = new FormData(this);
    const saveActions = { about:'save_about', hero_texto:'save_hero_texto', identidad:'save_identidad', subtitulos:'save_subtitulo', configuraciones:'save_config', nav_apariencia:'save_config', footer_apariencia:'save_config', secciones_visibilidad:'save_config' };
    const actionSection = currentSection.startsWith('bloques_') ? 'bloques_dinamicos' : currentSection;

    if (actionSection === 'bloques_dinamicos') {
        const tipo = formData.get('tipo');
        const contenido = {};
        (BLOQUE_TIPOS[tipo] || []).forEach(f => {
            if (!f.key || f.type === 'file') return;
            let v = formData.get(f.name);
            if (f.isJson) {
                try { v = JSON.parse(v || '[]'); } catch (err) { v = []; }
            }
            contenido[f.key] = v;
        });
        formData.set('contenido', JSON.stringify(contenido));
    }

    formData.set('action', currentMode === 'add' ? 'add_' + actionSection : (saveActions[currentSection] || 'edit_' + actionSection));
    try {
        const res = await fetch('?action=' + formData.get('action'), { method:'POST', body: formData });
        const data = await res.json();
        hideLoader();
        if (data.ok) {
            const section = currentSection;
            if (section === 'configuraciones') {
                await Swal.fire({ icon:'success', title:'Guardado', text:'Configuración actualizada.', timer:1500, showConfirmButton:false });
                closeModal();
                refreshSection(section);
            } else if (section === 'secciones_dinamicas' && currentMode === 'add') {
                await Swal.fire({ icon:'success', title:'Éxito', text:'Guardado correctamente.', timer:1500, showConfirmButton:false });
                location.reload();
            } else {
                await Swal.fire({ icon:'success', title:'Éxito', text:'Guardado correctamente.', timer:1500, showConfirmButton:false });
                closeModal();
                refreshSection(section);
            }
        } else {
            Swal.fire({ icon:'error', title:'Error', text:data.error || 'Ocurrió un error.' });
        }
    } catch(e) {
        hideLoader();
        Swal.fire({ icon:'error', title:'Error de conexión', text:'No se pudo completar la operación.' });
    }
});

async function deleteItem(section, id) {
    const result = await Swal.fire({
        title: '¿Eliminar?',
        text: 'Este registro se eliminará permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    });
    if (!result.isConfirmed) return;

    showLoader();
    const fd = new FormData();
    fd.set('id', id);
    fd.set('action', 'delete_' + (section.startsWith('bloques_') ? 'bloques_dinamicos' : section));
    try {
        const res = await fetch('?action=' + fd.get('action'), { method:'POST', body: fd });
        const data = await res.json();
        hideLoader();
        if (data.ok) {
            await Swal.fire({ icon:'success', title:'Eliminado', text:'Registro eliminado.', timer:1500, showConfirmButton:false });
            if (section === 'secciones_dinamicas') {
                location.reload();
            } else {
                refreshSection(section);
            }
        } else {
            Swal.fire({ icon:'error', title:'Error', text:data.error || 'Ocurrió un error.' });
        }
    } catch(e) {
        hideLoader();
        Swal.fire({ icon:'error', title:'Error de conexión', text:'No se pudo completar la operación.' });
    }
}

async function activarLogo(id) {
    showLoader();
    const fd = new FormData();
    fd.set('id', id);
    fd.set('action', 'activar_nav');
    try {
        const res = await fetch('?action=activar_nav', { method:'POST', body: fd });
        const data = await res.json();
        hideLoader();
        if (data.ok) {
            await Swal.fire({ icon:'success', title:'Activado', text:'Logo activado.', timer:1500, showConfirmButton:false });
            refreshSection('nav');
        } else {
            Swal.fire({ icon:'error', title:'Error', text:data.error || 'Ocurrió un error.' });
        }
    } catch(e) {
        hideLoader();
        Swal.fire({ icon:'error', title:'Error de conexión', text:'No se pudo completar la operación.' });
    }
}

async function refreshSection(section) {
    showLoader();
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
        return;
    } finally {
        hideLoader();
    }
}

function showLoader() { document.getElementById('globalLoader').style.display = 'flex'; }
function hideLoader() { document.getElementById('globalLoader').style.display = 'none'; }

document.getElementById('modalOverlay').addEventListener('click', function(e) { if (e.target === this) closeModal(); });

showGroup('nav');
</script>
</body>
</html>
