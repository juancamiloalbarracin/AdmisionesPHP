<?php
// Simple database test
try {
    $conn = new PDO(
        "mysql:host=localhost;dbname=admisiones_udc;charset=utf8mb4",
        "root", 
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Conexión exitosa\n";
    
    // Check users
    $stmt = $conn->query("SELECT email, nombres, apellidos FROM usuarios LIMIT 5");
    $users = $stmt->fetchAll();
    
    echo "👥 Usuarios encontrados: " . count($users) . "\n";
    foreach ($users as $user) {
        echo "- {$user['email']} ({$user['nombres']} {$user['apellidos']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
