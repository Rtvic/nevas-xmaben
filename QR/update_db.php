<?php
// update_db.php
require_once __DIR__ . '/config/database.php';

echo "<pre>Actualizando base de datos...\n";

try {
    // List of columns to add and their definitions
    $columns_to_add = [
        'dots_style' => "VARCHAR(50) DEFAULT 'square'",
        'corners_square_style' => "VARCHAR(50) DEFAULT 'square'",
        'corners_dot_style' => "VARCHAR(50) DEFAULT 'square'",
        'fg_color' => "VARCHAR(20) DEFAULT '#000000'",
        'bg_color' => "VARCHAR(20) DEFAULT '#ffffff'",
        'label_text' => "VARCHAR(100) DEFAULT NULL",
        'label_color' => "VARCHAR(20) DEFAULT '#000000'"
    ];

    foreach ($columns_to_add as $column => $definition) {
        // Check if column exists
        $check = $pdo->query("SHOW COLUMNS FROM qr_codes LIKE '$column'");
        if ($check->rowCount() == 0) {
            echo "Añadiendo columna: $column... ";
            $pdo->exec("ALTER TABLE qr_codes ADD COLUMN $column $definition");
            echo "OK\n";
        } else {
            echo "La columna $column ya existe.\n";
        }
    }

    echo "\n¡Base de datos actualizada con éxito!\n";
    echo "<a href='dashboard.php'>Volver al Dashboard</a>";
} catch (PDOException $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>
