<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= e($title ?? app_name()) ?> | <?= e(app_name()) ?>
    </title>

    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>

<body class="auth-shell">

    <main class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                GA
            </div>

            <h1><?= e(app_name()) ?></h1>

            <p>
                Plataforma de gestão de oficinas e produção artesanal para ONG.
            </p>
        </div>

        <?php foreach (flashes() as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?= e($type) ?>">
                    <?= e($message) ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <?= $content ?>

    </main>

</body>
</html>