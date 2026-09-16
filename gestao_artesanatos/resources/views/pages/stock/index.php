<?php
$materials = $materials ?? [];
$movements = $movements ?? $items ?? [];
?>

<div class="page-header">
    <div>
        <h2>Estoque</h2>
        <p>Controle de entradas, saídas e saldo dos materiais.</p>
    </div>

    <?php if (is_professor()): ?>
        <a href="<?= e(url('/estoque/criar')) ?>" class="btn btn-primary">
            Nova Movimentação
        </a>
    <?php endif; ?>
</div>

<div class="grid two">
    <section class="panel">
        <div class="panel-header">
            <h3>Saldo Atual</h3>
            <p>Quantidade disponível de cada material.</p>
        </div>

        <div class="panel-body">
            <?php if (!empty($materials)): ?>
                <?php foreach ($materials as $material): ?>
                    <div class="metric-line">
                        <span>
                            <strong><?= e($material['name']) ?></strong><br>
                            <small><?= e($material['category']) ?></small>
                        </span>

                        <strong>
                            <?= e($material['current_quantity']) ?>
                            <?= e($material['unit']) ?>

                            <?php if ((float) $material['current_quantity'] <= (float) $material['min_quantity']): ?>
                                <span class="badge badge-warning">Baixo</span>
                            <?php endif; ?>
                        </strong>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">
                    Nenhum material cadastrado.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h3>Histórico de Movimentações</h3>
            <p>Entradas e saídas registradas no estoque.</p>
        </div>

        <div class="panel-body">
            <?php if (!empty($movements)): ?>
                <div class="feed-list">
                    <?php foreach ($movements as $movement): ?>
                        <div class="feed-item">
                            <strong>
                                <?= e($movement['material_name']) ?>

                                <?php if ($movement['movement_type'] === 'entrada'): ?>
                                    <span class="badge badge-success">Entrada</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Saída</span>
                                <?php endif; ?>
                            </strong>

                            <span>
                                Quantidade:
                                <?= e($movement['quantity']) ?>
                                <?= e($movement['unit']) ?>
                            </span>

                            <small>
                                Data:
                                <?= e(date('d/m/Y', strtotime($movement['movement_date']))) ?>
                            </small>

                            <small>
                                Responsável:
                                <?= e($movement['user_name'] ?? 'Não informado') ?>
                            </small>

                            <?php if (!empty($movement['notes'])): ?>
                                <small>
                                    Observação:
                                    <?= e($movement['notes']) ?>
                                </small>
                            <?php endif; ?>

                            <?php if (is_professor()): ?>
                                <div class="actions" style="margin-top: 10px;">
                                    <a class="btn btn-secondary" href="<?= e(url('/estoque/' . $movement['id'] . '/editar')) ?>">Editar</a>
                                    <form
                                        method="POST"
                                        action="<?= e(url('/estoque/' . $movement['id'] . '/excluir')) ?>"
                                        data-confirm="Deseja excluir esta movimentação?"
                                    >
                                        <?= csrf_field() ?>

                                        <button type="submit" class="btn btn-danger">
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">
                    Nenhuma movimentação registrada ainda.
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>