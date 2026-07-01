<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$accessToken = 'APP_USR-7272894554891156-070103-614f6dfb34e271a73de7140de63e2eb5-1306973285';

$input = json_decode(file_get_contents('php://input'), true);
$producto = $input['producto'] ?? 'paquete_basico';

$precios = [
    'paquete_basico'     => ['title' => 'Paquete Básico - Página Web',      'price' => 1499],
    'paquete_profesional' => ['title' => 'Paquete Profesional - Sitio Web',  'price' => 2999],
    'paquete_premium'    => ['title' => 'Paquete Premium - Tienda Online',   'price' => 5999],
    'paquete_demo'       => ['title' => 'Demo - Pago de Prueba',             'price' => 10],
    'personalizado'      => ['title' => 'Cotización Personalizada',          'price' => 3999],
];

if (!isset($precios[$producto])) {
    http_response_code(400);
    echo json_encode(['error' => 'Producto no válido']);
    exit;
}

$item = $precios[$producto];

$data = [
    'items' => [[
        'title'       => $item['title'],
        'quantity'    => 1,
        'currency_id' => 'MXN',
        'unit_price'  => (float)$item['price'],
    ]],
    'back_urls' => [
        'success' => 'https://' . $_SERVER['HTTP_HOST'] . '/pago_exitoso.html',
        'failure' => 'https://' . $_SERVER['HTTP_HOST'] . '/pago_fallido.html',
        'pending' => 'https://' . $_SERVER['HTTP_HOST'] . '/pago_pendiente.html',
    ],
    'auto_return' => 'approved',
    'notification_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/ipn.php',
    'statement_descriptor' => 'NEVAS XMABEN',
    'metadata' => [
        'source' => 'nevas_xmaben',
        'seller' => 'Victor Ontiveros',
    ],
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken,
    ],
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$resultado = json_decode($response, true);

if ($httpCode === 201 && isset($resultado['init_point'])) {
    echo json_encode([
        'success' => true,
        'init_point' => $resultado['init_point'],
        'preference_id' => $resultado['id'],
        'producto' => $item['title'],
        'precio' => $item['price'],
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al crear preferencia MP',
        'http_code' => $httpCode,
        'response' => $resultado['message'] ?? $response,
    ]);
}
