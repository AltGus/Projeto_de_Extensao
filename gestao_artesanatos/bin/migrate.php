<?php
// Idempotent migration for the procedural PHP schema; never runs reset.sql/seed.sql.
if (PHP_SAPI !== 'cli') exit(1);
require __DIR__ . '/../src/bootstrap.php';
try {
    if (!(int)query_one("SELECT GET_LOCK('gestao_local_migration', 10) AS ok")['ok']) throw new RuntimeException('Outra migração está em execução.');
    foreach (['users', 'workshops', 'products', 'materials'] as $table) {
        if (!query_one('SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=?', [$table, 'active'])) {
            db()->exec("ALTER TABLE `$table` ADD active TINYINT(1) NOT NULL DEFAULT 1");
        }
    }
    if (!query_one("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='materials' AND column_name='applies_to_all'")) {
        db()->exec('ALTER TABLE materials ADD applies_to_all TINYINT(1) NOT NULL DEFAULT 1');
    }
    db()->exec('CREATE TABLE IF NOT EXISTS material_workshop (
        material_id INT NOT NULL, workshop_id INT NOT NULL, PRIMARY KEY(material_id, workshop_id),
        FOREIGN KEY(material_id) REFERENCES materials(id) ON DELETE CASCADE,
        FOREIGN KEY(workshop_id) REFERENCES workshops(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    db()->exec('ALTER TABLE materials MODIFY current_quantity DECIMAL(12,3) NOT NULL DEFAULT 0, MODIFY min_quantity DECIMAL(12,3) NOT NULL DEFAULT 0');
    db()->exec('ALTER TABLE stock_movements MODIFY quantity DECIMAL(12,3) NOT NULL');
    db()->exec('DELETE a FROM workshop_user a JOIN workshop_user b ON a.workshop_id=b.workshop_id AND a.user_id=b.user_id AND a.id>b.id');
    if (!query_one("SELECT INDEX_NAME FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='workshop_user' AND index_name='uq_workshop_user'")) {
        db()->exec('ALTER TABLE workshop_user ADD UNIQUE KEY uq_workshop_user(workshop_id,user_id)');
    }
    // Keep historical relations even if someone bypasses the UI and deletes with SQL.
    $relations = [
        ['productions','product_id','products'], ['productions','workshop_id','workshops'],
        ['productions','responsible_user_id','users'], ['stock_movements','material_id','materials'],
        ['stock_movements','user_id','users'], ['activity_logs','user_id','users'],
        ['products','workshop_id','workshops'],
    ];
    foreach ($relations as [$table, $column, $parent]) {
        $keys = query_all('SELECT CONSTRAINT_NAME FROM information_schema.key_column_usage WHERE table_schema=DATABASE() AND table_name=? AND column_name=? AND referenced_table_name IS NOT NULL', [$table,$column]);
        $rule = query_one('SELECT DELETE_RULE FROM information_schema.referential_constraints WHERE constraint_schema=DATABASE() AND table_name=? AND constraint_name=?', [$table, $keys[0]['CONSTRAINT_NAME'] ?? '']);
        if (count($keys) === 1 && ($rule['DELETE_RULE'] ?? '') === 'RESTRICT') continue;
        $drops = [];
        foreach ($keys as $key) $drops[] = 'DROP FOREIGN KEY `' . str_replace('`','``',$key['CONSTRAINT_NAME']) . '`';
        $drops[] = "ADD CONSTRAINT `fk_local_{$table}_{$column}` FOREIGN KEY (`$column`) REFERENCES `$parent`(id) ON DELETE RESTRICT ON UPDATE CASCADE";
        db()->exec("ALTER TABLE `$table` " . implode(', ', $drops));
    }
    echo "Migração concluída; dados preservados.\n";
} catch (Throwable $e) { fwrite(STDERR, $e->getMessage() . "\n"); exit(1); }
finally { if (isset($table)) query_one("SELECT RELEASE_LOCK('gestao_local_migration')"); }
