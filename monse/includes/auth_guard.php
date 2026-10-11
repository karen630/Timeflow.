<?php
// Incluir en todas las páginas protegidas
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['usuario_id'])) {
    $scriptPath = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', __FILE__));
    $base = str_repeat('../', max(0, substr_count($scriptPath, '/') - 1));
    header('Location: ' . $base . 'auth/login.php');
    exit;
}