<?php
$materials = $materials ?? $items ?? [];
?>

<div class="page-header">
    <div>
        <h2>Materiais</h2>
        <p>Gerencie os materiais usados na produção dos produtos artesanais.</p>
    </div>

    <?php if (is_professor()): ?>
        <a href="<?= e(url('/materiais/criar')) ?>" class="btn btn-primary">
            Adicionar Material
        </a>
    <?php endif; ?>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Lista de Materiais</h3>
        <p>
            Visualize, adicione, edite ou remova os materiais utilizados nas oficinas.
        </p>
    </div>

    <div class="panel-body">
        <?php if (!empty($materials)): ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Categoria</th>
                            <th>Unidade</th>
                            <th>Quantidade Atual</th>
                            <th>Quantidade Mínima</th>
                            <th>Destino</th>

                            <?php if (is_professor()): ?>
                                <th>Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($materials as $material): ?>
                            <tr>
                                <td>
                                    <strong><?= e($material['name']) ?></strong>
                                </td>

                                <td>
                                    <span class="badge badge-primary">
                                        <?= e($material['category']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e($material['unit']) ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= e($material['current_quantity']) ?>
                                    </strong>

                                    <?php if ((int) $material['current_quantity'] <= (int) $material['min_quantity']): ?>
                                        <span class="badge badge-warning">
                                            Baixo estoque
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= e($material['min_quantity']) ?>
                                </td>

                                <td>
                                    <?= e($material['workshop_destinations'] ?? 'Todas as oficinas') ?>
                                </td>

                                <?php if (is_professor()): ?>
                                    <td>
                                        <div class="actions">
                                            <a
                                                href="<?= e(url('/materiais/' . $material['id'] . '/editar')) ?>"
                                                class="btn btn-secondary"
                                            >
                                                Editar
                                            </a>

                                            <form
                                                method="POST"
                                                action="<?= e(url('/materiais/' . $material['id'] . '/excluir')) ?>"
                                                data-confirm="Deseja remover este material?"
                                            >
                                                <?= csrf_field() ?>

                                                <button type="submit" class="btn btn-danger">
                                                    Remover
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
                <h3>Nenhum material cadastrado</h3>

                <p>
                    Cadastre materiais como tintas, pincéis, tesouras, linha, lã, tecido, papel e cola.
                </p>

                <?php if (is_professor()): ?>
                    <a href="<?= e(url('/materiais/criar')) ?>" class="btn btn-primary">
                        Adicionar Primeiro Material
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>