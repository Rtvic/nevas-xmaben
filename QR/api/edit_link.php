<?php
// api/edit_link.php
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

// Support both JSON and FormData
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

$link_id = $data['id'] ?? null;
if (!$link_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de enlace es obligatorio']);
    exit;
}

// Verify ownership
$stmtCheck = $pdo->prepare("SELECT id, type, destination_url, content, theme_data, campaign FROM links WHERE id = ? AND user_id = ?");
$stmtCheck->execute([$link_id, $user_id]);
$link = $stmtCheck->fetch();

if (!$link) {
    http_response_code(404);
    echo json_encode(['error' => 'Enlace no encontrado o no tienes permiso']);
    exit;
}

$destination_url = $data['destination_url'] ?? $link['destination_url'];
$campaign = $data['campaign'] ?? $link['campaign'];
$content = $data['content'] ?? $link['content'];
$theme_data = $data['theme_data'] ?? $link['theme_data'];

// Handle PDF Upload if file is provided
if ($link['type'] === 'pdf' && isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['pdfFile'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) === 'pdf') {
        $uploadDir = __DIR__ . '/../uploads/pdfs/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = uniqid() . '.pdf';
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
            $content = json_encode(['file' => 'uploads/pdfs/' . $fileName]);
            $destination_url = 'pdf';
        }
    }
}

try {
    $stmt = $pdo->prepare("UPDATE links SET destination_url = ?, campaign = ?, content = ?, theme_data = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$destination_url, $campaign, $content, $theme_data, $link_id, $user_id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
