<?php
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$file = realpath(__DIR__ . $uri);
$assets = realpath(__DIR__ . '/assets');
if ($file && $assets && str_starts_with($file, $assets . DIRECTORY_SEPARATOR) && is_file($file)) return false;
if ($file && preg_match('~^/uploads/workshops/[a-f0-9]{24}\.jpg$~D', $uri) && is_file($file)) {
    header('Content-Type: image/jpeg');
    header('X-Content-Type-Options: nosniff');
    readfile($file);
    return;
}
require __DIR__ . '/index.php';
