<?php
require __DIR__ . '/../../src/bootstrap.php';
if (!str_starts_with(config_database()['dbname'], 'test_')) exit(1);
db()->exec("UPDATE users SET active=1, role=IF(email='prof@example.org','professor','aluno'); UPDATE workshops SET active=1; UPDATE products SET active=1;");
