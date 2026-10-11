<?php
require_once '../includes/auth_guard.php';
require_once '../config/conexion.php';
$uid = $_SESSION['usuario_id'];
$db = conectar();

$stmt = $db->prepare('SELECT tareas_completadas, horas_estudio, racha_dias FROM estadisticas WHERE usuario_id = ?');
$stmt->bind_param('i', $uid); $stmt->execute();
$stats = $stmt->get_result()->fetch_assoc() ?? ['tareas_completadas'=>0,'horas_estudio'=>0,'racha_dias'=>0];
$stmt->close();

$stmt = $db->prepare('SELECT COUNT(*) as total FROM tareas WHERE usuario_id = ?');
$stmt->bind_param('i', $uid); $stmt->execute();
$totalTasks = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();
$db->close();

$pageTitle = 'Estadísticas';
require_once '../includes/header.php';
?>
<div class="app-layout">
    <?php require_once '../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div>
                <div class="page-title">Estadísticas
                    <span>Observa tu progreso con información clara.</span>
                </div>
            </div>
            <div class="topbar-right">
                <button class="hamburger"> <span></span><span></span><span></span></button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= (int)$stats['tareas_completadas'] ?></div>
                <div class="stat-label">Tareas completadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= (int)$stats['horas_estudio'] ?></div>
                <div class="stat-label">Minutos de estudio</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= (int)$stats['racha_dias'] ?></div>
                <div class="stat-label">Racha de días</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= (int)$totalTasks ?></div>
                <div class="stat-label">Tareas totales</div>
            </div>
        </div>

        <div class="section-block">
            <div class="card">
                <h2 style="margin-bottom:14px;">Gráficas simples</h2>
                <div style="display:grid;grid-template-columns:1fr;gap:18px;">
                    <div style="background:var(--bg);padding:18px;border-radius:var(--radius-sm);border:1px solid var(--border);">
                        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-mid);margin-bottom:12px;">
                            <span>Tareas completadas</span><span><?= (int)$stats['tareas_completadas'] ?></span>
                        </div>
                        <div style="height:12px;background:var(--border);border-radius:999px;overflow:hidden;">
                            <div style="width:<?= min(100, ($totalTasks ? $stats['tareas_completadas'] / $totalTasks * 100 : 0)) ?>%;height:100%;background:var(--primary);"></div>
                        </div>
                    </div>
                    <div style="background:var(--bg);padding:18px;border-radius:var(--radius-sm);border:1px solid var(--border);">
                        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-mid);margin-bottom:12px;">
                            <span>Horas de estudio</span><span><?= (int)$stats['horas_estudio'] ?></span>
                        </div>
                        <div style="height:12px;background:var(--border);border-radius:999px;overflow:hidden;">
                            <div style="width:<?= min(100, $stats['horas_estudio'] / 200 * 100) ?>%;height:100%;background:var(--success);"></div>
                        </div>
                    </div>
                    <div style="background:var(--bg);padding:18px;border-radius:var(--radius-sm);border:1px solid var(--border);">
                        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-mid);margin-bottom:12px;">
                            <span>Días de racha</span><span><?= (int)$stats['racha_dias'] ?></span>
                        </div>
                        <div style="height:12px;background:var(--border);border-radius:999px;overflow:hidden;">
                            <div style="width:<?= min(100, $stats['racha_dias'] / 30 * 100) ?>%;height:100%;background:var(--warning);"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require_once '../includes/footer.php'; ?>
