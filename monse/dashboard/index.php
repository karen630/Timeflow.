<?php
require_once '../includes/auth_guard.php';
require_once '../config/conexion.php';
$uid = $_SESSION['usuario_id'];
$db = conectar();

$categoryColors = [
    'Estudio' => '#4A90D9',
    'Trabajo' => '#9B59B6',
    'Ejercicio' => '#27AE60',
    'Social' => '#E91E8C',
    'Hábito' => '#F39C12',
];

// Resumen de tareas
$stmt = $db->prepare('SELECT COUNT(*) as total, SUM(completada=1) as completadas, SUM(completada=0) as pendientes FROM tareas WHERE usuario_id = ?');
$stmt->bind_param('i', $uid); $stmt->execute();
$res = $stmt->get_result()->fetch_assoc(); $stmt->close();

// Próximas tareas
$stmt = $db->prepare('SELECT * FROM tareas WHERE usuario_id = ? AND fecha >= CURDATE() ORDER BY fecha ASC, hora ASC LIMIT 4');
$stmt->bind_param('i', $uid); $stmt->execute();
$proximas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
$db->close();

$pageTitle = 'Dashboard';
require_once '../includes/header.php';
?>
<div class="app-layout">
    <?php require_once '../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div>
                <div class="page-title">Hola <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?> 👋
                    <span>Bienvenido a tu panel de productividad</span>
                </div>
            </div>
            <div class="topbar-right">
                <button class="hamburger"> <span></span><span></span><span></span></button>
            </div>
        </div>

        <div class="cards-grid">
            <div class="card card-small">
                <div class="card-title">Tareas totales</div>
                <div class="card-value"><?= (int)$res['total'] ?></div>
                <div class="card-note">Incluye tareas completadas y pendientes.</div>
            </div>
            <div class="card card-small">
                <div class="card-title">Pendientes</div>
                <div class="card-value"><?= (int)$res['pendientes'] ?></div>
                <div class="card-note">Organiza tu día claramente.</div>
            </div>
            <div class="card card-small">
                <div class="card-title">Completadas</div>
                <div class="card-value"><?= (int)$res['completadas'] ?></div>
                <div class="card-note">Revisa tu progreso semanal.</div>
            </div>
        </div>

        <div class="section-block">
            <div class="section-header">
                <div>
                    <h2>Próximas tareas</h2>
                    <p>Tu lista de tareas programadas para los próximos días.</p>
                </div>
                <a href="../tareas/index.php" class="btn btn-outline">Ver tareas</a>
            </div>

            <?php if (count($proximas) === 0): ?>
                <div class="empty-card">
                    <p>No tienes tareas próximas. Agrega una nueva tarea para organizar tu agenda.</p>
                    <a href="../tareas/index.php" class="btn btn-primary">Agregar tarea</a>
                </div>
            <?php else: ?>
                <div class="task-list">
                    <?php foreach ($proximas as $task): ?>
                    <div class="task-item <?= $task['completada'] ? 'task-completed' : '' ?>">
                        <div>
                            <?php $color = $categoryColors[$task['categoria']] ?? '#4A90D9'; ?>
                            <div class="task-tag" style="background: <?= $color ?>22; width: 10px; height: 10px; border-radius: 50%; margin-right: 12px; float:left; margin-top:8px;"></div>
                            <div style="margin-left: 18px;">
                                <h3><?= htmlspecialchars($task['titulo']) ?></h3>
                                <p><?= htmlspecialchars($task['descripcion']) ?></p>
                            </div>
                        </div>
                        <div class="task-meta">
                            <span><?= $task['fecha'] ? date('d M', strtotime($task['fecha'])) : 'Sin fecha' ?></span>
                            <span><?= $task['hora'] ? date('h:i a', strtotime($task['hora'])) : 'Hora libre' ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php require_once '../includes/footer.php'; ?>
