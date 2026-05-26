<?php
$workshops = $workshops ?? [];
?>

<div class="page-header">
    <div>
        <h2>Novo Produto Artesanal</h2>
        <p>Cadastre um produto que poderá ser produzido nas oficinas.</p>
    </div>

    <a href="<?= e(url('/produtos')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados do Produto</h3>
        <p>Informe nome, categoria, oficina relacionada e descrição.</p>
    </div>

    <div class="panel-body">
        <form class="form" method="POST" action="<?= e(url('/produtos')) ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Nome do produto</label>

                <input
                    class="input"
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Ex: Pote Decorado"
                    required
                >
            </div>

            <div class="form-group">
                <label for="category">Categoria</label>

                <input
                    class="input"
                    type="text"
                    id="category"
                    name="category"
                    placeholder="Ex: Decoração, Pintura, Costura"
                    required
                >
            </div>

            <div class="form-group">
                <label for="workshop_id">Oficina relacionada</label>

                <select class="select" id="workshop_id" name="workshop_id">
                    <option value="">Produto geral</option>

                    <?php foreach ($workshops as $workshop): ?>
                        <option value="<?= e($workshop['id']) ?>">
                            <?= e($workshop['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Descrição</label>

                <textarea
                    class="textarea"
                    id="description"
                    name="description"
                    placeholder="Descreva o produto artesanal"
                    required
                ></textarea>
            </div>

            <div class="form-actions">
                <a href="<?= e(url('/produtos')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Produto
                </button>
            </div>
        </form>
    </div>
</section>