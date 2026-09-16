<?php
// resources/views/pages/products/fields.php
$item = $item ?? ['name' => '', 'category' => '', 'description' => ''];
?>
<div class="form-group">
    <label for="name">Nome do produto</label>
    <input class="input" id="name" name="name" type="text" required value="<?= e($item['name']) ?>">
</div>

<div class="form-group">
    <label for="category">Categoria</label>
    <input class="input" id="category" name="category" type="text" required value="<?= e($item['category']) ?>">
</div>

<div class="form-group">
    <label for="description">Descrição</label>
    <textarea class="textarea" id="description" name="description" rows="4"><?= e($item['description']) ?></textarea>
</div>

<?php
// resources/views/pages/products/index.php
$rows = [];
foreach ($items as $item) {
    $actions = Auth::isAdmin()
        ? '<div class="actions-inline">'
            . '<a class="btn btn-secondary" href="' . e(url('/produtos/' . $item['id'] . '/editar')) . '">Editar</a>'
            . '<form method="post" action="' . e(url('/produtos/' . $item['id'] . '/excluir')) . '" onsubmit="return confirm(\'Arquivar produto?\')">'
            . csrf_field()
            . '<button class="btn btn-danger" type="submit">Arquivar</button>'
            . '</form>'
        . '</div>'
        : '—';

    $rows[] = [
        e($item['name']),
        e($item['category']),
        e($item['description']),
        $actions,
    ];
}

template_part('crud-index', [
    'pageTitle' => 'Produtos artesanais',
    'pageSubtitle' => 'Catálogo usado nos registros de produção.',
    'createUrl' => Auth::isAdmin() ? url('/produtos/criar') : null,
    'createLabel' => 'Novo produto',
    'headers' => ['Nome', 'Categoria', 'Descrição', 'Ações'],
    'rows' => $rows,
    'emptyTitle' => 'Nenhum produto cadastrado',
    'emptyText' => 'Cadastre produtos como pinturas, cestos e potes decorados.',
]);
?>

<?php
// resources/views/pages/products/create.php
ob_start();
$item = ['name' => '', 'category' => '', 'description' => ''];
include view_path('pages/products/fields');
$body = ob_get_clean();

template_part('crud-form', [
    'action' => url('/produtos'),
    'formTitle' => 'Novo produto artesanal',
    'formSubtitle' => 'Cadastre itens que poderão ser vinculados às produções.',
    'body' => $body,
    'cancelUrl' => url('/produtos'),
    'submitLabel' => 'Salvar produto',
]);
?>

<?php
// resources/views/pages/products/edit.php
ob_start();
$item = $product;
include view_path('pages/products/fields');
$body = ob_get_clean();

template_part('crud-form', [
    'action' => url('/produtos/' . $product['id'] . '/atualizar'),
    'formTitle' => 'Editar produto',
    'formSubtitle' => 'Atualize as informações do catálogo artesanal.',
    'body' => $body,
    'cancelUrl' => url('/produtos'),
    'submitLabel' => 'Salvar alterações',
]);
?>

<?php
// resources/views/pages/materials/fields.php
$item = $item ?? ['name' => '', 'category' => 'materiais', 'unit' => 'un', 'current_stock' => 0, 'min_stock' => 0, 'description' => ''];
?>
<div class="form-group">
    <label for="name">Nome do material</label>
    <input class="input" id="name" name="name" type="text" required value="<?= e($item['name']) ?>">
</div>

<div class="form-group">
    <label for="category">Categoria</label>
    <select class="select" id="category" name="category" required>
        <?php foreach (['tintas' => 'Tintas', 'ferramentas' => 'Ferramentas', 'materiais' => 'Materiais'] as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= $item['category'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="grid two-cols">
    <div class="form-group">
        <label for="unit">Unidade</label>
        <input class="input" id="unit" name="unit" type="text" required value="<?= e($item['unit']) ?>">
    </div>

    <div class="form-group">
        <label for="min_stock">Estoque mínimo</label>
        <input class="input" id="min_stock" name="min_stock" type="number" min="0" value="<?= e((string) $item['min_stock']) ?>">
    </div>
</div>

<div class="grid two-cols">
    <div class="form-group">
        <label for="current_stock">Estoque atual</label>
        <input class="input" id="current_stock" name="current_stock" type="number" min="0" value="<?= e((string) $item['current_stock']) ?>">
    </div>

    <div class="form-group">
        <label for="description">Descrição</label>
        <textarea class="textarea" id="description" name="description" rows="3"><?= e($item['description']) ?></textarea>
    </div>
</div>

<?php
// resources/views/pages/materials/index.php
$rows = [];
foreach ($items as $item) {
    $stockHtml = e((string) $item['current_stock']) . ' ' . e($item['unit']);
    if ((int) $item['current_stock'] <= (int) $item['min_stock']) {
        $stockHtml .= ' <span class="badge badge-warning">Baixo estoque</span>';
    }

    $actions = Auth::isAdmin()
        ? '<div class="actions-inline">'
            . '<a class="btn btn-secondary" href="' . e(url('/materiais/' . $item['id'] . '/editar')) . '">Editar</a>'
            . '<form method="post" action="' . e(url('/materiais/' . $item['id'] . '/excluir')) . '" onsubmit="return confirm(\'Arquivar material?\')">'
            . csrf_field()
            . '<button class="btn btn-danger" type="submit">Arquivar</button>'
            . '</form>'
        . '</div>'
        : '—';

    $rows[] = [
        e($item['name']),
        e(ucfirst($item['category'])),
        e($item['unit']),
        $stockHtml,
        e((string) $item['min_stock']),
        $actions,
    ];
}

template_part('crud-index', [
    'pageTitle' => 'Materiais',
    'pageSubtitle' => 'Cadastro de materiais e insumos utilizados nas oficinas.',
    'createUrl' => Auth::isAdmin() ? url('/materiais/criar') : null,
    'createLabel' => 'Novo material',
    'headers' => ['Nome', 'Categoria', 'Unid.', 'Estoque atual', 'Mínimo', 'Ações'],
    'rows' => $rows,
    'emptyTitle' => 'Nenhum material cadastrado',
    'emptyText' => 'Cadastre materiais como tintas, pincéis, linha, lã e cola.',
]);
?>

<?php
// resources/views/pages/materials/create.php
ob_start();
$item = ['name' => '', 'category' => 'materiais', 'unit' => 'un', 'current_stock' => 0, 'min_stock' => 0, 'description' => ''];
include view_path('pages/materials/fields');
$body = ob_get_clean();

template_part('crud-form', [
    'action' => url('/materiais'),
    'formTitle' => 'Novo material',
    'formSubtitle' => 'Cadastre um novo insumo ou ferramenta.',
    'body' => $body,
    'cancelUrl' => url('/materiais'),
    'submitLabel' => 'Salvar material',
]);
?>

<?php
// resources/views/pages/materials/edit.php
ob_start();
$item = $material;
include view_path('pages/materials/fields');
$body = ob_get_clean();

template_part('crud-form', [
    'action' => url('/materiais/' . $material['id'] . '/atualizar'),
    'formTitle' => 'Editar material',
    'formSubtitle' => 'Atualize as informações do material.',
    'body' => $body,
    'cancelUrl' => url('/materiais'),
    'submitLabel' => 'Salvar alterações',
]);
?>
