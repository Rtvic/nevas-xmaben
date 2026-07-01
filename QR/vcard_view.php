<?php
// vcard_view.php - Premium Mobile Business Card
$vdata = json_decode($content, true);
$fullName = ($vdata['name'] ?? '') . ' ' . ($vdata['last'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($fullName); ?> - Tarjeta Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
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
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), #ec4899);
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        h1 { font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; }
        .work { color: #94a3b8; font-size: 1rem; margin-bottom: 2rem; }

        .info-list { text-align: left; margin-bottom: 2.5rem; }
        .info-item { display: flex; align-items: center; gap: 1rem; padding: 1rem 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .info-item i { color: var(--primary); width: 20px; text-align: center; }
        .info-label { font-size: 0.75rem; color: #64748b; display: block; }
        .info-value { font-weight: 600; color: #f1f5f9; }

        .btn-download {
            background: var(--primary);
            color: white;
            width: 100%;
            padding: 1.25rem;
            border-radius: 1rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-download:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(79, 70, 229, 0.4); }
    </style>
</head>
<body>
    <div class="card">
        <div class="avatar">
            <?php echo strtoupper(substr($vdata['name'] ?? 'U', 0, 1)); ?>
        </div>
        <h1><?php echo htmlspecialchars($fullName); ?></h1>
        <p class="work"><?php echo htmlspecialchars($vdata['work'] ?? 'Profesional'); ?></p>

        <div class="info-list">
            <div class="info-item">
                <i class="fas fa-phone"></i>
                <div>
                    <span class="info-label">Teléfono</span>
                    <a href="tel:<?php echo htmlspecialchars($vdata['phone']); ?>" class="info-value" style="text-decoration: none;"><?php echo htmlspecialchars($vdata['phone']); ?></a>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <div>
                    <span class="info-label">Email</span>
                    <a href="mailto:<?php echo htmlspecialchars($vdata['email']); ?>" class="info-value" style="text-decoration: none;"><?php echo htmlspecialchars($vdata['email']); ?></a>
                </div>
            </div>
        </div>

        <button onclick="downloadVCard()" class="btn-download">
            <i class="fas fa-user-plus"></i> Guardar Contacto
        </button>
    </div>

    <script>
        function downloadVCard() {
            const vcard = `BEGIN:VCARD
VERSION:3.0
FN:<?php echo $fullName; ?>
N:<?php echo ($vdata['last'] ?? '') . ';' . ($vdata['name'] ?? ''); ?>;;;
TEL;TYPE=CELL:<?php echo $vdata['phone'] ?? ''; ?>
EMAIL;TYPE=INTERNET:<?php echo $vdata['email'] ?? ''; ?>
ORG:<?php echo $vdata['work'] ?? ''; ?>
END:VCARD`;

            const blob = new Blob([vcard], { type: 'text/vcard' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = '<?php echo str_replace(' ', '_', $fullName); ?>.vcf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>
