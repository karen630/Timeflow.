<?php
session_start();
if (!empty($_SESSION['usuario_id'])) {
    header('Location: ../dashboard/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña — TimeFlow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
<div class="auth-page" style="align-items:center;justify-content:center;background:var(--bg)">
    <div class="auth-card" style="max-width:420px;width:100%;padding:40px 36px;background:#fff;border-radius:var(--radius);box-shadow:var(--shadow-lg);border:1px solid var(--border)">
        <div style="text-align:center;margin-bottom:28px">
            <div style="font-size:48px;margin-bottom:12px">🔑</div>
            <h1 style="font-family:var(--font-head);font-size:24px;font-weight:800;color:var(--text-dark);margin-bottom:8px">Recuperar Contraseña</h1>
            <p style="color:var(--text-light);font-size:14px">Ingresa tu correo electrónico y recibe instrucciones para restablecer tu contraseña.</p>
        </div>

        <div class="alert alert-info" style="margin-bottom:20px">
            ℹ️ Esta opción es solo visual y representa el flujo de recuperación.
        </div>

        <div class="form-group">
            <label class="form-label">Correo electrónico</label>
            <div class="input-icon-wrap">
                <span class="input-icon">✉️</span>
                <input type="email" class="form-control" placeholder="tu@correo.com" disabled>
            </div>
        </div>

        <button class="btn btn-primary" style="width:100%;padding:14px;opacity:0.65;cursor:not-allowed" disabled>
            Enviar enlace
        </button>

        <div class="auth-footer" style="margin-top:20px">
            <a href="login.php">← Volver a inicio de sesión</a>
        </div>
    </div>
</div>
</body>
</html>
