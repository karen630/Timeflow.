<?php
session_start();
if (!empty($_SESSION['usuario_id'])) {
    header('Location: dashboard/index.php');
} else {
    header('Location: auth/login.php');
}
exit;
?>

<div class="app-layout">
    <?php require_once '../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div>
                <div class="page-title">
                    👤 Perfil
                    <span>Administra tu información personal</span>
                </div>
            </div>
            <div class="topbar-right">
                <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open');document.getElementById('sidebarOverlay').classList.toggle('open')">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>

        <?php if ($msg): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if ($err): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:320px 1fr;gap:24px;align-items:start">
            <!-- Card de avatar/resumen -->
            <div class="card" style="text-align:center">
                <!-- Avatar -->
                <div style="width:100px;height:100px;border-radius:50%;background:var(--primary-gradient);margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:40px;font-weight:800;color:#fff;overflow:hidden;box-shadow:0 8px 24px rgba(74,144,217,0.3)" id="avatarPreview">
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
                        <div style="font-size:22px;font-weight:800;color:var(--primary)"><?= (int)$tot['t'] ?></div>
                        <div style="font-size:10px;color:var(--text-light);font-weight:600;text-transform:uppercase">Tareas</div>
                    </div>
                    <div>
                        <div style="font-size:22px;font-weight:800;color:var(--success)"><?= (int)($stats['tareas_completadas']??0) ?></div>
                        <div style="font-size:10px;color:var(--text-light);font-weight:600;text-transform:uppercase">Hechas</div>
                    </div>
                    <div>
                        <div style="font-size:22px;font-weight:800;color:var(--warning)"><?= (int)($stats['racha_dias']??0) ?> 🔥</div>
                        <div style="font-size:10px;color:var(--text-light);font-weight:600;text-transform:uppercase">Racha</div>
                    </div>
                </div>

                <!-- Metas principales -->
                <div style="margin-top:20px;text-align:left">
                    <div style="font-size:12px;font-weight:700;color:var(--text-mid);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px">Metas</div>
                    <?php
                    $metas = ['Estudios'=>'#4A90D9','Ejercicio'=>'#27AE60','Trabajo'=>'#9B59B6'];
                    foreach ($metas as $m=>$c): ?>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;font-size:13px;font-weight:600;color:var(--text-mid)">
                        <div style="width:10px;height:10px;border-radius:3px;background:<?=$c?>"></div>
                        <?=$m?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Formularios -->
            <div style="display:flex;flex-direction:column;gap:20px">
                <!-- Editar info -->
                <div class="card">
                    <h3 class="card-title" style="margin-bottom:20px">✏️ Editar Información</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_info">
                        <div class="form-group">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="nombre" class="form-control"
                                   value="<?= htmlspecialchars($user['nombre']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control"
                                   value="<?= htmlspecialchars($user['correo']) ?>" disabled
                                   style="opacity:0.6;cursor:not-allowed">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Foto de perfil (opcional)</label>
                            <input type="file" name="foto" class="form-control"
                                   accept="image/*" onchange="previewFoto(this)">
                            <small style="font-size:11px;color:var(--text-light)">JPG, PNG, WEBP · Máx 2 MB</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </form>
                </div>

                <!-- Cambiar contraseña -->
                <div class="card">
                    <h3 class="card-title" style="margin-bottom:20px">🔒 Cambiar Contraseña</h3>
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

                <!-- Peligro -->
                <div class="card" style="border-color:rgba(231,76,60,0.2)">
                    <h3 class="card-title" style="color:var(--danger);margin-bottom:16px">⚠️ Zona de peligro</h3>
                    <p style="font-size:13px;color:var(--text-light);margin-bottom:16px">Acciones irreversibles para tu cuenta.</p>
                    <a href="../auth/logout.php" class="btn btn-danger btn-sm">🚪 Cerrar sesión</a>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="../assets/js/app.js"></script>
<script>
document.getElementById('sidebarOverlay').addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
});

function previewFoto(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const prev = document.getElementById('avatarPreview');
        prev.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover" alt="Preview">`;
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
</body>
</html>