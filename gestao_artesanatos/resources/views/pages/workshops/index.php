<?php
$workshops = $workshops ?? $items ?? [];
?>

<div class="page-header">
    <div>
        <h2>Oficinas</h2>
        <p>Gerencie as oficinas da ONG, como salas no modelo do Google Classroom.</p>
    </div>

    <?php if (is_professor()): ?>
        <a href="<?= e(url('/oficinas/criar')) ?>" class="btn btn-primary">
            Nova Oficina
        </a>
    <?php endif; ?>
</div>

<?php if (!empty($workshops)): ?>
    <div class="grid three">
        <?php foreach ($workshops as $workshop): ?>
            <section class="card">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                    <div style="
                        width: 44px;
                        height: 44px;
                        border-radius: 14px;
                        background: <?= e($workshop['color'] ?? '#4f46e5') ?>;
                    "></div>

                    <div>
                        <h3 style="margin: 0;">
                            <?= e($workshop['name']) ?>
                        </h3>

                        <small>
                            Oficina artesanal
                        </small>
                    </div>
                </div>

                <p style="color: var(--muted);">
                    <?= e($workshop['description']) ?>
                </p>

                <div class="metric-line">
                    <span>Participantes</span>
                    <strong><?= e($workshop['participants_count'] ?? 0) ?></strong>
                </div>

                <div class="metric-line">
                    <span>Total produzido</span>
                    <strong><?= e($workshop['total_production'] ?? 0) ?></strong>
                </div>

                <div class="actions" style="margin-top: 16px;">
                    <a href="<?= e(url('/oficinas/' . $workshop['id'])) ?>" class="btn btn-secondary">
                        Ver detalhes
                    </a>

                    <?php if (is_professor()): ?>
                        <a href="<?= e(url('/oficinas/' . $workshop['id'] . '/editar')) ?>" class="btn btn-secondary">
                            Editar
                        </a>

                        <form
                            method="POST"
                            action="<?= e(url('/oficinas/' . $workshop['id'] . '/excluir')) ?>"
                            data-confirm="Deseja arquivar esta oficina?"
                        >
                            <?= csrf_field() ?>

                            <button type="submit" class="btn btn-danger">
                                Arquivar
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <section class="panel">
        <div class="panel-body empty">
            <h3>Nenhuma oficina cadastrada</h3>

            <p>
                Cadastre oficinas como Arte e Pintura, Costura, Artesanato ou Pintura em Potes.
            </p>

            <?php if (is_professor()): ?>
                <a href="<?= e(url('/oficinas/criar')) ?>" class="btn btn-primary">
                    Criar primeira oficina
                </a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>