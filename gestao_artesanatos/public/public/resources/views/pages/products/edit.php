<?php
$product = $product ?? [];
$workshops = $workshops ?? [];
?>

<div class="page-header">
    <div>
        <h2>Editar Produto</h2>
        <p>Atualize os dados do produto artesanal.</p>
    </div>

    <a href="<?= e(url('/produtos')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados do Produto</h3>
        <p>Altere nome, categoria, oficina relacionada e descrição.</p>
    </div>

    <div class="panel-body">
        <form
            class="form"
            method="POST"
            action="<?= e(url('/produtos/' . $product['id'] . '/atualizar')) ?>"
        >
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Nome do produto</label>

                <input
                    class="input"
                    type="text"
                    id="name"
                    name="name"
                    value="<?= e($product['name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="category">Categoria</label>

                <input
                    class="input"
                    type="text"
                    id="category"
                    name="category"
                    value="<?= e($product['category'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="workshop_id">Oficina relacionada</label>

                <select class="select" id="workshop_id" name="workshop_id">
                    <option value="">Produto geral</option>

                    <?php foreach ($workshops as $workshop): ?>
                        <option
                            value="<?= e($workshop['id']) ?>"
                            <?= (string) ($product['workshop_id'] ?? '') === (string) $workshop['id'] ? 'selected' : '' ?>
                        >
                            <?= e($workshop['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Descrição</label>

                <textarea
                    class="textarea"
                    id="description"
                    name="description"
                    required
                ><?= e($product['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="<?= e(url('/produtos')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</section>