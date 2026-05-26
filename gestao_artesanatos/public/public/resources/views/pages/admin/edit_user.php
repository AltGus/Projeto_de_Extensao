<?php
$userItem = $userItem ?? $user ?? [];
?>

<div class="page-header">
    <div>
        <h2>Editar Usuário</h2>
        <p>Atualize os dados e permissões do usuário.</p>
    </div>

    <a href="<?= e(url('/admin')) ?>" class="btn btn-secondary">
        Voltar
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Dados do Usuário</h3>
        <p>Altere nome, e-mail, senha ou tipo de acesso.</p>
    </div>

    <div class="panel-body">
        <form
            class="form"
            method="POST"
            action="<?= e(url('/admin/usuarios/' . $userItem['id'] . '/atualizar')) ?>"
        >
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Nome completo</label>

                <input
                    class="input"
                    type="text"
                    id="name"
                    name="name"
                    value="<?= e($userItem['name'] ?? '') ?>"
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
                    value="<?= e($userItem['email'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Nova senha</label>

                <input
                    class="input"
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Deixe em branco para manter a senha atual"
                >
            </div>

            <div class="form-group">
                <label for="role">Tipo de usuário</label>

                <select class="select" id="role" name="role" required>
                    <option value="professor" <?= ($userItem['role'] ?? '') === 'professor' ? 'selected' : '' ?>>
                        Professor / Orientador
                    </option>

                    <option value="aluno" <?= ($userItem['role'] ?? '') === 'aluno' ? 'selected' : '' ?>>
                        Aluno / Participante
                    </option>
                </select>
            </div>

            <div class="form-actions">
                <a href="<?= e(url('/admin')) ?>" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</section>