<?php
// api/manage_folders.php
require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
$user_id = $_SESSION['user_id'];

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$folder_id = $input['folder_id'] ?? null;

if (!$folder_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de carpeta es obligatorio']);
    exit;
}

// Verify ownership
$stmtCheck = $pdo->prepare("SELECT id FROM folders WHERE id = ? AND user_id = ?");
$stmtCheck->execute([$folder_id, $user_id]);
if (!$stmtCheck->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permiso sobre esta carpeta']);
    exit;
}

try {
    if ($action === 'rename') {
        $newName = $input['name'] ?? '';
        if (empty($newName)) {
            echo json_encode(['error' => 'El nombre no puede estar vacío']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE folders SET name = ? WHERE id = ?");
        $stmt->execute([$newName, $folder_id]);
        echo json_encode(['success' => true]);
    } 
    else if ($action === 'delete') {
        // When a folder is deleted, links are automatically updated to folder_id = NULL 
        // if the FK constraint is ON DELETE SET NULL. 
        // Let's check or enforce it manually to be safe.
        $pdo->prepare("UPDATE links SET folder_id = NULL WHERE folder_id = ?")->execute([$folder_id]);
        
        $stmt = $pdo->prepare("DELETE FROM folders WHERE id = ?");
        $stmt->execute([$folder_id]);
        echo json_encode(['success' => true]);
    }
    else {
        echo json_encode(['error' => 'Acción no válida']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
