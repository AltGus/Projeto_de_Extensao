<?php
// app/Controllers/AdminController.php
class AdminController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function index(): void
    {
        $this->view('admin/index', [
            'title' => 'Painel administrativo',
            'items' => $this->users->all(),
        ]);
    }

    public function createUser(): void
    {
        $this->view('admin/create_user', ['title' => 'Novo usuário']);
    }

    public function storeUser(): void
    {
        $this->requireFields($_POST, ['name', 'email', 'password', 'role'], '/admin/usuarios/criar');

        if ($this->users->findByEmail($_POST['email'])) {
            flash('error', 'Já existe um usuário com este e-mail.');
            redirect_to('/admin/usuarios/criar');
        }

        $this->attempt(function (): void {
            $this->users->create($_POST);
        }, 'Usuário criado com sucesso.', '/admin');
    }

    public function editUser($id): void
    {
        $user = $this->users->find((int) $id);
        if (!$user) {
            abort(404);
        }

        $this->view('admin/edit_user', [
            'title' => 'Editar usuário',
            'userItem' => $user,
        ]);
    }

    public function updateUser($id): void
    {
        $this->requireFields($_POST, ['name', 'email', 'role'], '/admin/usuarios/' . (int) $id . '/editar');

        $this->attempt(function () use ($id): void {
            $this->users->update((int) $id, $_POST);
        }, 'Usuário atualizado com sucesso.', '/admin');
    }

    public function deleteUser($id): void
    {
        if ((int) $id === (int) Auth::id()) {
            flash('error', 'Você não pode excluir o seu próprio usuário.');
            redirect_to('/admin');
        }

        $this->attempt(function () use ($id): void {
            $this->users->delete((int) $id);
        }, 'Usuário excluído com sucesso.', '/admin');
    }
}
