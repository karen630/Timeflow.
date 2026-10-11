<?php
session_start();
if (!empty($_SESSION['usuario_id'])) {
    header('Location: ../dashboard/index.php');
    exit;
}
require_once '../config/conexion.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$correo || !$password) {
        $error = 'Por favor completa correo y contraseña.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo electrónico inválido.';
    } else {
        $db = conectar();
        $stmt = $db->prepare('SELECT id, nombre, password, foto FROM usuarios WHERE correo = ?');
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        $db->close();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Correo o contraseña incorrectos.';
        } else {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['foto'] = $user['foto'];
            header('Location: ../dashboard/index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — TimeFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
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
            <h2>Organiza tu tiempo con claridad</h2>
            <p>Controla tus tareas, calendario y estadísticas en un solo lugar con una interfaz moderna y clara.</p>
        </div>
        <div class="auth-visual-features">
            <div class="feature-item">
                <span class="fi-icon">📅</span>
                <span>Calendario mensual</span>
            </div>
            <div class="feature-item">
                <span class="fi-icon">✅</span>
                <span>Gestión rápida de tareas</span>
            </div>
            <div class="feature-item">
                <span class="fi-icon">📊</span>
                <span>Estadísticas de productividad</span>
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
                <h1>Iniciar sesión</h1>
                <p>Bienvenido de nuevo, ingresa tus datos.</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Correo electrónico</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon">✉️</span>
                        <input type="email" name="correo" class="form-control" placeholder="tu@correo.com"
                               value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="password" class="form-control" placeholder="********" required>
                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <a href="recuperar.php" class="forgot-link">¿Olvidaste tu contraseña?</a>
                    <button type="submit" class="btn btn-primary" style="padding:12px 20px;">Entrar</button>
                </div>
            </form>

            <div class="auth-footer">
                ¿No tienes cuenta? <a href="registro.php">Crear cuenta</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
