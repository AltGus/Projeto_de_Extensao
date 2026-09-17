<?php
[$periodStart, $periodEnd] = report_period_dates($_GET);
$mode = $_GET['period_mode'] ?? 'custom';
$productionByWorkshop = $productionByWorkshop ?? [];
$productionByStudent = $productionByStudent ?? [];
$deliveriesInPeriod = $deliveriesInPeriod ?? [];
$materialConsumption = $materialConsumption ?? [];
$stockMovements = $stockMovements ?? [];
?>

<div class="page-header">
    <div>
        <h2>Relatórios</h2>
        <p>Indicadores administrativos por período, oficina, produto, aluno e responsável.</p>
    </div>
</div>

<form method="GET" class="panel panel-body form no-print">
    <div class="grid three">
        <div class="form-group">
            <label for="period_mode">Período</label>
            <select class="select" id="period_mode" name="period_mode">
                <option value="custom" <?= $mode === 'custom' ? 'selected' : '' ?>>Personalizado</option>
                <option value="monthly" <?= $mode === 'monthly' ? 'selected' : '' ?>>Mensal</option>
                <option value="semester" <?= $mode === 'semester' ? 'selected' : '' ?>>Semestral</option>
                <option value="annual" <?= $mode === 'annual' ? 'selected' : '' ?>>Anual</option>
            </select>
        </div>

        <div class="form-group">
            <label for="year">Ano</label>
            <input class="input" id="year" type="number" name="year" min="2000" max="2100" value="<?= e($_GET['year'] ?? date('Y')) ?>">
        </div>

        <div class="form-group">
            <label for="month">Mês</label>
            <select class="select" id="month" name="month">
                <?php for ($month = 1; $month <= 12; $month++): ?>
                    <option value="<?= $month ?>" <?= (int)($_GET['month'] ?? date('n')) === $month ? 'selected' : '' ?>>
                        <?= str_pad((string)$month, 2, '0', STR_PAD_LEFT) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <div class="grid three">
        <div class="form-group">
            <label for="semester">Semestre</label>
            <select class="select" id="semester" name="semester">
                <option value="1" <?= ($_GET['semester'] ?? '1') === '1' ? 'selected' : '' ?>>1º semestre</option>
                <option value="2" <?= ($_GET['semester'] ?? '') === '2' ? 'selected' : '' ?>>2º semestre</option>
            </select>
        </div>

        <div class="form-group">
            <label for="start">De</label>
            <input class="input" id="start" type="date" name="start" value="<?= e($_GET['start'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="end">Até</label>
            <input class="input" id="end" type="date" name="end" value="<?= e($_GET['end'] ?? '') ?>">
        </div>
    </div>

    <div class="grid four">
        <?php foreach ([
            'workshop_id' => ['Oficina', $workshops],
            'product_id' => ['Produto', $products],
            'student_id' => ['Aluno', $students],
            'responsible_user_id' => ['Responsável', $users],
        ] as $key => [$label, $options]): ?>
            <div class="form-group">
                <label for="<?= e($key) ?>"><?= e($label) ?></label>
                <select class="select" id="<?= e($key) ?>" name="<?= e($key) ?>">
                    <option value="">Todos</option>
                    <?php foreach ($options as $option): ?>
                        <option value="<?= e($option['id']) ?>" <?= (string)($_GET[$key] ?? '') === (string)$option['id'] ? 'selected' : '' ?>>
                            <?= e($option['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="actions">
        <button class="btn btn-primary">Aplicar filtros</button>
        <button class="btn btn-secondary" name="export" value="csv">Exportar CSV</button>
        <button class="btn btn-secondary" type="button" onclick="window.print()">Imprimir / salvar PDF</button>
    </div>
</form>

<p>
    <strong>Período efetivo:</strong>
    <?= e($periodStart ?: 'início dos registros') ?> a <?= e($periodEnd ?: 'data mais recente') ?>
</p>

<section class="panel">
    <div class="panel-header">
        <h3>Indicadores do período</h3>
        <p>Estes valores respeitam o intervalo selecionado. Os indicadores de produção e entrega também respeitam os filtros aplicáveis.</p>
    </div>
    <div class="panel-body">
        <div class="grid four">
            <div class="stat-card">
                <span class="stat-label">Registros de produção</span>
                <strong class="stat-value"><?= e($summary['production_records']) ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Quantidade produzida</span>
                <strong class="stat-value"><?= e($summary['production_units']) ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Alunos atendidos</span>
                <strong class="stat-value"><?= e($summary['students']) ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Entregas realizadas</span>
                <strong class="stat-value"><?= e($summary['deliveries']) ?></strong>
            </div>
        </div>

        <div class="grid three" style="margin-top:16px">
            <div class="stat-card">
                <span class="stat-label">Movimentações de estoque</span>
                <strong class="stat-value"><?= e($summary['stock_movements']) ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Movimentações de entrada</span>
                <strong class="stat-value"><?= e($summary['stock_entries']) ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Movimentações de saída</span>
                <strong class="stat-value"><?= e($summary['stock_exits']) ?></strong>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Situação atual</h3>
        <p>Estes indicadores representam o estado atual do sistema e não pertencem necessariamente ao período filtrado.</p>
    </div>
    <div class="panel-body">
        <div class="grid two">
            <div class="stat-card">
                <span class="stat-label">Entregas pendentes atualmente</span>
                <strong class="stat-value"><?= e($summary['pending_deliveries']) ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Materiais com estoque baixo atualmente</span>
                <strong class="stat-value"><?= e($summary['low_stock']) ?></strong>
            </div>
        </div>
    </div>
</section>

<div class="grid two">
    <section class="panel">
        <div class="panel-header">
            <h3>Produção por oficina</h3>
            <p>Resumo gerencial das produções dentro do período e dos filtros selecionados.</p>
        </div>
        <div class="panel-body">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Oficina</th>
                            <th>Registros</th>
                            <th>Qtd. produzida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productionByWorkshop as $item): ?>
                            <tr>
                                <td><?= e($item['name']) ?></td>
                                <td><?= e($item['production_records']) ?></td>
                                <td><?= e($item['production_units']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$productionByWorkshop): ?>
                            <tr><td colspan="3">Nenhuma produção neste filtro.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h3>Produção por aluno</h3>
            <p>Quantidade de trabalhos registrados por aluno no período.</p>
        </div>
        <div class="panel-body">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Trabalhos</th>
                            <th>Qtd. produzida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productionByStudent as $item): ?>
                            <tr>
                                <td><?= e($item['student_name']) ?></td>
                                <td><?= e($item['work_count']) ?></td>
                                <td><?= e($item['production_units']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$productionByStudent): ?>
                            <tr><td colspan="3">Nenhum aluno com produção neste filtro.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Entregas no período</h3>
        <p>Entregas cuja data de entrega está dentro do intervalo selecionado, respeitando os filtros de produção aplicáveis.</p>
    </div>
    <div class="panel-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Aluno</th>
                        <th>Ciclo</th>
                        <th>Trabalho</th>
                        <th>Oficina</th>
                        <th>Produto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deliveriesInPeriod as $delivery): ?>
                        <tr>
                            <td><?= e(date('d/m/Y H:i', strtotime($delivery['delivered_at']))) ?></td>
                            <td><?= e($delivery['student_name']) ?></td>
                            <td><?= e($delivery['cycle_number']) ?></td>
                            <td><?= e($delivery['work_number']) ?></td>
                            <td><?= e($delivery['workshop_name']) ?></td>
                            <td><?= e($delivery['product_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$deliveriesInPeriod): ?>
                        <tr><td colspan="6">Nenhuma entrega realizada neste período.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Produções detalhadas</h3>
        <p>Listagem completa das produções que correspondem ao período e filtros selecionados.</p>
    </div>
    <div class="panel-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Aluno</th>
                        <th>Trabalho</th>
                        <th>Oficina</th>
                        <th>Produto</th>
                        <th>Qtd.</th>
                        <th>Entrega</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= e($row['produced_at']) ?></td>
                            <td><?= e($row['student_name'] ?? 'Registro antigo') ?></td>
                            <td><?= e($row['work_number'] ?? '—') ?></td>
                            <td><?= e($row['workshop_name']) ?></td>
                            <td><?= e($row['product_name']) ?></td>
                            <td><?= e($row['quantity']) ?></td>
                            <td><?= $row['delivered_at'] ? e(date('d/m/Y', strtotime($row['delivered_at']))) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?>
                        <tr><td colspan="7">Nenhuma produção neste filtro.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Materiais no período</h3>
        <p>Entradas e saídas consolidadas somente dentro do mesmo intervalo de datas do relatório. O saldo atual do estoque não é alterado por esta consulta.</p>
    </div>
    <div class="panel-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Movimentações</th>
                        <th>Entradas</th>
                        <th>Saídas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materialConsumption as $item): ?>
                        <tr>
                            <td><?= e($item['material_name']) ?></td>
                            <td><?= e($item['movement_count']) ?></td>
                            <td><?= e($item['total_entrada']) ?> <?= e($item['unit']) ?></td>
                            <td><?= e($item['total_saida']) ?> <?= e($item['unit']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$materialConsumption): ?>
                        <tr><td colspan="4">Nenhuma movimentação de material neste período.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Movimentações de estoque no período</h3>
        <p>Detalhamento das entradas e saídas cuja data está dentro do intervalo selecionado.</p>
    </div>
    <div class="panel-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Material</th>
                        <th>Tipo</th>
                        <th>Quantidade</th>
                        <th>Observação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stockMovements as $movement): ?>
                        <tr>
                            <td><?= e($movement['movement_date']) ?></td>
                            <td><?= e($movement['material_name']) ?></td>
                            <td><?= e($movement['movement_type'] === 'entrada' ? 'Entrada' : 'Saída') ?></td>
                            <td><?= e($movement['quantity']) ?> <?= e($movement['unit']) ?></td>
                            <td><?= e($movement['notes']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$stockMovements): ?>
                        <tr><td colspan="5">Nenhuma movimentação de estoque neste período.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<style>
@media print {
    .sidebar, .topbar, .no-print { display: none !important; }
    .app-shell { display: block !important; }
    .main-area { margin: 0 !important; }
    .panel { box-shadow: none; break-inside: avoid; }
}
</style>
