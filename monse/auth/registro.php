<?php
session_start();
if (!empty($_SESSION['usuario_id'])) {
    header('Location: ../dashboard/index.php');
    exit;
}
require_once '../config/conexion.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $correo   = trim($_POST['correo']   ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';

    if (!$nombre || !$correo || !$password || !$confirm) {
        $error = 'Por favor completa todos los campos.';
    } elseif (strlen($nombre) < 2) {
        $error = 'El nombre debe tener al menos 2 caracteres.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo electrónico inválido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $db   = conectar();
        $stmt = $db->prepare('SELECT id FROM usuarios WHERE correo = ?');
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Ya existe una cuenta con ese correo.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt->close();

            $stmt = $db->prepare('INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $nombre, $correo, $hash);

            if ($stmt->execute()) {
                $uid = $db->insert_id;
                // Crear fila de estadísticas
                $s2 = $db->prepare('INSERT INTO estadisticas (usuario_id) VALUES (?)');
                $s2->bind_param('i', $uid);
                $s2->execute();
                $s2->close();

                // Auto-login
                $_SESSION['usuario_id'] = $uid;
                $_SESSION['nombre']     = $nombre;
                $_SESSION['foto']       = null;
                header('Location: ../dashboard/index.php');
                exit;
            } else {
                $error = 'Error al crear la cuenta. Intenta de nuevo.';
            }
        }
        $stmt->close();
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta — TimeFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-visual">
        <div class="auth-visual-logo">
            <div class="logo-icon">⏱️</div>
            <span>TimeFlow</span>
        </div>
        <div class="auth-visual-text">
            <h2>Empieza a organizar<br>tu vida hoy mismo</h2>
            <p>Únete a miles de estudiantes que ya gestionan su tiempo y productividad con TimeFlow.</p>
        </div>
        <div class="auth-visual-features">
            <div class="feature-item">
                <span class="fi-icon">🎯</span>
                <span>Metas y categorías personalizadas</span>
            </div>
            <div class="feature-item">
                <span class="fi-icon">🔥</span>
                <span>Racha diaria de productividad</span>
            </div>
            <div class="feature-item">
                <span class="fi-icon">🆓</span>
                <span>Totalmente gratuito</span>
            </div>
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-card">
            <div class="auth-mobile-logo">
                <div class="logo-icon">⏱️</div>
                <span>TimeFlow</span>
            </div>

            <div class="auth-card-header">
                <h1>Crear cuenta</h1>
                <p>Organiza tu tiempo desde hoy</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="regForm">
                <div class="form-group">
                    <label class="form-label">Nombre completo</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon">👤</span>
                        <input type="text" name="nombre" class="form-control"
                               placeholder="Tu nombre"
                               value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Correo electrónico</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon">✉️</span>
                        <input type="email" name="correo" class="form-control"
                               placeholder="tu@correo.com"
                               value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                               required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <div class="input-icon-wrap">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="password" id="passInput" class="form-control"
                                   placeholder="Mín. 6 caracteres" required minlength="6">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmar</label>
                        <div class="input-icon-wrap">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="confirm" id="confirmInput" class="form-control"
                                   placeholder="Repite la contraseña" required>
                        </div>
                    </div>
                </div>

                <!-- Indicador de fortaleza -->
                <div id="strengthWrap" style="margin-top:-10px;margin-bottom:16px;display:none">
                    <div style="height:4px;border-radius:2px;background:var(--border);overflow:hidden">
                        <div id="strengthBar" style="height:100%;width:0;transition:all 0.3s;border-radius:2px"></div>
                    </div>
                    <span id="strengthLabel" style="font-size:11px;color:var(--text-light)"></span>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;padding:14px">
                    Registrarme →
                </button>
            </form>

            <div class="auth-footer">
                ¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a>
            </div>
        </div>
    </div>
</div>
<script>
// Indicador de fortaleza de contraseña
const passInput = document.getElementById('passInput');
const strengthWrap = document.getElementById('strengthWrap');
const strengthBar = document.getElementById('strengthBar');
const strengthLabel = document.getElementById('strengthLabel');

passInput.addEventListener('input', function() {
    const v = this.value;
    strengthWrap.style.display = v ? 'block' : 'none';
    let score = 0;
    if (v.length >= 6) score++;
    if (v.length >= 10) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const labels = ['Muy débil','Débil','Regular','Fuerte','Muy fuerte'];
    const colors = ['#e74c3c','#e67e22','#f1c40f','#27ae60','#1abc9c'];
    strengthBar.style.width = (score * 20) + '%';
    strengthBar.style.background = colors[score - 1] || '#e74c3c';
    strengthLabel.textContent = labels[score - 1] || '';
});

// Validar que contraseñas coincidan
document.getElementById('regForm').addEventListener('submit', function(e) {
    const p = document.getElementById('passInput').value;
    const c = document.getElementById('confirmInput').value;
    if (p !== c) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
    }
});
</script>
</body>
</html>