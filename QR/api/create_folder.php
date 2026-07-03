<?php
// api/create_folder.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'] ?? '';

if (empty($name)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre es obligatorio']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO folders (name) VALUES (?)");
    $stmt->execute([$name]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
