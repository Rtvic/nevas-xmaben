<?php
// login.php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | QR Pro Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --dark: #0f172a;
            --light: #f8fafc;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body {
            background-color: var(--dark);
            color: var(--light);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .bg-glow {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 20% 30%, rgba(79, 70, 229, 0.15) 0%, transparent 40%),
                        radial-gradient(circle at 80% 70%, rgba(236, 72, 153, 0.15) 0%, transparent 40%);
        }

        .auth-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 3rem;
            border-radius: 2rem;
            width: 450px;
            max-width: 90%;
            box-shadow: 0 40px 100px rgba(0,0,0,0.5);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #818cf8, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 2.5rem;
        }

        h2 { text-align: center; margin-bottom: 0.5rem; font-weight: 700; }
        p.subtitle { text-align: center; color: #94a3b8; margin-bottom: 2rem; font-size: 0.95rem; }

        .input-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; color: #94a3b8; font-size: 0.85rem; font-weight: 500; }
        
        input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            padding: 1rem 1.25rem;
            border-radius: 1rem;
            color: white;
            font-size: 1rem;
            transition: 0.3s;
            outline: none;
        }

        input:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 1rem;
            border-radius: 1rem;
            border: none;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 1rem;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.6);
        }

        .footer-links {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.9rem;
            color: #94a3b8;
        }

        .footer-links a {
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            display: none;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>

    <div class="auth-card">
        <div class="logo">
            <i class="fas fa-qrcode"></i>
            QR PRO
        </div>
        <h2>Bienvenido de nuevo</h2>
        <p class="subtitle">Ingresa tus credenciales para administrar tus QRs</p>

        <div id="authAlert" class="alert"></div>

        <form id="loginForm">
            <div class="input-group">
                <label>Correo Electrónico</label>
                <input type="email" id="email" placeholder="tu@email.com" required>
            </div>
            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" id="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-primary">Iniciar Sesión</button>
        </form>

        <div class="footer-links">
            ¿No tienes cuenta? <a href="register.php">Regístrate gratis</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const alertBox = document.getElementById('authAlert');

            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);

            try {
                const response = await fetch('api/auth.php?action=login', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    alertBox.innerText = data.error;
                    alertBox.style.display = 'block';
                }
            } catch (err) {
                console.error(err);
                alertBox.innerText = 'Error al conectar con el servidor';
                alertBox.style.display = 'block';
            }
        });
    </script>
</body>
</html>
