<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$accessToken = 'APP_USR-7272894554891156-070103-614f6dfb34e271a73de7140de63e2eb5-1306973285';

$input = json_decode(file_get_contents('php://input'), true);

$plans = [
    'basico'      => ['title' => 'Sitio Web Básico',       'price' => 1500],
    'profesional' => ['title' => 'Sitio Web Profesional',   'price' => 3000],
    'premium'     => ['title' => 'Tienda Online Premium',   'price' => 5000],
];

$planKey = $input['plan'] ?? '';
$planPrice = (float)($input['plan_price'] ?? 0);
$hostingPrice = (float)($input['hosting'] ?? 0);
$hostingYears = $input['hosting_years'] ?? '1 año';
$paymentType = $input['payment_type'] ?? 'full';
$reference = $input['reference'] ?? 'victor_web_' . uniqid();

if (!isset($plans[$planKey])) {
    http_response_code(400);
    echo json_encode(['error' => 'Plan no válido']);
    exit;
}

$plan = $plans[$planKey];
$total = $planPrice + $hostingPrice;
$payNow = $paymentType === 'full' ? $total : round($total * 0.5);

$items = [
    [
        'title'       => $plan['title'] . ($paymentType === 'deposit' ? ' (50% anticipo)' : ''),
        'quantity'    => 1,
        'currency_id' => 'MXN',
        'unit_price'  => (float)($paymentType === 'full' ? $planPrice : round($planPrice * 0.5)),
    ],
    [
        'title'       => 'Hosting + Dominio ' . $hostingYears . ($paymentType === 'deposit' ? ' (50% anticipo)' : ''),
        'quantity'    => 1,
        'currency_id' => 'MXN',
        'unit_price'  => (float)($paymentType === 'full' ? $hostingPrice : round($hostingPrice * 0.5)),
    ],
];

$data = [
    'items'               => $items,
    'back_urls'           => [
        'success' => 'https://rtvic.github.io/nevas-xmaben/pago_exitoso.html',
        'failure' => 'https://rtvic.github.io/nevas-xmaben/pago_fallido.html',
        'pending' => 'https://rtvic.github.io/nevas-xmaben/pago_pendiente.html',
    ],
    'auto_return'         => 'approved',
    'notification_url'    => 'https://rtvic.github.io/nevas-xmaben/api/ipn.php',
    'statement_descriptor' => 'VICTOR ONTIVEROS',
    'external_reference'  => $reference,
    'metadata'            => [
        'source'        => 'victor_ontiveros',
        'plan'          => $planKey,
        'payment_type'  => $paymentType,
        'total'         => $total,
        'pay_now'       => $payNow,
    ],
];

if ($total != $payNow) {
    $data['metadata']['remaining'] = $total - $payNow;
}

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => 'https://api.mercadopago.com/checkout/preferences',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($data),
    CURLOPT_HTTPHEADER     => [
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
        'success'        => true,
        'init_point'     => $resultado['init_point'],
        'preference_id'  => $resultado['id'],
        'total'          => $total,
        'pay_now'        => $payNow,
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error'     => 'Error al crear preferencia MP',
        'http_code' => $httpCode,
        'response'  => $resultado['message'] ?? $response,
    ]);
}
