<?php
$workshop = [];
?>

<div class="page-header">
    <div>
        <h2>Nova Oficina</h2>
        <p>Cadastre uma nova oficina para organizar alunos e produções.</p>
    </div>

    <a href="<?= e(url('/oficinas')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados da Oficina</h3>
        <p>Preencha as informações principais da oficina artesanal.</p>
    </div>

    <div class="panel-body">
        <form class="form" method="POST" enctype="multipart/form-data" action="<?= e(url('/oficinas')) ?>">
            <?= csrf_field() ?>

            <?php require __DIR__ . '/fields.php'; ?>

            <div class="form-actions">
                <a href="<?= e(url('/oficinas')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Oficina
                </button>
            </div>
        </form>
    </div>
</section>