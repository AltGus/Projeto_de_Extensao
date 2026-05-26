<?php
$material = $material ?? [];
?>

<div class="page-header">
    <div>
        <h2>Editar Material</h2>
        <p>Atualize os dados do material cadastrado.</p>
    </div>

    <a href="<?= e(url('/materiais')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados do Material</h3>
        <p>Altere categoria, unidade e quantidades de controle.</p>
    </div>

    <div class="panel-body">
        <form
            class="form"
            method="POST"
            action="<?= e(url('/materiais/' . $material['id'] . '/atualizar')) ?>"
        >
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Nome do material</label>

                <input
                    class="input"
                    type="text"
                    id="name"
                    name="name"
                    value="<?= e($material['name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="category">Categoria</label>

                <select class="select" id="category" name="category" required>
                    <option value="">Selecione</option>

                    <option value="Tintas" <?= ($material['category'] ?? '') === 'Tintas' ? 'selected' : '' ?>>
                        Tintas
                    </option>

                    <option value="Ferramentas" <?= ($material['category'] ?? '') === 'Ferramentas' ? 'selected' : '' ?>>
                        Ferramentas
                    </option>

                    <option value="Materiais" <?= ($material['category'] ?? '') === 'Materiais' ? 'selected' : '' ?>>
                        Materiais
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="unit">Unidade</label>

                <input
                    class="input"
                    type="text"
                    id="unit"
                    name="unit"
                    value="<?= e($material['unit'] ?? 'un') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="current_quantity">Quantidade atual</label>

                <input
                    class="input"
                    type="number"
                    id="current_quantity"
                    name="current_quantity"
                    min="0"
                    value="<?= e($material['current_quantity'] ?? 0) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="min_quantity">Quantidade mínima</label>

                <input
                    class="input"
                    type="number"
                    id="min_quantity"
                    name="min_quantity"
                    min="0"
                    value="<?= e($material['min_quantity'] ?? 0) ?>"
                    required
                >
            </div>

            <div class="form-actions">
                <a href="<?= e(url('/materiais')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</section>