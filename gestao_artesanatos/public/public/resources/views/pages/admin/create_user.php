<div class="page-header">
    <div>
        <h2>Novo Usuário</h2>
        <p>Cadastre professores/orientadores ou alunos/participantes.</p>
    </div>

    <a href="<?= e(url('/admin')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados do Usuário</h3>
        <p>Defina nome, e-mail, senha e tipo de acesso.</p>
    </div>

    <div class="panel-body">
        <form class="form" method="POST" action="<?= e(url('/admin/usuarios')) ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Nome completo</label>

                <input
                    class="input"
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Digite o nome do usuário"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>

                <input
                    class="input"
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Digite o e-mail"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Senha</label>

                <input
                    class="input"
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Digite uma senha"
                    required
                >
            </div>

            <div class="form-group">
                <label for="role">Tipo de usuário</label>

                <select class="select" id="role" name="role" required>
                    <option value="">Selecione</option>
                    <option value="professor">Professor / Orientador</option>
                    <option value="aluno">Aluno / Participante</option>
                </select>
            </div>

            <div class="form-actions">
                <a href="<?= e(url('/admin')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Usuário
                </button>
            </div>
        </form>
    </div>
</section>