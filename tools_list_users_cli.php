<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=admisiones_udc;charset=utf8mb4','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query('SELECT id,email,nombres,apellidos,activo,fecha_registro,ultimo_acceso FROM usuarios ORDER BY id DESC LIMIT 50');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "TOTAL: ".count($rows)."\n";
    foreach ($rows as $r) {
        echo sprintf("#%d %s | %s %s | activo:%s | reg:%s | last:%s\n",
            $r['id'], $r['email'], $r['nombres'], $r['apellidos'],
            $r['activo'], $r['fecha_registro'] ?? '-', $r['ultimo_acceso'] ?? '-'
        );
    }
} catch (Exception $e) {
    echo "ERROR: ".$e->getMessage()."\n";
}
