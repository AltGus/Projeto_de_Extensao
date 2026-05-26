<?php
$ranking = $ranking ?? [];
$materialConsumption = $materialConsumption ?? [];
$activities = $activities ?? [];
$lowStock = $lowStock ?? [];
$stats = $stats ?? [];

$rankingLabels = [];
$rankingValues = [];

foreach ($ranking as $item) {
    $rankingLabels[] = $item['name'];
    $rankingValues[] = (int) $item['total'];
}
?>

<div class="page-header">
    <div>
        <h2>Relatórios</h2>
        <p>Resumo administrativo da produção, estoque e atividades do sistema.</p>
    </div>
</div>

<div class="grid four">
    <div class="stat-card">
        <span class="stat-label">Oficinas</span>
        <strong class="stat-value"><?= e($stats['workshops'] ?? 0) ?></strong>
    </div>

    <div class="stat-card">
        <span class="stat-label">Participantes</span>
        <strong class="stat-value"><?= e($stats['participants'] ?? 0) ?></strong>
    </div>

    <div class="stat-card">
        <span class="stat-label">Materiais</span>
        <strong class="stat-value"><?= e($stats['materials'] ?? 0) ?></strong>
    </div>

    <div class="stat-card">
        <span class="stat-label">Produção Total</span>
        <strong class="stat-value"><?= e($stats['production'] ?? 0) ?></strong>
    </div>
</div>

<div class="grid two">
    <section class="panel">
        <div class="panel-header">
            <h3>Produção por Oficina</h3>
            <p>Ranking das oficinas que mais produziram.</p>
        </div>

        <div class="panel-body">
            <canvas
                class="chart-canvas"
                width="900"
                height="320"
                data-chart="bar"
                data-labels='<?= e(json_encode($rankingLabels, JSON_UNESCAPED_UNICODE)) ?>'
                data-values='<?= e(json_encode($rankingValues, JSON_UNESCAPED_UNICODE)) ?>'
            ></canvas>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h3>Baixo Estoque</h3>
            <p>Materiais que precisam de atenção.</p>
        </div>

        <div class="panel-body">
            <?php if (!empty($lowStock)): ?>
                <?php foreach ($lowStock as $material): ?>
                    <div class="metric-line">
                        <span>
                            <?= e($material['name']) ?>
                            <span class="badge badge-warning">Baixo estoque</span>
                        </span>

                        <strong>
                            <?= e($material['current_quantity']) ?>
                            <?= e($material['unit']) ?>
                        </strong>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">
                    Nenhum material em baixo estoque.
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Consumo de Materiais</h3>
        <p>Entradas e saídas consolidadas por material.</p>
    </div>

    <div class="panel-body">
        <?php if (!empty($materialConsumption)): ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Total de Entradas</th>
                            <th>Total de Saídas</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($materialConsumption as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= e($item['material_name']) ?></strong>
                                </td>

                                <td>
                                    <span class="badge badge-success">
                                        <?= e($item['total_entrada']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge badge-danger">
                                        <?= e($item['total_saida']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty">
                Nenhum dado de consumo encontrado.
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Atividades Recentes</h3>
        <p>Últimas ações registradas no sistema.</p>
    </div>

    <div class="panel-body">
        <?php if (!empty($activities)): ?>
            <div class="feed-list">
                <?php foreach ($activities as $activity): ?>
                    <div class="feed-item">
                        <strong><?= e($activity['action']) ?></strong>

                        <span>
                            <?= e($activity['description']) ?>
                        </span>

                        <small>
                            Usuário:
                            <?= e($activity['user_name'] ?? 'Sistema') ?>
                            —
                            <?= e(date('d/m/Y H:i', strtotime($activity['created_at']))) ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty">
                Nenhuma atividade registrada.
            </div>
        <?php endif; ?>
    </div>
</section>