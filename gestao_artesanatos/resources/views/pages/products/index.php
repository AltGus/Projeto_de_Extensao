<?php
$products = $products ?? $items ?? [];
?>

<div class="page-header">
    <div>
        <h2>Produtos Artesanais</h2>
        <p>Catálogo de produtos produzidos pelas oficinas da ONG.</p>
    </div>

    <?php if (is_professor()): ?>
        <a href="<?= e(url('/produtos/criar')) ?>" class="btn btn-primary">
            Novo Produto
        </a>
    <?php endif; ?>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Lista de Produtos</h3>
        <p>Produtos disponíveis para registro de produção artesanal.</p>
    </div>

    <div class="panel-body">
        <?php if (!empty($products)): ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Oficina</th>
                            <th>Descrição</th>
                            <?php if (is_professor()): ?>
                                <th>Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <a class="text-link" href="<?= e(url('/produtos/' . $product['id'])) ?>"><strong><?= e($product['name']) ?></strong></a>
                                </td>

                                <td>
                                    <span class="badge badge-primary">
                                        <?= e($product['category']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e($product['workshop_name'] ?? 'Geral') ?>
                                </td>

                                <td>
                                    <?= e($product['description']) ?>
                                </td>

                                <?php if (is_professor()): ?>
                                    <td>
                                        <div class="actions">
                                            <a
                                                href="<?= e(url('/produtos/' . $product['id'] . '/editar')) ?>"
                                                class="btn btn-secondary"
                                            >
                                                Editar
                                            </a>

                                            <form
                                                method="POST"
                                                action="<?= e(url('/produtos/' . $product['id'] . '/excluir')) ?>"
                                                data-confirm="Deseja arquivar este produto?"
                                            >
                                                <?= csrf_field() ?>

                                                <button type="submit" class="btn btn-danger">
                                                    Arquivar
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
                <h3>Nenhum produto cadastrado</h3>
                <p>Cadastre produtos como pinturas, cestos, potes decorados, bordados e peças artesanais.</p>
            </div>
        <?php endif; ?>
    </div>
</section>