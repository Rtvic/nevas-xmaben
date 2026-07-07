<?php
require_once __DIR__ . '/auth_config.php';

// ===== ROUTING (solo cuando se ejecuta directo, no al hacer require) =====
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success' => false, 'error' => 'Method not allowed']); exit; }

    requireAuth();

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['client_id']) || !isset($input['stage'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing client_id or stage']);
        exit;
    }

    $client_id = $input['client_id'];
    $stage = $input['stage'];
    $client_message = isset($input['client_message']) ? trim($input['client_message']) : '';

    try {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=crm_webdesign_v2;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ? LIMIT 1");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();

    if (!$client) { echo json_encode(['success' => false, 'error' => 'Client not found']); exit; }

    $message = generateWithAI($client, $stage, $client_message);
    if ($message === null || $message === '') {
        echo json_encode(['success' => false, 'error' => 'No se pudo generar el mensaje. Intenta de nuevo.']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'stage' => $stage,
        'client_name' => $client['nombre'] ?: 'Negocio'
    ]);
    exit;
}

// ==================== URLS POR CATEGORÍA ====================
function getDemoUrl($categoria) {
    // Primero buscar en config_negocio.json
    $config = loadBusinessConfig();
    if ($config && isset($config['demos'])) {
        $cats = strtolower($categoria);
        foreach ($config['demos'] as $key => $demo) {
            if (strpos($cats, $key) !== false) {
                return [$demo['url'], $demo['giro'] ?? $key, $demo['giro'] ?? $key];
            }
        }
    }
    
    // Fallback a hardcoded
    $cats = strtolower($categoria);
    
    // COMIDA
    if (strpos($cats, 'taquer') !== false || strpos($cats, 'taco') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/taqueria.html', 'taqueria', 'taquería'];
    }
    if (strpos($cats, 'restaurante') !== false || strpos($cats, 'fonda') !== false || strpos($cats, 'comida') !== false) {
        return ['https://rtvic.github.io/restaurante-web/', 'restaurante', 'restaurante'];
    }
    if (strpos($cats, 'caf') !== false || strpos($cats, 'cafe') !== false || strpos($cats, 'kiosko') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/taqueria.html', 'cafetería', 'cafetería'];
    }
    
    // SERVICIOS
    if (strpos($cats, 'taller') !== false || strpos($cats, 'mecan') !== false || strpos($cats, 'automotriz') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/taller.html', 'taller', 'taller mecánico'];
    }
    if (strpos($cats, 'herrer') !== false || strpos($cats, 'carpinter') !== false || strpos($cats, 'ferreter') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/taller.html', 'taller', 'taller de herrería'];
    }
    
    // BELLEZA
    if (strpos($cats, 'salon') !== false || strpos($cats, 'belleza') !== false || strpos($cats, 'spa') !== false || strpos($cats, 'estet') !== false || strpos($cats, 'peluquer') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/barberia.html', 'salón', 'salón de belleza'];
    }
    
    // SALUD
    if (strpos($cats, 'dentist') !== false || strpos($cats, 'dental') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/clinica.html', 'clínica dental', 'consultorio dental'];
    }
    if (strpos($cats, 'clinica') !== false || strpos($cats, 'medic') !== false || strpos($cats, 'doctor') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/clinica.html', 'clínica', 'clínica médica'];
    }
    
    // FITNESS
    if (strpos($cats, 'gimnasio') !== false || strpos($cats, 'fitness') !== false || strpos($cats, 'gym') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/taqueria.html', 'gimnasio', 'gimnasio'];
    }
    
    // TIENDA
    if (strpos($cats, 'abarrotes') !== false || strpos($cats, 'tienda') !== false || strpos($cats, 'miscel') !== false || strpos($cats, 'conveniencia') !== false || strpos($cats, 'minisuper') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/taqueria.html', 'tienda', 'tienda de abarrotes'];
    }
    
    // PROFESIONALES
    if (strpos($cats, 'abogado') !== false || strpos($cats, 'legal') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/clinica.html', 'despacho legal', 'despacho de abogados'];
    }
    if (strpos($cats, 'contad') !== false || strpos($cats, 'fiscal') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/clinica.html', 'despacho contable', 'despacho de contabilidad'];
    }
    
    // EVENTOS
    if (strpos($cats, 'salon_evento') !== false || strpos($cats, 'evento') !== false || strpos($cats, 'banquete') !== false || strpos($cats, 'boda') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/taqueria.html', 'salón de eventos', 'salón de fiestas'];
    }
    
    // HOTELERÍA
    if (strpos($cats, 'hotel') !== false || strpos($cats, 'hospedaje') !== false || strpos($cats, 'hostal') !== false || strpos($cats, 'airbnb') !== false) {
        return ['https://rtvic.github.io/restaurante-web/', 'hotel', 'hotel boutique'];
    }
    
    // FOTOGRAFÍA
    if (strpos($cats, 'fotogra') !== false || strpos($cats, 'foto') !== false || strpos($cats, 'video') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/barberia.html', 'portafolio', 'estudio de fotografía'];
    }
    
    // FLORERÍA
    if (strpos($cats, 'flor') !== false || strpos($cats, 'arregl') !== false || strpos($cats, 'floreria') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/taqueria.html', 'florería', 'florería'];
    }
    
    // VIAJES
    if (strpos($cats, 'agencia_viaje') !== false || strpos($cats, 'turismo') !== false || strpos($cats, 'tour') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/barberia.html', 'agencia de viajes', 'agencia de turismo'];
    }
    
    // VETERINARIA
    if (strpos($cats, 'veterin') !== false || strpos($cats, 'mascota') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/taller.html', 'veterinaria', 'veterinaria'];
    }
    
    // BARBERÍA
    if (strpos($cats, 'barber') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/barberia.html', 'barbería', 'barbería'];
    }
    
    // LAVANDERÍA
    if (strpos($cats, 'lavand') !== false || strpos($cats, 'lavado') !== false) {
        return ['https://rtvic.github.io/nevas-xmaben/demos/taller.html', 'lavandería', 'lavandería'];
    }
    
    // DEFAULT
    return ['https://rtvic.github.io/nevas-xmaben/demos/taqueria.html', 'negocio', 'negocio'];
}

// ==================== URLs POR PLAN ====================
function getPlanDemoUrls() {
    return [
        'basico'     => 'https://rtvic.github.io/nevas-xmaben/demo_basico.html',
        'profesional' => 'https://rtvic.github.io/nevas-xmaben/demo_profesional.html',
        'premium'    => 'https://rtvic.github.io/nevas-xmaben/demo_premium.html',
        'pago'       => 'https://rtvic.github.io/nevas-xmaben/pagar.html',
    ];
}

// ==================== PERFIL DEL NEGOCIO ( pain points reales ) ====================
function getBusinessProfile($categoria) {
    $cats = strtolower($categoria);
    
    // RESTAURANTE / TAQUERÍA / FONDA
    if (strpos($cats, 'taquer') !== false || strpos($cats, 'taco') !== false || strpos($cats, 'restaurante') !== false || strpos($cats, 'fonda') !== false || strpos($cats, 'comida') !== false) {
        return [
            'nombre_giro' => 'restaurante',
            'pain_points' => [
                'La gente busca "taquerías cerca de mí" en Google y no apareces',
                'Tus clientes potenciales van a la competencia que SÍ tiene página',
                'Sin menú en línea, la gente no sabe qué ofreces antes de ir',
                'No puedes recibir pedidos por WhatsApp mientras duermes',
                'Google Maps es tu mejor vendedor gratuito, pero sin web es débil',
            ],
            'beneficios_clave' => [
                'Menú digital con fotos que dan hambre',
                'Botón de WhatsApp para pedidos directos',
                'Google Maps para que te encuentren al buscar "comida en [ciudad]"',
                'Horarios y ubicación siempre actualizados',
                'La gente comparte tu página con amigos',
            ],
            'datos_impacto' => [
                'El 78% de las búsquedas de restaurantes en celular terminan en una visita el mismo día',
                'Los restaurantes con menú digital reciben 30% más pedidos por WhatsApp',
                'El 60% de los clientes eligen restaurante por lo que ven en internet',
            ],
            'cierre_sugerido' => '¿Te gustaría que tu restaurante aparezca cuando alguien busque "taquerías en [ciudad]"?',
        ];
    }
    
    // TALLER MECÁNICO
    if (strpos($cats, 'taller') !== false || strpos($cats, 'mecan') !== false || strpos($cats, 'automotriz') !== false) {
        return [
            'nombre_giro' => 'taller mecánico',
            'pain_points' => [
                'La gente busca "mecánico cerca de mí" y encuentra a tu competencia',
                'Sin página web, pareces menos profesional que la competencia',
                'No puedes mostrar tus servicios ni precios antes de que lleguen',
                'Los clientes no saben si eres de confianza sin ver reseñas',
                'Perdes clientes que buscan en Google y van al primero que aparece',
            ],
            'beneficios_clave' => [
                'Lista de servicios con precios para que sepan qué pagan',
                'Botón de WhatsApp para agendar cita sin llamar',
                'Google Maps para que te encuentren rápido',
                'Sección de reseñas de clientes satisfechos',
                'Fotos de tu taller que generan confianza',
            ],
            'datos_impacto' => [
                'El 85% de los clientes buscan mecánico en Google antes de ir',
                'Los talleres con página reciben 40% más llamadas de nuevos clientes',
                'El 70% prefiere talleres que muestran precios en internet',
            ],
            'cierre_sugerido' => '¿Te gustaría que cuando alguien busque "mecánico en [ciudad]" aparezca tu taller primero?',
        ];
    }
    
    // DENTISTA / CLÍNICA
    if (strpos($cats, 'dentist') !== false || strpos($cats, 'dental') !== false || strpos($cats, 'clinica') !== false || strpos($cats, 'medic') !== false || strpos($cats, 'doctor') !== false) {
        return [
            'nombre_giro' => 'consultorio dental',
            'pain_points' => [
                'Los pacientes buscan "dentista bueno en [ciudad]" y no te encuentran',
                'Sin página profesional, no generas la confianza que necesitan',
                'No puedes mostrar tu experiencia ni certificaciones',
                'La competencia con página captura pacientes que serían tuyos',
                'No puedes agendar citas 24/7, solo cuando contestas el teléfono',
            ],
            'beneficios_clave' => [
                'Agendamiento de citas en línea 24/7',
                'Lista de servicios y tratamientos con precios',
                'Tu experiencia y certificaciones que dan confianza',
                'Google Maps para que te encuentren',
                'WhatsApp para consultas rápidas',
            ],
            'datos_impacto' => [
                'El 92% de los pacientes buscan dentista en Google',
                'Los consultorios con página reciben 50% más pacientes nuevos',
                'El 80% confía más en un dentista que tiene página profesional',
            ],
            'cierre_sugerido' => '¿Te gustaría que los pacientes te encuentren cuando busquen "dentista en [ciudad]" y agenden cita directo desde tu página?',
        ];
    }
    
    // GIMNASIO
    if (strpos($cats, 'gimnasio') !== false || strpos($cats, 'fitness') !== false || strpos($cats, 'gym') !== false) {
        return [
            'nombre_giro' => 'gimnasio',
            'pain_points' => [
                'La gente busca "gym cerca de mí" y va al que aparece en Google',
                'Sin página, no puedes mostrar tus instalaciones ni clases',
                'Los potenciales socios no saben qué ofreces antes de ir',
                'La competencia con página captura tus clientes',
                'No puedes vender membresías en línea',
            ],
            'beneficios_clave' => [
                'Galería de instalaciones que dan ganas de entrenar',
                'Horarios de clases siempre actualizados',
                'Precios de membresías para que elijan antes de ir',
                'WhatsApp para agendar clase gratis',
                'Google Maps para que te encuentren',
            ],
            'datos_impacto' => [
                'El 75% de los socios nuevos buscan el gym en Google primero',
                'Los gym con página tienen 35% más conversiones de visitas a socios',
                'El 65% prefiere gym que muestra precios en internet',
            ],
            'cierre_sugerido' => '¿Te gustaría que cuando alguien busque "gym en [ciudad]" aparezca el tuyo con fotos de las instalaciones y horarios?',
        ];
    }
    
    // SALÓN DE BELLEZA / SPA
    if (strpos($cats, 'salon') !== false || strpos($cats, 'belleza') !== false || strpos($cats, 'spa') !== false || strpos($cats, 'estet') !== false || strpos($cats, 'peluquer') !== false || strpos($cats, 'barber') !== false) {
        return [
            'nombre_giro' => 'salón de belleza',
            'pain_points' => [
                'La gente busca "salón de belleza en [ciudad]" y no apareces',
                'Sin página, no pueden ver tu trabajo antes de ir',
                'No puedes mostrar tus servicios y precios',
                'La competencia con página te quita clientes',
                'No pueden agendar cita cuando quieren, solo cuando contestas',
            ],
            'beneficios_clave' => [
                'Portafolio de trabajos que convence',
                'Lista de servicios con precios',
                'Botón de WhatsApp para agendar cita',
                'Google Maps para que te encuentren',
                'Horarios y ubicación claros',
            ],
            'datos_impacto' => [
                'El 80% de las clientas buscan salón en Google',
                'Los salones con portafolio digital reciben 45% más citas',
                'El 70% elige salón por lo que ve en internet antes de ir',
            ],
            'cierre_sugerido' => '¿Te gustaría que tus clientas vean tu trabajo y agenden cita directo desde Google?',
        ];
    }
    
    // HOTEL / HOSPEDAJE
    if (strpos($cats, 'hotel') !== false || strpos($cats, 'hospedaje') !== false || strpos($cats, 'hostal') !== false || strpos($cats, 'airbnb') !== false) {
        return [
            'nombre_giro' => 'hotel',
            'pain_points' => [
                'Los turistas buscan "hotel en [ciudad]" y encuentran a la competencia',
                'Sin página, no puedes mostrar tus habitaciones ni vistas',
                'No pueden reservar directo, van a Booking que cobra comisión',
                'La competencia con página y fotos captura tus huéspedes',
                'No puedes mostrar tours ni experiencias que ofreces',
            ],
            'beneficios_clave' => [
                'Galería de fotos profesional de habitaciones y vistas',
                'Sistema de reservas directas sin comisiones',
                'Tours y experiencias que ofreces',
                'Google Maps y reseñas de huéspedes',
                'WhatsApp para consultas y reservas',
            ],
            'datos_impacto' => [
                'El 83% de los huéspedes buscan hotel en Google',
                'Los hoteles con reserva directa ahorran 15-20% en comisiones',
                'El 76% elige hotel por fotos que ve en internet',
            ],
            'cierre_sugerido' => '¿Te gustaría que los turistas te encuentren, vean tus habitaciones y reserven directo sin comisiones de Booking?',
        ];
    }
    
    // ABOGADO
    if (strpos($cats, 'abogado') !== false || strpos($cats, 'legal') !== false) {
        return [
            'nombre_giro' => 'despacho de abogados',
            'pain_points' => [
                'Los clientes buscan "abogado en [ciudad]" y no te encuentran',
                'Sin página profesional, no generas la confianza necesaria',
                'No puedes mostrar tu especialidad ni experiencia',
                'La competencia con página te quita clientes importantes',
                'No pueden agendar consulta inicial fácilmente',
            ],
            'beneficios_clave' => [
                'Tu experiencia y casos de éxito que dan confianza',
                'Lista de servicios legales especializados',
                'Botón de WhatsApp para consulta inicial',
                'Google Maps para que te encuentren',
                'Artículos legales que demuestran tu conocimiento',
            ],
            'datos_impacto' => [
                'El 88% de los clientes buscan abogado en Google',
                'Los despachos con página reciben 55% más consultas',
                'El 82% confía más en abogado con página profesional',
            ],
            'cierre_sugerido' => '¿Te gustaría que cuando alguien busque "abogado en [ciudad]" aparezca tu despacho con tu experiencia y agenden consulta directo?',
        ];
    }
    
    // CONTADOR
    if (strpos($cats, 'contad') !== false || strpos($cats, 'fiscal') !== false) {
        return [
            'nombre_giro' => 'despacho contable',
            'pain_points' => [
                'Los negocios buscan "contador en [ciudad]" y no te encuentran',
                'Sin página, pareces menos profesional que la competencia',
                'No puedes mostrar tus servicios ni especialidades',
                'La competencia con página captura clientes que serían tuyos',
                'No pueden agendar consulta fácilmente',
            ],
            'beneficios_clave' => [
                'Tu experiencia y certificaciones que dan confianza',
                'Lista de servicios contables y fiscales',
                'Botón de WhatsApp para agendar consulta',
                'Google Maps para que te encuentren',
                'Artículos fiscales que demuestran conocimiento',
            ],
            'datos_impacto' => [
                'El 80% de los negocios buscan contador en Google',
                'Los despachos con página reciben 45% más clientes',
                'El 75% confía más en contador con página profesional',
            ],
            'cierre_sugerido' => '¿Te gustaría que los negocios te encuentren cuando busquen "contador en [ciudad]" y agenden consulta directo?',
        ];
    }
    
    // FLORERÍA
    if (strpos($cats, 'flor') !== false || strpos($cats, 'arregl') !== false || strpos($cats, 'floreria') !== false) {
        return [
            'nombre_giro' => 'florería',
            'pain_points' => [
                'La gente busca "florería en [ciudad]" y no apareces',
                'Sin página, no pueden ver tus arreglos antes de comprar',
                'No puedes recibir pedidos en línea 24/7',
                'La competencia con página te quita bodas y eventos',
                'No pueden ver precios ni hacer pedidos por WhatsApp fácil',
            ],
            'beneficios_clave' => [
                'Galería de arreglos que dan ganas de comprar',
                'Catálogo con precios para cada evento',
                'Botón de WhatsApp para pedidos urgentes',
                'Google Maps para que te encuentren',
                'Sección de eventos especiales (bodas, XV años, etc.)',
            ],
            'datos_impacto' => [
                'El 70% de las flores se compran por impulso o urgencia',
                'Las florerías con página reciben 40% más pedidos de eventos',
                'El 65% busca "florería cerca" en Google cuando necesita flores',
            ],
            'cierre_sugerido' => '¿Te gustaría que cuando alguien busque "florería en [ciudad]" aparezca tu tienda con fotos de tus arreglos y puedan pedir directo?',
        ];
    }
    
    // FOTÓGRAFO
    if (strpos($cats, 'fotogra') !== false || strpos($cats, 'foto') !== false || strpos($cats, 'video') !== false) {
        return [
            'nombre_giro' => 'estudio de fotografía',
            'pain_points' => [
                'Los clientes buscan "fotógrafo en [ciudad]" y no te encuentran',
                'Sin portafolio en línea, no pueden ver tu trabajo',
                'No pueden agendar sesión fácilmente',
                'La competencia con página te quita bodas y eventos',
                'No puedes mostrar tu estilo ni especialidad',
            ],
            'beneficios_clave' => [
                'Portafolio profesional que convence',
                'Galería organizada por tipo de sesión',
                'Botón de WhatsApp para agendar sesión',
                'Precios y paquetes visibles',
                'Google Maps para que te encuentren',
            ],
            'datos_impacto' => [
                'El 90% de los clientes buscan fotógrafo en Google',
                'Los fotógrafos con portafolio digital reciben 60% más reservas',
                'El 85% elige fotógrafo por lo que ve en internet',
            ],
            'cierre_sugerido' => '¿Te gustaría que cuando alguien busque "fotógrafo en [ciudad]" vea tu portafolio y agende sesión directo?',
        ];
    }
    
    // AGENCIA DE VIAJES
    if (strpos($cats, 'agencia_viaje') !== false || strpos($cats, 'turismo') !== false || strpos($cats, 'tour') !== false) {
        return [
            'nombre_giro' => 'agencia de viajes',
            'pain_points' => [
                'Los turistas buscan "tours en [ciudad]" y encuentran a la competencia',
                'Sin página, no puedes mostrar tus tours ni destinos',
                'No pueden reservar tours en línea',
                'La competencia con página te quita turistas',
                'No puedes mostrar reseñas de clientes satisfechos',
            ],
            'beneficios_clave' => [
                'Galería de tours y destinos que dan ganas de viajar',
                'Precios y paquetes organizados',
                'Botón de WhatsApp para reservar tour',
                'Reseñas de turistas satisfechos',
                'Google Maps para que te encuentren',
            ],
            'datos_impacto' => [
                'El 85% de los turistas buscan tours en Google',
                'Las agencias con página reciben 50% más reservas',
                'El 78% elige tour por lo que ve en internet',
            ],
            'cierre_sugerido' => '¿Te gustaría que cuando alguien busque "tours en [ciudad]" aparezca tu agencia con fotos de los destinos y puedan reservar directo?',
        ];
    }
    
    // TIENDA / ABARROTES
    if (strpos($cats, 'abarrotes') !== false || strpos($cats, 'tienda') !== false || strpos($cats, 'miscel') !== false || strpos($cats, 'minisuper') !== false) {
        return [
            'nombre_giro' => 'tienda',
            'pain_points' => [
                'La gente busca "tienda de abarrotes en [ciudad]" y no apareces',
                'Sin página, no puedes mostrar tus productos',
                'No puedes recibir pedidos a domicilio por WhatsApp',
                'La competencia con página te quita clientes',
                'No pueden ver precios ni hacer pedidos fácil',
            ],
            'beneficios_clave' => [
                'Catálogo de productos con precios',
                'Botón de WhatsApp para pedidos a domicilio',
                'Google Maps para que te encuentren',
                'Horarios y ubicación claros',
                'Ofertas y promociones visibles',
            ],
            'datos_impacto' => [
                'El 60% de los clientes buscan tienda en Google',
                'Las tiendas con catálogo digital reciben 35% más pedidos',
                'El 55% prefiere tienda que muestra precios en internet',
            ],
            'cierre_sugerido' => '¿Te gustaría que cuando alguien busque "tienda en [ciudad]" vea tu catálogo y pueda pedir por WhatsApp?',
        ];
    }
    
    // VETERINARIA
    if (strpos($cats, 'veterin') !== false || strpos($cats, 'mascota') !== false) {
        return [
            'nombre_giro' => 'veterinaria',
            'pain_points' => [
                'Los dueños de mascotas buscan "veterinaria en [ciudad]" y no apareces',
                'Sin página, no pueden ver tus servicios ni horarios',
                'No pueden agendar cita para su mascota fácilmente',
                'La competencia con página te quita pacientes',
                'No generas la confianza necesaria sin página profesional',
            ],
            'beneficios_clave' => [
                'Lista de servicios veterinarios con precios',
                'Botón de WhatsApp para agendar cita',
                'Google Maps para que te encuentren',
                'Horarios de emergencia visibles',
                'Fotos que generan confianza',
            ],
            'datos_impacto' => [
                'El 78% de los dueños buscan veterinaria en Google',
                'Las veterinarias con página reciben 45% más pacientes',
                'El 70% confía más en veterinaria con página profesional',
            ],
            'cierre_sugerido' => '¿Te gustaría que cuando alguien busque "veterinaria en [ciudad]" aparezca la tuya con servicios y puedan agendar cita directo?',
        ];
    }
    
    // DEFAULT
    return [
        'nombre_giro' => 'negocio',
        'pain_points' => [
            'La gente busca tu tipo de negocio en Google y no te encuentra',
            'Sin página web, pareces menos profesional que la competencia',
            'No puedes mostrar lo que ofreces antes de que lleguen',
            'La competencia con página te quita clientes',
            'No pueden contactarte fácilmente por WhatsApp',
        ],
        'beneficios_clave' => [
            'Presencia profesional en internet',
            'Google Maps para que te encuentren',
            'WhatsApp para contacto directo',
            'Información de servicios y precios',
            'Reseñas de clientes satisfechos',
        ],
        'datos_impacto' => [
            'El 80% de los clientes buscan negocios en Google',
            'Los negocios con página reciben 40% más clientes',
            'El 70% confía más en negocio con página profesional',
        ],
        'cierre_sugerido' => '¿Te gustaría que cuando alguien busque tu tipo de negocio en [ciudad] aparezca el tuyo?',
        ];
}

// ==================== GENERAR CON OPENCODE ====================
function generateWithAI($client, $stage, $client_message = '') {
    return callOpencode($client, $stage, $client_message);
}

// ==================== OBTENER HISTORIAL DE CONVERSACION ====================
function getInteractionHistory($clientId, $limit = 10) {
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=crm_webdesign_v2;charset=utf8mb4', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $stmt = $pdo->prepare("SELECT direccion, asunto, descripcion, fecha FROM interacciones WHERE cliente_id = ? ORDER BY fecha ASC LIMIT " . (int)$limit);
        $stmt->execute([$clientId]);
        $rows = $stmt->fetchAll();
        
        if (empty($rows)) return '';
        
        $history = '';
        foreach ($rows as $row) {
            $dir = ($row['direccion'] === 'entrante') ? 'CLIENTE' : 'VICTOR';
            $text = $row['descripcion'] ?: $row['asunto'];
            if (mb_strlen($text) > 200) $text = mb_substr($text, 0, 200) . '...';
            $history .= "[{$dir}]: {$text}\n";
        }
        return $history;
    } catch (Exception $e) {
        return '';
    }
}

// ==================== CARGAR CONFIGURACION DEL NEGOCIO ====================
function loadBusinessConfig() {
    static $config = null;
    if ($config !== null) return $config;
    
    $file = __DIR__ . '/../config_negocio.json';
    if (!file_exists($file)) return null;
    
    $json = file_get_contents($file);
    $config = json_decode($json, true);
    return $config ?: null;
}

function buildBusinessContext($config, $categoria = '') {
    if (!$config) return '';
    
    $b = $config['negocio'];
    $ctx = "═══════════════════════════════════\n";
    $ctx .= "SOBRE VICTOR (el vendedor)\n";
    $ctx .= "═══════════════════════════════════\n";
    $ctx .= "Nombre: {$b['nombre']}\n";
    $ctx .= "Rol: {$b['rol']}\n";
    $ctx .= "Ubicacion: {$b['ciudad']}, {$b['estado']}\n";
    $ctx .= "WhatsApp: +52{$b['whatsapp']}\n";
    $ctx .= "Descripcion: {$b['descripcion']}\n";
    
    $ctx .= "\nServicios de Victor (NO solo paginas web):\n";
    foreach ($config['servicios'] as $s) {
        $ctx .= "- {$s}\n";
    }
    
    $ctx .= "\nPlanes y precios (mencionar SOLO en cotizacion, etapa 5):\n";
    foreach ($config['planes'] as $p) {
        $ctx .= "- {$p['nombre']}: {$p['precio']} | {$p['incluye']}\n";
    }
    $ctx .= "- NOTA: CRMs, POS, sistemas a medida cuestan MAS segun complejidad\n";
    
    $ctx .= "\nPago: {$config['pago']['url']} | Metodos: " . implode(', ', $config['pago']['metodos']) . " | Anticipo: {$config['pago']['anticipo']}\n";
    
    if ($categoria && isset($config['problemas_por_giro'][$categoria])) {
        $ctx .= "\nProblemas tipicos de negocios '{$categoria}':\n";
        foreach ($config['problemas_por_giro'][$categoria] as $p) {
            $ctx .= "- {$p}\n";
        }
    }
    
    return $ctx;
}

// ==================== PROMPT USANDO PLANTILLA ESPECIALIZADA ====================
function buildPrompt($client, $stage, $client_message = '') {
    $nombre = $client['nombre_contacto'] ?? $client['nombre'] ?? 'Negocio';
    $negocio = $client['nombre'] ?? 'su negocio';
    $ciudad = $client['ciudad'] ?? 'Merida';
    $estado = $client['estado'] ?? 'Yucatan';
    $categoria = $client['categoria'] ?? 'negocio';
    $servicios = $client['servicios'] ?? '';
    $descripcion = $client['descripcion'] ?? '';
    
    list($url_demo, $tipo_demo, $nombre_giro) = getDemoUrl($categoria);
    
    $stageMap = [
        'inicial' => 1, 'seguimiento_1' => 2, 'negociacion' => 3,
        'mostrar_ejemplos' => 4, 'cotizacion' => 5, 'seguimiento_2' => 6,
        'pago_pendiente' => 7, 'pago_recibido' => 8, 'entrega' => 9,
        'seguimiento_post' => 10
    ];
    
    $perfil = "Nombre: {$negocio}. Giro: {$nombre_giro}. Ciudad: {$ciudad}, {$estado}";
    if ($servicios) $perfil .= ". Servicios: {$servicios}";
    if ($descripcion) $perfil .= ". Descripcion: {$descripcion}";
    
    $history = getInteractionHistory($client['id']);
    $config = loadBusinessConfig();
    $businessCtx = buildBusinessContext($config, $categoria);
    
    $promptBase = file_get_contents(__DIR__ . '/../prompt_generador.txt');
    if (!$promptBase) {
        $promptBase = "Eres el motor de generacion de mensajes de un CRM de ventas por WhatsApp.\n\n";
    }
    
    $prompt = $promptBase;
    
    $prompt .= "\n\n{$businessCtx}\n";
    
    $prompt .= "\n═══════════════════════════════════\n";
    $prompt .= "DATOS DEL CLIENTE\n";
    $prompt .= "═══════════════════════════════════\n";
    
    if ($stage === 'smart') {
        $prompt .= "Etapa: AUTO-DETECTADA (determinada por IA basandose en la conversacion)\n";
        $prompt .= "MODO SMART ACTIVADO:\n";
        $prompt .= "- Analiza el historial de conversacion completo y el ULTIMO MENSAJE DEL CLIENTE\n";
        $prompt .= "- Determina AUTOMATICAMENTE en que etapa del embudo se encuentra la conversacion\n";
        $prompt .= "- Aplica las reglas de la etapa que corresponda (de las tablas A, B, C arriba)\n";
        $prompt .= "- APLICA PRIORITARIAMENTE las reglas F1 (RESPUESTA OBLIGATORIA), F2 (DETECCION DE GANCHOS) y F3 (ENCADENAMIENTO) — estan por encima de cualquier regla de etapa\n";
        $prompt .= "- APLICA la regla E4 de TRANSICION FORZOSA: si el cliente ya respondio preguntas o solto un gancho, DEJA de preguntar y OFRECE tu solucion\n";
        $prompt .= "- APLICA las reglas G1 (ROL FIJO: Victor es el VENDEDOR), G2 (PIVOT cuando el cliente ofrece su servicio) y G3 (SEÑAL DE COMPRA vs SEÑAL DE VENTA) — CRITICO para no invertir el rol\n";
        $prompt .= "- APLICA G4 (AUTO-CORRECCION): antes de entregar el mensaje, verifica que Victor NO este comprando/agendando/aceptando servicios del cliente\n";
        $prompt .= "- APLICA H1 (ADAPTACION DE PERSONALIDAD): refleja el tono del cliente (formal/casual/emojis)\n";
        $prompt .= "- APLICA H2 (DETECCION DE URGENCIA): si el cliente muestra urgencia, acelera y ofrece llamada\n";
        $prompt .= "- Genera el mensaje mas adecuado para AVANZAR en la venta desde esa etapa\n";
        $prompt .= "- IMPORTANTE: No menciones que estas detectando la etapa ni expliques tu razonamiento\n";
    } else {
        $stageNum = $stageMap[$stage] ?? 3;
        $prompt .= "Etapa: {$stageNum} ({$stage})\n";
    }
    
    $prompt .= "Cliente: {$perfil}\n";
    $prompt .= "Demo: {$url_demo}\n";
    
    if ($history) {
        $prompt .= "\nHistorial de conversacion:\n{$history}\n";
    }
    
    if ($client_message) {
        $prompt .= "\nULTIMO MENSAJE DEL CLIENTE: \"{$client_message}\"\n";
    }
    
    $prompt .= "\nINSTRUCCION: Responde UNICAMENTE con el texto del mensaje de WhatsApp. Sin notas ni explicaciones.";
    
    return $prompt;
}

// ==================== LLAMAR A OPENCODE CLI LOCAL ====================
function callOpencode($client, $stage, $client_message = '') {
    $prompt = buildPrompt($client, $stage, $client_message);
    
    $toolsDir = 'C:\\victor ontiveros\\tools';
    if (!is_dir($toolsDir)) { mkdir($toolsDir, 0777, true); }
    
    $tmpFile = $toolsDir . '\\_crm_prompt_temp.txt';
    file_put_contents($tmpFile, $prompt, LOCK_EX);
    
    $pyScript = $toolsDir . '\\oc_gen.py';
    
    if (!file_exists($pyScript)) {
        @unlink($tmpFile);
        return null;
    }
    
    set_time_limit(300);
    $pythonExe = 'C:\\Python314\\python.exe';
    $cmd = "chcp 65001 > nul && \"$pythonExe\" \"$pyScript\" \"$tmpFile\" 2>&1";
    $output = shell_exec($cmd);
    
    @unlink($tmpFile);
    
    if (!$output) {
        error_log("[CRM][callOpencode] shell_exec returned null");
        return null;
    }
    $result = trim($output);
    if (stripos($result, 'ERROR:') !== false) {
        error_log("[CRM][callOpencode] Error: " . substr($result, 0, 500));
        return null;
    }
    if (strlen($result) < 5) {
        error_log("[CRM][callOpencode] Output too short: " . substr($result, 0, 200));
        return null;
    }
    
    return $result;
}

// ==================== LIMPIAR OUTPUT DE OPENCODE ====================
function cleanOpencodeOutput($output) {
    if (!$output) return null;
    
    $lines = explode("\n", $output);
    $clean = [];
    $skipPatterns = [
        '/^\>\s/',           // > build ...
        '/^$/i',             // empty
        '/^\x1b\[/',         // ANSI escape codes
        '/^\s*$/',           // whitespace only
    ];
    
    foreach ($lines as $line) {
        $line = preg_replace('/\x1b\[[0-9;]*m/', '', $line); // strip ANSI
        $line = rtrim($line);
        
        $skip = false;
        foreach ($skipPatterns as $pat) {
            if (preg_match($pat, $line)) {
                $skip = true;
                break;
            }
        }
        
        if (!$skip && strlen($line) > 5) {
            $clean[] = $line;
        }
    }
    
    $result = trim(implode("\n", $clean));
    $result = preg_replace('/^["\']|["\']$/', '', $result);
    
    return $result ?: null;
}

// ==================== FUNCIONES AUXILIARES MANTENIDAS ====================
// getStagePrompt() - ELIMINADA, reemplazada por prompt_generador.txt
// generateSmartMessage() - ELIMINADA, el sistema usa opencode directamente

// ==================== GENERAR PLANTILLA BULK (1 sola llamada opencode) ====================
function generateBulkTemplate($stage, $sampleClient) {
    $cat = $sampleClient['categoria'] ?? 'negocio';
    $ciudad = $sampleClient['ciudad'] ?? 'Mérida';
    list($url_demo, $tipo_demo, $nombre_giro) = getDemoUrl($cat);
    $perfil = getBusinessProfile($cat);

    $hora = (int)date('H');
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');

    $msg = "Genera 3 VARIACIONES de mensaje de WhatsApp para etapa \"$stage\".\n";
    $msg .= "Eres Victor Ontiveros, diseñador web en Mérida, Yucatán.\n\n";

    if ($stage === 'inicial') {
        $msg .= "ETAPA: CONTACTO INICIAL (ANTI-SPAM)\n";
        $msg .= "REGLA ABSOLUTA: NO vender. NO mencionar paginas web. NO links. NO precios.\n";
        $msg .= "Solo hacer UNA PREGUNTA corta de calificacion (si/no).\n";
        $msg .= "2-3 lineas MAXIMO. Sonar natural, como persona real.\n";
        $msg .= "Usa estos placeholders: {nombre}, {negocio}, {ciudad}, {categoria}\n";
        $msg .= "Ejemplo: \"{$saludo}, hablo con el encargado de {negocio}?\"\n\n";
    } else {
        $msg .= "ETAPA: {$stage}\n";
        $msg .= "Demo: {$url_demo}\n";
        $msg .= "Link de pago: https://rtvic.github.io/nevas-xmaben/pagar.html\n";
        $msg .= "Usa placeholders: {nombre}, {negocio}, {ciudad}, {categoria}, {servicios}\n\n";
    }

    $msg .= "DATOS DEL GIRO: {$nombre_giro}\n";
    $msg .= "DEMO: {$url_demo}\n\n";
    $msg .= "REGLAS:\n";
    $msg .= "- Cada variación debe ser TOTALMENTE DIFERENTE (palabras, estructura, inicio)\n";
    $msg .= "- Usa los placeholders {nombre} {negocio} {ciudad} {categoria} {servicios}\n";
    $msg .= "- Máximo 1-2 emojis por mensaje\n";
    $msg .= "- Sin comillas, sin \"Mensaje:\" al inicio\n\n";
    $msg .= "Responde SOLO con las 3 variaciones, separadas por ---";

    $tmpFile = dirname(__DIR__, 2) . '\\tools\\_crm_template_prompt.txt';
    file_put_contents($tmpFile, $msg, LOCK_EX);
    $pyScript = dirname(__DIR__, 2) . '\\tools\\oc_gen.py';
    $pythonExe = 'C:\\Python314\\python.exe';
    $cmd = "chcp 65001 > nul && \"$pythonExe\" \"$pyScript\" \"$tmpFile\" 2>&1";
    set_time_limit(300);
    $output = shell_exec($cmd);
    @unlink($tmpFile);

    if (!$output) {
        error_log("[CRM][generateTemplates] shell_exec returned null");
        return null;
    }
    $result = trim($output);
    if (stripos($result, 'ERROR:') !== false) {
        error_log("[CRM][generateTemplates] Error: " . substr($result, 0, 500));
        return null;
    }
    $result = preg_replace('/^["\']|["\']$/', '', $result);

    $parts = preg_split('/---+/', $result);
    $templates = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if (strlen($p) > 10) $templates[] = $p;
    }

    return !empty($templates) ? $templates : null;
}
?>