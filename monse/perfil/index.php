<?php
require_once '../includes/auth_guard.php';
require_once '../config/conexion.php';
$uid = $_SESSION['usuario_id'];
$db = conectar();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_info') {
        $nombre = trim($_POST['nombre'] ?? '');
        if (strlen($nombre) < 2) {
            $err = 'El nombre debe tener al menos 2 caracteres.';
        } else {
            $fotoNueva = null;
            if (!empty($_FILES['foto']['tmp_name'])) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (!in_array($ext, $allowed)) {
                    $err = 'Formato de imagen no permitido.';
                } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                    $err = 'La imagen no debe superar 2 MB.';
                } else {
                    $dir = '../assets/img/fotos/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $fname = 'user_' . $uid . '_' . time() . '.' . $ext;
                    move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $fname);
                    $fotoNueva = $fname;
                }
            }
            if (!$err) {
                if ($fotoNueva) {
                    $stmt = $db->prepare('UPDATE usuarios SET nombre=?, foto=? WHERE id=?');
                    $stmt->bind_param('ssi', $nombre, $fotoNueva, $uid);
                } else {
                    $stmt = $db->prepare('UPDATE usuarios SET nombre=? WHERE id=?');
                    $stmt->bind_param('si', $nombre, $uid);
                }
                $stmt->execute();
                $stmt->close();
                $_SESSION['nombre'] = $nombre;
                if ($fotoNueva) {
                    $_SESSION['foto'] = $fotoNueva;
                }
                $msg = 'Perfil actualizado correctamente.';
            }
        }
    } elseif ($action === 'change_pass') {
        $actual = $_POST['actual'] ?? '';
        $nueva = $_POST['nueva'] ?? '';
        $confirmar = $_POST['confirmar'] ?? '';
        if (!$actual || !$nueva || !$confirmar) {
            $err = 'Completa todos los campos de contraseña.';
        } elseif (strlen($nueva) < 6) {
            $err = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } elseif ($nueva !== $confirmar) {
            $err = 'Las contraseñas no coinciden.';
        } else {
            $stmt = $db->prepare('SELECT password FROM usuarios WHERE id=?');
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!password_verify($actual, $row['password'])) {
                $err = 'Contraseña actual incorrecta.';
            } else {
                $hash = password_hash($nueva, PASSWORD_BCRYPT);
                $stmt = $db->prepare('UPDATE usuarios SET password=? WHERE id=?');
                $stmt->bind_param('si', $hash, $uid);
                $stmt->execute();
                $stmt->close();
                $msg = 'Contraseña actualizada correctamente.';
            }
        }
    }
}

$stmt = $db->prepare('SELECT * FROM usuarios WHERE id=?');
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $db->prepare('SELECT tareas_completadas, horas_estudio, racha_dias FROM estadisticas WHERE usuario_id=?');
$stmt->bind_param('i', $uid);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc() ?? ['tareas_completadas'=>0,'horas_estudio'=>0,'racha_dias'=>0];
$stmt->close();

$stmt = $db->prepare('SELECT COUNT(*) as total FROM tareas WHERE usuario_id=?');
$stmt->bind_param('i', $uid);
$stmt->execute();
$tot = $stmt->get_result()->fetch_assoc();
$stmt->close();
$db->close();

$pageTitle = 'Perfil';
require_once '../includes/header.php';
$initial = strtoupper(substr($user['nombre'], 0, 1));
?>
<div class="app-layout">
    <?php require_once '../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div>
                <div class="page-title">Perfil
                    <span>Gestiona tus ajustes y datos de cuenta.</span>
                </div>
            </div>
            <div class="topbar-right">
                <button class="hamburger"> <span></span><span></span><span></span></button>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if ($err): ?>
            <div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:320px 1fr;gap:24px;align-items:start;">
            <div class="card" style="text-align:center;">
                <div style="width:100px;height:100px;border-radius:50%;background:var(--primary-gradient);margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:40px;font-weight:800;color:#fff;overflow:hidden;box-shadow:0 8px 24px rgba(74,144,217,0.3)">
                    <?php if ($user['foto']): ?>
                        <img src="../assets/img/fotos/<?= htmlspecialchars($user['foto']) ?>" style="width:100%;height:100%;object-fit:cover" alt="Foto">
                    <?php else: ?>
                        <?= $initial ?>
                    <?php endif; ?>
                </div>
                <h2 style="font-family:var(--font-head);font-size:20px;font-weight:800;margin-bottom:4px"><?= htmlspecialchars($user['nombre']) ?></h2>
                <p style="font-size:13px;color:var(--text-light);margin-bottom:20px"><?= htmlspecialchars($user['correo']) ?></p>
                <p style="font-size:12px;color:var(--text-light)">Miembro desde <?= date('d M Y', strtotime($user['fecha_registro'])) ?></p>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:24px;padding-top:20px;border-top:1px solid var(--border)">
                    <div>
                        <div style="font-size:22px;font-weight:800;color:var(--primary)"><?= (int)$tot['total'] ?></div>
                        <div style="font-size:10px;color:var(--text-light);font-weight:600;text-transform:uppercase">Tareas</div>
                    </div>
                    <div>
                        <div style="font-size:22px;font-weight:800;color:var(--success)"><?= (int)$stats['tareas_completadas'] ?></div>
                        <div style="font-size:10px;color:var(--text-light);font-weight:600;text-transform:uppercase">Completadas</div>
                    </div>
                    <div>
                        <div style="font-size:22px;font-weight:800;color:var(--warning)"><?= (int)$stats['racha_dias'] ?></div>
                        <div style="font-size:10px;color:var(--text-light);font-weight:600;text-transform:uppercase">Racha</div>
                    </div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:20px;">
                <div class="card">
                    <h3 class="card-title" style="margin-bottom:20px">Editar información</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_info">
                        <div class="form-group">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($user['nombre']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['correo']) ?>" disabled style="opacity:0.6;cursor:not-allowed;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Foto de perfil (opcional)</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </form>
                </div>

                <div class="card">
                    <h3 class="card-title" style="margin-bottom:20px">Cambiar contraseña</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_pass">
                        <div class="form-group">
                            <label class="form-label">Contraseña actual</label>
                            <input type="password" name="actual" class="form-control" placeholder="Tu contraseña actual">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" name="nueva" class="form-control" placeholder="Mín. 6 caracteres">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirmar</label>
                                <input type="password" name="confirmar" class="form-control" placeholder="Repite la contraseña">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline">Actualizar contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require_once '../includes/footer.php'; ?>
