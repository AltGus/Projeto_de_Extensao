<div class="page-header">
    <div>
        <h2>Novo Material</h2>
        <p>Cadastre um novo material, ferramenta ou insumo.</p>
    </div>

    <a href="<?= e(url('/materiais')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados do Material</h3>
        <p>Informe categoria, unidade e quantidades de controle.</p>
    </div>

    <div class="panel-body">
        <form class="form" method="POST" action="<?= e(url('/materiais')) ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Nome do material</label>

                <input
                    class="input"
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Ex: Tinta acrílica"
                    required
                >
            </div>

            <div class="form-group">
                <label for="category">Categoria</label>

                <select class="select" id="category" name="category" required>
                    <option value="">Selecione</option>
                    <option value="Tintas">Tintas</option>
                    <option value="Ferramentas">Ferramentas</option>
                    <option value="Materiais">Materiais</option>
                </select>
            </div>

            <div class="form-group">
                <label for="unit">Unidade</label>

                <input
                    class="input"
                    type="text"
                    id="unit"
                    name="unit"
                    placeholder="Ex: un, m, pacote, rolo"
                    value="un"
                    required
                >
            </div>

            <div class="form-group">
                <label for="current_quantity">Quantidade atual</label>

                <input
                    class="input"
                    type="number"
                    id="current_quantity"
                    name="current_quantity"
                    min="0"
                    value="0"
                    required
                >
            </div>

            <div class="form-group">
                <label for="min_quantity">Quantidade mínima</label>

                <input
                    class="input"
                    type="number"
                    id="min_quantity"
                    name="min_quantity"
                    min="0"
                    value="0"
                    required
                >
            </div>

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