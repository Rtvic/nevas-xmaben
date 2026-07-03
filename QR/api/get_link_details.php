<?php
// api/get_link_details.php
require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
$user_id = $_SESSION['user_id'];

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de enlace es obligatorio']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM links WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$link = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$link) {
    http_response_code(404);
    echo json_encode(['error' => 'Enlace no encontrado']);
    exit;
}

echo json_encode($link);
?>
