<?php
require __DIR__ . '/../../src/bootstrap.php';
if (!str_starts_with(config_database()['dbname'], 'test_')) exit(1);
if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
    fwrite(STDERR, "SKIP: extensão pdo_mysql indisponível neste ambiente.\n");
    exit(2);
}

$_SESSION = [];
$mode = $argv[1] ?? 'parent';

if ($mode === 'stock-worker') {
    $ok = stock_create_movement([
        'material_id' => $argv[2],
        'movement_type' => 'saida',
        'quantity' => '1',
        'movement_date' => '2026-09-16',
    ]);
    exit($ok ? 0 : 2);
}

if ($mode === 'production-worker') {
    $studentId = (int)$argv[2];
    $productId = (int)$argv[3];
    $workshopId = (int)$argv[4];
    $professorId = (int)$argv[5];
    $_SESSION['user'] = ['id' => $professorId, 'role' => 'professor'];
    db()->beginTransaction();
    query_one('SELECT COUNT(*) n FROM productions'); // snapshot anterior ao bloqueio
    $ok = production_create([
        'product_id' => $productId,
        'workshop_id' => $workshopId,
        'student_id' => $studentId,
        'quantity' => '1',
        'produced_at' => '2026-09-16',
        'purpose' => 'Teste concorrente',
        'description' => 'Produção criada em processo concorrente',
        'responsible_user_id' => $professorId,
        'availability_status' => 'disponivel',
    ]);
    if ($ok) db()->commit(); else db()->rollBack();
    exit($ok ? 0 : 2);
}

if ($mode === 'delivery-worker') {
    $_SESSION['user']=['id'=>(int)$argv[4],'role'=>'professor'];
    db()->beginTransaction();
    query_one('SELECT COUNT(*) n FROM deliveries');
    try {
        $ok=delivery_mark((int)$argv[2],(int)$argv[3]);
        db()->commit(); exit($ok ? 0 : 2);
    } catch (DomainException $e) { db()->rollBack();exit(2); }
}

function wait_codes(array $children): array {
    $codes = array_map('proc_close', $children);
    sort($codes);
    return $codes;
}
function spawn(array $args) {
    return proc_open(
        array_merge([PHP_BINARY, __FILE__], $args),
        [0 => ['file', '/dev/null', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
        $pipes
    );
}

try {
    // Concorrência de estoque: duas saídas disputam uma única unidade.
    material_create([
        'name' => 'Concorrência ' . uniqid(),
        'category' => 'Teste',
        'unit' => 'un',
        'quantity_mode' => 'integer',
        'current_quantity' => '1',
        'min_quantity' => '0',
        'applies_to_all' => 1,
    ]);
    $materialId = (int)query_one('SELECT MAX(id) id FROM materials')['id'];

    db()->beginTransaction();
    query_one('SELECT id FROM materials WHERE id=? FOR UPDATE', [$materialId]);
    $children = [
        spawn(['stock-worker', (string)$materialId]),
        spawn(['stock-worker', (string)$materialId]),
    ];
    usleep(500000);
    db()->commit();

    if (wait_codes($children) !== [0, 2] || material_find($materialId)['current_quantity'] !== '0.000') {
        throw new RuntimeException('Falha no teste concorrente de estoque.');
    }

    // Concorrência de produção: as duas chamadas diretas devem concluir e gerar
    // números diferentes, mesmo sem action_attempt() envolvendo a função.
    $suffix = uniqid();
    user_create([
        'name' => 'Professor Concorrência',
        'email' => "concorrencia-$suffix@example.org",
        'password' => 'senha-de-teste-123',
        'role' => 'professor',
    ]);
    $professorId = (int)query_one('SELECT MAX(id) id FROM users')['id'];
    $_SESSION['user'] = ['id' => $professorId, 'role' => 'professor'];

    workshop_create([
        'name' => 'Oficina Concorrência ' . $suffix,
        'description' => 'Teste de numeração concorrente',
        'responsible_user_id' => $professorId,
    ]);
    $workshopId = (int)query_one('SELECT MAX(id) id FROM workshops')['id'];

    student_create([
        'full_name' => 'Aluno Concorrência ' . $suffix,
        'guardian_name' => 'Responsável Teste',
        'workshop_ids' => [$workshopId],
    ]);
    $studentId = (int)query_one('SELECT MAX(id) id FROM students')['id'];

    product_create([
        'name' => 'Produto Concorrência ' . $suffix,
        'category' => 'Teste',
        'workshop_id' => $workshopId,
    ]);
    $productId = (int)query_one('SELECT MAX(id) id FROM products')['id'];

    db()->beginTransaction();
    query_one('SELECT id FROM students WHERE id=? FOR UPDATE', [$studentId]);
    $children = [
        spawn(['production-worker', (string)$studentId, (string)$productId, (string)$workshopId, (string)$professorId]),
        spawn(['production-worker', (string)$studentId, (string)$productId, (string)$workshopId, (string)$professorId]),
    ];
    usleep(500000);
    db()->commit();

    if (wait_codes($children) !== [0, 0]) {
        throw new RuntimeException('Uma das produções concorrentes falhou.');
    }

    $numbers = array_map('intval', array_column(
        query_all('SELECT work_number FROM productions WHERE student_id=? ORDER BY work_number', [$studentId]),
        'work_number'
    ));
    if ($numbers !== [1, 2]) {
        throw new RuntimeException('Produções concorrentes receberam numeração incorreta.');
    }

    $children=[spawn(['production-worker',(string)$studentId,(string)$productId,(string)$workshopId,(string)$professorId]),spawn(['production-worker',(string)$studentId,(string)$productId,(string)$workshopId,(string)$professorId])];
    if(wait_codes($children)!==[0,0]) throw new RuntimeException('Preparação do ciclo falhou');
    $works=student_productions($studentId);
    db()->beginTransaction();
    query_one('SELECT id FROM students WHERE id=? FOR UPDATE',[$studentId]);
    $children=[spawn(['delivery-worker',(string)$studentId,(string)$works[0]['id'],(string)$professorId]),spawn(['delivery-worker',(string)$studentId,(string)$works[1]['id'],(string)$professorId])];
    usleep(500000);db()->commit();
    if(wait_codes($children)!==[0,2] || student_delivery_state($studentId)['delivered_cycles']!==1) throw new RuntimeException('Duas entregas no mesmo ciclo');
    echo "OK: concorrência de estoque, numeração e entregas com snapshot anterior ao bloqueio\n";
} catch (Throwable $e) {
    try {
        $connection = db();
        if ($connection->inTransaction()) $connection->rollBack();
    } catch (Throwable $ignored) {
        // A própria conexão pode estar indisponível no ambiente de teste.
    }
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
