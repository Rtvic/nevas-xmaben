<?php
// wifi_view.php - Premium WiFi Details Page
$wdata = json_decode($content, true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conexión WiFi - QR Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --dark: #0f172a;
            --light: #f8fafc;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--dark); color: var(--light); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 1.5rem; }
        
        .card { 
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            text-align: center;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: rgba(16, 185, 129, 0.2);
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary);
            border: 2px solid rgba(16, 185, 129, 0.3);
        }

        h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: 2rem; }

        .wifi-box {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: left;
            margin-bottom: 2rem;
        }

        .field { margin-bottom: 1rem; }
        .field:last-child { margin-bottom: 0; }
        .label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.25rem; }
        .value { font-family: monospace; font-size: 1.1rem; color: white; word-break: break-all; font-weight: 700; }

        .btn-copy {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            width: 100%;
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: 0.2s;
        }
        .btn-copy:hover { background: rgba(255, 255, 255, 0.15); }

        .instructions { font-size: 0.85rem; color: #94a3b8; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-circle">
            <i class="fas fa-wifi"></i>
        </div>
        <h1>Conectar al WiFi</h1>

        <div class="wifi-box">
            <div class="field">
                <span class="label">Red (SSID)</span>
                <span class="value"><?php echo htmlspecialchars($wdata['ssid']); ?></span>
            </div>
            <div class="field">
                <span class="label">Contraseña</span>
                <span class="value" id="wifiPass"><?php echo htmlspecialchars($wdata['pass']); ?></span>
            </div>
        </div>

        <button onclick="copyPass()" class="btn-copy">
            <i class="far fa-copy"></i> Copiar Contraseña
        </button>

        <p class="instructions">Copia la contraseña y pégala en los ajustes de WiFi de tu dispositivo para conectarte.</p>
    </div>

    <script>
        function copyPass() {
            const pass = "<?php echo addslashes($wdata['pass']); ?>";
            navigator.clipboard.writeText(pass).then(() => {
                const btn = document.querySelector('.btn-copy');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
                btn.style.background = '#10b981';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = 'rgba(255, 255, 255, 0.1)';
                }, 2000);
            });
        }
    </script>
</body>
</html>
