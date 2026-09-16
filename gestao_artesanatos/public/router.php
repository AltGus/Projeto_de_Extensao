<?php
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$file = realpath(__DIR__ . $uri);
$assets = realpath(__DIR__ . '/assets');
if ($file && $assets && str_starts_with($file, $assets . DIRECTORY_SEPARATOR) && is_file($file)) return false;
require __DIR__ . '/index.php';
