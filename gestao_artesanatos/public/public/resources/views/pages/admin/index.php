<?php
$users = $users ?? $items ?? [];
?>

<div class="page-header">
    <div>
        <h2>Painel Administrativo</h2>
        <p>Gerenciamento de usuários, professores/orientadores e participantes.</p>
    </div>

    <a href="<?= e(url('/admin/usuarios/criar')) ?>" class="btn btn-primary">
        Novo Usuário
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Usuários do Sistema</h3>
        <p>Controle de acesso ao sistema de gestão artesanal.</p>
    </div>

    <div class="panel-body">
        <?php if (!empty($users)): ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?= e($user['name']) ?></strong>
                                </td>

                                <td>
                                    <?= e($user['email']) ?>
                                </td>

                                <td>
                                    <?php if ($user['role'] === 'professor'): ?>
                                        <span class="badge badge-primary">
                                            Professor / Orientador
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success">
                                            Aluno / Participante
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= e(date('d/m/Y', strtotime($user['created_at']))) ?>
                                </td>

                                <td>
                                    <div class="actions">
                                        <a
                                            href="<?= e(url('/admin/usuarios/' . $user['id'] . '/editar')) ?>"
                                            class="btn btn-secondary"
                                        >
                                            Editar
                                        </a>

                                        <?php if ((int) $user['id'] !== (int) user_id()): ?>
                                            <form
                                                method="POST"
                                                action="<?= e(url('/admin/usuarios/' . $user['id'] . '/excluir')) ?>"
                                                data-confirm="Deseja excluir este usuário?"
                                            >
                                                <?= csrf_field() ?>

                                                <button type="submit" class="btn btn-danger">
                                                    Excluir
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty">
                Nenhum usuário cadastrado.
            </div>
        <?php endif; ?>
    </div>
</section>