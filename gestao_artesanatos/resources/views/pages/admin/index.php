<?php
$users = $users ?? $items ?? [];
?>

<div class="page-header">
    <div>
        <h2>Painel Administrativo</h2>
        <p>Gerencie somente as contas que acessam o sistema. Crianças são cadastradas separadamente em Alunos.</p>
    </div>

    <a href="<?= e(url('/admin/usuarios/criar')) ?>" class="btn btn-primary">
        Novo Usuário
    </a>
</div>

<section class="panel">
    <div class="panel-header">
        <h3>Alunos atendidos</h3>
        <p>Cadastre e gerencie as crianças atendidas pela entidade. Esses registros são independentes das contas de acesso ao sistema.</p>
    </div>
    <div class="panel-body">
        <div class="actions">
            <a href="<?= e(url('/alunos')) ?>" class="btn btn-primary">Gerenciar alunos</a>
            <a href="<?= e(url('/alunos/criar')) ?>" class="btn btn-secondary">Cadastrar aluno</a>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Usuários do Sistema</h3>
        <p>Controle de acesso ao sistema de Gestão Artesanal.</p>
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
                                            Usuário Operacional
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
                                                data-confirm="Deseja arquivar este usuário?"
                                            >
                                                <?= csrf_field() ?>

                                                <button type="submit" class="btn btn-danger">
                                                    Arquivar
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                Usuário logado
                                            </span>
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
                <h3>Nenhum usuário cadastrado</h3>

                <p>
                    Cadastre professores/orientadores e, quando necessário, usuários operacionais. Crianças não recebem conta de acesso.
                </p>

                <a href="<?= e(url('/admin/usuarios/criar')) ?>" class="btn btn-primary">
                    Criar primeiro usuário
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>