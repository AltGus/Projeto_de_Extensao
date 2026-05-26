<?php
$productions = $productions ?? $items ?? [];
?>

<div class="page-header">
    <div>
        <h2>Produções</h2>
        <p>Registro detalhado dos produtos produzidos nas oficinas.</p>
    </div>

    <a href="<?= e(url('/producoes/criar')) ?>" class="btn btn-primary">
        Nova Produção
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Histórico de Produções</h3>
        <p>Acompanhe produto, oficina, quantidade, data, responsável e finalidade.</p>
    </div>

    <div class="panel-body">
        <?php if (!empty($productions)): ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Oficina</th>
                            <th>Quantidade</th>
                            <th>Data</th>
                            <th>Responsável</th>
                            <th>Finalidade</th>

                            <?php if (is_professor()): ?>
                                <th>Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($productions as $production): ?>
                            <tr>
                                <td>
                                    <strong><?= e($production['product_name']) ?></strong>
                                </td>

                                <td>
                                    <?= e($production['workshop_name']) ?>
                                </td>

                                <td>
                                    <span class="badge badge-success">
                                        <?= e($production['quantity']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e(date('d/m/Y', strtotime($production['produced_at']))) ?>
                                </td>

                                <td>
                                    <?= e($production['responsible_name'] ?? 'Não informado') ?>
                                </td>

                                <td>
                                    <?= e($production['purpose']) ?>
                                </td>

                                <?php if (is_professor()): ?>
                                    <td>
                                        <div class="actions">
                                            <a
                                                href="<?= e(url('/producoes/' . $production['id'] . '/editar')) ?>"
                                                class="btn btn-secondary"
                                            >
                                                Editar
                                            </a>

                                            <form
                                                method="POST"
                                                action="<?= e(url('/producoes/' . $production['id'] . '/excluir')) ?>"
                                                data-confirm="Deseja excluir esta produção?"
                                            >
                                                <?= csrf_field() ?>

                                                <button type="submit" class="btn btn-danger">
                                                    Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty">
                <h3>Nenhuma produção registrada</h3>
                <p>Registre produtos como pinturas, cestos, potes decorados, bordados e peças artesanais.</p>
            </div>
        <?php endif; ?>
    </div>
</section>