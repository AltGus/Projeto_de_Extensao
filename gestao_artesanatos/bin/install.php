<?php
if (PHP_SAPI !== 'cli') exit(1);
require __DIR__ . '/../src/bootstrap.php';
try {
    if (query_one("SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = DATABASE()")['n'] > 0) {
        throw new RuntimeException('Banco não vazio. Use migrate.php para uma instalação existente.');
    }
    $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
    db()->exec($sql);
    echo "Banco instalado. Execute php bin/create-admin.php.\n";
} catch (Throwable $e) { fwrite(STDERR, $e->getMessage() . "\n"); exit(1); }
