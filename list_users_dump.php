<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=admisiones_udc;charset=utf8mb4','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query('SELECT id,email,nombres,apellidos,activo FROM usuarios ORDER BY id ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "TOTAL: ".count($rows)."\n";
    foreach ($rows as $r) {
        echo sprintf("#%d %s | %s %s | activo:%s\n", $r['id'], $r['email'], $r['nombres'], $r['apellidos'], $r['activo']);
    }
} catch (Throwable $e) {
    echo "ERROR: ".$e->getMessage()."\n";
}
