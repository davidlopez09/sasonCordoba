<?php
session_start();
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $db = getDB();
    $stmt = $db->prepare("SELECT id, contrasena, rol FROM usuarios WHERE correo = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['contrasena']) && $user['rol'] === 'admin') {
        $_SESSION['admin'] = true;
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['ok' => true]);
        exit;
    }
    echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - Sazón Córdoba</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f6f8;
            color: #1a1a1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #ffffff;
            border: 1px solid #e9ecef;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            border-radius: 24px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        .login-card img { max-height: 60px; margin: 0 auto 24px; border-radius: 8px; }
        .login-card h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 8px;
            color: #1a1a1a;
        }
        .login-card p { color: #5a6066; margin-bottom: 32px; font-size: 0.95rem; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: #5a6066;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            background: #f8f9fa;
            color: #1a1a1a;
            font-size: 1rem;
            outline: none;
            transition: border 0.3s;
        }
        .form-group input:focus { border-color: #ff6b00; }
        .btn {
            width: 100%;
            padding: 14px;
            border-radius: 50px;
            border: none;
            background: linear-gradient(135deg, #ff6b00, #ffb703);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255,107,0,0.4); }
        .error {
            background: rgba(255,0,0,0.1);
            border: 1px solid rgba(255,0,0,0.3);
            color: #ff6b6b;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .back-link { display: block; margin-top: 20px; color: #adb5bd; font-size: 0.9rem; text-decoration: none; }
        .back-link:hover { color: #ff6b00; }
        .error { display: none; }

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
    </style>
</head>
<body>
    <div class="loader-overlay" id="globalLoader">
        <div class="loader"></div>
    </div>
    <div class="login-card">
        <img src="../../img/logos/logosason.jpg" alt="Sazón Córdoba">
        <h1>Panel de Administración</h1>
        <p>Ingresa para gestionar el contenido del sitio</p>
        <div class="error" id="loginError"></div>
        <form id="loginForm" method="POST">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn">Ingresar</button>
        </form>
        <a href="../../index.html" class="back-link">← Volver al sitio</a>
    </div>
    <script>
        function showLoader() { document.getElementById('globalLoader').style.display = 'flex'; }
        function hideLoader() { document.getElementById('globalLoader').style.display = 'none'; }

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const errorBox = document.getElementById('loginError');
            errorBox.style.display = 'none';
            showLoader();
            try {
                const res = await fetch('login.php', { method: 'POST', body: new FormData(this) });
                const data = await res.json();
                if (data.ok) {
                    window.location.href = 'index.php';
                    return;
                }
                errorBox.textContent = data.error || 'Usuario o contraseña incorrectos';
                errorBox.style.display = 'block';
            } catch (err) {
                errorBox.textContent = 'Error de conexión. Intentá de nuevo.';
                errorBox.style.display = 'block';
            } finally {
                hideLoader();
            }
        });
    </script>
</body>
</html>
