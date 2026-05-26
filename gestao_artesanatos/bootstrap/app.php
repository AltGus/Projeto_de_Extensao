<?php
// bootstrap/app.php
$config = require __DIR__ . '/../config/app.php';

date_default_timezone_set($config['timezone']);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('gestao_artesanal_session');
    session_start();
}

require_once __DIR__ . '/../app/Helpers/functions.php';

spl_autoload_register(function (string $class): void {
    $folders = [
        __DIR__ . '/../app/Core/',
        __DIR__ . '/../app/Controllers/',
        __DIR__ . '/../app/Models/',
        __DIR__ . '/../app/Middleware/',
    ];

    foreach ($folders as $folder) {
        $file = $folder . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
