<?php
$material = $material ?? [];
$workshops = $workshops ?? [];
$selectedWorkshops = $selectedWorkshops ?? [];

$name = $material['name'] ?? '';
$category = $material['category'] ?? '';
$unit = $material['unit'] ?? 'un';
$currentQuantity = $material['current_quantity'] ?? 0;
$minQuantity = $material['min_quantity'] ?? 0;
$appliesToAll = (int) ($material['applies_to_all'] ?? 1);
?>

<div class="form-group">
    <label for="name">Nome do material</label>

    <input
        class="input"
        type="text"
        id="name"
        name="name"
        value="<?= e($name) ?>"
        placeholder="Ex: Tinta acrílica, cola, tecido, linha, lã, pincel"
        required
    >

    <small style="color: var(--muted);">
        Escreva o nome do item/material que será usado na produção dos produtos.
    </small>
</div>

<div class="form-group">
    <label for="category">Categoria</label>

    <select class="select" id="category" name="category" required>
        <option value="">Selecione uma categoria</option>

        <option value="Tintas" <?= $category === 'Tintas' ? 'selected' : '' ?>>
            Tintas
        </option>

        <option value="Ferramentas" <?= $category === 'Ferramentas' ? 'selected' : '' ?>>
            Ferramentas
        </option>

        <option value="Materiais" <?= $category === 'Materiais' ? 'selected' : '' ?>>
            Materiais
        </option>

        <option value="Tecidos e Linhas" <?= $category === 'Tecidos e Linhas' ? 'selected' : '' ?>>
            Tecidos e Linhas
        </option>

        <option value="Embalagens" <?= $category === 'Embalagens' ? 'selected' : '' ?>>
            Embalagens
        </option>

        <option value="Outros" <?= $category === 'Outros' ? 'selected' : '' ?>>
            Outros
        </option>
    </select>
</div>

<div class="form-group">
    <label for="unit">Unidade de medida</label>

    <select class="select" id="unit" name="unit" required>
        <option value="un" <?= $unit === 'un' ? 'selected' : '' ?>>Unidade (un)</option>
        <option value="m" <?= $unit === 'm' ? 'selected' : '' ?>>Metro (m)</option>
        <option value="kg" <?= $unit === 'kg' ? 'selected' : '' ?>>Quilograma (kg)</option>
        <option value="g" <?= $unit === 'g' ? 'selected' : '' ?>>Grama (g)</option>
        <option value="l" <?= $unit === 'l' ? 'selected' : '' ?>>Litro (l)</option>
        <option value="ml" <?= $unit === 'ml' ? 'selected' : '' ?>>Mililitro (ml)</option>
        <option value="pacote" <?= $unit === 'pacote' ? 'selected' : '' ?>>Pacote</option>
        <option value="rolo" <?= $unit === 'rolo' ? 'selected' : '' ?>>Rolo</option>
        <option value="caixa" <?= $unit === 'caixa' ? 'selected' : '' ?>>Caixa</option>
    </select>
</div>

<div class="form-group">
    <label for="current_quantity">Quantidade atual</label>

    <input
        class="input"
        type="number"
        id="current_quantity"
        name="current_quantity"
        min="0"
        value="<?= e($currentQuantity) ?>"
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
        value="<?= e($minQuantity) ?>"
        required
    >

    <small style="color: var(--muted);">
        Quando a quantidade atual ficar igual ou abaixo deste valor, o sistema exibirá alerta de baixo estoque.
    </small>
</div>

<div class="form-group">
    <label>Destino do material</label>

    <label style="display: flex; gap: 10px; align-items: center; font-weight: normal;">
        <input
            type="checkbox"
            name="applies_to_all"
            value="1"
            <?= $appliesToAll === 1 ? 'checked' : '' ?>
        >

        Usar este material em todas as oficinas
    </label>

    <small style="color: var(--muted);">
        Marque esta opção se o material puder ser usado em qualquer oficina.
    </small>
</div>

<div class="form-group">
    <label>Ou selecione oficinas específicas</label>

    <div style="
        display: grid;
        gap: 10px;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 14px;
        background: #fff;
    ">
        <?php if (!empty($workshops)): ?>
            <?php foreach ($workshops as $workshop): ?>
                <label style="display: flex; gap: 10px; align-items: center; font-weight: normal;">
                    <input
                        type="checkbox"
                        name="workshop_ids[]"
                        value="<?= e($workshop['id']) ?>"
                        <?= in_array((int) $workshop['id'], array_map('intval', $selectedWorkshops), true) ? 'checked' : '' ?>
                    >

                    <?= e($workshop['name']) ?>
                </label>
            <?php endforeach; ?>
        <?php else: ?>
            <small style="color: var(--danger);">
                Nenhuma oficina cadastrada ainda.
            </small>
        <?php endif; ?>
    </div>

    <small style="color: var(--muted);">
        Caso o material não seja para todas as oficinas, selecione uma ou mais oficinas específicas.
    </small>
</div>