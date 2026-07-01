<?php
// api/save_template.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'] ?? 'Mi Plantilla';
$design = $input['design'] ?? [];

if (empty($design)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos de diseño vacíos']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO templates (name, dots_style, corners_square_style, corners_dot_style, fg_color, bg_color, logo_path, label_text, frame_type, frame_color, fg_color_2, fg_gradient_type, fg_gradient_rotation, bg_color_2, bg_gradient_type, bg_gradient_rotation)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $name,
        $design['dotsStyle'] ?? 'square',
        $design['cornersStyle'] ?? 'square',
        $design['cornersStyle'] ?? 'square', 
        $design['fgColor'] ?? '#000000',
        $design['bgColor'] ?? '#ffffff',
        $design['logoUrl'] ?? null,
        $design['label_text'] ?? null,
        $design['frameType'] ?? 'none',
        $design['frameColor'] ?? '#4f46e5',
        $design['fgColor2'] ?? null,
        $design['fgGradientType'] ?? 'none',
        $design['fgRotation'] ?? 0,
        $design['bgColor2'] ?? null,
        $design['bgGradientType'] ?? 'none',
        $design['bgRotation'] ?? 0
    ]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
