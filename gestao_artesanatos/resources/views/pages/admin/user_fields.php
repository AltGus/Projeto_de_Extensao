<?php
$userItem = $userItem ?? $user ?? [];

$name = $userItem['name'] ?? '';
$email = $userItem['email'] ?? '';
$role = $userItem['role'] ?? 'aluno';
$isEdit = !empty($userItem['id']);
?>

<div class="form-group">
    <label for="name">Nome completo</label>

    <input
        class="input"
        type="text"
        id="name"
        name="name"
        value="<?= e($name) ?>"
        placeholder="Digite o nome completo"
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
        value="<?= e($email) ?>"
        placeholder="Digite o e-mail"
        required
    >
</div>

<div class="form-group">
    <label for="password">
        <?= $isEdit ? 'Nova senha' : 'Senha' ?>
    </label>

    <input
        class="input"
        type="password"
        id="password"
        name="password"
        placeholder="<?= $isEdit ? 'Deixe em branco para manter a senha atual' : 'Digite uma senha' ?>"
        <?= $isEdit ? '' : 'required' ?>
    >

    <?php if ($isEdit): ?>
        <small style="color: var(--muted);">
            Preencha apenas se quiser alterar a senha do usuário.
        </small>
    <?php endif; ?>
</div>

<div class="form-group">
    <label for="role">Tipo de usuário</label>

    <select class="select" id="role" name="role" required>
        <option value="">Selecione o perfil</option>

        <option value="professor" <?= $role === 'professor' ? 'selected' : '' ?>>
            Professor / Orientador
        </option>

        <option value="aluno" <?= $role === 'aluno' ? 'selected' : '' ?>>
            Aluno / Participante
        </option>
    </select>
</div>