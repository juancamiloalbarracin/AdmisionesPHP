<?php
// Test simple para verificar rutas de la API
echo "Testing API endpoints...\n\n";

$baseUrl = 'http://localhost:8000/api';

$endpoints = [
    '/info-personal/get',
    '/info-academica/get',
    '/solicitudes'
];

foreach ($endpoints as $endpoint) {
    $url = $baseUrl . $endpoint;
    echo "Testing: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json',
            'timeout' => 5
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ FAILED - Could not connect\n";
    } else {
        $httpCode = explode(' ', $http_response_header[0])[1];
        echo "✅ Response Code: $httpCode\n";
        if ($httpCode == '404') {
            echo "   Content: " . substr($response, 0, 100) . "...\n";
        }
    }
    echo "\n";
}
?>
