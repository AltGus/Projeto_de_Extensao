<form class="form" method="POST" action="<?= e(url('/login')) ?>">
    <?= csrf_field() ?>

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
            placeholder="Digite sua senha"
            required
        >
    </div>

    <button type="submit" class="btn btn-primary btn-block">
        Entrar
    </button>
</form>

<div class="auth-footer">
    Ainda não tem conta?
    <a href="<?= e(url('/cadastro')) ?>">Criar cadastro</a>
</div>

