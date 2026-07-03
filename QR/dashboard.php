<?php
// dashboard.php
require_once __DIR__ . '/config/database.php';

// --- AUTH CHECK ---
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// --- AUTO-REPAIR DATABASE START ---
// This block ensures the database is always in sync with the required columns
try {
    $required_columns = [
        'dots_style' => "VARCHAR(50) DEFAULT 'square'",
        'corners_square_style' => "VARCHAR(50) DEFAULT 'square'",
        'corners_dot_style' => "VARCHAR(50) DEFAULT 'square'",
        'fg_color' => "VARCHAR(20) DEFAULT '#000000'",
        'bg_color' => "VARCHAR(20) DEFAULT '#ffffff'",
        'logo_path' => "VARCHAR(255) DEFAULT NULL",
        'label_text' => "VARCHAR(100) DEFAULT NULL",
        'label_color' => "VARCHAR(20) DEFAULT '#000000'",
        'frame_type' => "VARCHAR(50) DEFAULT 'none'",
        'frame_color' => "VARCHAR(20) DEFAULT '#4f46e5'",
        'fg_color_2' => "VARCHAR(20) DEFAULT NULL",
        'fg_gradient_type' => "VARCHAR(20) DEFAULT 'none'",
        'fg_gradient_rotation' => "INT DEFAULT 0",
        'bg_color_2' => "VARCHAR(20) DEFAULT NULL",
        'bg_gradient_type' => "VARCHAR(20) DEFAULT 'none'",
        'bg_gradient_rotation' => "INT DEFAULT 0"
    ];

    // Ensure users table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        plan VARCHAR(20) DEFAULT 'free',
        link_limit INT DEFAULT 5,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Fix existing users table if columns are missing
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS plan VARCHAR(20) DEFAULT 'free'");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS link_limit INT DEFAULT 5");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL");

    // Ensure folders table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS folders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        name VARCHAR(100) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Ensure templates table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        dots_style VARCHAR(50) DEFAULT 'square',
        corners_square_style VARCHAR(50) DEFAULT 'square',
        corners_dot_style VARCHAR(50) DEFAULT 'square',
        fg_color VARCHAR(20) DEFAULT '#000000',
        bg_color VARCHAR(20) DEFAULT '#ffffff',
        logo_path VARCHAR(255) DEFAULT NULL,
        label_text VARCHAR(100) DEFAULT NULL,
        frame_type VARCHAR(50) DEFAULT 'none',
        frame_color VARCHAR(20) DEFAULT '#4f46e5',
        fg_color_2 VARCHAR(20) DEFAULT NULL,
        fg_gradient_type VARCHAR(20) DEFAULT 'none',
        fg_gradient_rotation INT DEFAULT 0,
        bg_color_2 VARCHAR(20) DEFAULT NULL,
        bg_gradient_type VARCHAR(20) DEFAULT 'none',
        bg_gradient_rotation INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Ensure links table has status, folder_id, type and content
    $pdo->exec("ALTER TABLE links ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL AFTER id");
    $pdo->exec("ALTER TABLE links ADD COLUMN IF NOT EXISTS archived BOOLEAN DEFAULT FALSE");
    $pdo->exec("ALTER TABLE links ADD COLUMN IF NOT EXISTS folder_id INT DEFAULT NULL");
    $pdo->exec("ALTER TABLE links ADD COLUMN IF NOT EXISTS type VARCHAR(20) DEFAULT 'url'");
    $pdo->exec("ALTER TABLE links ADD COLUMN IF NOT EXISTS content TEXT DEFAULT NULL");
    $pdo->exec("ALTER TABLE links ADD COLUMN IF NOT EXISTS lead_gen_enabled BOOLEAN DEFAULT FALSE");
    $pdo->exec("ALTER TABLE links ADD COLUMN IF NOT EXISTS theme_data TEXT DEFAULT NULL");

    // NEW Leads table
    $pdo->exec("CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        link_id INT NOT NULL,
        name VARCHAR(100) DEFAULT NULL,
        email VARCHAR(150) DEFAULT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Ensure qr_codes table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS qr_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        link_id INT NOT NULL,
        UNIQUE KEY(link_id),
        FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    foreach ($required_columns as $col => $def) {
        $check = $pdo->query("SHOW COLUMNS FROM qr_codes LIKE '$col'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE qr_codes ADD COLUMN $col $def");
        }
    }
} catch (Exception $e) {
    // Silently fail or log error if necessary
}
// --- AUTO-REPAIR DATABASE END ---

// Fetch stats for the dashboard (filtered by user)
$total_links = $pdo->query("SELECT COUNT(*) FROM links WHERE user_id = $user_id")->fetchColumn();
$total_scans = $pdo->query("SELECT COUNT(*) FROM events e JOIN links l ON e.link_id = l.id WHERE l.user_id = $user_id")->fetchColumn();
$unique_scans = $pdo->query("SELECT COUNT(DISTINCT ip) FROM events e JOIN links l ON e.link_id = l.id WHERE l.user_id = $user_id")->fetchColumn();

// Filtering logic
$view = $_GET['view'] ?? 'active';
$folder_id = $_GET['folder_id'] ?? null;

$query = "
    SELECT l.*, 
           (SELECT COUNT(*) FROM events e WHERE e.link_id = l.id) as scan_count,
           q.dots_style, q.corners_square_style, q.fg_color, q.bg_color, q.logo_path, q.label_text, q.frame_type, q.frame_color,
           q.fg_color_2, q.fg_gradient_type, q.fg_gradient_rotation, q.bg_color_2, q.bg_gradient_type, q.bg_gradient_rotation,
           f.name as folder_name,
           (SELECT COUNT(*) FROM leads WHERE link_id = l.id) as lead_count
    FROM links l 
    LEFT JOIN qr_codes q ON l.id = q.link_id
    LEFT JOIN folders f ON l.folder_id = f.id
    WHERE l.user_id = $user_id
";

if ($view === 'archived') {
    $query .= " AND l.archived = 1";
} else {
    $query .= " AND l.archived = 0";
}

if ($folder_id) {
    $query .= " AND l.folder_id = " . intval($folder_id);
}

$links = $pdo->query($query . " ORDER BY l.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// 2. User Info for Settings
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

// 3. All Folders for Sidebar
$folders = $pdo->query("SELECT * FROM folders WHERE user_id = $user_id ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// 4. All Leads for Global CRM
$all_leads = [];
if ($view === 'leads') {
    $all_leads = $pdo->query("
        SELECT ld.*, l.campaign, l.code 
        FROM leads ld 
        JOIN links l ON ld.link_id = l.id 
        WHERE l.user_id = $user_id 
        ORDER BY ld.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch current user details (plan, limits)
$stmtUser = $pdo->prepare("SELECT plan, link_limit, name FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user_row = $stmtUser->fetch();
$user_name = $user_row['name'] ?? "Usuario";
$user_plan = $user_row['plan'] ?? "free";
$link_limit = $user_row['link_limit'] ?? 5;

// Usage calculation
$stmtUsage = $pdo->prepare("SELECT COUNT(*) FROM links WHERE user_id = ?");
$stmtUsage->execute([$user_id]);
$current_usage = $stmtUsage->fetchColumn();
$usage_percent = ($current_usage / $link_limit) * 100;

$user_email = "victormanuelontiverosnoh@gmail.com"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR.io Clone | Dashboard</title>
    <link rel="stylesheet" href="public/assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script type="text/javascript" src="https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
    <style>
        /* Micro-animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .qr-item { animation: fadeIn 0.3s ease-out forwards; }
        .nav-item { transition: all 0.2s ease; }
        .nav-item:hover { transform: translateX(5px); }
        .btn { transition: all 0.2s ease; position: relative; overflow: hidden; }
        .btn:active { transform: scale(0.95); }
        .premium-card { transition: box-shadow 0.3s ease; }
        .premium-card:hover { box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" style="display: flex; flex-direction: column;">
        <div class="sidebar-logo">
            <i class="fas fa-qrcode"></i>
            <span>QR.io</span>
        </div>
        
        <!-- User Profile Quick View -->
        <div class="sidebar-user" onclick="switchTab('settings')" style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem 0.75rem; margin-bottom: 1rem; cursor: pointer; border-radius: 0.75rem; transition: background 0.2s;">
            <img id="sidebarAvatar" src="<?php echo $user_data['avatar'] ? 'uploads/avatars/'.$user_data['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($user_data['name']).'&background=4f46e5&color=fff'; ?>" 
                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0;">
            <div style="overflow: hidden;">
                <div style="font-weight: 600; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #1e293b;"><?php echo htmlspecialchars($user_data['name']); ?></div>
                <div style="font-size: 0.75rem; color: #64748b;"><?php echo ucfirst($user_data['plan']); ?> Plan</div>
            </div>
        </div>
        <style>.sidebar-user:hover { background: #f1f5f9; }</style>

        <nav class="nav-section">
            <div class="nav-label">General</div>
            <a href="index.php" class="nav-item"><i class="fas fa-home"></i> Inicio</a>
            <a href="?view=active" class="nav-item <?php echo $view === 'active' ? 'active' : ''; ?>"><i class="fas fa-qrcode"></i> Mis Códigos QR</a>
            <a href="?view=leads" class="nav-item <?php echo $view === 'leads' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Leads / CRM</a>
            <a href="?view=archived" class="nav-item <?php echo $view === 'archived' ? 'active' : ''; ?>"><i class="fas fa-archive"></i> Archivado</a>
            <a href="#" class="nav-item" id="navItem_stats" onclick="switchTab('stats')"><i class="fas fa-chart-line"></i> Estadísticas</a>
            <a href="#" class="nav-item" id="navItem_bulk" onclick="switchTab('bulk')"><i class="fas fa-layer-group"></i> Creación Masiva</a>
            <a href="#" class="nav-item" id="navItem_settings" onclick="switchTab('settings')"><i class="fas fa-cog"></i> Configuración</a>
        </nav>

        <nav class="nav-section">
            <div class="nav-label" style="display: flex; justify-content: space-between; align-items: center;">
                Carpetas
                <i class="fas fa-plus" style="cursor: pointer; font-size: 0.7rem;" onclick="createNewFolder()"></i>
            </div>
            <?php foreach($folders as $f): ?>
                <div class="nav-item-wrapper" style="display: flex; align-items: center; justify-content: space-between; padding-right: 0.5rem;">
                    <a href="dashboard.php?folder_id=<?php echo $f['id']; ?>" class="nav-item <?php echo $folder_id==$f['id']?'active':''; ?>" style="flex: 1; border: none; margin-right: 0;">
                        <i class="fas fa-folder"></i> <?php echo htmlspecialchars($f['name']); ?>
                    </a>
                    <div class="folder-actions" style="display: flex; gap: 0.5rem; opacity: 0.5; transition: opacity 0.2s;">
                        <i class="fas fa-edit" style="cursor: pointer; font-size: 0.75rem;" onclick="event.preventDefault(); event.stopPropagation(); renameFolder(<?php echo $f['id']; ?>, '<?php echo addslashes($f['name']); ?>')"></i>
                        <i class="fas fa-trash" style="cursor: pointer; font-size: 0.75rem; color: #ef4444;" onclick="event.preventDefault(); event.stopPropagation(); deleteFolder(<?php echo $f['id']; ?>)"></i>
                    </div>
                </div>
            <?php endforeach; ?>
            <style>
                .nav-item-wrapper:hover .folder-actions { opacity: 1 !important; }
                .nav-item-wrapper .nav-item.active { background: #4f46e5; color: white; border-radius: 0.5rem; }
            </style>
            <?php if(empty($folders)): ?>
                <div style="font-size: 0.75rem; color: var(--text-muted); padding: 0.5rem 0.75rem;">Sin carpetas</div>
            <?php endif; ?>
        </nav>

        <div style="margin-top: auto;">
            <div class="nav-label">Cuenta</div>
            <a href="#" class="nav-item"><i class="fas fa-cog"></i> Configuración</a>
            <a href="#" class="nav-item"><i class="fas fa-envelope"></i> Contacto</a>
        </div>

        <div class="notice-box" style="background: rgba(79, 70, 229, 0.1); border-color: rgba(79, 70, 229, 0.2); padding: 1.25rem;">
            <div style="font-size: 0.75rem; color: #6366f1; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Plan de Uso</div>
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.5rem;">
                <span style="color: #1e293b; font-weight: 600;"><?php echo "$current_usage / $link_limit"; ?> QRs</span>
                <span style="color: #64748b;"><?php echo round($usage_percent); ?>%</span>
            </div>
            <div style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 0.75rem;">
                <div style="width: <?php echo min(100, $usage_percent); ?>%; height: 100%; background: <?php echo $usage_percent > 90 ? '#ef4444' : '#4f46e5'; ?>; border-radius: 10px;"></div>
            </div>
            <?php if($user_plan === 'free'): ?>
                <a href="#" style="font-size: 0.75rem; color: #4f46e5; font-weight: 700; text-decoration: none; display: block; text-align: center;">🚀 Mejorar Plan</a>
            <?php endif; ?>
        </div>
        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 1rem; text-align: center; padding: 0 1rem;">
            Las reseñas son el alma de los negocios de software.
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-wrapper">
        <header class="main-header">
            <div>
                <h1 id="pageTitle" style="font-size: 1.5rem; font-weight: 700;">
                    <?php 
                        if ($view === 'archived') echo "Códigos QR Archivados";
                        else if ($view === 'leads') echo "Gestión de Leads (CRM)";
                        else if ($folder_id) echo "Carpeta: " . htmlspecialchars($links[0]['folder_name'] ?? 'Vacía');
                        else echo "Códigos QR Activos";
                    ?> (<?php echo $view === 'leads' ? count($all_leads) : count($links); ?>)
                </h1>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <?php if ($view !== 'leads'): ?>
                    <button class="btn btn-primary" onclick="openCreateModal()">+ Crear Código QR</button>
                <?php else: ?>
                    <a href="api/export_leads.php?all=1" class="btn btn-primary" style="background: #10b981;"><i class="fas fa-file-excel"></i> Exportar Todos los Leads</a>
                <?php endif; ?>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem; margin-left: auto;">
                <div class="premium-card" style="padding: 0.5rem 1rem; display: flex; flex-direction: column; align-items: flex-end;">
                    <div style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($user_name); ?></div>
                    <div style="font-size: 0.75rem; color: #64748b; text-transform: capitalize;"><?php echo $user_plan; ?> Plan</div>
                </div>
                <a href="api/auth.php?action=logout" style="color: #94a3b8; font-size: 1.2rem;" title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </header>

        <!-- Upgrade Banner for Free Users -->
        <?php if ($user_plan === 'free'): ?>
        <div class="premium-card" style="background: linear-gradient(135deg, #4f46e5, #818cf8); color: white; padding: 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1.5rem; position: relative; overflow: hidden; border: none;">
            <div style="font-size: 2.5rem; opacity: 0.5;"><i class="fas fa-rocket"></i></div>
            <div style="flex: 1;">
                <h3 style="font-weight: 800; margin-bottom: 0.25rem;">¡Sube al Plan PRO y domina el mercado!</h3>
                <p style="font-size: 0.9rem; opacity: 0.9;">Desbloquea QRs de PDF, Link-in-Bio Social y analíticas avanzadas sin límites.</p>
            </div>
            <button class="btn" style="background: white; color: #4f46e5; font-weight: 700; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; cursor: pointer;">Mejorar Ahora</button>
            <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        </div>
        <?php endif; ?>

        <!-- Stats Overview Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total de Códigos QR</div>
                <div class="stat-value"><?php echo $total_links; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total de Escaneos</div>
                <div class="stat-value" style="color: var(--primary);"><?php echo $total_scans; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Escaneos Únicos</div>
                <div class="stat-value" style="color: var(--accent);"><?php echo $unique_scans; ?></div>
            </div>
        </div>

        <!-- Main Dashboard View (QR List) -->
        <div id="qrListView">
            <?php if ($view === 'leads'): ?>
                <!-- GLOBAL CRM VIEW -->
                <div class="premium-card" style="padding: 0; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">
                            <tr>
                                <th style="padding: 1.25rem 1.5rem;">Campaña</th>
                                <th style="padding: 1.25rem 1.5rem;">Contacto</th>
                                <th style="padding: 1.25rem 1.5rem;">Email</th>
                                <th style="padding: 1.25rem 1.5rem;">Teléfono</th>
                                <th style="padding: 1.25rem 1.5rem;">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_leads)): ?>
                                <tr>
                                    <td colspan="5" style="padding: 3rem; text-align: center; color: #94a3b8;">
                                        <i class="fas fa-users-slash" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                                        Aún no has capturado ningún lead. ¡Activa "Captura de Leads" en tus códigos QR!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($all_leads as $lead): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; transition: 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1.25rem 1.5rem;">
                                        <div style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($lead['campaign'] ?: $lead['code']); ?></div>
                                        <div style="font-size: 0.75rem; color: #94a3b8;">ID: <?php echo $lead['code']; ?></div>
                                    </td>
                                    <td style="padding: 1.25rem 1.5rem; font-weight: 600; color: #4f46e5;"><?php echo htmlspecialchars($lead['name']); ?></td>
                                    <td style="padding: 1.25rem 1.5rem; color: #475569;"><?php echo htmlspecialchars($lead['email']); ?></td>
                                    <td style="padding: 1.25rem 1.5rem; color: #475569;"><?php echo htmlspecialchars($lead['phone'] ?: '-'); ?></td>
                                    <td style="padding: 1.25rem 1.5rem; color: #94a3b8; font-size: 0.85rem;"><?php echo date('d M, Y', strtotime($lead['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- SEARCH BOX -->
                <div class="premium-card" style="padding: 1rem; margin-bottom: 2rem; display: flex; align-items: center;">
                    <i class="fas fa-search" style="color: var(--text-muted); margin-right: 1rem;"></i>
                    <input type="text" placeholder="Buscar código QR" style="border: none; outline: none; width: 100%; font-size: 0.95rem;">
                </div>

                <div class="qr-list">
                    <?php if (empty($links)): ?>
                        <div style="text-align: center; padding: 4rem; color: #94a3b8;">
                            <i class="fas fa-qrcode" style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.3;"></i>
                            <h3>No se encontraron códigos QR</h3>
                            <p>Crea tu primer código para empezar a trackear.</p>
                        </div>
                    <?php endif; ?>

                    <?php foreach($links as $link): ?>
                    <div class="qr-item">
                        <div style="padding: 0 1rem;">
                            <input type="checkbox" class="qr-checkbox" value="<?php echo $link['id']; ?>" onchange="updateBulkSelection()">
                        </div>
                        <div class="qr-icon">
                            <?php 
                                if ($link['type'] === 'vcard') echo '<i class="fas fa-address-card" style="color: #4f46e5;"></i>';
                                else if ($link['type'] === 'wifi') echo '<i class="fas fa-wifi" style="color: #10b981;"></i>';
                                else if ($link['type'] === 'whatsapp') echo '<i class="fab fa-whatsapp" style="color: #25d366;"></i>';
                                else if ($link['type'] === 'pdf') echo '<i class="fas fa-file-pdf" style="color: #ef4444;"></i>';
                                else if ($link['type'] === 'social') echo '<i class="fas fa-share-alt" style="color: #ec4899;"></i>';
                                else echo '<i class="fas fa-link" style="color: #64748b;"></i>';
                            ?>
                        </div>
                        <div class="qr-info">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <div class="qr-name"><?php echo htmlspecialchars($link['campaign'] ?: 'URL del sitio web'); ?></div>
                                    <div class="qr-meta">
                                        <span><i class="far fa-calendar-alt"></i> Actualizado <?php echo date('d M H:i, Y', strtotime($link['created_at'])); ?></span>
                                        <span><i class="fas fa-signal"></i> <?php echo $link['scan_count']; ?> Escaneos</span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: #64748b; margin-top: 0.25rem;">
                                        <?php if ($link['type'] === 'vcard'): ?>
                                            <i class="fas fa-id-badge"></i> Tarjeta Digital (vCard)
                                        <?php elseif ($link['type'] === 'wifi'): ?>
                                            <i class="fas fa-wifi"></i> Red WiFi: <?php 
                                                $wi = json_decode($link['content'], true); 
                                                echo htmlspecialchars($wi['ssid'] ?? 'Desconocida'); 
                                            ?>
                                        <?php elseif ($link['type'] === 'whatsapp'): ?>
                                            <i class="fab fa-whatsapp"></i> WhatsApp: <?php 
                                                $wa = json_decode($link['content'], true); 
                                                echo htmlspecialchars($wa['phone'] ?? 'Sin número'); 
                                            ?>
                                        <?php elseif ($link['type'] === 'pdf'): ?>
                                            <i class="fas fa-file-pdf"></i> Archivo PDF: <?php 
                                                $pdfData = json_decode($link['content'], true);
                                                echo basename($pdfData['file'] ?? 'documento.pdf');
                                            ?>
                                        <?php elseif ($link['type'] === 'social'): ?>
                                            <i class="fas fa-share-alt"></i> 
                                            <?php 
                                                $soc = json_decode($link['content'], true);
                                                $active = [];
                                                if(!empty($soc['insta'])) $active[] = '<i class="fab fa-instagram"></i>';
                                                if(!empty($soc['tiktok'])) $active[] = '<i class="fab fa-tiktok"></i>';
                                                if(!empty($soc['fb'])) $active[] = '<i class="fab fa-facebook"></i>';
                                                if(!empty($soc['yt'])) $active[] = '<i class="fab fa-youtube"></i>';
                                                echo implode(' ', $active) ?: 'Link-in-Bio';
                                            ?>
                                        <?php else: ?>
                                            <i class="fas fa-link"></i> <?php echo htmlspecialchars($link['destination_url']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="qr-actions">
                                    <button class="btn" style="background: #3b82f6; color: white; font-size: 0.85rem;" onclick="openEditModal(<?php echo $link['id']; ?>)">Editar contenido</button>
                                    <button class="btn" style="background: #10b981; color: white; font-size: 0.85rem;" 
                                            onclick='showQR("<?php echo $link['code']; ?>", <?php echo json_encode($link); ?>)'>Editar color y forma</button>
                                    
                                    <button class="btn" style="background: #6366f1; color: white; font-size: 0.85rem;" 
                                            onclick="openIndividualStats(<?php echo $link['id']; ?>, '<?php echo addslashes($link['campaign'] ?: $link['code']); ?>')">Estadísticas</button>
                                    
                                    <button class="btn" style="background: #f1f5f9; color: var(--text-main); font-size: 0.85rem;" onclick="toggleArchive(<?php echo $link['id']; ?>, <?php echo $link['archived']; ?>)">
                                        <?php echo $link['archived'] ? 'Desarchivar' : 'Archivar'; ?>
                                    </button>
                                    
                                    <button class="btn" style="background: #f1f5f9; color: var(--text-main); font-size: 0.85rem;" onclick="openFolderModal(<?php echo $link['id']; ?>)">Carpeta</button>
                                    
                                    <button class="btn" style="background: #fee2e2; color: #991b1b; font-size: 0.85rem;" onclick="deleteLink(<?php echo $link['id']; ?>)">Eliminar</button>
                                </div>
                            </div>
                            <div class="qr-links" style="margin-top: 0.5rem;">
                                <?php 
                                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                                    $dir = str_replace('dashboard.php', '', $_SERVER['SCRIPT_NAME']);
                                    $base_short = $protocol . "://" . $_SERVER['HTTP_HOST'] . $dir . "r/";
                                ?>
                                <span class="qr-short-url" style="color: var(--primary); font-weight: 600; margin-right: 1.5rem;"><?php echo str_replace('https://', '', str_replace('http://', '', $base_short)) . $link['code']; ?></span>
                                <a href="<?php echo $base_short . $link['code']; ?>" target="_blank" class="qr-link" style="color: #64748b; font-size: 0.85rem; text-decoration: none;"><i class="fas fa-external-link-alt"></i> Abrir link</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        </div>

        </div>

        <!-- Settings View (Hidden by default) -->
        <div id="settingsView" style="display: none;">
            <div style="display: grid; grid-template-columns: 280px 1fr; gap: 2rem;">
                <!-- Settings Tabs -->
                <div class="premium-card" style="padding: 1rem; height: fit-content;">
                    <div class="settings-nav">
                        <div class="settings-nav-item active" onclick="switchSettingsTab('general')" id="setTab_general">
                            <i class="fas fa-user-circle"></i> Perfil General
                        </div>
                        <div class="settings-nav-item" onclick="switchSettingsTab('security')" id="setTab_security">
                            <i class="fas fa-shield-alt"></i> Seguridad
                        </div>
                        <div class="settings-nav-item" onclick="switchSettingsTab('billing')" id="setTab_billing">
                            <i class="fas fa-credit-card"></i> Plan y Facturación
                        </div>
                    </div>
                </div>

                <!-- Settings Content -->
                <div class="premium-card" style="padding: 2rem;">
                    <!-- General Settings -->
                    <div id="setCont_general" class="settings-content">
                        <h3 style="margin-bottom: 2rem;">Configuración de Perfil</h3>
                        <form id="profileForm" onsubmit="updateProfile(event)">
                            <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="position: relative;">
                                    <img id="settingsAvatar" src="<?php echo $user_data['avatar'] ? 'uploads/avatars/'.$user_data['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($user_data['name']).'&background=4f46e5&color=fff'; ?>" 
                                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #f1f5f9;">
                                    <label for="avatarInput" style="position: absolute; bottom: 0; right: 0; background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid white;">
                                        <i class="fas fa-camera" style="font-size: 0.8rem;"></i>
                                    </label>
                                    <input type="file" id="avatarInput" hidden accept="image/*" onchange="previewAvatar(this)">
                                </div>
                                <div>
                                    <h4 style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($user_data['name']); ?></h4>
                                    <p style="color: #64748b; font-size: 0.9rem;"><?php echo strtoupper($user_data['plan']); ?> Member</p>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e293b;">Nombre Completo</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                            </div>
                            <div class="form-group" style="margin-bottom: 2rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e293b;">Correo Electrónico</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 200px;">Guardar Cambios</button>
                        </form>
                    </div>

                    <!-- Security Settings -->
                    <div id="setCont_security" class="settings-content" style="display: none;">
                        <h3 style="margin-bottom: 2rem;">Seguridad de la Cuenta</h3>
                        <form id="passwordForm" onsubmit="updatePassword(event)">
                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e293b;">Contraseña Actual</label>
                                <input type="password" name="current_password" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                            </div>
                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e293b;">Nueva Contraseña</label>
                                <input type="password" name="new_password" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                            </div>
                            <div class="form-group" style="margin-bottom: 2rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1e293b;">Confirmar Nueva Contraseña</label>
                                <input type="password" name="confirm_password" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 200px; background: #1e293b;">Actualizar Contraseña</button>
                        </form>
                    </div>

                    <!-- Billing Settings -->
                    <div id="setCont_billing" class="settings-content" style="display: none;">
                        <h3 style="margin-bottom: 2rem;">Plan y Facturación</h3>
                        <div class="premium-card" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <div>
                                    <span style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Tu Plan Actual</span>
                                    <h2 style="color: var(--primary); margin-top: 0.25rem;">Plan <?php echo ucfirst($user_data['plan']); ?></h2>
                                </div>
                                <?php if($user_data['plan'] !== 'business'): ?>
                                    <button onclick="upgradeAccount()" class="btn btn-primary" style="background: #10b981;">Mejorar Plan</button>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 0.9rem; color: #64748b;">
                                <p><i class="fas fa-check-circle" style="color: #10b981;"></i> Límite de enlaces: <strong><?php echo $user_data['link_limit']; ?></strong></p>
                                <p><i class="fas fa-check-circle" style="color: #10b981;"></i> Soporte prioritario</p>
                                <p><i class="fas fa-check-circle" style="color: #10b981;"></i> Analíticas detalladas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Create View (Hidden by default) -->
        <div id="bulkView" style="display: none;">
            <div class="premium-card" style="max-width: 800px; margin: 0 auto; padding: 3rem; text-align: center;">
                <div style="width: 80px; height: 80px; background: #eef2ff; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; font-size: 2rem;">
                    <i class="fas fa-file-csv"></i>
                </div>
                <h2 style="margin-bottom: 1rem;">Creación Masiva de QRs</h2>
                <p style="color: #64748b; margin-bottom: 2rem;">Sube un archivo CSV para generar múltiples códigos QR dinámicos al instante. Perfecto para campañas de gran volumen.</p>
                
                <div style="background: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 1rem; padding: 3rem; margin-bottom: 2rem; transition: 0.3s;" ondragover="this.style.borderColor='var(--primary)'" ondragleave="this.style.borderColor='#e2e8f0'">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #94a3b8; margin-bottom: 1rem;"></i>
                    <h4 style="margin-bottom: 0.5rem;">Arrastra tu archivo aquí</h4>
                    <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 1.5rem;">O selecciona un archivo de tu computadora</p>
                    <input type="file" id="bulkCsvInput" accept=".csv" hidden onchange="handleBulkFile(this)">
                    <button class="btn btn-primary" onclick="document.getElementById('bulkCsvInput').click()">Seleccionar CSV</button>
                </div>

                <div style="display: flex; justify-content: center; gap: 2rem;">
                    <a href="assets/templates/bulk_template.csv" download style="color: var(--primary); text-decoration: none; font-size: 0.9rem; font-weight: 600;">
                        <i class="fas fa-download"></i> Descargar Plantilla CSV
                    </a>
                    <span style="color: #cbd5e1;">|</span>
                    <span style="color: #64748b; font-size: 0.9rem;">Formato: <code>Nombre, URL</code></span>
                </div>

                <!-- Progress Tracker -->
                <div id="bulkProgress" style="display: none; margin-top: 3rem; text-align: left;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span id="bulkStatusText" style="font-weight: 600; font-size: 0.9rem;">Procesando...</span>
                        <span id="bulkPercentText" style="color: var(--primary); font-weight: 700;">0%</span>
                    </div>
                    <div style="width: 100%; height: 10px; background: #f1f5f9; border-radius: 10px; overflow: hidden;">
                        <div id="bulkProgressBar" style="width: 0%; height: 100%; background: var(--primary); transition: width 0.3s;"></div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .settings-nav-item {
                padding: 1rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                cursor: pointer;
                border-radius: 0.5rem;
                color: #64748b;
                transition: all 0.2s;
                font-weight: 500;
            }
            .settings-nav-item:hover { background: #f1f5f9; color: var(--primary); }
            .settings-nav-item.active { background: #f1f5f9; color: var(--primary); }
            .settings-nav-item i { width: 20px; font-size: 1.1rem; }
        </style>
    </main>

    <!-- Bulk Actions Bar -->
    <div id="bulkActionsBar" style="display: none; position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%); background: #1e293b; color: white; padding: 1rem 2rem; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.3); z-index: 2000; align-items: center; gap: 2rem;">
        <span id="bulkCount" style="font-weight: 600;">0 seleccionados</span>
        <div style="display: flex; gap: 1rem;">
            <button class="btn" style="background: #4f46e5; color: white;" onclick="openBulkFolderModal()"><i class="fas fa-folder-plus"></i> Mover</button>
            <button class="btn" style="background: #64748b; color: white;" onclick="bulkArchive()"><i class="fas fa-archive"></i> Archivar</button>
            <button class="btn" style="background: #ef4444; color: white;" onclick="bulkDelete()"><i class="fas fa-trash"></i> Eliminar</button>
        </div>
        <button onclick="clearSelection()" style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1.2rem;">&times;</button>
    </div>

    <!-- Modal Create -->
    <!-- Modal Individual Stats -->
    <div id="individualStatsModal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
        <div class="premium-card" style="width: 1000px; max-width: 95%; background: white; padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 id="indivStatsTitle" style="color: #1e293b;">Estadísticas del Código</h2>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-primary" id="csvExportBtn" style="background: #10b981;">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </button>
                    <button onclick="closeIndivStats()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stats-card">
                    <div class="stats-label">Escaneos Totales</div>
                    <div class="stats-value" id="indivTotalScans">0</div>
                </div>
                <div class="stats-card">
                    <div class="stats-label">Escaneos Únicos</div>
                    <div class="stats-value" id="indivUniqueScans">0</div>
                </div>
            </div>

            <!-- Time Series Chart -->
            <div class="premium-card" style="margin-bottom: 2rem; padding: 1.5rem;">
                <h4 style="margin-bottom: 1rem; color: #64748b;">Escaneos en el tiempo (Últimos 30 días)</h4>
                <div style="height: 300px;">
                    <canvas id="dailyStatsChart"></canvas>
                </div>
            </div>

            <div class="stats-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 2rem;">
                 <div class="premium-card" style="padding: 1.5rem;">
                    <h4 style="margin-bottom: 1rem; color: #64748b;">Sistemas Operativos</h4>
                    <div style="height: 250px;"><canvas id="indivOsChart"></canvas></div>
                </div>
                <div class="premium-card" style="padding: 1.5rem;">
                    <h4 style="margin-bottom: 1rem; color: #64748b;">Navegadores</h4>
                    <div style="height: 250px;"><canvas id="indivBrowserChart"></canvas></div>
                </div>
            </div>

            <div class="stats-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 2rem;">
                <div class="premium-card" style="padding: 1.5rem;">
                    <h4 style="margin-bottom: 1rem; color: #64748b;">Dispositivos</h4>
                    <div style="height: 250px;"><canvas id="indivDeviceChart"></canvas></div>
                </div>
                <div class="premium-card" style="padding: 1.5rem;">
                    <h4 style="margin-bottom: 1rem; color: #64748b;">Países</h4>
                    <div style="height: 250px;"><canvas id="indivCountryChart"></canvas></div>
                </div>
            </div>

            <!-- Captured Leads Table -->
            <div id="leadsSection" style="margin-top: 2rem; display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h4 style="color: #1e293b; font-weight: 700;">
                        <i class="fas fa-users-viewfinder" style="color: #4f46e5;"></i> Leads Capturados
                    </h4>
                    <button id="exportLeadsBtn" class="btn" style="background: #10b981; color: white; font-size: 0.8rem; padding: 0.5rem 1rem;">
                        <i class="fas fa-file-excel"></i> Exportar a CSV
                    </button>
                </div>
                <div class="premium-card" style="padding: 0; overflow: hidden; border: 1px solid #e2e8f0;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                        <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600;">
                            <tr>
                                <th style="padding: 1rem;">Nombre</th>
                                <th style="padding: 1rem;">Email</th>
                                <th style="padding: 1rem;">Teléfono</th>
                                <th style="padding: 1rem;">Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="leadsTableBody">
                            <!-- Leads will be injected here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Folder Selection -->
    <div id="bulkFolderModal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
        <div class="premium-card" style="width: 400px; background: white; padding: 2rem;">
            <h3 style="margin-bottom: 1.5rem; color: #1e293b;">Mover seleccionados</h3>
            <div class="input-group">
                <label>Selecciona carpeta destino</label>
                <select id="bulkFolderSelect" class="input-field">
                    <option value="">Ninguna (Dashboard principal)</option>
                    <?php foreach($folders as $f): ?>
                        <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button class="btn" onclick="closeBulkFolderModal()" style="flex: 1; background: #f1f5f9; color: #1e293b;">Cancelar</button>
                <button class="btn btn-primary" onclick="confirmBulkMove()" style="flex: 1;">Mover</button>
            </div>
        </div>
    </div>
    <div id="folderModal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
        <div class="premium-card" style="width: 400px; background: white; padding: 2rem;">
            <h3 style="margin-bottom: 1.5rem; color: #1e293b;">Mover a Carpeta</h3>
            <div class="input-group">
                <label>Selecciona una carpeta</label>
                <select id="folderSelect" class="input-field">
                    <option value="">Ninguna (Dashboard principal)</option>
                    <?php foreach($folders as $f): ?>
                        <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button class="btn" onclick="closeFolderModal()" style="flex: 1; background: #f1f5f9; color: #1e293b;">Cancelar</button>
                <button class="btn btn-primary" onclick="confirmFolderMove()" style="flex: 1;">Mover</button>
            </div>
        </div>
    </div>
        <div class="premium-card" style="width: 550px; max-width: 90%; background: white; padding: 2rem;">
            <h2 id="modalTitle" style="margin-bottom: 1.5rem; color: #1e293b;">Nuevo Código QR</h2>
            <form id="createLinkForm">
                <input type="hidden" id="edit_link_id" value="">
                <div class="input-group">
                    <label style="color: #64748b;">Tipo de QR</label>
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                        <button type="button" class="btn btn-type active" onclick="setQRType('url')" id="btnTypeUrl" style="flex: 1; font-size: 0.8rem; background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1;">URL</button>
                        <button type="button" class="btn btn-type" <?php echo ($user_plan === 'free' ? 'disabled title="Función Pro"' : 'onclick="setQRType(\'vcard\')"'); ?> id="btnTypeVcard" style="flex: 1; font-size: 0.8rem; background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; position: relative;">
                            vCard
                            <?php if($user_plan === 'free'): ?><span style="position: absolute; top: -8px; right: -8px; background: #f59e0b; color: white; font-size: 0.6rem; padding: 2px 4px; border-radius: 4px;">PRO</span><?php endif; ?>
                        </button>
                        <button type="button" class="btn btn-type" onclick="setQRType('wifi')" id="btnTypeWifi" style="flex: 1; font-size: 0.8rem; background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1;">WiFi</button>
                        <button type="button" class="btn btn-type" onclick="setQRType('whatsapp')" id="btnTypeWhatsapp" style="flex: 1; font-size: 0.8rem; background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1;"><i class="fab fa-whatsapp"></i> WhatsApp</button>
                        <button type="button" class="btn btn-type" <?php echo ($user_plan === 'free' ? 'disabled title="Función Pro"' : 'onclick="setQRType(\'pdf\')"'); ?> id="btnTypePdf" style="flex: 1; font-size: 0.8rem; background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; position: relative;">
                            <i class="fas fa-file-pdf"></i> PDF
                            <?php if($user_plan === 'free'): ?><span style="position: absolute; top: -8px; right: -8px; background: #f59e0b; color: white; font-size: 0.6rem; padding: 2px 4px; border-radius: 4px;">PRO</span><?php endif; ?>
                        </button>
                        <button type="button" class="btn btn-type" <?php echo ($user_plan === 'free' ? 'disabled title="Función Pro"' : 'onclick="setQRType(\'social\')"'); ?> id="btnTypeSocial" style="flex: 1; font-size: 0.8rem; background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; position: relative;">
                            <i class="fas fa-share-alt"></i> Social
                            <?php if($user_plan === 'free'): ?><span style="position: absolute; top: -8px; right: -8px; background: #f59e0b; color: white; font-size: 0.6rem; padding: 2px 4px; border-radius: 4px;">PRO</span><?php endif; ?>
                        </button>
                    </div>
                    <input type="hidden" id="qr_type" value="url">
                </div>

                <div class="input-group">
                    <label style="color: #64748b;">Nombre de la Campaña / Título</label>
                    <input type="text" id="campaign" class="input-field" placeholder="Ej: Mi Tarjeta Personal" required style="color: #1e293b; border: 1px solid #e2e8f0;">
                </div>

                <!-- URL Content -->
                <div id="content_url" class="type-content">
                    <div class="input-group">
                        <label style="color: #64748b;">URL de Destino</label>
                        <input type="url" id="destination_url" class="input-field" placeholder="https://..." style="color: #1e293b; border: 1px solid #e2e8f0;">
                    </div>
                </div>

                <!-- vCard Content -->
                <div id="content_vcard" class="type-content" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <div class="input-group"><label style="font-size: 0.75rem;">Nombre</label><input type="text" id="vName" class="input-field" placeholder="Juan"></div>
                        <div class="input-group"><label style="font-size: 0.75rem;">Apellido</label><input type="text" id="vLast" class="input-field" placeholder="Pérez"></div>
                    </div>
                    <div class="input-group"><label style="font-size: 0.75rem;">Teléfono</label><input type="text" id="vPhone" class="input-field" placeholder="+52 ..."></div>
                    <div class="input-group"><label style="font-size: 0.75rem;">Email</label><input type="email" id="vEmail" class="input-field" placeholder="juan@empleo.com"></div>
                    <div class="input-group"><label style="font-size: 0.75rem;">Empresa / Cargo</label><input type="text" id="vWork" class="input-field" placeholder="CEO en Mi Empresa"></div>
                </div>

                <!-- WiFi Content -->
                <div id="content_wifi" class="type-content" style="display: none;">
                    <div class="input-group"><label style="font-size: 0.75rem;">Nombre de Red (SSID)</label><input type="text" id="wifiSsid" class="input-field" placeholder="Mi_WiFi_Casa"></div>
                    <div class="input-group"><label style="font-size: 0.75rem;">Contraseña</label><input type="text" id="wifiPass" class="input-field" placeholder="********"></div>
                    <div class="input-group">
                        <label style="font-size: 0.75rem;">Encriptación</label>
                        <select id="wifiEnc" class="input-field">
                            <option value="WPA">WPA/WPA2</option>
                            <option value="WEP">WEP</option>
                            <option value="nopass">Sin contraseña</option>
                        </select>
                    </div>
                <!-- WhatsApp Content -->
                <div id="content_whatsapp" class="type-content" style="display: none;">
                    <div class="input-group"><label style="font-size: 0.75rem;">Número de Teléfono</label><input type="text" id="waPhone" class="input-field" placeholder="Ej: 5212223334455"></div>
                    <div class="input-group"><label style="font-size: 0.75rem;">Mensaje Pre-llenado</label><textarea id="waMsg" class="input-field" placeholder="Hola, me interesa tu servicio..." style="height: 80px;"></textarea></div>
                </div>

                <!-- PDF Content -->
                <div id="content_pdf" class="type-content" style="display: none;">
                    <div class="input-group">
                        <label style="font-size: 0.75rem;">Subir Archivo PDF</label>
                        <input type="file" id="pdfFile" class="input-field" accept=".pdf" style="padding-top: 0.5rem;">
                        <small style="color: #64748b; font-size: 0.7rem;">Máximo 10MB. El PDF se mostrará en un visor premium.</small>
                    </div>
                </div>

                <!-- Social Content -->
                <div id="content_social" class="type-content" style="display: none;">
                    <p style="font-size: 0.75rem; color: #64748b; margin-bottom: 1rem;">Ingresa los enlaces de tus redes sociales para crear tu "Link-in-Bio".</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="input-group">
                            <label style="font-size: 0.7rem;"><i class="fab fa-instagram"></i> Instagram</label>
                            <input type="text" id="sInsta" class="input-field" placeholder="@usuario">
                        </div>
                        <div class="input-group">
                            <label style="font-size: 0.7rem;"><i class="fab fa-tiktok"></i> TikTok</label>
                            <input type="text" id="sTiktok" class="input-field" placeholder="@usuario">
                        </div>
                        <div class="input-group">
                            <label style="font-size: 0.7rem;"><i class="fab fa-facebook"></i> Facebook</label>
                            <input type="text" id="sFb" class="input-field" placeholder="mi.pagina">
                        </div>
                        <div class="input-group">
                            <label style="font-size: 0.7rem;"><i class="fab fa-youtube"></i> YouTube</label>
                            <input type="text" id="sYt" class="input-field" placeholder="@canal">
                        </div>
                        <div class="input-group">
                            <label style="font-size: 0.7rem;"><i class="fab fa-linkedin"></i> LinkedIn</label>
                            <input type="text" id="sLi" class="input-field" placeholder="in/usuario">
                        </div>
                        <div class="input-group">
                            <label style="font-size: 0.7rem;"><i class="fab fa-twitter"></i> Twitter/X</label>
                            <input type="text" id="sTw" class="input-field" placeholder="@usuario">
                        </div>
                    </div>
                    <div class="input-group">
                        <label style="font-size: 0.7rem;"><i class="fas fa-globe"></i> Sitio Web Personal</label>
                        <input type="url" id="sWeb" class="input-field" placeholder="https://tuweb.com">
                    </div>

                    <!-- PRO Style Customizer -->
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <label style="font-size: 0.85rem; font-weight: 800; color: #1e293b;"><i class="fas fa-palette"></i> Personalización Visual</label>
                            <?php if ($user_plan === 'free'): ?>
                                <span style="background: #f59e0b; color: white; font-size: 0.65rem; padding: 3px 8px; border-radius: 20px; font-weight: 700;">PRO FEATURE</span>
                            <?php endif; ?>
                        </div>

                        <div style="<?php echo ($user_plan === 'free' ? 'opacity: 0.5; pointer-events: none;' : ''); ?>">
                            <div class="input-group">
                                <label style="font-size: 0.7rem;">Tema Predefinido</label>
                                <select id="sTheme" class="input-field" onchange="applyThemePreset(this.value)">
                                    <option value="glass">Cristal Moderno (Default)</option>
                                    <option value="dark">Modo Oscuro Profundo</option>
                                    <option value="minimal">Minimalista Blanco</option>
                                    <option value="vibrant">Vibrante Gradiente</option>
                                    <option value="retro">Retro Terminal</option>
                                </select>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="input-group">
                                    <label style="font-size: 0.7rem;">Color Principal (Botones)</label>
                                    <input type="color" id="sColor" class="input-field" value="#4f46e5" style="height: 40px; padding: 2px;">
                                </div>
                                <div class="input-group">
                                    <label style="font-size: 0.7rem;">Fuente</label>
                                    <select id="sFont" class="input-field">
                                        <option value="'Plus Jakarta Sans', sans-serif">Jakarta Sans</option>
                                        <option value="'Inter', sans-serif">Inter</option>
                                        <option value="'Roboto Mono', monospace">Roboto Mono</option>
                                        <option value="'Outfit', sans-serif">Outfit</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <label style="color: #64748b;">Enlace personalizado (opcional)</label>
                    <div style="display: flex; align-items: center;">
                        <span style="background: #f1f5f9; padding: 0.75rem; border-radius: 0.5rem 0 0 0.5rem; border: 1px solid #cbd5e1; border-right: none; font-size: 0.9rem; color: #64748b;">qr.link/</span>
                        <input type="text" id="custom_slug" class="input-field" placeholder="mi-enlace" style="border-radius: 0 0.5rem 0.5rem 0; color: #1e293b; border: 1px solid #e2e8f0; margin-bottom: 0;">
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="button" class="btn" onclick="closeCreateModal()" style="flex: 1; background: #f1f5f9; color: #1e293b;">Cancelar</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary" style="flex: 2;">Crear QR</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal QR Preview & Design -->
    <div id="qrModal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center; overflow-y: auto;">
        <div class="premium-card" style="width: 850px; max-width: 95%; background: white; display: flex; flex-direction: row; gap: 2rem; padding: 2rem;">
            <!-- Left: QR Preview -->
            <div style="flex: 1; text-align: center; border-right: 1px solid #e2e8f0; padding-right: 2rem;">
                <h3 id="qrModalTitle" style="margin-bottom: 1.5rem; color: #1e293b;">Diseño del QR</h3>
                <div id="qrcode" style="background: white; padding: 1rem; border: 1px solid #e2e8f0; border-radius: 1rem; display: inline-block; margin-bottom: 1.5rem; min-height: 300px; min-width: 300px;"></div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <div style="flex: 1;">
                        <label style="font-size: 0.75rem; color: #64748b; display: block; margin-bottom: 0.5rem; text-align: left;">Descargar como:</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-primary" style="flex: 1; padding: 0.5rem; font-size: 0.8rem;" onclick="downloadAs('png')">PNG</button>
                            <button class="btn btn-primary" style="flex: 1; padding: 0.5rem; font-size: 0.8rem; background: #6366f1;" onclick="downloadAs('svg')">SVG</button>
                            <button class="btn btn-primary" style="flex: 1; padding: 0.5rem; font-size: 0.8rem; background: #ec4899;" onclick="downloadAs('pdf')">PDF</button>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <button class="btn" style="width: 100%; border: 1px solid #10b981; color: #10b981; background: white;" onclick="saveDesign()">
                        <i class="fas fa-save"></i> Guardar diseño en este QR
                    </button>
                </div>
            </div>

            <!-- Right: Design Controls -->
            <div style="flex: 1.5; text-align: left; max-height: 80vh; overflow-y: auto; padding-right: 0.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h4 style="font-weight: 700; color: #1e293b;">Personalización Avanzada</h4>
                    <button onclick="closeQRModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
                </div>

                <!-- Template Section -->
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px dashed #cbd5e1;">
                    <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">Usar Plantilla Guardada</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <select id="templateSelect" class="input-field" style="flex: 2; margin-bottom: 0;" onchange="applySelectedTemplate()">
                            <option value="">-- Elige una plantilla --</option>
                        </select>
                        <button class="btn" style="background: #64748b; color: white; padding: 0 1rem; border-radius: 0.5rem;" onclick="saveAsTemplatePrompt()">
                            <i class="fas fa-plus"></i> Guardar como Nueva
                        </button>
                    </div>
                </div>

                <!-- Tabs/Accordions for categories -->
                <div class="input-group">
                    <label>Estilo de Puntos (Dots)</label>
                    <select id="dotsStyle" class="input-field" onchange="updateLivePreview()">
                        <option value="square">Cuadrado</option>
                        <option value="dots">Puntos</option>
                        <option value="rounded">Redondeado</option>
                        <option value="extra-rounded">Muy Redondeado</option>
                        <option value="classy">Elegante</option>
                        <option value="classy-rounded">Elegante Redondeado</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Estilo de Esquinas (Esquinas Cuadradas)</label>
                    <select id="cornersStyle" class="input-field" onchange="updateLivePreview()">
                        <option value="square">Cuadrado</option>
                        <option value="dot">Punto</option>
                        <option value="extra-rounded">Redondeado</option>
                    </select>
                </div>

                <!-- Dot Colors & Gradients -->
                <div class="input-group">
                    <label style="font-weight: 700; display: block; margin-top: 1rem;">Color de los Puntos (Dots)</label>
                    <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <div style="flex: 1;">
                                <label style="font-size: 0.75rem;">Color 1</label>
                                <input type="color" id="qrColor" class="input-field" value="#000000" style="height: 45px; padding: 5px;" onchange="updateLivePreview()">
                            </div>
                            <div style="flex: 1;">
                                <label style="font-size: 0.75rem;">Gradiente</label>
                                <select id="fgGradientType" class="input-field" onchange="toggleGradientOptions('fg')">
                                    <option value="none">Ninguno</option>
                                    <option value="linear">Lineal</option>
                                    <option value="radial">Radial</option>
                                </select>
                            </div>
                        </div>
                        <div id="fgGradientOptions" style="display: none; padding: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; margin-top: 0.5rem;">
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <div style="flex: 1;">
                                    <label style="font-size: 0.75rem;">Color 2</label>
                                    <input type="color" id="qrColor2" class="input-field" value="#4f46e5" style="height: 40px; padding: 5px;" onchange="updateLivePreview()">
                                </div>
                                <div style="flex: 1;">
                                    <label style="font-size: 0.75rem;">Rotación</label>
                                    <input type="number" id="fgRotation" class="input-field" value="0" min="0" max="360" onchange="updateLivePreview()" style="margin-bottom:0;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Background Colors & Gradients -->
                <div class="input-group">
                    <label style="font-weight: 700; display: block; margin-top: 1.5rem;">Color de Fondo</label>
                    <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <div style="flex: 1;">
                                <label style="font-size: 0.75rem;">Color 1</label>
                                <input type="color" id="bgColor" class="input-field" value="#ffffff" style="height: 45px; padding: 5px;" onchange="updateLivePreview()">
                            </div>
                            <div style="flex: 1;">
                                <label style="font-size: 0.75rem;">Gradiente</label>
                                <select id="bgGradientType" class="input-field" onchange="toggleGradientOptions('bg')">
                                    <option value="none">Ninguno</option>
                                    <option value="linear">Lineal</option>
                                    <option value="radial">Radial</option>
                                </select>
                            </div>
                        </div>
                        <div id="bgGradientOptions" style="display: none; padding: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; margin-top: 0.5rem;">
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <div style="flex: 1;">
                                    <label style="font-size: 0.75rem;">Color 2</label>
                                    <input type="color" id="bgColor2" class="input-field" value="#f1f5f9" style="height: 40px; padding: 5px;" onchange="updateLivePreview()">
                                </div>
                                <div style="flex: 1;">
                                    <label style="font-size: 0.75rem;">Rotación</label>
                                    <input type="number" id="bgRotation" class="input-field" value="0" min="0" max="360" onchange="updateLivePreview()" style="margin-bottom:0;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <label>Estilo de Marco (Frame)</label>
                    <select id="frameType" class="input-field" onchange="updateLivePreview()">
                        <option value="none">Sin Marco</option>
                        <option value="basic">Caja Simple</option>
                        <option value="header">Caja con Título</option>
                        <option value="label">Etiqueta Inferior (QR.io)</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Texto del Marco</label>
                    <input type="text" id="labelText" class="input-field" placeholder="Ejem: ESCANÉAME" oninput="updateLivePreview()">
                </div>

                <div class="input-group" id="frameColorGroup">
                    <label>Color del Marco</label>
                    <input type="color" id="frameColor" class="input-field" value="#4f46e5" style="height: 45px;" onchange="updateLivePreview()">
                </div>

                <div class="input-group">
                    <label>Imagen Logo (URL)</label>
                    <input type="text" id="logoUrl" class="input-field" placeholder="https://miweb.com/logo.png" oninput="updateLivePreview()">
                </div>

                <!-- LEAD GENERATION SECTION -->
                <div style="margin-top: 2rem; padding: 1.5rem; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-user-tag" style="color: #4f46e5; font-size: 1.2rem;"></i>
                            <h4 style="color: #1e293b; font-weight: 700;">Captura de Leads</h4>
                        </div>
                        <label class="switch" style="position: relative; display: inline-block; width: 50px; height: 26px;">
                            <input type="checkbox" id="lead_gen_enabled" style="opacity: 0; width: 0; height: 0;">
                            <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 34px;"></span>
                        </label>
                    </div>
                    <p style="font-size: 0.8rem; color: #475569;">Si habilitas esta opción, los usuarios deberán dejar su contacto antes de ver el contenido del QR.</p>
                </div>
                
                <style>
                    .switch input:checked + .slider { background-color: #4f46e5; }
                    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
                    .switch input:checked + .slider:before { transform: translateX(24px); }
                </style>
                
                <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 1rem;">
                    * Los cambios se previsualizan en tiempo real. No olvides guardar para aplicar al link.
                </p>
            </div>
        </div>
    </div>

    <script src="public/assets/js/qr_generator.js"></script>
    <script>
        function openCreateModal() { 
            document.getElementById('edit_link_id').value = '';
            document.getElementById('modalTitle').innerText = 'Nuevo Código QR';
            document.getElementById('submitBtn').innerText = 'Crear QR';
            document.getElementById('createLinkForm').reset();
            document.getElementById('createModal').style.display = 'flex'; 
        }
        function closeCreateModal() { document.getElementById('createModal').style.display = 'none'; }
        
        async function openEditModal(linkId) {
            const modal = document.getElementById('createModal');
            const title = document.getElementById('modalTitle');
            const btn = document.getElementById('submitBtn');
            const hiddenId = document.getElementById('edit_link_id');
            
            title.innerText = 'Editar Código QR';
            btn.innerText = 'Guardar Cambios';
            hiddenId.value = linkId;
            
            modal.style.display = 'flex';
            
            // Fetch current data
            try {
                const response = await fetch('api/get_link_details.php?id=' + linkId);
                const link = await response.json();
                
                if (link.error) {
                    alert(link.error);
                    closeCreateModal();
                    return;
                }
                
                // Set type and populate fields
                setQRType(link.type);
                document.getElementById('campaign').value = link.campaign;
                document.getElementById('custom_slug').value = link.code; // Note: slug usually not editable, but showing for ref
                document.getElementById('custom_slug').disabled = true;

                if (link.type === 'url') {
                    document.getElementById('destination_url').value = link.destination_url;
                } else if (link.type === 'vcard' || link.type === 'wifi' || link.type === 'whatsapp' || link.type === 'social') {
                    const content = JSON.parse(link.content);
                    // This part needs specific field mapping per type
                    populateTypeFields(link.type, content);
                }
                
                if (link.theme_data) {
                    const theme = JSON.parse(link.theme_data);
                    document.getElementById('sTheme').value = theme.preset || 'glass';
                    document.getElementById('sColor').value = theme.color || '#4f46e5';
                    document.getElementById('sFont').value = theme.font || "'Plus Jakarta Sans', sans-serif";
                }

            } catch (e) {
                console.error(e);
            }
        }

        function populateTypeFields(type, content) {
            if (type === 'vcard') {
                document.getElementById('vName').value = content.name;
                document.getElementById('vLast').value = content.last;
                document.getElementById('vPhone').value = content.phone;
                document.getElementById('vEmail').value = content.email;
                document.getElementById('vWork').value = content.work;
            } else if (type === 'wifi') {
                document.getElementById('wifiSsid').value = content.ssid;
                document.getElementById('wifiPass').value = content.pass;
                document.getElementById('wifiEnc').value = content.enc;
            } else if (type === 'whatsapp') {
                document.getElementById('waPhone').value = content.phone;
                document.getElementById('waMsg').value = content.msg;
            } else if (type === 'social') {
                document.getElementById('sInsta').value = content.insta;
                document.getElementById('sTiktok').value = content.tiktok;
                document.getElementById('sFb').value = content.fb;
                document.getElementById('sYt').value = content.yt;
                document.getElementById('sLi').value = content.li;
                document.getElementById('sTw').value = content.tw;
                document.getElementById('sWeb').value = content.web;
            }
        }
        function closeQRModal() { document.getElementById('qrModal').style.display = 'none'; }
        
        const originalShowQR = showQR;
        showQR = function(code, design = {}) {
            document.getElementById('qrModal').style.display = 'flex';
            document.getElementById('qrModalTitle').innerText = 'Código: ' + code;
            
            // Populate design fields if we have saved data
            if (design.dots_style) document.getElementById('dotsStyle').value = design.dots_style;
            if (design.corners_square_style) document.getElementById('cornersStyle').value = design.corners_square_style;
            if (design.lead_gen_enabled !== undefined) document.getElementById('lead_gen_enabled').checked = (design.lead_gen_enabled == 1);
            if (design.fg_color) document.getElementById('qrColor').value = design.fg_color;
            if (design.bg_color) document.getElementById('bgColor').value = design.bg_color;
            if (design.logo_path) document.getElementById('logoUrl').value = design.logo_path;
            if (design.label_text) document.getElementById('labelText').value = design.label_text;

            originalShowQR(code);
        }

        function switchTab(tab) {
            const qrList = document.getElementById('qrListView');
            const stats = document.getElementById('statsView');
            const settings = document.getElementById('settingsView');
            const bulk = document.getElementById('bulkView');
            const navItems = document.querySelectorAll('.nav-item');
            const pageTitle = document.getElementById('pageTitle');
            
            navItems.forEach(item => item.classList.remove('active'));
            
            if (tab === 'qrs') {
                qrList.style.display = 'block';
                stats.style.display = 'none';
                settings.style.display = 'none';
                bulk.style.display = 'none';
                pageTitle.innerText = "Códigos QR Activos (<?php echo count($links); ?>)";
                document.querySelector('[onclick="switchTab(\'qrs\')"]').classList.add('active');
            } else if (tab === 'stats') {
                qrList.style.display = 'none';
                stats.style.display = 'block';
                settings.style.display = 'none';
                bulk.style.display = 'none';
                pageTitle.innerText = "Estadísticas de Escaneo (Últimos 30 días)";
                document.getElementById('navItem_stats').classList.add('active');
                loadStatsCharts();
            } else if (tab === 'settings') {
                qrList.style.display = 'none';
                stats.style.display = 'none';
                settings.style.display = 'block';
                bulk.style.display = 'none';
                pageTitle.innerText = "Configuración de Cuenta";
                document.getElementById('navItem_settings').classList.add('active');
            } else if (tab === 'bulk') {
                qrList.style.display = 'none';
                stats.style.display = 'none';
                settings.style.display = 'none';
                bulk.style.display = 'block';
                pageTitle.innerText = "Creación Masiva de QRs";
                document.getElementById('navItem_bulk').classList.add('active');
            }
        }

        async function handleBulkFile(input) {
            if (!input.files || !input.files[0]) return;
            
            const file = input.files[0];
            if (file.type !== "text/csv" && !file.name.endsWith('.csv')) {
                alert("Por favor sube un archivo CSV válido.");
                return;
            }

            if (!confirm(`¿Estás seguro de que quieres procesar el archivo "${file.name}"?`)) return;

            const formData = new FormData();
            formData.append('csv', file);

            const progressDiv = document.getElementById('bulkProgress');
            const bar = document.getElementById('bulkProgressBar');
            const percentText = document.getElementById('bulkPercentText');
            const statusText = document.getElementById('bulkStatusText');

            progressDiv.style.display = 'block';
            statusText.innerText = "Subiendo y procesando...";
            bar.style.width = '20%';
            percentText.innerText = '20%';

            try {
                const response = await fetch('api/bulk_create.php', {
                    method: 'POST',
                    body: formData
                });
                
                bar.style.width = '60%';
                percentText.innerText = '60%';
                
                const data = await response.json();
                
                if (data.success) {
                    bar.style.width = '100%';
                    percentText.innerText = '100%';
                    statusText.innerText = `¡Éxito! Se crearon ${data.count} códigos QR.`;
                    statusText.style.color = '#10b981';
                    
                    setTimeout(() => {
                        window.location.href = 'dashboard.php?view=active';
                    }, 2000);
                } else {
                    statusText.innerText = "Error: " + data.error;
                    statusText.style.color = '#ef4444';
                    bar.style.backgroundColor = '#ef4444';
                }
            } catch (error) {
                console.error("Bulk Error:", error);
                statusText.innerText = "Ocurrió un error en el servidor.";
                statusText.style.color = '#ef4444';
            }
        }

        function switchSettingsTab(tab) {
            document.querySelectorAll('.settings-content').forEach(c => c.style.display = 'none');
            document.querySelectorAll('.settings-nav-item').forEach(i => i.classList.remove('active'));
            
            document.getElementById('setCont_' + tab).style.display = 'block';
            document.getElementById('setTab_' + tab).classList.add('active');
        }

        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('settingsAvatar').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        async function updateProfile(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const avatarFile = document.getElementById('avatarInput').files[0];
            if (avatarFile) formData.append('avatar', avatarFile);

            try {
                const response = await fetch('api/update_profile.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    alert('Perfil actualizado con éxito');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ocurrió un error al actualizar el perfil');
            }
        }

        async function updatePassword(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const newPass = formData.get('new_password');
            const confirmPass = formData.get('confirm_password');

            if (newPass !== confirmPass) {
                alert('Las nuevas contraseñas no coinciden');
                return;
            }

            try {
                const response = await fetch('api/update_password.php', {
                    method: 'POST',
                    body: JSON.stringify(Object.from_base64(btoa(new TextEncoder().encode(JSON.stringify(Object.fromEntries(formData)))))) // Simpler as object
                });
                // Let's use a cleaner approach for JSON post
                const response2 = await fetch('api/update_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(Object.fromEntries(formData))
                });
                const data = await response2.json();
                if (data.success) {
                    alert('Contraseña actualizada con éxito');
                    e.target.reset();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ocurrió un error al actualizar la contraseña');
            }
        }

        let charts = {};
        async function loadStatsCharts() {
            const res = await fetch('api/stats.php');
            const data = await res.json();

            if (charts.os) charts.os.destroy();
            if (charts.browser) charts.browser.destroy();
            if (charts.device) charts.device.destroy();
            if (charts.country) charts.country.destroy();
            if (charts.city) charts.city.destroy();
            if (charts.globalDaily) charts.globalDaily.destroy();

            const chartColors = ['#4f46e5', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4', '#84cc16'];

            charts.os = new Chart(document.getElementById('osChart'), {
                type: 'doughnut',
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
                data: {
                    labels: data.os.map(o => o.label),
                    datasets: [{
                        data: data.os.map(o => o.value),
                        backgroundColor: chartColors
                    }]
                }
            });

            charts.browser = new Chart(document.getElementById('browserChart'), {
                type: 'doughnut',
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
                data: {
                    labels: data.browsers.map(b => b.label),
                    datasets: [{
                        data: data.browsers.map(b => b.value),
                        backgroundColor: chartColors
                    }]
                }
            });

            charts.device = new Chart(document.getElementById('deviceChart'), {
                type: 'pie',
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
                data: {
                    labels: data.devices.map(d => d.label),
                    datasets: [{
                        data: data.devices.map(d => d.value),
                        backgroundColor: ['#4f46e5', '#10b981', '#f59e0b']
                    }]
                }
            });

            charts.country = new Chart(document.getElementById('countryChart'), {
                type: 'bar',
                options: { maintainAspectRatio: false, indexAxis: 'y' },
                data: {
                    labels: data.countries.map(c => c.label),
                    datasets: [{
                        label: 'Escaneos',
                        data: data.countries.map(c => c.value),
                        backgroundColor: '#4f46e5'
                    }]
                }
            });

            charts.city = new Chart(document.getElementById('cityChart'), {
                type: 'bar',
                options: { maintainAspectRatio: false },
                data: {
                    labels: data.cities.map(c => c.label),
                    datasets: [{
                        label: 'Escaneos',
                        data: data.cities.map(c => c.value),
                        backgroundColor: '#8b5cf6'
                    }]
                }
            });

            charts.globalDaily = new Chart(document.getElementById('globalDailyChart'), {
                type: 'line',
                options: { maintainAspectRatio: false },
                data: {
                    labels: data.daily.map(d => d.label),
                    datasets: [{
                        label: 'Escaneos Totales',
                        data: data.daily.map(d => d.value),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                }
            });
        }
    </script>
    <script>
        async function upgradeAccount() {
            if (!confirm("¿Deseas simular una mejora al Plan PRO? (Esto te dará enlaces ilimitados y acceso a funciones premium)")) return;
            try {
                const response = await fetch('api/auth.php?action=upgrade');
                const data = await response.json();
                if (data.success) {
                    alert('¡Felicidades! Ahora eres Usuario PRO. 🚀');
                    window.location.reload();
                } else {
                    alert('Error al mejorar plan: ' + data.error);
                }
            } catch (e) {
                console.error(e);
            }
        }

        // Connect the "Upgrade" buttons
        document.querySelectorAll('button:contains("Mejorar"), a:contains("Mejorar")').forEach(btn => {
            btn.addEventListener('click', (e) => { e.preventDefault(); upgradeAccount(); });
        });
        
        // jQuery-like contains selector fix
        document.querySelectorAll('button, a').forEach(el => {
            if (el.textContent.includes('Mejorar')) {
                el.onclick = (e) => { e.preventDefault(); upgradeAccount(); };
            }
        });

        function applyThemePreset(preset) {
            const colorInput = document.getElementById('sColor');
            const fontSelect = document.getElementById('sFont');

            switch(preset) {
                case 'glass':
                    colorInput.value = '#4f46e5';
                    fontSelect.value = "'Plus Jakarta Sans', sans-serif";
                    break;
                case 'dark':
                    colorInput.value = '#ffffff';
                    fontSelect.value = "'Inter', sans-serif";
                    break;
                case 'minimal':
                    colorInput.value = '#000000';
                    fontSelect.value = "'Inter', sans-serif";
                    break;
                case 'vibrant':
                    colorInput.value = '#ec4899';
                    fontSelect.value = "'Outfit', sans-serif";
                    break;
                case 'retro':
                    colorInput.value = '#22c55e';
                    fontSelect.value = "'Roboto Mono', monospace";
                    break;
            }
        }
    </script>
