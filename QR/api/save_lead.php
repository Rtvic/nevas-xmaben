<?php
// api/save_lead.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$link_id = $input['link_id'] ?? null;
$name = $input['name'] ?? '';
$email = $input['email'] ?? '';
$phone = $input['phone'] ?? '';

if (!$link_id || !$name || !$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO leads (link_id, name, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$link_id, $name, $email, $phone]);

    // Track in session that this user has already provided their details for this link
    session_start();
    $_SESSION['lead_captured_' . $link_id] = true;

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
