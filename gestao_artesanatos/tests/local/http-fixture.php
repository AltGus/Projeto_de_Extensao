<?php
require __DIR__ . '/../../src/bootstrap.php';
if (!str_starts_with(config_database()['dbname'], 'test_')) exit(1);
db()->exec("UPDATE users SET active=1, role=IF(email='prof@example.org','professor','aluno'); UPDATE workshops SET active=1; UPDATE products SET active=1;");

if (!query_one('SELECT id FROM users WHERE email=?', ['aluno@example.org'])) {
    user_create(['name'=>'Conta legada','email'=>'aluno@example.org','password'=>'senha-de-teste-123','role'=>'aluno']);
}
