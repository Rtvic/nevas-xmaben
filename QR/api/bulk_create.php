<?php
// api/bulk_create.php
require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'No autorizado']));
}
$user_id = $_SESSION['user_id'];

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Petición inválida']));
}

$file = $_FILES['csv']['tmp_name'];
$handle = fopen($file, "r");

if (!$handle) {
    die(json_encode(['error' => 'No se pudo abrir el archivo']));
}

// Get user limit
$user_stmt = $pdo->prepare("SELECT link_limit FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$link_limit = $user_stmt->fetchColumn();

// Get current count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM links WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$current_count = $count_stmt->fetchColumn();

$created_count = 0;
$errors = [];

// Skip header
fgetcsv($handle);

$pdo->beginTransaction();

try {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if (count($data) < 2) continue;

        $campaign = trim($data[0]);
        $url = trim($data[1]);

        if (empty($url)) continue;

        // Check limit
        if (($current_count + $created_count) >= $link_limit) {
            $errors[] = "Límite de plan alcanzado";
            break;
        }

        // Generate unique code
        $code = substr(md5(uniqid(mt_rand(), true)), 0, 6);
        
        // Insert Link
        $stmt = $pdo->prepare("INSERT INTO links (user_id, code, type, content, destination_url, campaign) VALUES (?, ?, 'url', ?, ?, ?)");
        $stmt->execute([$user_id, $code, $url, $url, $campaign]);
        $link_id = $pdo->lastInsertId();

        // Insert Default QR Code Design
        $pdo->prepare("INSERT INTO qr_codes (link_id) VALUES (?)")->execute([$link_id]);

        $created_count++;
    }

    $pdo->commit();
    fclose($handle);

    echo json_encode([
        'success' => true,
        'count' => $created_count,
        'errors' => $errors
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    fclose($handle);
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
