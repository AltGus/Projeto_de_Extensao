<?php
$materials = $materials ?? [];
$movement = [];
?>

<div class="page-header">
    <div>
        <h2>Nova Movimentação de Estoque</h2>
        <p>Registre entrada ou saída de materiais utilizados nas oficinas.</p>
    </div>

    <a href="<?= e(url('/estoque')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados da Movimentação</h3>
        <p>O saldo do material será atualizado automaticamente.</p>
    </div>

    <div class="panel-body">
        <form class="form" method="POST" action="<?= e(url('/estoque')) ?>">
            <?= csrf_field() ?>

            <?php require __DIR__ . '/fields.php'; ?>

            <div class="form-actions">
                <a href="<?= e(url('/estoque')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Movimentação
                </button>
            </div>
        </form>
    </div>
</section>