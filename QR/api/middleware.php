<?php
// api/middleware.php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'No autorizado. Por favor inicia sesión.']));
}

$user_id = $_SESSION['user_id'];

/**
 * Checks if a specific link belongs to the current user
 */
function checkLinkOwnership($pdo, $link_id, $user_id) {
    $stmt = $pdo->prepare("SELECT id FROM links WHERE id = ? AND user_id = ?");
    $stmt->execute([$link_id, $user_id]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        die(json_encode(['error' => 'Acceso denegado. No eres el dueño de este recurso.']));
    }
}

/**
 * Checks if a specific folder belongs to the current user
 */
function checkFolderOwnership($pdo, $folder_id, $user_id) {
    if (!$folder_id) return;
    $stmt = $pdo->prepare("SELECT id FROM folders WHERE id = ? AND user_id = ?");
    $stmt->execute([$folder_id, $user_id]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        die(json_encode(['error' => 'Acceso denegado. No eres el dueño de esta carpeta.']));
    }
}
