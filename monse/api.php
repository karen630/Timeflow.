<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['usuario_id'])) {
    echo json_encode(['success'=>false,'error'=>'No autorizado']);
    exit;
}

require_once '../config/conexion.php';
$uid = (int)$_SESSION['usuario_id'];
$db  = conectar();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'list':
        $where = 'usuario_id = ?';
        $params = [$uid];
        $types  = 'i';
        if (!empty($_GET['fecha'])) {
            $where .= ' AND fecha = ?';
            $params[] = $_GET['fecha'];
            $types .= 's';
        }
        if (!empty($_GET['categoria'])) {
            $where .= ' AND categoria = ?';
            $params[] = $_GET['categoria'];
            $types .= 's';
        }
        $stmt = $db->prepare("SELECT * FROM tareas WHERE $where ORDER BY fecha ASC, hora ASC");
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        echo json_encode(['success'=>true, 'data'=>$stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
        break;

    case 'create':
        $titulo    = trim($_POST['titulo']    ?? '');
        $desc      = trim($_POST['descripcion'] ?? '');
        $categoria = $_POST['categoria'] ?? 'Estudio';
        $prioridad = $_POST['prioridad'] ?? 'Media';
        $fecha     = $_POST['fecha']     ?? null;
        $hora      = $_POST['hora']      ?? null;
        if (!$titulo) { echo json_encode(['success'=>false,'error'=>'El título es requerido']); break; }
        $cats = ['Estudio','Trabajo','Ejercicio','Social','Hábito'];
        $pris = ['Baja','Media','Alta'];
        if (!in_array($categoria,$cats)) $categoria='Estudio';
        if (!in_array($prioridad,$pris)) $prioridad='Media';
        $fecha = $fecha ?: null;
        $hora  = $hora  ?: null;
        $stmt  = $db->prepare('INSERT INTO tareas (usuario_id,titulo,descripcion,categoria,prioridad,fecha,hora) VALUES (?,?,?,?,?,?,?)');
        $stmt->bind_param('issssss', $uid,$titulo,$desc,$categoria,$prioridad,$fecha,$hora);
        if ($stmt->execute()) {
            echo json_encode(['success'=>true,'id'=>$db->insert_id]);
        } else {
            echo json_encode(['success'=>false,'error'=>'Error al guardar']);
        }
        break;

    case 'update':
        $id   = (int)($_POST['id'] ?? 0);
        $titulo    = trim($_POST['titulo']    ?? '');
        $desc      = trim($_POST['descripcion'] ?? '');
        $categoria = $_POST['categoria'] ?? 'Estudio';
        $prioridad = $_POST['prioridad'] ?? 'Media';
        $fecha     = $_POST['fecha']     ?: null;
        $hora      = $_POST['hora']      ?: null;
        if (!$id||!$titulo) { echo json_encode(['success'=>false,'error'=>'Datos incompletos']); break; }
        $stmt = $db->prepare('UPDATE tareas SET titulo=?,descripcion=?,categoria=?,prioridad=?,fecha=?,hora=? WHERE id=? AND usuario_id=?');
        $stmt->bind_param('ssssssii',$titulo,$desc,$categoria,$prioridad,$fecha,$hora,$id,$uid);
        echo json_encode(['success'=>$stmt->execute()]);
        break;

    case 'toggle':
        $id = (int)($_POST['id'] ?? 0);
        // Get current state
        $stmt = $db->prepare('SELECT completada FROM tareas WHERE id=? AND usuario_id=?');
        $stmt->bind_param('ii',$id,$uid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) { echo json_encode(['success'=>false]); break; }
        $new = $row['completada'] ? 0 : 1;
        $stmt = $db->prepare('UPDATE tareas SET completada=? WHERE id=? AND usuario_id=?');
        $stmt->bind_param('iii',$new,$id,$uid);
        $stmt->execute();
        // Update stats if newly completed
        if ($new) {
            $db->query("UPDATE estadisticas SET tareas_completadas = tareas_completadas + 1 WHERE usuario_id = $uid");
            // Racha de días
            $db->query("UPDATE estadisticas SET ultima_actividad = CURDATE(),
                racha_dias = CASE
                    WHEN ultima_actividad = CURDATE() - INTERVAL 1 DAY THEN racha_dias + 1
                    WHEN ultima_actividad = CURDATE() THEN racha_dias
                    ELSE 1
                END
                WHERE usuario_id = $uid");
        }
        echo json_encode(['success'=>true,'completada'=>(bool)$new]);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare('DELETE FROM tareas WHERE id=? AND usuario_id=?');
        $stmt->bind_param('ii',$id,$uid);
        echo json_encode(['success'=>$stmt->execute()]);
        break;

    case 'get':
        $id   = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $stmt = $db->prepare('SELECT * FROM tareas WHERE id=? AND usuario_id=?');
        $stmt->bind_param('ii',$id,$uid);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();
        echo json_encode($row ? ['success'=>true,'data'=>$row] : ['success'=>false]);
        break;

    default:
        echo json_encode(['success'=>false,'error'=>'Acción desconocida']);
}
$db->close();