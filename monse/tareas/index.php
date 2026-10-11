
<?php
require_once '../includes/auth_guard.php';
require_once '../config/conexion.php';

$uid = $_SESSION['usuario_id'];
$db = conectar();

// Colores de las categorías
$categoryColors = [
    'Estudio'   => '#4A90D9',
    'Trabajo'   => '#9B59B6',
    'Ejercicio' => '#27AE60',
    'Social'    => '#E91E8C',
    'Hábito'    => '#F39C12',
];

// Colores suaves para las prioridades
$priorityColors = [
    'Alta'  => [
        'background' => '#FCE4EC',
        'text'       => '#AD526F',
        'border'     => '#F4A6B7',
    ],
    'Media' => [
        'background' => '#EFE8FF',
        'text'       => '#7052B5',
        'border'     => '#B69AFE',
    ],
    'Baja'  => [
        'background' => '#E5F0FA',
        'text'       => '#426D96',
        'border'     => '#A7C7E7',
    ],
];

$message = '';
$messageType = 'success';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Crear una tarea
    if (
        isset($_POST['action']) &&
        $_POST['action'] === 'save_task'
    ) {
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        $categoria = $_POST['categoria'] ?? 'Estudio';
        $prioridad = $_POST['prioridad'] ?? 'Media';

        $fecha = !empty($_POST['fecha'])
            ? $_POST['fecha']
            : null;

        $hora = !empty($_POST['hora'])
            ? $_POST['hora']
            : null;

        // Validar categoría y prioridad
        if (!array_key_exists($categoria, $categoryColors)) {
            $categoria = 'Estudio';
        }

        if (!array_key_exists($prioridad, $priorityColors)) {
            $prioridad = 'Media';
        }

        if ($titulo === '') {
            $message = 'El título es obligatorio.';
            $messageType = 'error';
        } else {
            $stmt = $db->prepare(
                'INSERT INTO tareas
                (usuario_id, titulo, descripcion, categoria,
                 prioridad, fecha, hora)
                VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->bind_param(
                'issssss',
                $uid,
                $titulo,
                $descripcion,
                $categoria,
                $prioridad,
                $fecha,
                $hora
            );

            if ($stmt->execute()) {
                $message = 'Tarea creada correctamente.';
            } else {
                $message = 'No se pudo crear la tarea.';
                $messageType = 'error';
            }

            $stmt->close();
        }
    }

    // Marcar tarea como completada o pendiente
    if (
        isset($_POST['action'], $_POST['task_id']) &&
        $_POST['action'] === 'toggle_complete'
    ) {
        $taskId = (int) $_POST['task_id'];

        $stmt = $db->prepare(
            'UPDATE tareas
             SET completada = NOT completada
             WHERE id = ? AND usuario_id = ?'
        );

        $stmt->bind_param('ii', $taskId, $uid);
        $stmt->execute();
        $stmt->close();

        $message = 'Estado de la tarea actualizado.';
    }

    // Eliminar tarea
    if (
        isset($_POST['action'], $_POST['task_id']) &&
        $_POST['action'] === 'delete_task'
    ) {
        $taskId = (int) $_POST['task_id'];

        $stmt = $db->prepare(
            'DELETE FROM tareas
             WHERE id = ? AND usuario_id = ?'
        );

        $stmt->bind_param('ii', $taskId, $uid);
        $stmt->execute();
        $stmt->close();

        $message = 'Tarea eliminada.';
    }
}

// Obtener las tareas del usuario
$stmt = $db->prepare(
    'SELECT *
     FROM tareas
     WHERE usuario_id = ?
     ORDER BY fecha DESC, hora DESC, id DESC'
);

$stmt->bind_param('i', $uid);
$stmt->execute();

$tareas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$db->close();

$pageTitle = 'Tareas';
require_once '../includes/header.php';
?>

<div class="app-layout">

    <?php require_once '../includes/sidebar.php'; ?>

    <main class="main-content">

        <!-- Barra superior -->
        <div class="topbar">
            <div>
                <div class="page-title">
                    Tareas
                    <span>
                        Gestiona tus actividades y marcas de progreso.
                    </span>
                </div>
            </div>

            <div class="topbar-right">
                <button class="hamburger" type="button">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if ($message !== ''): ?>
            <div class="alert <?= $messageType === 'error'
                ? 'alert-danger'
                : 'alert-success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Contenido principal -->
        <div class="section-block task-layout">

            <!-- Formulario para crear tareas -->
            <div class="card">
                <h2>Crear nueva tarea</h2>

                <form method="POST">

                    <input
                        type="hidden"
                        name="action"
                        value="save_task"
                    >

                    <div class="form-group">
                        <label class="form-label">Título</label>

                        <input
                            type="text"
                            name="titulo"
                            class="form-control"
                            placeholder="Ej. Estudiar matemáticas"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descripción</label>

                        <textarea
                            name="descripcion"
                            class="form-control"
                            rows="4"
                            placeholder="Notas breves"
                        ></textarea>
                    </div>

                    <div class="form-row">

                        <div class="form-group">
                            <label class="form-label">Categoría</label>

                            <select
                                name="categoria"
                                class="form-control"
                            >
                                <option value="Estudio">Estudio</option>
                                <option value="Trabajo">Trabajo</option>
                                <option value="Ejercicio">Ejercicio</option>
                                <option value="Social">Social</option>
                                <option value="Hábito">Hábito</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Prioridad</label>

                            <select
                                name="prioridad"
                                class="form-control"
                            >
                                <option value="Media">Media</option>
                                <option value="Baja">Baja</option>
                                <option value="Alta">Alta</option>
                            </select>
                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group">
                            <label class="form-label">Fecha</label>

                            <input
                                type="date"
                                name="fecha"
                                class="form-control"
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Hora</label>

                            <input
                                type="time"
                                name="hora"
                                class="form-control"
                            >
                        </div>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Guardar tarea
                    </button>

                </form>
            </div>

            <!-- Lista de tareas -->
            <div class="card">
                <h2>Lista de tareas</h2>

                <?php if (empty($tareas)): ?>

                    <div class="empty-card">
                        <p>
                            No tienes tareas registradas todavía.
                            Crea tu primera tarea para comenzar.
                        </p>
                    </div>

                <?php else: ?>

                    <div class="task-list">

                        <?php foreach ($tareas as $task): ?>

                            <?php
                            $categoriaActual = $task['categoria'];
                            $prioridadActual = $task['prioridad'];

                            $colorCategoria =
                                $categoryColors[$categoriaActual]
                                ?? '#B69AFE';

                            $coloresPrioridad =
                                $priorityColors[$prioridadActual]
                                ?? $priorityColors['Media'];
                            ?>

                            <div class="task-item
                                <?= $task['completada']
                                    ? 'task-completed'
                                    : '' ?>">

                                <div class="task-description">

                                    <!-- Indicador de categoría -->
                                    <span
                                        class="task-category-dot"
                                        style="background-color:
                                            <?= htmlspecialchars(
                                                $colorCategoria
                                            ) ?>;"
                                    ></span>

                                    <div class="task-content">

                                        <h3>
                                            <?= htmlspecialchars(
                                                $task['titulo']
                                            ) ?>
                                        </h3>

                                        <p>
                                            <?= htmlspecialchars(
                                                $task['descripcion'] ?? ''
                                            ) ?>
                                        </p>

                                        <div class="task-metadata">

                                            <span>
                                                <?= htmlspecialchars(
                                                    $categoriaActual
                                                ) ?>
                                            </span>

                                            <span
                                                class="priority-badge"
                                                style="
                                                    background-color:
                                                        <?= $coloresPrioridad['background'] ?>;
                                                    color:
                                                        <?= $coloresPrioridad['text'] ?>;
                                                    border: 1px solid
                                                        <?= $coloresPrioridad['border'] ?>;
                                                "
                                            >
                                                <?= htmlspecialchars(
                                                    $prioridadActual
                                                ) ?>
                                            </span>

                                            <span>
                                                <?= !empty($task['fecha'])
                                                    ? date(
                                                        'd/m/Y',
                                                        strtotime($task['fecha'])
                                                    )
                                                    : 'Sin fecha' ?>
                                            </span>

                                            <?php if (!empty($task['hora'])): ?>
                                                <span>
                                                    <?= date(
                                                        'h:i a',
                                                        strtotime($task['hora'])
                                                    ) ?>
                                                </span>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                </div>

                                <!-- Acciones de la tarea -->
                                <div class="task-actions">

                                    <form
                                        method="POST"
                                        class="task-action-form"
                                    >
                                        <input
                                            type="hidden"
                                            name="action"
                                            value="toggle_complete"
                                        >

                                        <input
                                            type="hidden"
                                            name="task_id"
                                            value="<?= (int) $task['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-link"
                                        >
                                            <?= $task['completada']
                                                ? 'Marcar como pendiente'
                                                : 'Completar' ?>
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        class="task-action-form"
                                        onsubmit="return confirm('¿Seguro que deseas eliminar esta tarea?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete_task"
                                        >

                                        <input
                                            type="hidden"
                                            name="task_id"
                                            value="<?= (int) $task['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-link btn-danger"
                                        >
                                            Eliminar
                                        </button>
                                    </form>

                                </div>
                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<!-- Estilos específicos de tareas -->
<style>
.task-layout {
    display: grid;
    grid-template-columns: 1fr 1.8fr;
    gap: 24px;
    align-items: start;
}

.task-description {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.task-category-dot {
    display: inline-block;
    flex-shrink: 0;
    width: 10px;
    height: 10px;
    margin-top: 7px;
    border-radius: 50%;
}

.task-content {
    flex: 1;
    min-width: 0;
}

.task-content h3 {
    margin: 0 0 6px;
}

.task-content p {
    margin: 0 0 10px;
    overflow-wrap: anywhere;
}

.task-metadata {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}

.priority-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.task-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-top: 14px;
}

.task-action-form {
    display: inline-block;
    margin: 0;
}

.task-item {
    padding: 18px 0;
    border-bottom: 1px solid #EDEAF3;
}

.task-item:last-child {
    border-bottom: none;
}

.task-completed .task-content h3 {
    text-decoration: line-through;
    opacity: 0.6;
}

@media (max-width: 900px) {
    .task-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .task-actions {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
