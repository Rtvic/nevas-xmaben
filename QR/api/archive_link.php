<?php
// api/archive_link.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;
$archived = $input['archived'] ?? 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID es obligatorio']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE links SET archived = ? WHERE id = ?");
    $stmt->execute([$archived, $id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
