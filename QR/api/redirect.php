<?php
// api/redirect.php
require_once __DIR__ . '/../config/database.php';

$code = $_GET['code'] ?? '';

if (empty($code)) {
    header("Location: ../index.php");
    exit;
}

// 1. Fetch the destination URL and Type
$stmt = $pdo->prepare("SELECT id, destination_url, type, content, theme_data FROM links WHERE code = ? AND archived = 0");
$stmt->execute([$code]);
$link = $stmt->fetch();

if (!$link) {
    header("HTTP/1.0 404 Not Found");
    echo "URL no encontrada o inactiva.";
    exit;
}

$link_id = $link['id'];
$destination_url = $link['destination_url'];
$type = $link['type'] ?? 'url';
$content = $link['content'] ?? null;

// 2. Capture Tracking Data
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Simple Browser/OS Parsing (Consider using a library like Piwik Device Detector for production)
function getBrowser($user_agent) {
    if (strpos($user_agent, 'MSIE') !== FALSE) return 'Internet Explorer';
    if (strpos($user_agent, 'Trident') !== FALSE) return 'Internet Explorer';
    if (strpos($user_agent, 'Firefox') !== FALSE) return 'Firefox';
    if (strpos($user_agent, 'Chrome') !== FALSE) return 'Chrome';
    if (strpos($user_agent, 'Opera Mini') !== FALSE) return 'Opera Mini';
    if (strpos($user_agent, 'Opera') !== FALSE) return 'Opera';
    if (strpos($user_agent, 'Safari') !== FALSE) return 'Safari';
    return 'Unknown';
}

function getOS($user_agent) {
    if (preg_match('/windows|win32/i', $user_agent)) return 'Windows';
    if (preg_match('/macintosh|mac os x/i', $user_agent)) return 'Mac OS';
    if (preg_match('/linux/i', $user_agent)) return 'Linux';
    if (preg_match('/iphone/i', $user_agent)) return 'iPhone';
    if (preg_match('/android/i', $user_agent)) return 'Android';
    if (preg_match('/ipad/i', $user_agent)) return 'iPad';
    return 'Unknown';
}

function getDevice($user_agent) {
    if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $user_agent)) return 'Tablet';
    if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $user_agent)) return 'Mobile';
    return 'Desktop';
}

$browser = getBrowser($user_agent);
$os = getOS($user_agent);
$device = getDevice($user_agent);

// GeoIP (Using free ip-api.com - Note: Free tier has rate limits)
$country = "Unknown"; 
$city = "Unknown";

if ($ip !== '127.0.0.1' && $ip !== '::1' && $ip !== 'Unknown') {
    try {
        $geo_json = @file_get_contents("http://ip-api.com/json/{$ip}");
        if ($geo_json) {
            $geo_data = json_decode($geo_json, true);
            if ($geo_data && $geo_data['status'] === 'success') {
                $country = $geo_data['country'] ?? 'Unknown';
                $city = $geo_data['city'] ?? 'Unknown';
            }
        }
    } catch (Exception $e) {
        // Fallback to Unknown on error
    }
} else {
    // Localhost fallback for testing
    $country = "Local/Mexico";
    $city = "Campeche";
}

// 3. Log the event
$insert_stmt = $pdo->prepare("INSERT INTO events (link_id, ip, country, city, os, browser, device) VALUES (?, ?, ?, ?, ?, ?, ?)");
$insert_stmt->execute([$link_id, $ip, $country, $city, $os, $browser, $device]);

// Check for Lead Generation
session_start();
if ($link['lead_gen_enabled'] && !isset($_SESSION['lead_captured_' . $link_id])) {
    require_once __DIR__ . '/../lead_gen_view.php';
    exit;
}

// 4. Route based on Type
if ($type === 'url' && !empty($destination_url)) {
    header("Location: " . $destination_url, true, 302);
} else if ($type === 'vcard') {
    require_once __DIR__ . '/../vcard_view.php';
} else if ($type === 'wifi') {
    require_once __DIR__ . '/../wifi_view.php';
} else if ($type === 'whatsapp') {
    $wadata = json_decode($content, true);
    $waUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', $wadata['phone']);
    if (!empty($wadata['msg'])) {
        $waUrl .= "?text=" . urlencode($wadata['msg']);
    }
    header("Location: " . $waUrl, true, 302);
} else if ($type === 'pdf') {
    require_once __DIR__ . '/../pdf_view.php';
} else if ($type === 'social') {
    require_once __DIR__ . '/../social_view.php';
} else {
    echo "Contenido no disponible.";
}
exit;
?>
