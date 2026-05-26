<?php
$materials = $materials ?? $items ?? [];
?>

<div class="page-header">
    <div>
        <h2>Materiais</h2>
        <p>Controle dos materiais, ferramentas e insumos usados nas oficinas.</p>
    </div>

    <?php if (is_professor()): ?>
        <a href="<?= e(url('/materiais/criar')) ?>" class="btn btn-primary">
            Novo Material
        </a>
    <?php endif; ?>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Lista de Materiais</h3>
        <p>Materiais disponíveis no estoque da ONG.</p>
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
                                    <?= e($material['current_quantity']) ?>

                                    <?php if ((int) $material['current_quantity'] <= (int) $material['min_quantity']): ?>
                                        <span class="badge badge-warning">
                                            Baixo estoque
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= e($material['min_quantity']) ?>
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
                                                data-confirm="Deseja excluir este material?"
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
                <h3>Nenhum material cadastrado</h3>
                <p>Cadastre materiais como tintas, pincéis, tesouras, linha, lã, tecido, papel e cola.</p>
            </div>
        <?php endif; ?>
    </div>
</section>