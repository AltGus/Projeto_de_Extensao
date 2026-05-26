<?php
$rankingLabels = [];
$rankingValues = [];

foreach ($ranking as $item) {
    $rankingLabels[] = $item['name'];
    $rankingValues[] = (int) $item['total'];
}
?>

<div class="page-header">
    <div>
        <h2>Dashboard</h2>
        <p>Visão geral das oficinas, participantes, produção artesanal e estoque.</p>
    </div>

    <div class="actions">
        <a href="<?= e(url('/producoes/criar')) ?>" class="btn btn-primary">
            Nova Produção
        </a>

        <?php if (is_professor()): ?>
            <a href="<?= e(url('/oficinas/criar')) ?>" class="btn btn-secondary">
                Nova Oficina
            </a>
        <?php endif; ?>
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
        <span class="stat-label">Produtos</span>
        <strong class="stat-value"><?= e($stats['products'] ?? 0) ?></strong>
    </div>

    <div class="stat-card">
        <span class="stat-label">Produção Total</span>
        <strong class="stat-value"><?= e($stats['production'] ?? 0) ?></strong>
    </div>
</div>

<div class="grid two">
    <section class="panel">
        <div class="panel-header">
            <h3>Ranking das Oficinas</h3>
            <p>Oficinas com maior quantidade produzida.</p>
        </div>

        <div class="panel-body">
            <div class="chart-box">
                <canvas
                    class="chart-canvas"
                    width="900"
                    height="320"
                    data-chart="bar"
                    data-labels='<?= e(json_encode($rankingLabels, JSON_UNESCAPED_UNICODE)) ?>'
                    data-values='<?= e(json_encode($rankingValues, JSON_UNESCAPED_UNICODE)) ?>'
                ></canvas>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h3>Produção Recente</h3>
            <p>Últimos produtos registrados no sistema.</p>
        </div>

        <div class="panel-body">
            <?php if (!empty($recentProductions)): ?>
                <div class="feed-list">
                    <?php foreach ($recentProductions as $production): ?>
                        <div class="feed-item">
                            <strong><?= e($production['product_name']) ?></strong>

                            <span>
                                Oficina:
                                <?= e($production['workshop_name']) ?>
                            </span>

                            <span>
                                Quantidade:
                                <?= e($production['quantity']) ?>
                            </span>

                            <small>
                                Data:
                                <?= e(date('d/m/Y', strtotime($production['produced_at']))) ?>
                                —
                                Responsável:
                                <?= e($production['responsible_name'] ?? 'Não informado') ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">
                    Nenhuma produção registrada ainda.
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="grid two">
    <section class="panel">
        <div class="panel-header">
            <h3>Resumo das Oficinas</h3>
            <p>Comparativo geral de produção entre oficinas.</p>
        </div>

        <div class="panel-body">
            <?php if (!empty($ranking)): ?>
                <?php foreach ($ranking as $index => $item): ?>
                    <div class="metric-line">
                        <span>
                            <?php if ($index === 0): ?>
                                🥇
                            <?php elseif ($index === 1): ?>
                                🥈
                            <?php elseif ($index === 2): ?>
                                🥉
                            <?php else: ?>
                                <?= e($index + 1) ?>º
                            <?php endif; ?>

                            <?= e($item['name']) ?>
                        </span>

                        <strong>
                            <?= e($item['total']) ?> produzido(s)
                        </strong>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">
                    Nenhuma oficina cadastrada.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h3>Alertas de Estoque</h3>
            <p>Materiais com quantidade igual ou abaixo do mínimo.</p>
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
