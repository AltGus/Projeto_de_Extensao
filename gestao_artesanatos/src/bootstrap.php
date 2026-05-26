<?php

/*
|--------------------------------------------------------------------------
| Bootstrap do Sistema
|--------------------------------------------------------------------------
| Este arquivo inicializa as configurações principais do sistema:
| - Configuração de timezone
| - Sessão
| - Carregamento das funções principais
|--------------------------------------------------------------------------
*/

$appConfigPath = __DIR__ . '/../config/app.php';

if (file_exists($appConfigPath)) {
    $appConfig = require $appConfigPath;
} else {
    $appConfig = [
        'name' => 'Gestão Artesanal',
        'base_url' => '',
        'timezone' => 'America/Sao_Paulo',
    ];
}

date_default_timezone_set($appConfig['timezone'] ?? 'America/Sao_Paulo');

if (session_status() === PHP_SESSION_NONE) {
    session_name('gestao_artesanal_session');
    session_start();
}

require_once __DIR__ . '/lib.php';