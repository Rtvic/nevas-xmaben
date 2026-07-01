<?php
// api/assign_folder.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$link_id = $input['link_id'] ?? null;
$folder_id = $input['folder_id'] ?? null;

if (!$link_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de link es obligatorio']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE links SET folder_id = ? WHERE id = ?");
    $stmt->execute([$folder_id, $link_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
