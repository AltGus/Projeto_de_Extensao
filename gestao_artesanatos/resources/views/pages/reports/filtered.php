<h2>Produções por período</h2>
<form method="GET" class="panel form no-print">
    <label>De <input type="date" name="start" value="<?= e($_GET['start'] ?? '') ?>"></label>
    <label>Até <input type="date" name="end" value="<?= e($_GET['end'] ?? '') ?>"></label>
    <?php foreach (['workshop_id'=>['Oficina',$workshops], 'product_id'=>['Produto',$products], 'responsible_user_id'=>['Responsável',$users]] as $key=>[$label,$options]): ?>
    <label><?= e($label) ?><select name="<?= e($key) ?>"><option value="">Todos</option>
    <?php foreach ($options as $option): ?><option value="<?= e($option['id']) ?>" <?= (string)($_GET[$key] ?? '') === (string)$option['id'] ? 'selected' : '' ?>><?= e($option['name']) ?></option><?php endforeach; ?>
    </select></label>
    <?php endforeach; ?>
    <button class="btn btn-primary">Filtrar</button>
    <button class="btn btn-secondary" name="export" value="csv">Exportar CSV (Excel)</button>
    <button class="btn btn-secondary" type="button" onclick="window.print()">Imprimir / salvar PDF</button>
</form>
<p>Período: <?= e($_GET['start'] ?? '') ?: 'início' ?> a <?= e($_GET['end'] ?? '') ?: 'hoje' ?> · <?= count($rows) ?> registros · <?= e(array_sum(array_column($rows,'quantity'))) ?> unidades</p>
<section class="panel"><div class="panel-body"><table class="table"><thead><tr><th>Data</th><th>Oficina</th><th>Produto</th><th>Responsável</th><th>Quantidade</th><th>Finalidade</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr>
<?php foreach (['produced_at','workshop_name','product_name','responsible_name','quantity','purpose'] as $key): ?><td><?= e($row[$key]) ?></td><?php endforeach; ?>
</tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="6">Nenhuma produção neste filtro.</td></tr><?php endif; ?>
</tbody></table></div></section>
<h3>Movimentações de materiais — todo o histórico</h3>
<p>O filtro acima aplica-se às produções. Materiais não possuem vínculo com produto ou responsável pela produção.</p>
<table class="table"><thead><tr><th>Material</th><th>Entradas</th><th>Saídas</th></tr></thead><tbody>
<?php foreach ($materialConsumption as $row): ?><tr><td><?= e($row['material_name']) ?></td><td><?= e($row['total_entrada']) ?></td><td><?= e($row['total_saida']) ?></td></tr><?php endforeach; ?>
</tbody></table>
<style>@media print { .sidebar,.topbar,.no-print {display:none!important} .app-shell {display:block!important} .main-area {margin:0!important} .panel {box-shadow:none;border:0} table {width:100%;border-collapse:collapse} td,th {border:1px solid #ccc;padding:5px} }</style>
