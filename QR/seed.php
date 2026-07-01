<?php
// seed.php
require_once __DIR__ . '/config/database.php';

$examples = [
    [
        'code' => 'Fd3xor',
        'destination_url' => 'https://meli.la/2GciHA2',
        'campaign' => 'Motoneta Italika Modena 175 Con Gps Azul Claro'
    ],
    [
        'code' => 'nwepIM',
        'destination_url' => 'https://meli.la/2tHgxqD',
        'campaign' => 'L’Oreal Paris Protector Solar Fluid FPS50+ UV Defender, 40ml'
    ],
    [
        'code' => 'Aj3H9e',
        'destination_url' => 'https://maps.google.com/?cbll=19.232466,-89.3114156&cbp=12,185.83,0,76.75&layer=c',
        'campaign' => 'MECANICA'
    ],
    [
        'code' => 'Xkdx4I',
        'destination_url' => 'https://meli.la/31d8GPR',
        'campaign' => 'Untitled'
    ]
];

try {
    foreach ($examples as $link) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO links (code, destination_url, campaign) VALUES (?, ?, ?)");
        $stmt->execute([$link['code'], $link['destination_url'], $link['campaign']]);
    }
    echo "Base de datos poblada con éxito.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
