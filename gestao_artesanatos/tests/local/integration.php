<?php
// Execute somente em banco vazio descartável cujo nome comece por test_.
require __DIR__ . '/../../src/bootstrap.php';
if (!str_starts_with(config_database()['dbname'], 'test_')) exit("Use DB_DATABASE=test_gestao\n");
if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
    fwrite(STDERR, "SKIP: extensão pdo_mysql indisponível neste ambiente.\n");
    exit(2);
}

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
function expect_domain(callable $callback, string $message): void {
    try {
        $callback();
    } catch (DomainException $e) {
        return;
    }
    throw new RuntimeException($message);
}

$_SESSION = [];

try {
    db()->exec(file_get_contents(__DIR__ . '/../../database/schema.sql'));

    check(user_create([
        'name' => 'Professor',
        'email' => 'prof@example.org',
        'password' => 'senha-de-teste-123',
        'role' => 'professor',
    ]), 'professor');
    $professorId = (int)query_one('SELECT MAX(id) id FROM users')['id'];
    $_SESSION['user'] = ['id' => $professorId, 'role' => 'professor'];

    check(workshop_create([
        'name' => 'Costura',
        'description' => 'Oficina teste',
        'responsible_user_id' => $professorId,
    ]), 'oficina');
    $workshopId = (int)query_one('SELECT MAX(id) id FROM workshops')['id'];

    check(student_create([
        'full_name' => 'Ana Silva',
        'guardian_name' => 'Maria Silva',
        'workshop_ids' => [$workshopId],
    ]), 'aluna');
    $anaId = (int)query_one('SELECT MAX(id) id FROM students')['id'];

    check(student_create([
        'full_name' => 'Ana Silva',
        'guardian_name' => 'João Silva',
        'workshop_ids' => [$workshopId],
    ]), 'nome duplicado permitido');
    $ana2Id = (int)query_one('SELECT MAX(id) id FROM students')['id'];

    check($anaId !== $ana2Id && count(students_all('Ana Silva')) === 2, 'IDs exclusivos e pesquisa');
    workshop_add_participant($workshopId, $anaId);
    workshop_add_participant($workshopId, $anaId);
    check(
        (int)query_one('SELECT COUNT(*) n FROM student_workshop WHERE student_id=? AND workshop_id=?', [$anaId, $workshopId])['n'] === 1,
        'vínculo único aluno/oficina'
    );

    check(product_create([
        'name' => 'Bolsa',
        'category' => 'Tecido',
        'workshop_id' => $workshopId,
    ]), 'produto');
    $productId = (int)query_one('SELECT MAX(id) id FROM products')['id'];

    check(material_create([
        'name' => 'Pincel',
        'category' => 'Ferramentas',
        'unit' => 'un',
        'quantity_mode' => 'integer',
        'current_quantity' => '5',
        'min_quantity' => '1',
        'applies_to_all' => 1,
    ]), 'material inteiro');
    $integerMaterialId = (int)query_one('SELECT MAX(id) id FROM materials')['id'];
    check(!stock_create_movement([
        'material_id' => $integerMaterialId,
        'movement_type' => 'saida',
        'quantity' => '1.5',
        'movement_date' => '2026-09-16',
    ]), 'fração rejeitada em inteiro');

    check(material_create([
        'name' => 'Tecido',
        'category' => 'Tecidos',
        'unit' => 'm',
        'quantity_mode' => 'decimal',
        'current_quantity' => '2,500',
        'min_quantity' => '0.500',
        'workshop_ids' => [$workshopId],
    ]), 'material decimal');
    $decimalMaterialId = (int)query_one('SELECT MAX(id) id FROM materials')['id'];
    check(stock_create_movement([
        'material_id' => $decimalMaterialId,
        'movement_type' => 'saida',
        'quantity' => '1.250',
        'movement_date' => '2026-09-16',
    ]), 'saída decimal');
    check(material_find($decimalMaterialId)['current_quantity'] === '1.250', 'saldo decimal');

    $base = [
        'product_id' => $productId,
        'workshop_id' => $workshopId,
        'student_id' => $anaId,
        'quantity' => '1',
        'produced_at' => '2026-09-16',
        'purpose' => 'Atividade pedagógica',
        'description' => 'Trabalho artesanal de integração',
        'responsible_user_id' => $professorId,
        'availability_status' => 'disponivel',
    ];

    // Chamadas diretas exercitam a transação interna de production_create().
    for ($i = 1; $i <= 4; $i++) check(production_create($base), 'produção ' . $i);
    $history = student_productions($anaId);
    check(count($history) === 4 && array_map('intval', array_column($history, 'work_number')) === [1,2,3,4], 'sequência 1-4');
    check(student_delivery_state($anaId)['pending_cycles'] === 1, 'entrega no quarto trabalho');

    $firstDeliveredProduction = (int)$history[0]['id'];
    check(delivery_mark($anaId, $firstDeliveredProduction), 'primeira entrega direta');
    check(student_delivery_state($anaId)['pending_cycles'] === 0, 'primeiro ciclo atendido');
    $deliveredRecord = student_productions($anaId)[0];
    check(!empty($deliveredRecord['delivery_id']), 'produção entregue mantém vínculo histórico de entrega');
    check($deliveredRecord['availability_status'] === 'disponivel', 'entrega não reescreve disponibilidade histórica armazenada');
    expect_domain(fn() => production_update($firstDeliveredProduction, $base), 'produção entregue pôde ser editada');

    for ($i = 5; $i <= 8; $i++) check(production_create($base), 'produção ' . $i);
    $history = student_productions($anaId);
    check((int)$history[7]['work_number'] === 8, 'sequência continua até 8');
    check(student_delivery_state($anaId)['pending_cycles'] === 1, 'nova entrega no oitavo trabalho');

    $unavailableProduction = (int)$history[1]['id'];
    execute_query("UPDATE productions SET availability_status='indisponivel' WHERE id=?", [$unavailableProduction]);
    expect_domain(fn() => delivery_mark($anaId, $unavailableProduction), 'produção indisponível foi entregue');
    execute_query("UPDATE productions SET availability_status='disponivel' WHERE id=?", [$unavailableProduction]);

    $secondDeliveredProduction = (int)$history[2]['id'];
    check(delivery_mark($anaId, $secondDeliveredProduction), 'segunda entrega');
    expect_domain(fn() => delivery_mark($anaId, (int)$history[3]['id']), 'mesmo ciclo recebeu duas entregas');

    for ($i = 9; $i <= 12; $i++) check(production_create($base), 'produção ' . $i);
    check(student_delivery_state($anaId)['pending_cycles'] === 1, 'terceiro ciclo pendente');
    expect_domain(fn() => delivery_mark($anaId, $firstDeliveredProduction), 'mesma produção foi entregue duas vezes');

    $baseAna2 = $base;
    $baseAna2['student_id'] = $ana2Id;
    for ($i = 1; $i <= 4; $i++) check(production_create($baseAna2), 'produção segunda Ana ' . $i);
    check(student_delivery_state($ana2Id)['pending_cycles'] === 1, 'segunda Ana tem ciclo próprio');
    expect_domain(fn() => delivery_mark($ana2Id, (int)$history[4]['id']), 'produção de outro aluno foi entregue');

    execute_query("UPDATE deliveries SET delivered_at='2026-09-16 12:00:00'");
    $monthly = ['period_mode' => 'monthly', 'year' => 2026, 'month' => 9];
    check(count(report_filtered_productions($monthly + ['student_id' => $anaId])) === 12, 'relatório mensal por aluno');
    check(count(report_filtered_productions(['period_mode' => 'semester', 'year' => 2026, 'semester' => 2])) === 16, 'relatório semestral');
    check(count(report_filtered_productions(['period_mode' => 'annual', 'year' => 2026])) === 16, 'relatório anual');

    $byWorkshop = report_production_by_workshop_filtered($monthly);
    check(count($byWorkshop) === 1 && (int)$byWorkshop[0]['production_records'] === 16, 'agrupamento por oficina');
    $byWorkshopFiltered = report_production_by_workshop_filtered($monthly + ['student_id' => $ana2Id]);
    check((int)$byWorkshopFiltered[0]['production_records'] === 4, 'agrupamento por oficina respeita filtro de aluno');
    $byStudent = report_production_by_student($monthly);
    check(count($byStudent) === 2, 'agrupamento por aluno');
    $deliveries = report_deliveries($monthly);
    check(count($deliveries) === 2, 'entregas do período');
    check(count(report_deliveries($monthly + ['student_id' => $anaId])) === 2, 'entregas respeitam filtro de aluno');
    $materials = report_material_consumption($monthly);
    check(count($materials) === 1 && (int)$materials[0]['movement_count'] === 1, 'materiais respeitam período');
    check(report_material_consumption(['period_mode' => 'monthly', 'year' => 2026, 'month' => 8]) === [], 'material fora do período não aparece');

    echo "OK: alunos, sequência, transações diretas, entregas, integridade, estoque e relatórios gerenciais\n";
} catch (Throwable $e) {
    fwrite(STDERR, (string)$e . "\n");
    exit(1);
}
