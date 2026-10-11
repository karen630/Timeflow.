<?php
require_once '../includes/auth_guard.php';
require_once '../config/conexion.php';
$uid = $_SESSION['usuario_id'];
$db = conectar();

$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
if ($month < 1) { $month = 1; }
if ($month > 12) { $month = 12; }

$firstDay = strtotime("{$year}-{$month}-01");
$daysInMonth = (int)date('t', $firstDay);
$startWeekday = (int)date('w', $firstDay);
$startWeekday = $startWeekday === 0 ? 7 : $startWeekday;

$months = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];
$monthName = $months[sprintf('%02d', $month)];

$stmt = $db->prepare('SELECT * FROM tareas WHERE usuario_id = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ? ORDER BY fecha ASC, hora ASC');
$stmt->bind_param('iii', $uid, $month, $year);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

$byDate = [];
foreach ($tasks as $task) {
    if (!$task['fecha']) continue;
    $byDate[$task['fecha']][] = $task;
}

function isHoy($dateStr) {
    return $dateStr === date('Y-m-d');
}

$colors = [
    'Estudio' => '#4A90D9',
    'Trabajo' => '#9B59B6',
    'Ejercicio' => '#27AE60',
    'Social' => '#E91E8C',
    'Hábito' => '#F39C12',
];

$pageTitle = 'Calendario';
require_once '../includes/header.php';
?>
<div class="app-layout">
    <?php require_once '../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div>
                <div class="page-title">Calendario
                    <span>Vista mensual de tus tareas programadas.</span>
                </div>
            </div>
            <div class="topbar-right">
                <button class="hamburger"> <span></span><span></span><span></span></button>
            </div>
        </div>

        <div class="section-block">
            <div class="section-header">
                <div>
                    <h2><?= ucfirst($monthName) ?> <?= $year ?></h2>
                    <p>Selecciona un día para ver las tareas que ya tienes pendientes.</p>
                </div>
                <div>
                    <a class="btn btn-outline" href="?year=<?= $month === 1 ? $year - 1 : $year ?>&month=<?= $month === 1 ? 12 : $month - 1 ?>">Anterior</a>
                    <a class="btn btn-outline" href="?year=<?= $month === 12 ? $year + 1 : $year ?>&month=<?= $month === 12 ? 1 : $month + 1 ?>">Siguiente</a>
                </div>
            </div>

            <div class="card">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="color:var(--text-light);font-size:12px;text-transform:uppercase;letter-spacing:1px;">
                            <th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th><th>Vie</th><th>Sáb</th><th>Dom</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $day = 1;
                        $weekDay = $startWeekday;
                        while ($day <= $daysInMonth) {
                            echo '<tr>';
                            for ($col = 1; $col <= 7; $col++) {
                                if (($day === 1 && $col < $weekDay) || $day > $daysInMonth) {
                                    echo '<td style="padding:12px;border:1px solid var(--border);min-height:100px;background:var(--bg);"></td>';
                                } else {
                                    $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                    $events = $byDate[$dateKey] ?? [];
                                    $class = isHoy($dateKey) ? 'background:rgba(74,144,217,.08);border:1px solid rgba(74,144,217,.25);' : '';
                                    echo '<td style="vertical-align:top;padding:12px;border:1px solid var(--border);min-height:110px;' . $class . '">';
                                    echo '<strong style="display:block;margin-bottom:10px;color:var(--text-dark);">' . $day . '</strong>';
                                    foreach ($events as $event) {
                                        $color = $colors[$event['categoria']] ?? '#4A90D9';
                                        echo '<div style="margin-bottom:6px;padding:8px;border-radius:12px;background:' . $color . '22;color:' . $color . ';font-size:12px;line-height:1.3;">';
                                        echo htmlspecialchars($event['titulo']) . '<br><small>' . ($event['hora'] ? date('h:i a', strtotime($event['hora'])) : 'Sin hora') . '</small>';
                                        echo '</div>';
                                    }
                                    echo '</td>';
                                    $day++;
                                }
                            }
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php require_once '../includes/footer.php'; ?>
