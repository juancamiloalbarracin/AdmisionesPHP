<?php
require_once __DIR__ . '/../config/bootstrap.php';
use UDC\SistemaAdmisiones\Utils\JwtHelper;

// If token not provided as arg, read the scripts/last_test_token.txt file
$token = $argv[1] ?? null;
if (!$token) {
    $path = __DIR__ . '/last_test_token.txt';
    if (file_exists($path)) {
        $token = trim(file_get_contents($path));
    }
}

if (!$token) {
    echo "USAGE: php inspect_token.php <token>\n";
    exit(1);
}

$info = JwtHelper::getTokenInfo($token);
echo json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
