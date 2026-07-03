<?php
// social_view.php
// Expected variables from redirect.php: $content (JSON string), $link (array with campaign name)

$data = json_decode($content, true);
$theme = json_decode($link['theme_data'] ?? '{}', true);
$campaign_name = $link['campaign'] ?? 'Mis Redes Sociales';

// Theme Defaults
$preset = $theme['preset'] ?? 'glass';
$accent = $theme['color'] ?? '#4f46e5';
$font = $theme['font'] ?? "'Plus Jakarta Sans', sans-serif";

$platforms = [
    'insta'  => ['name' => 'Instagram', 'icon' => 'fab fa-instagram', 'color' => 'linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%)', 'prefix' => 'https://instagram.com/'],
    'tiktok' => ['name' => 'TikTok', 'icon' => 'fab fa-tiktok', 'color' => '#000000', 'prefix' => 'https://tiktok.com/@'],
    'fb'     => ['name' => 'Facebook', 'icon' => 'fab fa-facebook-f', 'color' => '#1877f2', 'prefix' => 'https://facebook.com/'],
    'yt'     => ['name' => 'YouTube', 'icon' => 'fab fa-youtube', 'color' => '#ff0000', 'prefix' => 'https://youtube.com/'],
    'li'     => ['name' => 'LinkedIn', 'icon' => 'fab fa-linkedin-in', 'color' => '#0077b5', 'prefix' => 'https://linkedin.com/'],
    'tw'     => ['name' => 'Twitter / X', 'icon' => 'fab fa-twitter', 'color' => '#1da1f2', 'prefix' => 'https://twitter.com/'],
    'web'    => ['name' => 'Sitio Web', 'icon' => 'fas fa-globe', 'color' => $accent, 'prefix' => '']
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($campaign_name); ?> | Link-in-Bio</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;700&family=Outfit:wght@400;700&family=Roboto+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: <?php echo $accent; ?>;
            --dark: <?php 
                if($preset === 'dark' || $preset === 'retro') echo '#0a0a0a';
                else if($preset === 'minimal') echo '#ffffff';
                else echo '#0f172a'; 
            ?>;
            --light: <?php 
                if($preset === 'minimal') echo '#0a0a0a';
                else echo '#f8fafc'; 
            ?>;
            --glass: <?php 
                if($preset === 'minimal') echo 'rgba(0,0,0,0.03)';
                else if($preset === 'dark') echo 'rgba(255,255,255,0.05)';
                else echo 'rgba(255,255,255,0.03)';
            ?>;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: <?php echo $font; ?>;
        }

        body {
            background: var(--dark);
            color: var(--light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 3rem 1.5rem;
            overflow-x: hidden;
            <?php if($preset === 'retro'): ?>
                border: 10px solid var(--primary);
            <?php endif; ?>
        }

        .bg-glow {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            <?php if($preset === 'glass'): ?>
                background: radial-gradient(circle at 50% 0%, rgba(79, 70, 229, 0.2) 0%, transparent 50%),
                            radial-gradient(circle at 50% 100%, rgba(236, 72, 153, 0.15) 0%, transparent 50%);
            <?php elseif($preset === 'vibrant'): ?>
                background: linear-gradient(135deg, <?php echo $accent; ?>33 0%, #ec489933 100%);
            <?php elseif($preset === 'retro'): ?>
                background: repeating-linear-gradient(0deg, rgba(0,0,0,0.1) 0px, rgba(0,0,0,0.1) 1px, transparent 1px, transparent 2px);
            <?php endif; ?>
        }

        .profile-pic {
            width: 100px;
            height: 100px;
            background: <?php echo ($preset === 'minimal' ? 'var(--dark)' : 'linear-gradient(135deg, var(--primary), #ec4899)'); ?>;
            border-radius: <?php echo ($preset === 'retro' ? '0' : '50%'); ?>;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            border: 4px solid <?php echo ($preset === 'retro' ? 'var(--primary)' : 'rgba(255, 255, 255, 0.1)'); ?>;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            color: <?php echo ($preset === 'minimal' ? 'white' : 'inherit'); ?>;
        }

        .social-link {
            text-decoration: none;
            color: <?php echo ($preset === 'minimal' ? 'var(--dark)' : 'white'); ?>;
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            background: var(--glass);
            border: 1px solid <?php echo ($preset === 'retro' ? 'var(--primary)' : 'rgba(255, 255, 255, 0.1)'); ?>;
            border-radius: <?php echo ($preset === 'retro' ? '0' : '1.25rem'); ?>;
            backdrop-filter: blur(10px);
            transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .social-link:hover {
            transform: scale(1.05);
            background: <?php echo ($preset === 'retro' ? 'var(--primary)' : 'rgba(255, 255, 255, 0.08)'); ?>;
            color: <?php echo ($preset === 'retro' ? 'black' : 'inherit'); ?>;
        }

        .social-link i {
            font-size: 1.4rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: <?php echo ($preset === 'retro' ? '0' : '50%'); ?>;
            margin-right: 1rem;
            color: white;
        }

        .social-link .chevron {
            color: <?php echo ($preset === 'retro' ? 'inherit' : '#475569'); ?>;
        }

        footer {
            margin-top: auto;
            padding-top: 4rem;
            text-align: center;
            font-size: 0.8rem;
            color: #475569;
        }

        footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Mobile Adjustments */
        @media (max-width: 480px) {
            .links-container { padding: 0 0.5rem; }
            h1 { font-size: 1.25rem; }
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>

    <div class="profile-container">
        <div class="profile-pic">
            <i class="fas fa-user"></i>
        </div>
        <h1><?php echo htmlspecialchars($campaign_name); ?></h1>
        <p class="bio">Conecta conmigo en mis redes oficiales</p>
    </div>

    <div class="links-container">
        <?php foreach ($platforms as $key => $info): ?>
            <?php 
            if (!empty($data[$key])): 
                $handle = trim($data[$key]);
                // Basic cleanup for handles if they included the URL accidentally
                $clean_handle = (strpos($handle, 'http') === 0) ? $handle : $info['prefix'] . $handle;
            ?>
                <a href="<?php echo htmlspecialchars($clean_handle); ?>" target="_blank" class="social-link">
                    <i class="<?php echo $info['icon']; ?>" style="background: <?php echo $info['color']; ?>;"></i>
                    <span><?php echo $info['name']; ?></span>
                    <i class="fas fa-chevron-right chevron"></i>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <footer>
        Hecho con <a href="#">QR PRO Platform</a>
    </footer>
</body>
</html>
