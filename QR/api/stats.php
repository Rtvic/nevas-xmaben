<?php
// api/stats.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$link_id = $_GET['link_id'] ?? null;

// Security check: ensure the link belongs to the user
if ($link_id) {
    $stmt = $pdo->prepare("SELECT id FROM links WHERE id = ? AND user_id = ?");
    $stmt->execute([$link_id, $user_id]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        die(json_encode(['error' => 'Acceso denegado a este recurso']));
    }
}

try {
    // 1. Total & Unique Scans
    $total_scans_query = "SELECT COUNT(*) FROM events e JOIN links l ON e.link_id = l.id WHERE l.user_id = $user_id";
    if ($link_id) $total_scans_query .= " AND e.link_id = $link_id";
    $total_scans = $pdo->query($total_scans_query)->fetchColumn();

    $unique_scans_query = "SELECT COUNT(DISTINCT e.ip) FROM events e JOIN links l ON e.link_id = l.id WHERE l.user_id = $user_id";
    if ($link_id) $unique_scans_query .= " AND e.link_id = $link_id";
    $unique_scans = $pdo->query($unique_scans_query)->fetchColumn();

    // 2. OS Stats
    $os_query = "SELECT e.os as label, COUNT(*) as value FROM events e JOIN links l ON e.link_id = l.id WHERE l.user_id = $user_id";
    if ($link_id) $os_query .= " AND e.link_id = $link_id";
    $os_stats = $pdo->query($os_query . " GROUP BY e.os ORDER BY value DESC")->fetchAll(PDO::FETCH_ASSOC);

    // 3. Country Stats
    $country_query = "SELECT e.country as label, COUNT(*) as value FROM events e JOIN links l ON e.link_id = l.id WHERE l.user_id = $user_id";
    if ($link_id) $country_query .= " AND e.link_id = $link_id";
    $country_stats = $pdo->query($country_query . " GROUP BY e.country ORDER BY value DESC")->fetchAll(PDO::FETCH_ASSOC);

    // 4. City Stats
    $city_query = "SELECT e.city as label, COUNT(*) as value FROM events e JOIN links l ON e.link_id = l.id WHERE l.user_id = $user_id";
    if ($link_id) $city_query .= " AND e.link_id = $link_id";
    $city_stats = $pdo->query($city_query . " GROUP BY e.city ORDER BY value DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

    // 5. Browser Stats
    $browser_query = "SELECT e.browser as label, COUNT(*) as value FROM events e JOIN links l ON e.link_id = l.id WHERE l.user_id = $user_id";
    if ($link_id) $browser_query .= " AND e.link_id = $link_id";
    $browser_stats = $pdo->query($browser_query . " GROUP BY e.browser ORDER BY value DESC")->fetchAll(PDO::FETCH_ASSOC);

    // 6. Device Stats
    $device_query = "SELECT e.device as label, COUNT(*) as value FROM events e JOIN links l ON e.link_id = l.id WHERE l.user_id = $user_id";
    if ($link_id) $device_query .= " AND e.link_id = $link_id";
    $device_stats = $pdo->query($device_query . " GROUP BY e.device ORDER BY value DESC")->fetchAll(PDO::FETCH_ASSOC);

    // 5. Daily Stats (Last 30 days)
    $daily_query = "SELECT DATE_FORMAT(e.created_at, '%Y-%m-%d') as label, COUNT(*) as value FROM events e JOIN links l ON e.link_id = l.id WHERE l.user_id = $user_id AND e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    if ($link_id) $daily_query .= " AND e.link_id = $link_id";
    $daily_stats = $pdo->query($daily_query . " GROUP BY DATE_FORMAT(e.created_at, '%Y-%m-%d') ORDER BY label ASC")->fetchAll(PDO::FETCH_ASSOC);

    // 6. Leads (If link_id is provided)
    $leads = [];
    if ($link_id) {
        $leads = $pdo->query("SELECT name, email, phone, created_at FROM leads WHERE link_id = $link_id ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'total_scans' => $total_scans,
        'unique_scans' => $unique_scans,
        'os' => $os_stats,
        'browsers' => $browser_stats,
        'devices' => $device_stats,
        'countries' => $country_stats,
        'cities' => $city_stats,
        'daily' => $daily_stats,
        'leads' => $leads
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
