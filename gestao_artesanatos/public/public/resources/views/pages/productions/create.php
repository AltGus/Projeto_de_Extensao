<?php
$workshops = $workshops ?? [];
$products = $products ?? [];
$users = $users ?? [];
?>

<div class="page-header">
    <div>
        <h2>Nova Produção</h2>
        <p>Registre o produto produzido, quantidade, oficina responsável, data e finalidade.</p>
    </div>

    <a href="<?= e(url('/producoes')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Formulário de Produção</h3>
        <p>As informações serão usadas no dashboard, no ranking das oficinas e nos relatórios.</p>
    </div>

    <div class="panel-body">
        <form class="form" method="POST" action="<?= e(url('/producoes')) ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="product_id">Produto produzido</label>

                <select class="select" id="product_id" name="product_id" required>
                    <option value="">Selecione um produto</option>

                    <?php foreach ($products as $product): ?>
                        <option value="<?= e($product['id']) ?>">
                            <?= e($product['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="workshop_id">Oficina responsável</label>

                <select class="select" id="workshop_id" name="workshop_id" required>
                    <option value="">Selecione uma oficina</option>

                    <?php foreach ($workshops as $workshop): ?>
                        <option value="<?= e($workshop['id']) ?>">
                            <?= e($workshop['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="quantity">Quantidade produzida</label>

                <input
                    class="input"
                    type="number"
                    id="quantity"
                    name="quantity"
                    min="1"
                    value="1"
                    required
                >
            </div>

            <div class="form-group">
                <label for="produced_at">Data da produção</label>

                <input
                    class="input"
                    type="date"
                    id="produced_at"
                    name="produced_at"
                    value="<?= e(date('Y-m-d')) ?>"
                    required
                >
            </div>

            <?php if (is_professor()): ?>
                <div class="form-group">
                    <label for="responsible_user_id">Responsável</label>

                    <select class="select" id="responsible_user_id" name="responsible_user_id">
                        <option value="">Usuário logado</option>

                        <?php foreach ($users as $user): ?>
                            <option value="<?= e($user['id']) ?>">
                                <?= e($user['name']) ?> — <?= e($user['role']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="purpose">Finalidade</label>

                <input
                    class="input"
                    type="text"
                    id="purpose"
                    name="purpose"
                    placeholder="Ex: Venda beneficente, exposição, aprendizado ou doação"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Descrição</label>

                <textarea
                    class="textarea"
                    id="description"
                    name="description"
                    placeholder="Descreva detalhes da produção"
                    required
                ></textarea>
            </div>

            <div class="form-actions">
                <a href="<?= e(url('/producoes')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Produção
                </button>
            </div>
        </form>
    </div>
</section>