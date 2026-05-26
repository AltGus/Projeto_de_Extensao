<?php
$userItem = [];
?>

<div class="page-header">
    <div>
        <h2>Novo Usuário</h2>
        <p>Cadastre professores/orientadores ou alunos/participantes no sistema.</p>
    </div>

    <a href="<?= e(url('/admin')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados do Usuário</h3>
        <p>Defina nome, e-mail, senha e tipo de acesso.</p>
    </div>

    <div class="panel-body">
        <form class="form" method="POST" action="<?= e(url('/admin/usuarios')) ?>">
            <?= csrf_field() ?>

            <?php require __DIR__ . '/user_fields.php'; ?>

            <div class="form-actions">
                <a href="<?= e(url('/admin')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Usuário
                </button>
            </div>
        </form>
    </div>
</section>