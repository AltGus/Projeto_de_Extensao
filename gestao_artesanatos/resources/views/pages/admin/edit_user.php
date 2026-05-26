<?php
$userItem = $userItem ?? $user ?? [];
?>

<div class="page-header">
    <div>
        <h2>Editar Usuário</h2>
        <p>Atualize os dados e permissões do usuário selecionado.</p>
    </div>

    <a href="<?= e(url('/admin')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados do Usuário</h3>
        <p>Altere nome, e-mail, senha ou tipo de acesso.</p>
    </div>

    <div class="panel-body">
        <form
            class="form"
            method="POST"
            action="<?= e(url('/admin/usuarios/' . $userItem['id'] . '/atualizar')) ?>"
        >
            <?= csrf_field() ?>

            <?php require __DIR__ . '/user_fields.php'; ?>

            <div class="form-actions">
                <a href="<?= e(url('/admin')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</section>