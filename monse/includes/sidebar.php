<?php
// Requiere sesión activa
if (session_status() === PHP_SESSION_NONE) session_start();
$base = '../';
$userName = $_SESSION['nombre'] ?? 'Usuario';
$userInitial = strtoupper(substr($userName, 0, 1));
$userFoto = $_SESSION['foto'] ?? null;
?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">⏱️</div>
        <span>TimeFlow</span>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-title">Principal</span>

        <a href="<?= $base ?>dashboard/index.php" class="nav-item">
            <div class="icon">🏠</div>
            Dashboard
        </a>
        <a href="<?= $base ?>tareas/index.php" class="nav-item">
            <div class="icon">✅</div>
            Tareas
        </a>
        <a href="<?= $base ?>calendario/index.php" class="nav-item">
            <div class="icon">📅</div>
            Calendario
        </a>

        <span class="nav-section-title">Productividad</span>

        <a href="<?= $base ?>pomodoro/index.php" class="nav-item">
            <div class="icon">🍅</div>
            Modo Estudio
        </a>
        <a href="<?= $base ?>estadisticas/index.php" class="nav-item">
            <div class="icon">📊</div>
            Estadísticas
        </a>

        <span class="nav-section-title">Cuenta</span>

        <a href="<?= $base ?>perfil/index.php" class="nav-item">
            <div class="icon">👤</div>
            Perfil
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-mini" onclick="window.location='<?= $base ?>perfil/index.php'">
            <div class="avatar">
                <?php if ($userFoto): ?>
                    <img src="<?= $base ?>assets/img/fotos/<?= htmlspecialchars($userFoto) ?>" alt="Foto">
                <?php else: ?>
                    <?= $userInitial ?>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                <div class="user-role">TimeFlow User</div>
            </div>
        </div>
        <a href="<?= $base ?>auth/logout.php" class="nav-item" style="margin-top:8px;color:rgba(255,100,100,0.7)">
            <div class="icon">🚪</div>
            Cerrar sesión
        </a>
    </div>
</aside>