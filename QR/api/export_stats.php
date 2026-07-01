<?php
// api/export_stats.php
require_once __DIR__ . '/../config/database.php';

$link_id = $_GET['link_id'] ?? null;

if (!$link_id) {
    die("Error: No se proporcionó ID de link.");
}

try {
    // Get link info for filename
    $stmt = $pdo->prepare("SELECT campaign, code FROM links WHERE id = ?");
    $stmt->execute([$link_id]);
    $link = $stmt->fetch();
    $filename = ($link['campaign'] ?? $link['code'] ?? 'stats') . "_export.csv";

    // Fetch all events for this link
    $stmt = $pdo->prepare("SELECT ip, country, city, os, browser, created_at FROM events WHERE link_id = ? ORDER BY created_at DESC");
    $stmt->execute([$link_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Column headers
    fputcsv($output, ['Fecha', 'IP', 'País', 'Ciudad', 'SO', 'Navegador']);

    // Data rows
    foreach ($events as $event) {
        fputcsv($output, $event);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>
