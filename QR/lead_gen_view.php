<?php
// lead_gen_view.php - Premium Lead Capture Form
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceder al Contenido - QR Pro</title>
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
            max-width: 450px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            background: rgba(79, 70, 229, 0.1);
            border-radius: 1.5rem;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.5rem;
            border: 1px solid rgba(79, 70, 229, 0.2);
        }

        h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.75rem; color: white; }
        p { color: #94a3b8; font-size: 0.95rem; margin-bottom: 2rem; }

        .form-group { text-align: left; margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px; }
        
        input {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem;
            color: white;
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
        }
        input:focus { border-color: var(--primary); background: rgba(255, 255, 255, 0.05); }

        .btn-submit {
            width: 100%;
            padding: 1.25rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 1rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4); }

        .privacy { font-size: 0.75rem; color: #475569; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-box">
            <i class="fas fa-unlock-alt"></i>
        </div>
        <h1>Contenido Protegido</h1>
        <p>Por favor, completa tus datos para acceder a la información de este código QR.</p>

        <form id="leadForm">
            <input type="hidden" id="link_id" value="<?php echo $link_id; ?>">
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" id="leadName" placeholder="Tu nombre" required>
            </div>
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" id="leadEmail" placeholder="tucorreo@ejemplo.com" required>
            </div>
            <div class="form-group">
                <label>Teléfono (Opcional)</label>
                <input type="tel" id="leadPhone" placeholder="+52 ...">
            </div>

            <button type="submit" class="btn-submit">
                Ver Contenido <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <p class="privacy">Al continuar, aceptas que el propietario de este QR reciba tus datos de contacto.</p>
    </div>

    <script>
        document.getElementById('leadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';

            const payload = {
                link_id: document.getElementById('link_id').value,
                name: document.getElementById('leadName').value,
                email: document.getElementById('leadEmail').value,
                phone: document.getElementById('leadPhone').value
            };

            try {
                const response = await fetch('api/save_lead.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();
                if (data.success) {
                    // Refresh to bypass lead gen now that it's tracked in session (optional)
                    // Or just use the URL from the response
                    window.location.reload(); 
                } else {
                    alert('Error: ' + data.error);
                    btn.disabled = false;
                    btn.innerHTML = 'Ver Contenido <i class="fas fa-arrow-right"></i>';
                }
            } catch (err) {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = 'Ver Contenido <i class="fas fa-arrow-right"></i>';
            }
        });
    </script>
</body>
</html>
