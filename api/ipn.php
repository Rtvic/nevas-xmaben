<?php
$accessToken = 'APP_USR-7272894554891156-070103-614f6dfb34e271a73de7140de63e2eb5-1306973285';
$dataFile = __DIR__ . '/../pagos_recibidos.json';

$input = file_get_contents('php://input');
$body = json_decode($input, true);

file_put_contents(__DIR__ . '/../ipn_log.txt', date('Y-m-d H:i:s') . ' - ' . $input . PHP_EOL, FILE_APPEND);

if (isset($body['type']) && $body['type'] === 'payment') {
    $paymentId = $body['data']['id'];
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.mercadopago.com/v1/payments/$paymentId",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $payment = json_decode($response, true);
        $pagos = [];
        if (file_exists($dataFile)) {
            $pagos = json_decode(file_get_contents($dataFile), true) ?? [];
        }
        $pagos[] = [
            'id' => $payment['id'],
            'status' => $payment['status'],
            'status_detail' => $payment['status_detail'],
            'amount' => $payment['transaction_amount'],
            'currency' => $payment['currency_id'],
            'payer_email' => $payment['payer']['email'] ?? '',
            'payment_method' => $payment['payment_method_id'] ?? '',
            'payment_type' => $payment['payment_type_id'] ?? '',
            'date_created' => $payment['date_created'],
            'date_approved' => $payment['date_approved'] ?? '',
        ];
        file_put_contents($dataFile, json_encode($pagos, JSON_PRETTY_PRINT));

        if ($payment['status'] === 'approved') {
            $tgScript = 'C:\Users\Victor Ontiveros\.claude\toolbox\send_telegram.py';
            $msg = "PAGO RECIBIDO: $" . $payment['transaction_amount'] . " MXN - " . $payment['payment_method_id'];
            exec("python \"$tgScript\" \"\" \"$msg\"");
        }
    }
}

http_response_code(200);
echo 'OK';
