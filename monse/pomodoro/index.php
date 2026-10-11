<?php
require_once '../includes/auth_guard.php';
require_once '../config/conexion.php';
$uid = $_SESSION['usuario_id'];
$db = conectar();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $minutos = (int)($_POST['minutos'] ?? 25);
    if ($minutos < 5) { $minutos = 5; }
    $stmt = $db->prepare('INSERT INTO sesiones_pomodoro (usuario_id, duracion_minutos) VALUES (?, ?)');
    $stmt->bind_param('ii', $uid, $minutos);
    $stmt->execute();
    $stmt->close();

    $stmt = $db->prepare('UPDATE estadisticas SET horas_estudio = horas_estudio + ? WHERE usuario_id = ?');
    $stmt->bind_param('ii', $minutos, $uid);
    $stmt->execute();
    $stmt->close();
    $message = 'Sesión guardada. Has sumado ' . $minutos . ' minutos de estudio.';
}

$stmt = $db->prepare('SELECT tareas_completadas, horas_estudio, racha_dias FROM estadisticas WHERE usuario_id = ?');
$stmt->bind_param('i', $uid); $stmt->execute();
$stats = $stmt->get_result()->fetch_assoc() ?? ['tareas_completadas'=>0,'horas_estudio'=>0,'racha_dias'=>0];
$stmt->close();

$stmt = $db->prepare('SELECT * FROM sesiones_pomodoro WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 5');
$stmt->bind_param('i', $uid);
$stmt->execute();
$sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

$pageTitle = 'Modo Estudio';
require_once '../includes/header.php';
?>
<div class="app-layout">
    <?php require_once '../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div>
                <div class="page-title">Modo Estudio 🍅
                    <span>Registra tus sesiones y controla tu tiempo de estudio.</span>
                </div>
            </div>
            <div class="topbar-right">
                <button class="hamburger"> <span></span><span></span><span></span></button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="section-block">
            <div class="section-header">
                <div>
                    <h2>Temporizador Pomodoro</h2>
                    <p>Registra sesiones de estudio y suma tiempo a tus estadísticas.</p>
                </div>
                <a href="../dashboard/index.php" class="btn btn-outline">Volver al panel</a>
            </div>

            <div class="cards-grid">
                <div class="card stat-card">
                    <div class="stat-value"><?= (int)$stats['horas_estudio'] ?></div>
                    <div class="stat-label">Minutos de estudio</div>
                </div>
                <div class="card stat-card">
                    <div class="stat-value"><?= (int)$stats['tareas_completadas'] ?></div>
                    <div class="stat-label">Tareas completadas</div>
                </div>
                <div class="card stat-card">
                    <div class="stat-value"><?= (int)$stats['racha_dias'] ?></div>
                    <div class="stat-label">Racha diaria</div>
                </div>
            </div>

            <div class="card" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                <div>
                    <h3>Guardar sesión</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Minutos de estudio</label>
                            <input type="number" name="minutos" value="25" min="5" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar sesión</button>
                    </form>
                </div>
                <div style="background:var(--primary-light);border-radius:var(--radius);padding:24px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;">
                    <div style="font-size:40px;">🍅</div>
                    <p style="color:var(--text-dark);font-weight:700;margin-top:16px;">Potencia tus bloques de concentración con la técnica Pomodoro.</p>
                </div>
            </div>

            <div class="card">
                <h3>Últimas sesiones</h3>
                <?php if (empty($sessions)): ?>
                    <div class="empty-card">Aún no tienes sesiones guardadas.</div>
                <?php else: ?>
                    <div class="task-list">
                        <?php foreach ($sessions as $session): ?>
                            <div class="task-item">
                                <div>
                                    <h3>Sesión de <?= (int)$session['duracion_minutos'] ?> min</h3>
                                    <div class="task-meta"><?= date('d/m/Y H:i', strtotime($session['fecha'])) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<?php require_once '../includes/footer.php'; ?>
