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
            action="<?= e(url('/oficinas/' . $workshop['id'] . '/atualizar')) ?>"
        >
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Nome da oficina</label>

                <input
                    class="input"
                    type="text"
                    id="name"
                    name="name"
                    value="<?= e($workshop['name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Descrição</label>

                <textarea
                    class="textarea"
                    id="description"
                    name="description"
                    required
                ><?= e($workshop['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="color">Cor da oficina</label>

                <input
                    class="input"
                    type="color"
                    id="color"
                    name="color"
                    value="<?= e($workshop['color'] ?? '#4f46e5') ?>"
                >
            </div>

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