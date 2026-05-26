<?php
$workshops = $workshops ?? [];
$products = $products ?? [];
$users = $users ?? [];
$production = [];
?>

<div class="page-header">
    <div>
        <h2>Nova Produção</h2>
        <p>Registre o produto produzido, quantidade, oficina responsável, data e finalidade.</p>
    </div>

    <a href="<?= e(url('/producoes')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Formulário de Produção</h3>
        <p>As informações serão usadas no dashboard, no ranking das oficinas e nos relatórios.</p>
    </div>

    <div class="panel-body">
        <form class="form" method="POST" action="<?= e(url('/producoes')) ?>">
            <?= csrf_field() ?>

            <?php require __DIR__ . '/fields.php'; ?>

            <div class="form-actions">
                <a href="<?= e(url('/producoes')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Produção
                </button>
            </div>
        </form>
    </div>
</section>