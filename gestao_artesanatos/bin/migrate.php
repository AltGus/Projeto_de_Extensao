<?php
// Migração idempotente e preservadora para instalações existentes.
if (PHP_SAPI !== 'cli') exit(1);
require __DIR__ . '/../src/bootstrap.php';

function has_column(string $table, string $column): bool {
    return (bool) query_one('SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=?', [$table, $column]);
}
function has_index(string $table, string $index): bool {
    return (bool) query_one('SELECT INDEX_NAME FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=? AND index_name=?', [$table, $index]);
}

try {
    if (!(int)query_one("SELECT GET_LOCK('gestao_local_migration', 10) AS ok")['ok']) throw new RuntimeException('Outra migração está em execução.');

    foreach (['users', 'workshops', 'products', 'materials'] as $table) {
        if (!has_column($table, 'active')) db()->exec("ALTER TABLE `$table` ADD active TINYINT(1) NOT NULL DEFAULT 1");
    }
    if (!has_column('materials', 'applies_to_all')) db()->exec('ALTER TABLE materials ADD applies_to_all TINYINT(1) NOT NULL DEFAULT 1');
    if (!has_column('materials', 'quantity_mode')) db()->exec("ALTER TABLE materials ADD quantity_mode ENUM('integer','decimal') NOT NULL DEFAULT 'decimal' AFTER unit");
    if (!has_column('workshops', 'responsible_user_id')) db()->exec('ALTER TABLE workshops ADD responsible_user_id INT NULL AFTER description');
    if (!has_column('workshops', 'image_path')) db()->exec('ALTER TABLE workshops ADD image_path VARCHAR(255) NULL AFTER responsible_user_id');

    db()->exec('CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        active TINYINT(1) NOT NULL DEFAULT 1,
        full_name VARCHAR(160) NOT NULL,
        guardian_name VARCHAR(200) NOT NULL,
        father_phone VARCHAR(30) NULL,
        mother_phone VARCHAR(30) NULL,
        notes TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_students_name (full_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    db()->exec('CREATE TABLE IF NOT EXISTS student_workshop (
        student_id INT NOT NULL,
        workshop_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY(student_id, workshop_id),
        CONSTRAINT fk_student_workshop_student FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE RESTRICT ON UPDATE CASCADE,
        CONSTRAINT fk_student_workshop_workshop FOREIGN KEY(workshop_id) REFERENCES workshops(id) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    if (!has_column('productions', 'student_id')) db()->exec('ALTER TABLE productions ADD student_id INT NULL AFTER workshop_id');
    if (!has_column('productions', 'work_number')) db()->exec('ALTER TABLE productions ADD work_number INT NULL AFTER student_id');
    if (!has_column('productions', 'availability_status')) db()->exec("ALTER TABLE productions ADD availability_status ENUM('disponivel','indisponivel') NOT NULL DEFAULT 'disponivel' AFTER work_number");
    if (!has_index('productions', 'uq_student_work_number')) db()->exec('ALTER TABLE productions ADD UNIQUE KEY uq_student_work_number(student_id, work_number)');
    if (!has_index('productions', 'idx_productions_student')) db()->exec('ALTER TABLE productions ADD INDEX idx_productions_student(student_id)');

    $fkStudent = query_one("SELECT CONSTRAINT_NAME FROM information_schema.key_column_usage WHERE table_schema=DATABASE() AND table_name='productions' AND column_name='student_id' AND referenced_table_name='students'");
    if (!$fkStudent) db()->exec('ALTER TABLE productions ADD CONSTRAINT fk_productions_student FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE RESTRICT ON UPDATE CASCADE');

    db()->exec('CREATE TABLE IF NOT EXISTS deliveries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        production_id INT NOT NULL,
        cycle_number INT NOT NULL,
        delivered_at DATETIME NOT NULL,
        delivered_by_user_id INT NULL,
        notes TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_delivery_production (production_id),
        UNIQUE KEY uq_delivery_cycle (student_id, cycle_number),
        CONSTRAINT fk_deliveries_student FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE RESTRICT ON UPDATE CASCADE,
        CONSTRAINT fk_deliveries_production FOREIGN KEY(production_id) REFERENCES productions(id) ON DELETE RESTRICT ON UPDATE CASCADE,
        CONSTRAINT fk_deliveries_user FOREIGN KEY(delivered_by_user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    db()->exec('CREATE TABLE IF NOT EXISTS material_workshop (
        material_id INT NOT NULL, workshop_id INT NOT NULL, PRIMARY KEY(material_id, workshop_id),
        FOREIGN KEY(material_id) REFERENCES materials(id) ON DELETE CASCADE,
        FOREIGN KEY(workshop_id) REFERENCES workshops(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    db()->exec('ALTER TABLE materials MODIFY current_quantity DECIMAL(12,3) NOT NULL DEFAULT 0, MODIFY min_quantity DECIMAL(12,3) NOT NULL DEFAULT 0');
    db()->exec('ALTER TABLE stock_movements MODIFY quantity DECIMAL(12,3) NOT NULL');

    if (!has_index('workshops', 'idx_workshops_responsible')) db()->exec('ALTER TABLE workshops ADD INDEX idx_workshops_responsible(responsible_user_id)');
    $fkResp = query_one("SELECT CONSTRAINT_NAME FROM information_schema.key_column_usage WHERE table_schema=DATABASE() AND table_name='workshops' AND column_name='responsible_user_id' AND referenced_table_name='users'");
    if (!$fkResp) db()->exec('ALTER TABLE workshops ADD CONSTRAINT fk_workshops_responsible FOREIGN KEY(responsible_user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE');

    db()->exec('DELETE a FROM workshop_user a JOIN workshop_user b ON a.workshop_id=b.workshop_id AND a.user_id=b.user_id AND a.id>b.id');
    if (!has_index('workshop_user', 'uq_workshop_user')) db()->exec('ALTER TABLE workshop_user ADD UNIQUE KEY uq_workshop_user(workshop_id,user_id)');

    if (!has_column('students','last_work_number')) db()->exec('ALTER TABLE students ADD last_work_number INT NOT NULL DEFAULT 0');
    db()->exec('UPDATE students s SET last_work_number=GREATEST(last_work_number,COALESCE((SELECT MAX(work_number) FROM productions p WHERE p.student_id=s.id),0))');
    foreach ([['productions','product_id','products'],['productions','workshop_id','workshops'],['productions','responsible_user_id','users'],['stock_movements','material_id','materials'],['stock_movements','user_id','users'],['activity_logs','user_id','users'],['products','workshop_id','workshops']] as [$table,$column,$parent]) {
        $keys=query_all('SELECT CONSTRAINT_NAME FROM information_schema.key_column_usage WHERE table_schema=DATABASE() AND table_name=? AND column_name=? AND referenced_table_name IS NOT NULL',[$table,$column]);
        $rule=query_one('SELECT DELETE_RULE FROM information_schema.referential_constraints WHERE constraint_schema=DATABASE() AND table_name=? AND constraint_name=?',[$table,$keys[0]['CONSTRAINT_NAME']??'']);
        if (count($keys)===1 && ($rule['DELETE_RULE']??'')==='RESTRICT') continue;
        $clauses=[];
        foreach ($keys as $key) $clauses[]='DROP FOREIGN KEY `'.str_replace('`','``',$key['CONSTRAINT_NAME']).'`';
        $clauses[]="ADD CONSTRAINT `fk_review_{$table}_{$column}` FOREIGN KEY (`$column`) REFERENCES `$parent`(id) ON DELETE RESTRICT ON UPDATE CASCADE";
        db()->exec("ALTER TABLE `$table` ".implode(', ',$clauses));
    }
    echo "Migração concluída; dados existentes preservados.\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n"); exit(1);
} finally {
    try { query_one("SELECT RELEASE_LOCK('gestao_local_migration')"); } catch (Throwable $ignored) {}
}
