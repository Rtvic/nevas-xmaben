<?php
// api/bulk_actions.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$ids = $input['ids'] ?? [];
$action = $input['action'] ?? ''; // 'archive', 'delete', 'move'
$folder_id = $input['folder_id'] ?? null;

if (empty($ids) || !is_array($ids)) {
    http_response_code(400);
    echo json_encode(['error' => 'No se seleccionaron elementos']);
    exit;
}

$idList = implode(',', array_map('intval', $ids));

try {
    switch ($action) {
        case 'archive':
            $pdo->exec("UPDATE links SET archived = 1 WHERE id IN ($idList)");
            break;
        case 'delete':
            $pdo->exec("DELETE FROM links WHERE id IN ($idList)");
            $pdo->exec("DELETE FROM qr_codes WHERE link_code IN (SELECT code FROM links WHERE id IN ($idList))");
            break;
        case 'move':
            $stmt = $pdo->prepare("UPDATE links SET folder_id = ? WHERE id IN ($idList)");
            $stmt->execute([$folder_id ?: null]);
            break;
        default:
            throw new Exception("Acción no válida");
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
