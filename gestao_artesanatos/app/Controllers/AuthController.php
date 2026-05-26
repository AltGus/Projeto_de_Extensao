<?php
// app/Controllers/AuthController.php
class AuthController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function showLogin(): void
    {
        $this->view('auth/login', ['title' => 'Entrar'], 'guest');
    }

    public function login(): void
    {
        $this->requireFields($_POST, ['email', 'password'], '/login');

        $user = $this->users->findByEmail($_POST['email']);

        if (!$user || !password_verify($_POST['password'], $user['password'])) {
            flash('error', 'Credenciais inválidas.');
            redirect_to('/login');
        }

        Auth::login($user);
        flash('success', 'Login realizado com sucesso.');
        redirect_to('/dashboard');
    }

    public function showRegister(): void
    {
        $this->view('auth/register', ['title' => 'Cadastro'], 'guest');
    }

    public function register(): void
    {
        $this->requireFields($_POST, ['name', 'email', 'password', 'password_confirmation'], '/cadastro');

        if ($_POST['password'] !== $_POST['password_confirmation']) {
            flash('error', 'As senhas não coincidem.');
            redirect_to('/cadastro');
        }

        if ($this->users->findByEmail($_POST['email'])) {
            flash('error', 'Já existe um usuário com este e-mail.');
            redirect_to('/cadastro');
        }

        $this->users->create([
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'role' => 'aluno',
        ]);

        flash('success', 'Cadastro realizado com sucesso. Faça login para continuar.');
        redirect_to('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'Sessão encerrada.');
        redirect_to('/login');
    }
}
