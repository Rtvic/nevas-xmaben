<?php
// api/update_profile.php
require_once __DIR__ . '/../config/database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'No autorizado']));
}
$user_id = $_SESSION['user_id'];

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método no permitido']));
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';

if (empty($name) || empty($email)) {
    die(json_encode(['error' => 'Nombre y correo son obligatorios']));
}

try {
    // 1. Handle Avatar Upload
    $avatar_name = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/avatars/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatar_name = $user_id . '_' . time() . '.' . $file_ext;
        $target_file = $upload_dir . $avatar_name;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
            // Success
        } else {
            die(json_encode(['error' => 'Error al subir la imagen']));
        }
    }

    // 2. Update Database
    $sql = "UPDATE users SET name = ?, email = ?";
    $params = [$name, $email];

    if ($avatar_name) {
        $sql .= ", avatar = ?";
        $params[] = $avatar_name;
    }

    $sql .= " WHERE id = ?";
    $params[] = $user_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['error' => 'El correo electrónico ya está en uso']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
