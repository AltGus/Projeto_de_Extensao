<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
set_exception_handler(function (Throwable $e): void {
    error_log((string)$e);
    http_response_code(500);
    echo 'Não foi possível atender à solicitação. Consulte o administrador.';
});
// Native installations may use a non-versioned PHP configuration outside public/.
$local = __DIR__ . '/../config/local.php';
if (is_file($local)) {
    foreach (require $local as $key => $value) putenv($key . '=' . $value);
}
require_once __DIR__ . '/lib.php';
date_default_timezone_set(config_value('app.timezone', 'America/Sao_Paulo'));
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('gestao_artesanal_session');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => getenv('SESSION_SECURE_COOKIE') === '1' || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
    header('Cache-Control: no-store');
}
