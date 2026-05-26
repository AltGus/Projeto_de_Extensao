<?php
$material = $material ?? [];
$workshops = $workshops ?? [];
$selectedWorkshops = $selectedWorkshops ?? [];
?>

<div class="page-header">
    <div>
        <h2>Editar Material</h2>
        <p>Atualize os dados do material usado nas oficinas.</p>
    </div>

    <a href="<?= e(url('/materiais')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados do Material</h3>
        <p>Altere o item, quantidade, categoria e oficinas de destino.</p>
    </div>

    <div class="panel-body">
        <form
            class="form"
            method="POST"
            action="<?= e(url('/materiais/' . $material['id'] . '/atualizar')) ?>"
        >
            <?= csrf_field() ?>

            <?php require __DIR__ . '/fields.php'; ?>

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