<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../db.php';
$db = getDB();

function jsonResponse($data) { echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    header('Content-Type: application/json');
    try {
        $actions = [
            'add_slide' => ['INSERT INTO hero_slides (imagen, texto_badge, titulo, subtitulo, activo, orden) VALUES (?,?,?,?,?,?)', ['imagen','texto_badge','titulo','subtitulo','activo','orden']],
            'edit_slide' => ['UPDATE hero_slides SET imagen=?, texto_badge=?, titulo=?, subtitulo=?, activo=?, orden=? WHERE id=?', ['imagen','texto_badge','titulo','subtitulo','activo','orden','id']],
            'delete_slide' => ['DELETE FROM hero_slides WHERE id=?', ['id']],
            'add_estadistica' => ['INSERT INTO hero_estadisticas (numero, etiqueta, icono, orden) VALUES (?,?,?,?)', ['numero','etiqueta','icono','orden']],
            'edit_estadistica' => ['UPDATE hero_estadisticas SET numero=?, etiqueta=?, icono=?, orden=? WHERE id=?', ['numero','etiqueta','icono','orden','id']],
            'delete_estadistica' => ['DELETE FROM hero_estadisticas WHERE id=?', ['id']],
            'save_about' => ['UPDATE secciones_about SET imagen=?, titulo=?, descripcion=? WHERE id=?', ['imagen','titulo','descripcion','id']],
            'add_caracteristica' => ['INSERT INTO caracteristicas_about (icono, titulo, descripcion, orden) VALUES (?,?,?,?)', ['icono','titulo','descripcion','orden']],
            'edit_caracteristica' => ['UPDATE caracteristicas_about SET icono=?, titulo=?, descripcion=?, orden=? WHERE id=?', ['icono','titulo','descripcion','orden','id']],
            'delete_caracteristica' => ['DELETE FROM caracteristicas_about WHERE id=?', ['id']],
            'add_exponente' => ['INSERT INTO exponentes (nombre, especialidad, foto, instagram_url, twitter_url, orden) VALUES (?,?,?,?,?,?)', ['nombre','especialidad','foto','instagram_url','twitter_url','orden']],
            'edit_exponente' => ['UPDATE exponentes SET nombre=?, especialidad=?, foto=?, instagram_url=?, twitter_url=?, orden=? WHERE id=?', ['nombre','especialidad','foto','instagram_url','twitter_url','orden','id']],
            'delete_exponente' => ['DELETE FROM exponentes WHERE id=?', ['id']],
            'add_platillo' => ['INSERT INTO platillos_destacados (nombre, descripcion, imagen, orden) VALUES (?,?,?,?)', ['nombre','descripcion','imagen','orden']],
            'edit_platillo' => ['UPDATE platillos_destacados SET nombre=?, descripcion=?, imagen=?, orden=? WHERE id=?', ['nombre','descripcion','imagen','orden','id']],
            'delete_platillo' => ['DELETE FROM platillos_destacados WHERE id=?', ['id']],
            'add_itinerario' => ['INSERT INTO itinerario_items (hora, dia, titulo, nombre_chef, descripcion, orden) VALUES (?,?,?,?,?,?)', ['hora','dia','titulo','nombre_chef','descripcion','orden']],
            'edit_itinerario' => ['UPDATE itinerario_items SET hora=?, dia=?, titulo=?, nombre_chef=?, descripcion=?, orden=? WHERE id=?', ['hora','dia','titulo','nombre_chef','descripcion','orden','id']],
            'delete_itinerario' => ['DELETE FROM itinerario_items WHERE id=?', ['id']],
            'add_patrocinador' => ['INSERT INTO patrocinadores (nombre, logo, url, orden) VALUES (?,?,?,?)', ['nombre','logo','url','orden']],
            'edit_patrocinador' => ['UPDATE patrocinadores SET nombre=?, logo=?, url=?, orden=? WHERE id=?', ['nombre','logo','url','orden','id']],
            'delete_patrocinador' => ['DELETE FROM patrocinadores WHERE id=?', ['id']],
            // Identidad
            'save_identidad' => ['UPDATE seccion_identidad SET titulo=?, descripcion=?, activo=? WHERE id=?', ['titulo','descripcion','activo','id']],
            // Badges
            'add_badges' => ['INSERT INTO badges_identidad (texto, orden) VALUES (?,?)', ['texto','orden']],
            'edit_badges' => ['UPDATE badges_identidad SET texto=?, orden=? WHERE id=?', ['texto','orden','id']],
            'delete_badges' => ['DELETE FROM badges_identidad WHERE id=?', ['id']],
            // Menu Nav
            'add_menu_nav' => ['INSERT INTO menu_nav (etiqueta, href, orden, activo) VALUES (?,?,?,?)', ['etiqueta','href','orden','activo']],
            'edit_menu_nav' => ['UPDATE menu_nav SET etiqueta=?, href=?, orden=?, activo=? WHERE id=?', ['etiqueta','href','orden','activo','id']],
            'delete_menu_nav' => ['DELETE FROM menu_nav WHERE id=?', ['id']],
            // FAQ
            'add_faq' => ['INSERT INTO faq_items (pregunta, respuesta, orden, activo) VALUES (?,?,?,?)', ['pregunta','respuesta','orden','activo']],
            'edit_faq' => ['UPDATE faq_items SET pregunta=?, respuesta=?, orden=?, activo=? WHERE id=?', ['pregunta','respuesta','orden','activo','id']],
            'delete_faq' => ['DELETE FROM faq_items WHERE id=?', ['id']],
            // Footer
            'add_footer' => ['INSERT INTO footer_config (tipo, titulo, contenido, url, icono, columna, orden) VALUES (?,?,?,?,?,?,?)', ['tipo','titulo','contenido','url','icono','columna','orden']],
            'edit_footer' => ['UPDATE footer_config SET tipo=?, titulo=?, contenido=?, url=?, icono=?, columna=?, orden=? WHERE id=?', ['tipo','titulo','contenido','url','icono','columna','orden','id']],
            'delete_footer' => ['DELETE FROM footer_config WHERE id=?', ['id']],
            // Subtítulos
            'save_subtitulo' => ['UPDATE secciones_subtitulos SET titulo=?, subtitulo=? WHERE id=?', ['titulo','subtitulo','id']],
            // Configuraciones
            'save_config' => ['UPDATE configuraciones_sitio SET valor=? WHERE clave=?', ['valor','clave']],
        ];

        if (isset($actions[$action])) {
            [$sql, $params] = $actions[$action];
            $vals = array_map(fn($p) => $_POST[$p] ?? null, $params);
            $stmt = $db->prepare($sql);
            $stmt->execute($vals);
            jsonResponse(['ok' => true]);
        }
        jsonResponse(['error' => 'Acción no válida']);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()]);
    }
}

$slides = $db->query('SELECT * FROM hero_slides ORDER BY orden')->fetchAll();
$estadisticas = $db->query('SELECT * FROM hero_estadisticas ORDER BY orden')->fetchAll();
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

// Nuevas secciones
$identidad = $db->query('SELECT * FROM seccion_identidad LIMIT 1')->fetch();
if (!$identidad) {
    $db->query("INSERT INTO seccion_identidad (titulo, descripcion) VALUES ('Identidad', 'Descripción')");
    $identidad = $db->query('SELECT * FROM seccion_identidad LIMIT 1')->fetch();
}
$badges = $db->query('SELECT * FROM badges_identidad ORDER BY orden')->fetchAll();
$menu_nav = $db->query('SELECT * FROM menu_nav ORDER BY orden')->fetchAll();
$faq_items = $db->query('SELECT * FROM faq_items ORDER BY orden')->fetchAll();
$footer_items = $db->query('SELECT * FROM footer_config ORDER BY columna, orden')->fetchAll();
$subtitulos = $db->query('SELECT * FROM secciones_subtitulos ORDER BY seccion')->fetchAll();
$configs = $db->query('SELECT id, clave, valor FROM configuraciones_sitio ORDER BY clave')->fetchAll();
$configMap = [];
foreach ($configs as $c) { $configMap[$c['clave']] = $c['valor']; }

$sections = [
    'slides' => ['label' => 'Hero Slides', 'icon' => 'ph-images', 'rows' => $slides, 'fields' => ['imagen','texto_badge','titulo','subtitulo','activo','orden'], 'can_add' => true],
    'estadisticas' => ['label' => 'Estadísticas', 'icon' => 'ph-chart-bar', 'rows' => $estadisticas, 'fields' => ['numero','etiqueta','icono','orden'], 'can_add' => true],
    'about' => ['label' => 'Sección About', 'icon' => 'ph-info', 'rows' => [$about], 'fields' => ['imagen','titulo','descripcion'], 'can_add' => false],
    'caracteristicas' => ['label' => 'Características', 'icon' => 'ph-list-checks', 'rows' => $caracteristicas, 'fields' => ['icono','titulo','descripcion','orden'], 'can_add' => true],
    'exponentes' => ['label' => 'Exponentes', 'icon' => 'ph-users-three', 'rows' => $exponentes, 'fields' => ['nombre','especialidad','foto','instagram_url','twitter_url','orden'], 'can_add' => true],
    'platillos' => ['label' => 'Platillos', 'icon' => 'ph-fork-knife', 'rows' => $platillos, 'fields' => ['nombre','descripcion','imagen','orden'], 'can_add' => true],
    'itinerario' => ['label' => 'Itinerario', 'icon' => 'ph-calendar', 'rows' => $itinerario, 'fields' => ['hora','dia','titulo','nombre_chef','descripcion','orden'], 'can_add' => true],
    'patrocinadores' => ['label' => 'Patrocinadores', 'icon' => 'ph-handshake', 'rows' => $patrocinadores, 'fields' => ['nombre','logo','url','orden'], 'can_add' => true],
    'identidad' => ['label' => 'Identidad', 'icon' => 'ph-seal-check', 'rows' => [$identidad], 'fields' => ['titulo','descripcion','activo'], 'can_add' => false],
    'badges' => ['label' => 'Badges Identidad', 'icon' => 'ph-tags', 'rows' => $badges, 'fields' => ['texto','orden'], 'can_add' => true],
    'menu_nav' => ['label' => 'Menú Nav', 'icon' => 'ph-list', 'rows' => $menu_nav, 'fields' => ['etiqueta','href','orden','activo'], 'can_add' => true],
    'faq' => ['label' => 'FAQ', 'icon' => 'ph-question', 'rows' => $faq_items, 'fields' => ['pregunta','respuesta','orden','activo'], 'can_add' => true],
    'footer' => ['label' => 'Footer', 'icon' => 'ph-article', 'rows' => $footer_items, 'fields' => ['tipo','titulo','contenido','url','icono','columna','orden'], 'can_add' => true],
    'subtitulos' => ['label' => 'Subtítulos', 'icon' => 'ph-text-aa', 'rows' => $subtitulos, 'fields' => ['seccion','titulo','subtitulo'], 'can_add' => false],
    'configuraciones' => ['label' => 'Config', 'icon' => 'ph-gear', 'rows' => $configs, 'fields' => ['clave','valor'], 'can_add' => false],
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

        /* SWEETALERT DARK THEME */
        .swal2-popup { background: #141419 !important; color: #f8f9fa !important; border-radius: 20px !important; border: 1px solid rgba(255,255,255,0.08) !important; }
        .swal2-title { color: #f8f9fa !important; font-family: 'Outfit', sans-serif !important; }
        .swal2-html-container { color: #adb5bd !important; }
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
        <img src="../images/logosason.jpg" alt="Sazón Córdoba">
        <h2>Sazón Córdoba</h2>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Contenido</div>
        <?php foreach ($sections as $key => $sec): ?>
            <div class="nav-item" data-section="<?= $key ?>" onclick="showSection('<?= $key ?>')">
                <i class="<?= $sec['icon'] ?>"></i>
                <span><?= $sec['label'] ?></span>
                <span class="count-badge"><?= count($sec['rows']) ?></span>
            </div>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
        <a href="../index.php"><i class="ph ph-arrow-left"></i> <span>Ver Sitio</span></a>
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
                                    <?php if ($row[$f]): ?><img src="<?= htmlspecialchars($row[$f]) ?>" class="img-preview"><?php else: ?>—<?php endif; ?>
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
                            <?php if (!in_array($key, ['about','identidad','subtitulos','configuraciones'])): ?>
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
    slides: [
        {name:'imagen', label:'URL Imagen', type:'url'},
        {name:'texto_badge', label:'Texto Badge', type:'text'},
        {name:'titulo', label:'Título', type:'text'},
        {name:'subtitulo', label:'Subtítulo', type:'textarea'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
        {name:'orden', label:'Orden', type:'number'},
    ],
    estadisticas: [
        {name:'numero', label:'Número (ej: 15+)', type:'text'},
        {name:'etiqueta', label:'Etiqueta', type:'text'},
        {name:'icono', label:'Icono (clase Phospor)', type:'text'},
        {name:'orden', label:'Orden', type:'number'},
    ],
    about: [
        {name:'imagen', label:'URL Imagen', type:'url'},
        {name:'titulo', label:'Título', type:'text'},
        {name:'descripcion', label:'Descripción', type:'textarea'},
    ],
    caracteristicas: [
        {name:'icono', label:'Icono (clase Phospor)', type:'text'},
        {name:'titulo', label:'Título', type:'text'},
        {name:'descripcion', label:'Descripción', type:'textarea'},
        {name:'orden', label:'Orden', type:'number'},
    ],
    exponentes: [
        {name:'nombre', label:'Nombre', type:'text'},
        {name:'especialidad', label:'Especialidad', type:'text'},
        {name:'foto', label:'URL Foto', type:'url'},
        {name:'instagram_url', label:'Instagram URL', type:'url'},
        {name:'twitter_url', label:'Twitter URL', type:'url'},
        {name:'orden', label:'Orden', type:'number'},
    ],
    platillos: [
        {name:'nombre', label:'Nombre', type:'text'},
        {name:'descripcion', label:'Descripción', type:'textarea'},
        {name:'imagen', label:'URL Imagen', type:'url'},
        {name:'orden', label:'Orden', type:'number'},
    ],
    itinerario: [
        {name:'hora', label:'Hora (ej: 12:00 PM)', type:'text'},
        {name:'dia', label:'Día', type:'text'},
        {name:'titulo', label:'Título', type:'text'},
        {name:'nombre_chef', label:'Chef', type:'text'},
        {name:'descripcion', label:'Descripción', type:'textarea'},
        {name:'orden', label:'Orden', type:'number'},
    ],
    patrocinadores: [
        {name:'nombre', label:'Nombre', type:'text'},
        {name:'logo', label:'URL Logo', type:'url'},
        {name:'url', label:'Sitio Web', type:'url'},
        {name:'orden', label:'Orden', type:'number'},
    ],
    identidad: [
        {name:'titulo', label:'Título', type:'text'},
        {name:'descripcion', label:'Descripción', type:'textarea'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
    ],
    badges: [
        {name:'texto', label:'Texto', type:'text'},
        {name:'orden', label:'Orden', type:'number'},
    ],
    menu_nav: [
        {name:'etiqueta', label:'Etiqueta', type:'text'},
        {name:'href', label:'Enlace (href)', type:'text'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
    ],
    faq: [
        {name:'pregunta', label:'Pregunta', type:'textarea'},
        {name:'respuesta', label:'Respuesta', type:'textarea'},
        {name:'orden', label:'Orden', type:'number'},
        {name:'activo', label:'Activo', type:'select', options:[{v:'1',l:'Sí'},{v:'0',l:'No'}]},
    ],
    footer: [
        {name:'tipo', label:'Tipo', type:'select', options:[{v:'texto',l:'Texto'},{v:'red_social',l:'Red Social'},{v:'enlace',l:'Enlace'}]},
        {name:'titulo', label:'Título / Columna', type:'text'},
        {name:'contenido', label:'Contenido', type:'text'},
        {name:'url', label:'URL', type:'url'},
        {name:'icono', label:'Icono (clase Phospor)', type:'text'},
        {name:'columna', label:'Columna', type:'select', options:[{v:'1',l:'Col 1'},{v:'2',l:'Col 2'},{v:'3',l:'Col 3'}]},
        {name:'orden', label:'Orden', type:'number'},
    ],
    subtitulos: [
        {name:'seccion', label:'Sección (clave)', type:'text'},
        {name:'titulo', label:'Título', type:'text'},
        {name:'subtitulo', label:'Subtítulo', type:'textarea'},
    ],
    configuraciones: [
        {name:'clave', label:'Clave', type:'text'},
        {name:'valor', label:'Valor', type:'text'},
    ],
};

const pageTitles = {
    slides: 'Hero Slides', estadisticas: 'Estadísticas del Hero', about: 'Sección About',
    caracteristicas: 'Características About', exponentes: 'Exponentes (Chefs)',
    platillos: 'Platillos Destacados', itinerario: 'Itinerario', patrocinadores: 'Patrocinadores',
    identidad: 'Sección Identidad', badges: 'Badges Identidad', menu_nav: 'Menú Navegación',
    faq: 'Preguntas Frecuentes', footer: 'Configuración Footer',
    subtitulos: 'Subtítulos de Secciones', configuraciones: 'Configuraciones del Sitio'
};

const canAdd = {
    slides:true, estadisticas:true, about:false, caracteristicas:true,
    exponentes:true, platillos:true, itinerario:true, patrocinadores:true,
    identidad:false, badges:true, menu_nav:true, faq:true, footer:true,
    subtitulos:false, configuraciones:false
};

let currentSection = 'slides';
let currentMode = '';

function showSection(section) {
    currentSection = section;
    document.querySelectorAll('.page-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    document.getElementById('page-' + section).classList.add('active');
    document.querySelector(`.nav-item[data-section="${section}"]`).classList.add('active');
    document.getElementById('pageTitle').textContent = pageTitles[section] || section;
    document.getElementById('btnAdd').style.display = canAdd[section] ? 'inline-flex' : 'none';
}

function openModal(section, mode, data = null) {
    currentSection = section;
    currentMode = mode;
    document.getElementById('modalOverlay').classList.add('active');
    document.getElementById('modalTitle').textContent = mode === 'add' ? `Agregar - ${pageTitles[section]}` : `Editar - ${pageTitles[section]}`;

    const container = document.getElementById('formFields');
    container.innerHTML = '';
    (fieldConfig[section] || []).forEach(f => {
        const val = data ? (data[f.name] ?? '') : '';
        const div = document.createElement('div');
        div.className = 'form-group';
        div.innerHTML = `<label>${f.label}</label>`;
        if (f.type === 'textarea') {
            div.innerHTML += `<textarea name="${f.name}">${val}</textarea>`;
        } else if (f.type === 'select') {
            div.innerHTML += `<select name="${f.name}">${f.options.map(o => `<option value="${o.v}"${val == o.v ? ' selected' : ''}>${o.l}</option>`).join('')}</select>`;
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
    const saveActions = { about:'save_about', identidad:'save_identidad', subtitulos:'save_subtitulo', configuraciones:'save_config' };
    formData.set('action', currentMode === 'add' ? 'add_' + currentSection : (saveActions[currentSection] || 'edit_' + currentSection));
    try {
        const res = await fetch('?action=' + formData.get('action'), { method:'POST', body: formData });
        const data = await res.json();
        hideLoader();
        if (data.ok) {
            if (currentSection === 'configuraciones') {
                await Swal.fire({ icon:'success', title:'Guardado', text:'Configuración actualizada.', timer:1500, showConfirmButton:false });
                closeModal();
            } else {
                await Swal.fire({ icon:'success', title:'Éxito', text:'Guardado correctamente.', timer:1500, showConfirmButton:false });
                closeModal();
                setTimeout(() => location.reload(), 500);
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
    fd.set('action', 'delete_' + section);
    try {
        const res = await fetch('?action=' + fd.get('action'), { method:'POST', body: fd });
        const data = await res.json();
        hideLoader();
        if (data.ok) {
            await Swal.fire({ icon:'success', title:'Eliminado', text:'Registro eliminado.', timer:1500, showConfirmButton:false });
            setTimeout(() => location.reload(), 500);
        } else {
            Swal.fire({ icon:'error', title:'Error', text:data.error || 'Ocurrió un error.' });
        }
    } catch(e) {
        hideLoader();
        Swal.fire({ icon:'error', title:'Error de conexión', text:'No se pudo completar la operación.' });
    }
}

function showLoader() { document.getElementById('globalLoader').style.display = 'flex'; }
function hideLoader() { document.getElementById('globalLoader').style.display = 'none'; }

document.getElementById('modalOverlay').addEventListener('click', function(e) { if (e.target === this) closeModal(); });

showSection('slides');
</script>
</body>
</html>
