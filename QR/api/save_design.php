<?php
// api/save_design.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$code = $input['code'] ?? '';
$design = $input['design'] ?? [];

if (empty($code)) {
    http_response_code(400);
    echo json_encode(['error' => 'Código link es obligatorio']);
    exit;
}

try {
    // Get link ID
    $stmt = $pdo->prepare("SELECT id FROM links WHERE code = ?");
    $stmt->execute([$code]);
    $link = $stmt->fetch();
    if (!$link) throw new Exception("Link no encontrado");

    $link_id = $link['id'];

    // Upsert design
    $stmt = $pdo->prepare("
        INSERT INTO qr_codes (link_id, dots_style, corners_square_style, corners_dot_style, fg_color, bg_color, logo_path, label_text, frame_type, frame_color, fg_color_2, fg_gradient_type, fg_gradient_rotation, bg_color_2, bg_gradient_type, bg_gradient_rotation)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            dots_style = VALUES(dots_style),
            corners_square_style = VALUES(corners_square_style),
            corners_dot_style = VALUES(corners_dot_style),
            fg_color = VALUES(fg_color),
            bg_color = VALUES(bg_color),
            logo_path = VALUES(logo_path),
            label_text = VALUES(label_text),
            frame_type = VALUES(frame_type),
            frame_color = VALUES(frame_color),
            fg_color_2 = VALUES(fg_color_2),
            fg_gradient_type = VALUES(fg_gradient_type),
            fg_gradient_rotation = VALUES(fg_gradient_rotation),
            bg_color_2 = VALUES(bg_color_2),
            bg_gradient_type = VALUES(bg_gradient_type),
            bg_gradient_rotation = VALUES(bg_gradient_rotation)
    ");
    
    $stmt->execute([
        $link_id,
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

    // Update Lead Gen status in links table
    $stmtLead = $pdo->prepare("UPDATE links SET lead_gen_enabled = ? WHERE id = ?");
    $stmtLead->execute([($design['leadGenEnabled'] ? 1 : 0), $link_id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
