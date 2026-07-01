<?php
// api/auth.php
require_once __DIR__ . '/../config/database.php';
session_start();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        die(json_encode(['error' => 'Todos los campos son obligatorios']));
    }

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die(json_encode(['error' => 'El correo electrónico ya está registrado']));
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hash]);
        $user_id = $pdo->lastInsertId();

        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al registrar: ' . $e->getMessage()]);
    }
} 

else if ($action === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        die(json_encode(['error' => 'Ingresa tus credenciales']));
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Credenciales incorrectas']);
    }
} 

else if ($action === 'logout') {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

else if ($action === 'upgrade') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    // Update to PRO: Unlimited links (9999) and plan='pro'
    $stmt = $pdo->prepare("UPDATE users SET plan = 'pro', link_limit = 9999 WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Error al actualizar el plan']);
    }
    exit;
}
