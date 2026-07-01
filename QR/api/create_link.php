// api/create_link.php
require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
$user_id = $_SESSION['user_id'];

// Get user plan and limit
$stmtUser = $pdo->prepare("SELECT plan, link_limit FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user_info = $stmtUser->fetch();
$user_plan = $user_info['plan'] ?? 'free';
$link_limit = $user_info['link_limit'] ?? 5;

// Check current link count
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM links WHERE user_id = ?");
$stmtCount->execute([$user_id]);
$current_count = $stmtCount->fetchColumn();

if ($current_count >= $link_limit) {
    http_response_code(402);
    echo json_encode(['error' => 'Límite de enlaces alcanzado. Mejora tu plan para crear más.']);
    exit;
}

if ($type === 'social' && $user_plan === 'free') {
    http_response_code(403);
    echo json_encode(['error' => 'Los códigos QR Sociales son una función Pro. Mejora tu plan.']);
    exit;
}

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

$destination_url = $data['destination_url'] ?? '';
$campaign = $data['campaign'] ?? '';
$type = $data['type'] ?? 'url';
$custom_slug = trim($data['custom_slug'] ?? '');
$content = $data['content'] ?? null;
$folder_id = $data['folder_id'] ?? null;
$theme_data = $data['theme_data'] ?? null;

// Handle PDF Upload
if ($type === 'pdf') {
    if ($user_plan === 'free') {
        http_response_code(403);
        echo json_encode(['error' => 'Los códigos QR de PDF son una función Pro. Mejora tu plan.']);
        exit;
    }
    if (!isset($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Error al subir el archivo PDF']);
        exit;
    }

    $file = $_FILES['pdfFile'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'pdf') {
        http_response_code(400);
        echo json_encode(['error' => 'Solo se permiten archivos PDF']);
        exit;
    }

    $uploadDir = __DIR__ . '/../uploads/pdfs/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = uniqid() . '.pdf';
    $destPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        $content = json_encode(['file' => 'uploads/pdfs/' . $fileName]);
        $destination_url = 'pdf'; // Placeholder for PDF type
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo guardar el archivo PDF']);
        exit;
    }
}

if (empty($destination_url) && $type !== 'pdf') {
    http_response_code(400);
    echo json_encode(['error' => 'Contenido de destino es obligatorio']);
    exit;
}

if (!empty($custom_slug)) {
    if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $custom_slug)) {
        http_response_code(400);
        echo json_encode(['error' => 'Slug inválido']);
        exit;
    }
    $code = $custom_slug;
    $stmt = $pdo->prepare("SELECT id FROM links WHERE code = ?");
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'Enlace ya existe']);
        exit;
    }
} else {
    function generateCode($length = 6) {
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $length)), 0, $length);
    }
    $code = generateCode();
}

try {
    $stmt = $pdo->prepare("INSERT INTO links (user_id, code, destination_url, campaign, type, content, folder_id, theme_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $code, $destination_url, $campaign, $type, $content, $folder_id, $theme_data]);
    
    echo json_encode([
        'id' => $pdo->lastInsertId(),
        'code' => $code
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
