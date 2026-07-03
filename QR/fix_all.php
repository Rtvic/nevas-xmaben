<?php
// fix_all.php
require_once __DIR__ . '/config/database.php';

echo "<pre><h3>Iniciando Reparación Total de Base de Datos...</h3>";

try {
    // 1. Asegurar que la tabla 'links' existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(10) UNIQUE NOT NULL,
        destination_url TEXT NOT NULL,
        campaign VARCHAR(100),
        active BOOLEAN DEFAULT TRUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "[OK] Tabla 'links' verificada.\n";

    // 2. Asegurar que la tabla 'qr_codes' existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS qr_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        link_id INT NOT NULL,
        dots_style VARCHAR(50) DEFAULT 'square',
        corners_square_style VARCHAR(50) DEFAULT 'square',
        corners_dot_style VARCHAR(50) DEFAULT 'square',
        fg_color VARCHAR(20) DEFAULT '#000000',
        bg_color VARCHAR(20) DEFAULT '#ffffff',
        logo_path VARCHAR(255),
        label_text VARCHAR(100),
        label_color VARCHAR(20) DEFAULT '#000000',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY(link_id),
        FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "[OK] Tabla 'qr_codes' verificada.\n";

    // 3. Verificar columnas específicas de personalización por si la tabla ya existía pero vieja
    $columns = [
        'dots_style' => "VARCHAR(50) DEFAULT 'square'",
        'corners_square_style' => "VARCHAR(50) DEFAULT 'square'",
        'corners_dot_style' => "VARCHAR(50) DEFAULT 'square'",
        'fg_color' => "VARCHAR(20) DEFAULT '#000000'",
        'bg_color' => "VARCHAR(20) DEFAULT '#ffffff'",
        'label_text' => "VARCHAR(100) DEFAULT NULL",
        'label_color' => "VARCHAR(20) DEFAULT '#000000'"
    ];

    foreach ($columns as $col => $def) {
        $check = $pdo->query("SHOW COLUMNS FROM qr_codes LIKE '$col'");
        if ($check->rowCount() == 0) {
            echo "-> Agregando columna faltante: $col... ";
            $pdo->exec("ALTER TABLE qr_codes ADD COLUMN $col $def");
            echo "OK\n";
        }
    }

    // 4. Asegurar que la tabla 'events' existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        link_id INT NOT NULL,
        ip VARCHAR(45) NOT NULL,
        country VARCHAR(100),
        city VARCHAR(100),
        os VARCHAR(50),
        browser VARCHAR(50),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "[OK] Tabla 'events' verificada.\n";

    echo "\n<b>¡TODO LISTO!</b> El error de 'Column not found' debe haber desaparecido.\n";
    echo "<a href='dashboard.php' style='color:green; font-weight:bold; font-size:1.2rem;'>IR AL DASHBOARD AHORA</a>";

} catch (PDOException $e) {
    echo "\n<b style='color:red;'>ERROR FATAL:</b> " . $e->getMessage() . "\n";
    echo "\n<i>Sugerencia: Asegúrate de que MySQL esté prendido en XAMPP y que la base de datos 'qr_tracking_db' exista.</i>";
}
echo "</pre>";
?>
