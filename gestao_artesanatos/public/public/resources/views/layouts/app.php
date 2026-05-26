<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= e($title ?? app_name()) ?> - <?= e(app_name()) ?>
    </title>

    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>

<body>
    <div class="app-shell">

        <aside class="sidebar">
            <div class="brand">
                <div class="brand-logo">
                    GA
                </div>

                <div class="brand-text">
                    <strong><?= e(app_name()) ?></strong>
                    <span>ONG Artesanal</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="<?= e(url('/dashboard')) ?>" class="<?= request_path() === '/dashboard' ? 'active' : '' ?>">
                    Dashboard
                </a>

                <a href="<?= e(url('/oficinas')) ?>" class="<?= str_starts_with(request_path(), '/oficinas') ? 'active' : '' ?>">
                    Oficinas
                </a>

                <a href="<?= e(url('/producoes')) ?>" class="<?= str_starts_with(request_path(), '/producoes') ? 'active' : '' ?>">
                    Produções
                </a>

                <a href="<?= e(url('/produtos')) ?>" class="<?= str_starts_with(request_path(), '/produtos') ? 'active' : '' ?>">
                    Produtos
                </a>

                <a href="<?= e(url('/materiais')) ?>" class="<?= str_starts_with(request_path(), '/materiais') ? 'active' : '' ?>">
                    Materiais
                </a>

                <a href="<?= e(url('/estoque')) ?>" class="<?= str_starts_with(request_path(), '/estoque') ? 'active' : '' ?>">
                    Estoque
                </a>

                <?php if (is_professor()): ?>
                    <a href="<?= e(url('/relatorios')) ?>" class="<?= str_starts_with(request_path(), '/relatorios') ? 'active' : '' ?>">
                        Relatórios
                    </a>

                    <a href="<?= e(url('/admin')) ?>" class="<?= str_starts_with(request_path(), '/admin') ? 'active' : '' ?>">
                        Administração
                    </a>
                <?php endif; ?>
            </nav>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <div class="topbar-title">
                    <h1><?= e($title ?? app_name()) ?></h1>
                    <p>Controle de oficinas, produção artesanal, materiais e estoque.</p>
                </div>

                <div class="topbar-user">
                    <?php $loggedUser = current_user(); ?>

                    <div class="user-pill">
                        <?= e($loggedUser['name'] ?? 'Usuário') ?>
                        —
                        <?= e($loggedUser['role'] ?? '') ?>
                    </div>

                    <form action="<?= e(url('/logout')) ?>" method="POST">
                        <?= csrf_field() ?>

                        <button type="submit" class="btn btn-secondary">
                            Sair
                        </button>
                    </form>
                </div>
            </header>

            <section class="content">
                <?php foreach (flashes() as $type => $messages): ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="alert alert-<?= e($type) ?>">
                            <?= e($message) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <?= $content ?>
            </section>
        </main>

    </div>

    <script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>