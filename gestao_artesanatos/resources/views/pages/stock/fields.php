<?php
$movement = $movement ?? [];

$materials = $materials ?? [];

$selectedMaterialId = $movement['material_id'] ?? '';
$movementType = $movement['movement_type'] ?? 'entrada';
$quantity = $movement['quantity'] ?? 1;
$movementDate = $movement['movement_date'] ?? date('Y-m-d');
$notes = $movement['notes'] ?? '';
?>

<div class="form-group">
    <label for="material_id">Material</label>

    <select class="select" id="material_id" name="material_id" required>
        <option value="">Selecione um material</option>

        <?php foreach ($materials as $material): ?>
            <option
                value="<?= e($material['id']) ?>"
                <?= (string) $selectedMaterialId === (string) $material['id'] ? 'selected' : '' ?>
            >
                <?= e($material['name']) ?>
                —
                saldo atual:
                <?= e($material['current_quantity']) ?>
                <?= e($material['unit']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if (empty($materials)): ?>
        <small style="color: var(--danger);">
            Nenhum material cadastrado. Cadastre um material antes de movimentar o estoque.
        </small>
    <?php endif; ?>
</div>

<div class="form-group">
    <label for="movement_type">Tipo de movimentação</label>

    <select class="select" id="movement_type" name="movement_type" required>
        <option value="entrada" <?= $movementType === 'entrada' ? 'selected' : '' ?>>
            Entrada
        </option>

        <option value="saida" <?= $movementType === 'saida' ? 'selected' : '' ?>>
            Saída
        </option>
    </select>
</div>

<div class="form-group">
    <label for="quantity">Quantidade</label>

    <input
        class="input"
        type="number" step="0.001"
        id="quantity"
        name="quantity"
        min="0.001"
        value="<?= e($quantity) ?>"
        required
    >
</div>

<div class="form-group">
    <label for="movement_date">Data da movimentação</label>

    <input
        class="input"
        type="date"
        id="movement_date"
        name="movement_date"
        value="<?= e($movementDate) ?>"
        required
    >
</div>

<div class="form-group">
    <label for="notes">Observações</label>

    <textarea
        class="textarea"
        id="notes"
        name="notes"
        placeholder="Ex: Compra de materiais, uso em oficina, reposição de estoque"
        required
    ><?= e($notes) ?></textarea>
</div>