<?php
// pdf_view.php
require_once __DIR__ . '/config/database.php';

$code = $_GET['code'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM links WHERE code = ?");
$stmt->execute([$code]);
$link = $stmt->fetch();

if (!$link) {
    die("Link no encontrado");
}

$data = json_decode($link['content'], true);
$pdfUrl = $data['file'] ?? '';

if (!$pdfUrl) {
    die("Archivo no encontrado");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($link['campaign']); ?> | PDF Viewer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --bg: #f8fafc;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg); 
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            z-index: 10;
        }
        .btn {
            background: var(--primary);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: 0.2s;
        }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        
        .viewer-container {
            flex: 1;
            padding: 2rem;
            display: flex;
            justify-content: center;
            overflow: hidden;
        }
        iframe {
            width: 100%;
            max-width: 1000px;
            height: 100%;
            border: none;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
        }
        @media (max-width: 768px) {
            .viewer-container { padding: 0.5rem; }
            header { padding: 1rem; }
            .campaign-name { font-size: 0.9rem; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        }
    </style>
</head>
<body>
    <header>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <i class="fas fa-file-pdf" style="color: #ef4444; font-size: 1.5rem;"></i>
            <h1 class="campaign-name"><?php echo htmlspecialchars($link['campaign']); ?></h1>
        </div>
        <a href="<?php echo $pdfUrl; ?>" download class="btn">
            <i class="fas fa-download"></i> 
            <span class="btn-text">Descargar</span>
        </a>
    </header>

    <div class="viewer-container">
        <iframe src="<?php echo $pdfUrl; ?>#toolbar=0" title="PDF Viewer"></iframe>
    </div>

    <!-- Micro-animation for load -->
    <script>
        document.querySelector('iframe').style.opacity = '0';
        document.querySelector('iframe').style.transition = 'opacity 0.5s ease-in-out';
        document.querySelector('iframe').onload = function() {
            this.style.opacity = '1';
        };
    </script>
</body>
</html>
