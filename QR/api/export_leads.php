// api/export_leads.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/middleware.php';

$link_id = $_GET['link_id'] ?? null;
$export_all = isset($_GET['all']) && $_GET['all'] == '1';

if (!$link_id && !$export_all) {
    die("Link ID o parámetro Global requerido");
}

try {
    if ($export_all) {
        $campaign = "Global_CRM";
        $stmt = $pdo->prepare("
            SELECT ld.name, ld.email, ld.phone, ld.created_at, l.campaign as origen 
            FROM leads ld 
            JOIN links l ON ld.link_id = l.id 
            WHERE l.user_id = ?
            ORDER BY ld.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $headers = ['Nombre', 'Email', 'Teléfono', 'Fecha', 'Campaña de Origen'];
    } else {
        // Ownership check
        checkLinkOwnership($pdo, $link_id, $user_id);

        $stmtC = $pdo->prepare("SELECT campaign FROM links WHERE id = ?");
        $stmtC->execute([$link_id]);
        $campaign = $stmtC->fetchColumn() ?: "leads";
        $stmt = $pdo->prepare("SELECT name, email, phone, created_at FROM leads WHERE link_id = ? ORDER BY created_at DESC");
        $stmt->execute([$link_id]);
        $headers = ['Nombre', 'Email', 'Teléfono', 'Fecha'];
    }
    
    $filename = str_replace(' ', '_', $campaign) . "_leads_" . date('Y-m-d') . ".csv";
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, $headers);

    // Loop over the rows, outputting them
    foreach ($leads as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    die("Error al exportar: " . $e->getMessage());
}
