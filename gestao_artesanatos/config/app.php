<?php
return [
    'name' => 'Gestão Artesanal',
    'base_url' => rtrim(getenv('APP_BASE_PATH') ?: '', '/'),
    'timezone' => 'America/Sao_Paulo',
    'allow_registration' => getenv('ALLOW_REGISTRATION') === '1',
];
