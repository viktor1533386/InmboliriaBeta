<?php
// router.php para el servidor PHP embebido con -t public
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Si el archivo existe físicamente en la carpeta public, dejamos que el servidor interno lo despache
$file = __DIR__ . '/public' . $path;
if ($path !== '/' && file_exists($file) && is_file($file)) {
    return false;
}

// Si no es un archivo estático, enrutamos al MVC
$_GET['url'] = ltrim($path, '/');
require __DIR__ . '/public/index.php';
