<?php
// resources/views/pages/auth/login.php
?>
<div class="stack">
    <div class="auth-copy">
        <h2>Entrar</h2>
        <p>Acesse o ambiente de gestão artesanal da ONG.</p>
    </div>

    <form class="stack" method="post" action="<?= e(url('/login')) ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="email">E-mail</label>
            <input class="input" id="email" name="email" type="email" required placeholder="voce@exemplo.com">
        </div>

        <div class="form-group">
            <label for="password">Senha</label>
            <input class="input" id="password" name="password" type="password" required placeholder="Digite sua senha">
        </div>

        <button class="btn btn-primary btn-block" type="submit">Entrar</button>
    </form>

    <p class="hint">Ainda não possui conta? <a href="<?= e(url('/cadastro')) ?>">Cadastre-se</a>.</p>
</div>

<?php
// resources/views/pages/auth/register.php
?>
<div class="stack">
    <div class="auth-copy">
        <h2>Cadastro</h2>
        <p>Crie sua conta de participante para acessar o sistema.</p>
    </div>

    <form class="stack" method="post" action="<?= e(url('/cadastro')) ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name">Nome</label>
            <input class="input" id="name" name="name" type="text" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input class="input" id="email" name="email" type="email" required>
        </div>

        <div class="form-group">
            <label for="password">Senha</label>
            <input class="input" id="password" name="password" type="password" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmar senha</label>
            <input class="input" id="password_confirmation" name="password_confirmation" type="password" required>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Cadastrar</button>
    </form>

    <p class="hint">Já possui conta? <a href="<?= e(url('/login')) ?>">Entrar</a>.</p>
</div>

<?php
// resources/views/pages/dashboard/index.php
$rankingLabels = array_map(fn ($item) => $item['name'], $ranking);
$rankingValues = array_map(fn ($item) => (int) $item['total'], $ranking);
?>
<div class="page-header">
    <div>
        <h2>Dashboard</h2>
        <p>Visão geral das oficinas, da produção artesanal e do estoque.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="<?= e(url('/producoes/criar')) ?>">Nova produção</a>
    </div>
</div>

<div class="stats-grid">
    <?php component('atoms/stat', ['label' => 'Oficinas', 'value' => $stats['oficinas']]); ?>
    <?php component('atoms/stat', ['label' => 'Participantes', 'value' => $stats['participantes']]); ?>
    <?php component('atoms/stat', ['label' => 'Produtos', 'value' => $stats['produtos']]); ?>
    <?php component('atoms/stat', ['label' => 'Materiais', 'value' => $stats['materiais']]); ?>
    <?php component('atoms/stat', ['label' => 'Produção total', 'value' => $stats['producao_total']]); ?>
</div>

<div class="grid two-cols">
    <section class="panel-card">
        <div class="panel-card-header">
            <h3>Ranking de oficinas</h3>
            <p>Quantidade produzida por oficina.</p>
        </div>
        <div class="panel-card-body">
            <?php component('organisms/chart-bars', [
                'id' => 'workshopRanking',
                'labels' => $rankingLabels,
                'values' => $rankingValues,
            ]); ?>
        </div>
    </section>

    <section class="panel-card">
        <div class="panel-card-header">
            <h3>Produção recente</h3>
            <p>Últimos registros cadastrados.</p>
        </div>
        <div class="panel-card-body">
            <?php if ($recentProductions): ?>
                <?php foreach ($recentProductions as $row): ?>
                    <article class="feed-item">
                        <strong><?= e($row['product_name']) ?></strong>
                        <span><?= e($row['workshop_name']) ?> • <?= e((string) $row['quantity']) ?> unidade(s)</span>
                        <small><?= e(date('d/m/Y', strtotime($row['produced_at']))) ?> • <?= e($row['responsible_name']) ?></small>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhuma produção registrada ainda.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="grid two-cols">
    <section class="panel-card">
        <div class="panel-card-header">
            <h3>Resumo mensal</h3>
            <p>Produção consolidada por mês.</p>
        </div>
        <div class="panel-card-body">
            <?php if ($monthlySummary): ?>
                <?php foreach ($monthlySummary as $month): ?>
                    <div class="metric-line">
                        <span><?= e($month['month_label']) ?></span>
                        <strong><?= e((string) $month['total']) ?></strong>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Sem dados mensais ainda.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel-card">
        <div class="panel-card-header">
            <h3>Estoque</h3>
            <p>Entradas, saídas e alertas de baixo estoque.</p>
        </div>
        <div class="panel-card-body">
            <div class="metric-line"><span>Total de entradas</span><strong><?= e((string) ($stockTotals['entradas'] ?? 0)) ?></strong></div>
            <div class="metric-line"><span>Total de saídas</span><strong><?= e((string) ($stockTotals['saidas'] ?? 0)) ?></strong></div>
            <hr class="divider">
            <?php if ($lowStock): ?>
                <?php foreach ($lowStock as $material): ?>
                    <div class="metric-line">
                        <span><?= e($material['name']) ?></span>
                        <strong><?= e((string) $material['current_stock']) ?> <?= e($material['unit']) ?></strong>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum material em nível crítico.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
// resources/views/pages/workshops/fields.php
$item = $item ?? ['name' => '', 'description' => ''];
?>
<div class="form-group">
    <label for="name">Nome da oficina</label>
    <input class="input" id="name" name="name" type="text" required value="<?= e($item['name']) ?>">
</div>

<div class="form-group">
    <label for="description">Descrição</label>
    <textarea class="textarea" id="description" name="description" rows="4"><?= e($item['description']) ?></textarea>
</div>

<?php
// resources/views/pages/workshops/index.php
$rows = [];
foreach ($items as $workshop) {
    $actions = '<div class="actions-inline">'
        . '<a class="btn btn-secondary" href="' . e(url('/oficinas/' . $workshop['id'])) . '">Ver</a>';

    if (Auth::isAdmin()) {
        $actions .= '<a class="btn btn-secondary" href="' . e(url('/oficinas/' . $workshop['id'] . '/editar')) . '">Editar</a>'
            . '<form method="post" action="' . e(url('/oficinas/' . $workshop['id'] . '/excluir')) . '" onsubmit="return confirm(\'Excluir oficina?\')">'
            . csrf_field()
            . '<button class="btn btn-danger" type="submit">Excluir</button>'
            . '</form>';
    }

    $actions .= '</div>';

    $rows[] = [
        e($workshop['name']),
        e((string) $workshop['participant_count']),
        e((string) $workshop['total_production']),
        $actions,
    ];
}

template_part('crud-index', [
    'pageTitle' => 'Oficinas',
    'pageSubtitle' => 'Gerencie as salas/oficinas da ONG.',
    'createUrl' => Auth::isAdmin() ? url('/oficinas/criar') : null,
    'createLabel' => 'Nova oficina',
    'headers' => ['Nome', 'Participantes', 'Produção', 'Ações'],
    'rows' => $rows,
    'emptyTitle' => 'Nenhuma oficina cadastrada',
    'emptyText' => 'Cadastre a primeira oficina para iniciar a gestão.',
]);
?>

<?php
// resources/views/pages/workshops/create.php
ob_start();
$item = ['name' => '', 'description' => ''];
include view_path('pages/workshops/fields');
$body = ob_get_clean();

template_part('crud-form', [
    'action' => url('/oficinas'),
    'formTitle' => 'Nova oficina',
    'formSubtitle' => 'Cadastre uma nova sala/oficina para agrupar participantes e produções.',
    'body' => $body,
    'cancelUrl' => url('/oficinas'),
    'submitLabel' => 'Salvar oficina',
]);
?>

<?php
// resources/views/pages/workshops/edit.php
ob_start();
$item = $workshop;
include view_path('pages/workshops/fields');
$body = ob_get_clean();

template_part('crud-form', [
    'action' => url('/oficinas/' . $workshop['id'] . '/atualizar'),
    'formTitle' => 'Editar oficina',
    'formSubtitle' => 'Atualize as informações da oficina.',
    'body' => $body,
    'cancelUrl' => url('/oficinas'),
    'submitLabel' => 'Salvar alterações',
]);
?>

<?php
// resources/views/pages/workshops/show.php
?>
<div class="page-header">
    <div>
        <h2><?= e($workshop['name']) ?></h2>
        <p><?= e($workshop['description'] ?: 'Sem descrição cadastrada.') ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-secondary" href="<?= e(url('/oficinas')) ?>">Voltar</a>
        <?php if (Auth::isAdmin()): ?>
            <a class="btn btn-primary" href="<?= e(url('/oficinas/' . $workshop['id'] . '/editar')) ?>">Editar</a>
        <?php endif; ?>
    </div>
</div>

<div class="grid two-cols">
    <section class="panel-card">
        <div class="panel-card-header">
            <h3>Participantes</h3>
            <p>Pessoas vinculadas a esta oficina.</p>
        </div>
        <div class="panel-card-body">
            <?php if ($participants): ?>
                <?php foreach ($participants as $participant): ?>
                    <div class="metric-line actions-between">
                        <span><?= e($participant['name']) ?> <small>(<?= e($participant['email']) ?>)</small></span>
                        <?php if (Auth::isAdmin()): ?>
                            <form method="post" action="<?= e(url('/oficinas/' . $workshop['id'] . '/participantes/' . $participant['id'] . '/remover')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-danger" type="submit">Remover</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum participante vinculado.</p>
            <?php endif; ?>

            <?php if (Auth::isAdmin() && $availableParticipants): ?>
                <hr class="divider">
                <form method="post" action="<?= e(url('/oficinas/' . $workshop['id'] . '/participantes')) ?>" class="stack">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="user_id">Adicionar participante</label>
                        <select class="select" id="user_id" name="user_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($availableParticipants as $participant): ?>
                                <option value="<?= e((string) $participant['id']) ?>"><?= e($participant['name']) ?> — <?= e($participant['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit">Vincular participante</button>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel-card">
        <div class="panel-card-header">
            <h3>Histórico de produção</h3>
            <p>Últimas produções registradas para esta oficina.</p>
        </div>
        <div class="panel-card-body">
            <?php if ($productions): ?>
                <?php foreach ($productions as $production): ?>
                    <article class="feed-item">
                        <strong><?= e($production['product_name']) ?></strong>
                        <span><?= e((string) $production['quantity']) ?> unidade(s)</span>
                        <small><?= e(date('d/m/Y', strtotime($production['produced_at']))) ?> • <?= e($production['responsible_name']) ?></small>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Sem produção registrada ainda.</p>
            <?php endif; ?>
        </div>
    </section>
</div>
