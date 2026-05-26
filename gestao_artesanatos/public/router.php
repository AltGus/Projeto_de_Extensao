<?php

/*
|--------------------------------------------------------------------------
| Router para servidor embutido do PHP
|--------------------------------------------------------------------------
| Use o comando:
|
| php -S localhost:8000 -t public public/router.php
|--------------------------------------------------------------------------
*/

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';