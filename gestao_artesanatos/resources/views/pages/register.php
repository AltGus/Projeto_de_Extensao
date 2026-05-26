<form class="form" method="POST" action="<?= e(url('/cadastro')) ?>">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="name">Nome completo</label>
        <input
            class="input"
            type="text"
            id="name"
            name="name"
            placeholder="Digite seu nome"
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
            placeholder="Digite seu e-mail"
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
        <label for="password_confirmation">Confirmar senha</label>
        <input
            class="input"
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            placeholder="Confirme sua senha"
            required
        >
    </div>

    <button type="submit" class="btn btn-primary btn-block">
        Cadastrar
    </button>
</form>

<div class="auth-footer">
    Já tem conta?
    <a href="<?= e(url('/login')) ?>">Entrar</a>
</div>