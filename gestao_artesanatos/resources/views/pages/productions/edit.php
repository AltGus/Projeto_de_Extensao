<?php
$production = $production ?? [];
$workshops = $workshops ?? [];
$products = $products ?? [];
$users = $users ?? [];
?>

<div class="page-header">
    <div>
        <h2>Editar Produção</h2>
        <p>Atualize as informações da produção artesanal registrada.</p>
    </div>

    <a href="<?= e(url('/producoes')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados da Produção</h3>
        <p>Altere produto, oficina, quantidade, data, responsável e finalidade.</p>
    </div>

    <div class="panel-body">
        <form
            class="form"
            method="POST"
            action="<?= e(url('/producoes/' . $production['id'] . '/atualizar')) ?>"
        >
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="product_id">Produto produzido</label>

                <select class="select" id="product_id" name="product_id" required>
                    <option value="">Selecione um produto</option>

                    <?php foreach ($products as $product): ?>
                        <option
                            value="<?= e($product['id']) ?>"
                            <?= (string) ($production['product_id'] ?? '') === (string) $product['id'] ? 'selected' : '' ?>
                        >
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
                        <option
                            value="<?= e($workshop['id']) ?>"
                            <?= (string) ($production['workshop_id'] ?? '') === (string) $workshop['id'] ? 'selected' : '' ?>
                        >
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
                    value="<?= e($production['quantity'] ?? 1) ?>"
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
                    value="<?= e($production['produced_at'] ?? date('Y-m-d')) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="responsible_user_id">Responsável</label>

                <select class="select" id="responsible_user_id" name="responsible_user_id">
                    <option value="">Usuário logado</option>

                    <?php foreach ($users as $user): ?>
                        <option
                            value="<?= e($user['id']) ?>"
                            <?= (string) ($production['responsible_user_id'] ?? '') === (string) $user['id'] ? 'selected' : '' ?>
                        >
                            <?= e($user['name']) ?> — <?= e($user['role']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="purpose">Finalidade</label>

                <input
                    class="input"
                    type="text"
                    id="purpose"
                    name="purpose"
                    value="<?= e($production['purpose'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Descrição</label>

                <textarea
                    class="textarea"
                    id="description"
                    name="description"
                    required
                ><?= e($production['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="<?= e(url('/producoes')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</section>