<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=admisiones_udc;charset=utf8mb4','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $rows = $pdo->query('SELECT id,email,nombres,apellidos,activo,fecha_registro,ultimo_acceso FROM usuarios ORDER BY id DESC LIMIT 50')->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($rows, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error'=>$e->getMessage()]);
}
