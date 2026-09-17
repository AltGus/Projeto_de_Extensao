<div class="page-header"><div><h2>Alunos</h2><p>Crianças e participantes são registros administrativos, sem login próprio.</p></div><a class="btn btn-primary" href="<?= e(url('/alunos/criar')) ?>">Novo Aluno</a></div>
<section class="panel"><div class="panel-body">
<form method="GET" class="search-row"><input class="input" type="search" name="q" value="<?= e($search??'') ?>" placeholder="Pesquisar aluno pelo nome"><button class="btn btn-secondary">Pesquisar</button><?php if(!empty($search)): ?><a class="btn btn-secondary" href="<?= e(url('/alunos')) ?>">Limpar</a><?php endif; ?></form>
<div class="table-wrap"><table class="table"><thead><tr><th>ID</th><th>Aluno</th><th>Responsável</th><th>Trabalhos</th><th>Ações</th></tr></thead><tbody>
<?php foreach($students as $s): ?><tr><td>#<?= e($s['id']) ?></td><td><a class="text-link" href="<?= e(url('/alunos/'.$s['id'])) ?>"><strong><?= e($s['full_name']) ?></strong></a></td><td><?= e($s['guardian_name']) ?></td><td><?= e($s['total_works']) ?></td><td><div class="actions"><a class="btn btn-secondary" href="<?= e(url('/alunos/'.$s['id'])) ?>">Ver</a><a class="btn btn-secondary" href="<?= e(url('/alunos/'.$s['id'].'/editar')) ?>">Editar</a></div></td></tr><?php endforeach; ?>
<?php if(!$students): ?><tr><td colspan="5">Nenhum aluno encontrado.</td></tr><?php endif; ?>
</tbody></table></div></div></section>
