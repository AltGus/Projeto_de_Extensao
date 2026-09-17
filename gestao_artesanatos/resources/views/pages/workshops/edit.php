<?php
$workshop = $workshop ?? [];
?>

<div class="page-header">
    <div>
        <h2>Editar Oficina</h2>
        <p>Atualize os dados da oficina selecionada.</p>
    </div>

    <a href="<?= e(url('/oficinas')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados da Oficina</h3>
        <p>Altere o nome, descrição e cor de identificação da oficina.</p>
    </div>

    <div class="panel-body">
        <form
            class="form"
            method="POST"
            enctype="multipart/form-data"
            action="<?= e(url('/oficinas/' . $workshop['id'] . '/atualizar')) ?>"
        >
            <?= csrf_field() ?>

            <?php require __DIR__ . '/fields.php'; ?>

            <div class="form-actions">
                <a href="<?= e(url('/oficinas')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</section>