<?php
function conectar() {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbName = 'timeflow';

    $db = new mysqli($host, $user, $pass, $dbName);
    if ($db->connect_errno) {
        die('Error de conexión: ' . $db->connect_error);
    }
    $db->set_charset('utf8mb4');
    return $db;
}
