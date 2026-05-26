<?php
$production = $production ?? [];

$workshops = $workshops ?? [];
$products = $products ?? [];
$users = $users ?? [];

$selectedProductId = $production['product_id'] ?? '';
$selectedWorkshopId = $production['workshop_id'] ?? '';
$selectedResponsibleId = $production['responsible_user_id'] ?? '';

$quantity = $production['quantity'] ?? 1;
$producedAt = $production['produced_at'] ?? date('Y-m-d');
$purpose = $production['purpose'] ?? '';
$description = $production['description'] ?? '';
?>

<div class="form-group">
    <label for="product_id">Produto produzido</label>

    <select class="select" id="product_id" name="product_id" required>
        <option value="">Selecione um produto</option>

        <?php foreach ($products as $product): ?>
            <option
                value="<?= e($product['id']) ?>"
                <?= (string) $selectedProductId === (string) $product['id'] ? 'selected' : '' ?>
            >
                <?= e($product['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if (empty($products)): ?>
        <small style="color: var(--danger);">
            Nenhum produto cadastrado. Cadastre um produto antes de registrar uma produção.
        </small>
    <?php endif; ?>
</div>

<div class="form-group">
    <label for="workshop_id">Oficina responsável</label>

    <select class="select" id="workshop_id" name="workshop_id" required>
        <option value="">Selecione uma oficina</option>

        <?php foreach ($workshops as $workshop): ?>
            <option
                value="<?= e($workshop['id']) ?>"
                <?= (string) $selectedWorkshopId === (string) $workshop['id'] ? 'selected' : '' ?>
            >
                <?= e($workshop['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if (empty($workshops)): ?>
        <small style="color: var(--danger);">
            Nenhuma oficina cadastrada. Cadastre uma oficina antes de registrar uma produção.
        </small>
    <?php endif; ?>
</div>

<div class="form-group">
    <label for="quantity">Quantidade produzida</label>

    <input
        class="input"
        type="number"
        id="quantity"
        name="quantity"
        min="1"
        value="<?= e($quantity) ?>"
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
        value="<?= e($producedAt) ?>"
        required
    >
</div>

<?php if (is_professor()): ?>
    <div class="form-group">
        <label for="responsible_user_id">Responsável</label>

        <select class="select" id="responsible_user_id" name="responsible_user_id">
            <option value="">Usuário logado</option>

            <?php foreach ($users as $responsibleUser): ?>
                <option
                    value="<?= e($responsibleUser['id']) ?>"
                    <?= (string) $selectedResponsibleId === (string) $responsibleUser['id'] ? 'selected' : '' ?>
                >
                    <?= e($responsibleUser['name']) ?> — <?= e($responsibleUser['role']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
<?php endif; ?>

<div class="form-group">
    <label for="purpose">Finalidade da produção</label>

    <select class="select" id="purpose" name="purpose" required>
        <option value="">Selecione uma finalidade</option>

        <option value="Venda beneficente" <?= $purpose === 'Venda beneficente' ? 'selected' : '' ?>>
            Venda beneficente
        </option>

        <option value="Exposição interna" <?= $purpose === 'Exposição interna' ? 'selected' : '' ?>>
            Exposição interna
        </option>

        <option value="Doação" <?= $purpose === 'Doação' ? 'selected' : '' ?>>
            Doação
        </option>

        <option value="Aprendizado em oficina" <?= $purpose === 'Aprendizado em oficina' ? 'selected' : '' ?>>
            Aprendizado em oficina
        </option>

        <option value="Feira da ONG" <?= $purpose === 'Feira da ONG' ? 'selected' : '' ?>>
            Feira da ONG
        </option>

        <option value="Decoração da instituição" <?= $purpose === 'Decoração da instituição' ? 'selected' : '' ?>>
            Decoração da instituição
        </option>

        <option value="Encomenda" <?= $purpose === 'Encomenda' ? 'selected' : '' ?>>
            Encomenda
        </option>

        <option value="Atividade pedagógica" <?= $purpose === 'Atividade pedagógica' ? 'selected' : '' ?>>
            Atividade pedagógica
        </option>
    </select>
</div>

<div class="form-group">
    <label for="description">Descrição</label>

    <textarea
        class="textarea"
        id="description"
        name="description"
        minlength="50"
        placeholder="Descreva a produção artesanal com pelo menos 50 caracteres"
        required
    ><?= e($description) ?></textarea>

    <small style="color: var(--muted);">
        A descrição deve conter no mínimo 50 caracteres.
    </small>
</div>