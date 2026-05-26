<?php
$workshop = $workshop ?? [];
$participants = $participants ?? [];
$availableParticipants = $availableParticipants ?? [];
$productions = $productions ?? [];
?>

<div class="page-header">
    <div>
        <h2><?= e($workshop['name'] ?? 'Oficina') ?></h2>
        <p><?= e($workshop['description'] ?? '') ?></p>
    </div>

    <div class="actions">
        <a href="<?= e(url('/oficinas')) ?>" class="btn btn-secondary">
            Voltar
        </a>

        <?php if (is_professor()): ?>
            <a href="<?= e(url('/oficinas/' . $workshop['id'] . '/editar')) ?>" class="btn btn-primary">
                Editar Oficina
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="grid two">
    <section class="panel">
        <div class="panel-header">
            <h3>Participantes</h3>
            <p>Alunos/participantes vinculados a esta oficina.</p>
        </div>

        <div class="panel-body">
            <?php if (!empty($participants)): ?>
                <?php foreach ($participants as $participant): ?>
                    <div class="metric-line">
                        <span>
                            <strong><?= e($participant['name']) ?></strong><br>
                            <small><?= e($participant['email']) ?></small>
                        </span>

                        <?php if (is_professor()): ?>
                            <form
                                method="POST"
                                action="<?= e(url('/oficinas/' . $workshop['id'] . '/participantes/' . $participant['id'] . '/remover')) ?>"
                                data-confirm="Remover este participante da oficina?"
                            >
                                <?= csrf_field() ?>

                                <button type="submit" class="btn btn-danger">
                                    Remover
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">
                    Nenhum participante vinculado a esta oficina.
                </div>
            <?php endif; ?>

            <?php if (is_professor() && !empty($availableParticipants)): ?>
                <hr style="border: 0; border-top: 1px solid var(--line); margin: 20px 0;">

                <form
                    class="form"
                    method="POST"
                    action="<?= e(url('/oficinas/' . $workshop['id'] . '/participantes')) ?>"
                >
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="user_id">Adicionar participante</label>

                        <select class="select" id="user_id" name="user_id" required>
                            <option value="">Selecione um participante</option>

                            <?php foreach ($availableParticipants as $participant): ?>
                                <option value="<?= e($participant['id']) ?>">
                                    <?= e($participant['name']) ?> — <?= e($participant['email']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Adicionar Participante
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h3>Histórico de Produção</h3>
            <p>Produtos produzidos dentro desta oficina.</p>
        </div>

        <div class="panel-body">
            <?php if (!empty($productions)): ?>
                <div class="feed-list">
                    <?php foreach ($productions as $production): ?>
                        <div class="feed-item">
                            <strong><?= e($production['product_name']) ?></strong>

                            <span>
                                Quantidade:
                                <?= e($production['quantity']) ?>
                            </span>

                            <small>
                                Data:
                                <?= e(date('d/m/Y', strtotime($production['produced_at']))) ?>
                            </small>

                            <small>
                                Responsável:
                                <?= e($production['responsible_name'] ?? 'Não informado') ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">
                    Nenhuma produção registrada para esta oficina.
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>