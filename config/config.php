<?php
// ============================================================
//  CONFIGURACIÓN GLOBAL – Bienes Raíces Framework MVC
// ============================================================
// --- CARGAR VARIABLES LOCALES (Ignorado en GitHub) ---
$env_file = dirname(__DIR__) . '/env.local.php';
if (file_exists($env_file)) {
    $variables = require $env_file;
    foreach ($variables as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

// --- BASE URL ---
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");

// Si estamos en XAMPP clásico (la URL tiene la ruta de la carpeta)
if (strpos($_SERVER['REQUEST_URI'], '/APF1-') === 0 && strpos($host, 'localhost') !== false && strpos($host, ':') === false) {
    $default_url = $protocol . '://' . $host . '/APF1-Railway/public';
} else {
    // Para php -S localhost:8000 o Railway
    $default_url = $protocol . '://' . $host;
}

$env_base_url = getenv('BASE_URL');
if ($env_base_url) {
    $env_base_url = rtrim($env_base_url, '/');
    if (substr($env_base_url, -7) === '/public') {
        $env_base_url = substr($env_base_url, 0, -7);
    }
    define('BASE_URL', $env_base_url);
} else {
    define('BASE_URL', $default_url);
}

// --- RUTAS ABSOLUTAS ---
define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_DIR', APP_ROOT . '/public/uploads/propiedades/');
define('UPLOAD_URL', BASE_URL . '/uploads/propiedades/');
define('LOG_FILE',   APP_ROOT . '/logs/auth.log');

// --- BASE DE DATOS ---
define('DB_HOST',    getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'bdfhwiw83jhvyecnhylo-mysql.services.clever-cloud.com');
define('DB_USER',    getenv('DB_USER') !== false ? getenv('DB_USER') : 'ujptsj5evlkffgy4');
define('DB_PASS',    getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'mMCTnsZP4ezV1smB9lYB');
define('DB_NAME',    getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'bdfhwiw83jhvyecnhylo');
define('DB_CHARSET', 'utf8mb4');

// --- APP ---
define('APP_NAME',    'Hogar Ideal Perú');
define('APP_TAGLINE', 'Tu hogar perfecto, garantizado');
