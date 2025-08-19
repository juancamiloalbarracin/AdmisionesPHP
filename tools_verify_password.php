<?php
$pdo = new PDO('mysql:host=localhost;dbname=admisiones_udc;charset=utf8mb4','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$email = $argv[1] ?? 'user1@gmail.com';
$pass = $argv[2] ?? 'user1234';
$stmt = $pdo->prepare('SELECT id,email,password_hash FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) { echo "NOT_FOUND\n"; exit(0);} 
echo $row['email'] . "\n";
$ok = password_verify($pass, $row['password_hash']);
echo $ok ? "OK\n" : "FAIL\n";
