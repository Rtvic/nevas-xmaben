<?php
$file = __DIR__ . '/../pagos_recibidos.json';
$total_mxn = 0;
$meta_mxn = 4000;
if (file_exists($file)) {
    $pagos = json_decode(file_get_contents($file), true) ?: [];
    foreach ($pagos as $p) {
        if ($p['status'] === 'approved') $total_mxn += $p['amount'];
    }
}
$total_usd = round($total_mxn / 20, 2);
$pct = round(($total_mxn / $meta_mxn) * 100, 1);
header('Content-Type: application/json');
echo json_encode([
    'total_mxn' => $total_mxn,
    'total_usd' => $total_usd,
    'meta_mxn' => $meta_mxn,
    'meta_usd' => 200,
    'progreso' => $pct,
    'pagos' => $pagos ?? []
]);
