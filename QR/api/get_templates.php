<?php
// api/get_templates.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $templates = $pdo->query("SELECT * FROM templates ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($templates);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
