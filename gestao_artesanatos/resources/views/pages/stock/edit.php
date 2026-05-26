<?php
$movement = $movement ?? [];
$materials = $materials ?? [];
?>

<div class="page-header">
    <div>
        <h2>Editar Movimentação de Estoque</h2>
        <p>Atualize a entrada ou saída de materiais registrada no sistema.</p>
    </div>

    <a href="<?= e(url('/estoque')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados da Movimentação</h3>
        <p>Ao salvar, o saldo do estoque será recalculado automaticamente.</p>
    </div>

    <div class="panel-body">
        <form
            class="form"
            method="POST"
            action="<?= e(url('/estoque/' . $movement['id'] . '/atualizar')) ?>"
        >
            <?= csrf_field() ?>

            <?php require __DIR__ . '/fields.php'; ?>

            <div class="form-actions">
                <a href="<?= e(url('/estoque')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</section>