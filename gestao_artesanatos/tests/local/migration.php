<?php
// Run in a SEPARATE empty test_ database; the legacy schema is from main 2718f35.
require __DIR__.'/../../src/bootstrap.php';
if (!str_starts_with(config_database()['dbname'],'test_')) exit(1);
try {
    db()->exec(file_get_contents(__DIR__.'/fixtures/legacy-schema.sql'));
    db()->exec("INSERT INTO users(name,email,password,role) VALUES('Legado','legacy@example.org','hash','professor');
    INSERT INTO workshops(name,description) VALUES('Legado','Oficina anterior');
    INSERT INTO products(name,description,category,workshop_id) VALUES('Legado','Descrição','Teste',1);
    INSERT INTO productions(product_id,workshop_id,quantity,produced_at,responsible_user_id,purpose,description) VALUES(1,1,1,'2026-01-01',1,'Teste','Descrição legada');");
    for($i=0;$i<2;$i++) {
        $proc=proc_open([PHP_BINARY,__DIR__.'/../../bin/migrate.php'],[0=>STDIN,1=>STDOUT,2=>STDERR],$pipes);
        if(proc_close($proc)!==0) throw new RuntimeException('Migração falhou');
    }
    $p=production_find(1);
    if (!$p || $p['student_id']!==null || $p['description']!=='Descrição legada') throw new RuntimeException('Histórico alterado');
    try { db()->exec('DELETE FROM products WHERE id=1'); throw new RuntimeException('Exclusão em cascata ainda permitida'); }
    catch(PDOException $e) { if($e->getCode()!=='23000') throw $e; }
    echo "OK: migração do esquema original, repetição e preservação do histórico\n";
} catch(Throwable $e) { fwrite(STDERR,(string)$e."\n");exit(1); }
