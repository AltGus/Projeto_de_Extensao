<?php
$material = [];
$workshops = $workshops ?? [];
$selectedWorkshops = [];
?>

<div class="page-header">
    <div>
        <h2>Novo Material</h2>
        <p>Cadastre materiais usados na produção dos produtos artesanais.</p>
    </div>

    <a href="<?= e(url('/materiais')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Adicionar Material</h3>
        <p>Informe o item, quantidade e para quais oficinas ele será destinado.</p>
    </div>

    <div class="panel-body">
        <form class="form" method="POST" action="<?= e(url('/materiais')) ?>">
            <?= csrf_field() ?>

            <?php require __DIR__ . '/fields.php'; ?>

            <div class="form-actions">
                <a href="<?= e(url('/materiais')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Material
                </button>
            </div>
        </form>
    </div>
</section>