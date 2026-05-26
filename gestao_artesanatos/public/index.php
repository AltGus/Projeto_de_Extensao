<?php

require_once __DIR__ . '/../src/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = request_path();

if ($method === 'POST') {
    verify_csrf();
}

function route_params(string $pattern, string $path): ?array
{
    $pattern = rtrim($pattern, '/') ?: '/';
    $path = rtrim($path, '/') ?: '/';

    $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';

    if (!preg_match($regex, $path, $matches)) {
        return null;
    }

    $params = [];

    foreach ($matches as $key => $value) {
        if (is_string($key)) {
            $params[$key] = $value;
        }
    }

    return $params;
}

function required_fields(array $fields, string $redirect): void
{
    foreach ($fields as $field) {
        if (trim((string) ($_POST[$field] ?? '')) === '') {
            flash('error', 'Preencha todos os campos obrigatórios.');
            redirect_to($redirect);
        }
    }
}

function action_attempt(callable $callback, string $successMessage, string $redirect): void
{
    try {
        $result = $callback();

        if ($result === false) {
            redirect_to($redirect);
        }

        flash('success', $successMessage);
        redirect_to($redirect);
    } catch (Throwable $e) {
        flash('error', 'Erro: ' . $e->getMessage());
        redirect_to($redirect);
    }
}

/*
|--------------------------------------------------------------------------
| Página inicial
|--------------------------------------------------------------------------
*/

if ($path === '/') {
    redirect_to(is_logged() ? '/dashboard' : '/login');
}

/*
|--------------------------------------------------------------------------
| Rotas públicas - Login / Cadastro
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && $path === '/login') {
    guest_only();

    view('login', [
        'title' => 'Login',
    ], 'auth');

    exit;
}

if ($method === 'POST' && $path === '/login') {
    guest_only();

    required_fields(['email', 'password'], '/login');

    if (auth_attempt($_POST['email'], $_POST['password'])) {
        flash('success', 'Login realizado com sucesso.');
        redirect_to('/dashboard');
    }

    flash('error', 'E-mail ou senha inválidos.');
    redirect_to('/login');
}

if ($method === 'GET' && ($path === '/cadastro' || $path === '/register')) {
    guest_only();

    view('register', [
        'title' => 'Cadastro',
    ], 'auth');

    exit;
}

if ($method === 'POST' && ($path === '/cadastro' || $path === '/register')) {
    guest_only();

    if (auth_register($_POST)) {
        redirect_to('/login');
    }

    redirect_to('/cadastro');
}

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

if ($method === 'POST' && $path === '/logout') {
    require_login();

    auth_logout();

    flash('success', 'Sessão encerrada com sucesso.');
    redirect_to('/login');
}

/*
|--------------------------------------------------------------------------
| A partir daqui precisa estar logado
|--------------------------------------------------------------------------
*/

require_login();

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && $path === '/dashboard') {
    view('dashboard', [
        'title' => 'Dashboard',
        'stats' => dashboard_stats(),
        'ranking' => dashboard_workshop_ranking(),
        'recentProductions' => dashboard_recent_productions(),
        'lowStock' => dashboard_low_stock(),
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Oficinas
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && ($path === '/oficinas' || $path === '/workshops')) {
    view('workshops/index', [
        'title' => 'Oficinas',
        'workshops' => workshops_all(),
        'items' => workshops_all(),
    ]);

    exit;
}

if ($method === 'GET' && $path === '/oficinas/criar') {
    require_professor();

    view('workshops/create', [
        'title' => 'Nova Oficina',
    ]);

    exit;
}

if ($method === 'POST' && $path === '/oficinas') {
    require_professor();

    required_fields(['name'], '/oficinas/criar');

    action_attempt(function () {
        activity_log('Oficina criada', 'Uma nova oficina foi cadastrada.');
        return workshop_create($_POST);
    }, 'Oficina criada com sucesso.', '/oficinas');
}

if ($method === 'GET' && ($params = route_params('/oficinas/{id}', $path))) {
    $id = (int) $params['id'];

    $workshop = workshop_find($id);

    if (!$workshop) {
        http_response_code(404);
        echo 'Oficina não encontrada.';
        exit;
    }

    view('workshops/show', [
        'title' => 'Detalhes da Oficina',
        'workshop' => $workshop,
        'participants' => workshop_participants($id),
        'availableParticipants' => workshop_available_participants($id),
        'productions' => productions_by_workshop($id),
    ]);

    exit;
}

if ($method === 'GET' && ($params = route_params('/oficinas/{id}/editar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    view('workshops/edit', [
        'title' => 'Editar Oficina',
        'workshop' => workshop_find($id),
    ]);

    exit;
}

if ($method === 'POST' && ($params = route_params('/oficinas/{id}/atualizar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    required_fields(['name'], '/oficinas/' . $id . '/editar');

    action_attempt(function () use ($id) {
        activity_log('Oficina atualizada', 'Uma oficina foi atualizada.');
        return workshop_update($id, $_POST);
    }, 'Oficina atualizada com sucesso.', '/oficinas');
}

if ($method === 'POST' && ($params = route_params('/oficinas/{id}/excluir', $path))) {
    require_professor();

    $id = (int) $params['id'];

    action_attempt(function () use ($id) {
        activity_log('Oficina excluída', 'Uma oficina foi removida.');
        return workshop_delete($id);
    }, 'Oficina excluída com sucesso.', '/oficinas');
}

if ($method === 'POST' && ($params = route_params('/oficinas/{id}/participantes', $path))) {
    require_professor();

    $id = (int) $params['id'];

    required_fields(['user_id'], '/oficinas/' . $id);

    action_attempt(function () use ($id) {
        return workshop_add_participant($id, (int) $_POST['user_id']);
    }, 'Participante adicionado à oficina.', '/oficinas/' . $id);
}

if ($method === 'POST' && ($params = route_params('/oficinas/{id}/participantes/{userId}/remover', $path))) {
    require_professor();

    $id = (int) $params['id'];
    $userId = (int) $params['userId'];

    action_attempt(function () use ($id, $userId) {
        return workshop_remove_participant($id, $userId);
    }, 'Participante removido da oficina.', '/oficinas/' . $id);
}

/*
|--------------------------------------------------------------------------
| Produtos
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && ($path === '/produtos' || $path === '/products')) {
    view('products/index', [
        'title' => 'Produtos Artesanais',
        'products' => products_all(),
        'items' => products_all(),
    ]);

    exit;
}

if ($method === 'GET' && $path === '/produtos/criar') {
    require_professor();

    view('products/create', [
        'title' => 'Novo Produto',
        'workshops' => workshops_all(),
    ]);

    exit;
}

if ($method === 'POST' && $path === '/produtos') {
    require_professor();

    required_fields(['name', 'category'], '/produtos/criar');

    action_attempt(function () {
        activity_log('Produto criado', 'Um produto artesanal foi cadastrado.');
        return product_create($_POST);
    }, 'Produto criado com sucesso.', '/produtos');
}

if ($method === 'GET' && ($params = route_params('/produtos/{id}/editar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    view('products/edit', [
        'title' => 'Editar Produto',
        'product' => product_find($id),
        'workshops' => workshops_all(),
    ]);

    exit;
}

if ($method === 'POST' && ($params = route_params('/produtos/{id}/atualizar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    required_fields(['name', 'category'], '/produtos/' . $id . '/editar');

    action_attempt(function () use ($id) {
        activity_log('Produto atualizado', 'Um produto artesanal foi atualizado.');
        return product_update($id, $_POST);
    }, 'Produto atualizado com sucesso.', '/produtos');
}

if ($method === 'POST' && ($params = route_params('/produtos/{id}/excluir', $path))) {
    require_professor();

    $id = (int) $params['id'];

    action_attempt(function () use ($id) {
        activity_log('Produto excluído', 'Um produto artesanal foi removido.');
        return product_delete($id);
    }, 'Produto excluído com sucesso.', '/produtos');
}

/*
|--------------------------------------------------------------------------
| Produções
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && ($path === '/producoes' || $path === '/productions')) {
    view('productions/index', [
        'title' => 'Produções',
        'productions' => productions_all(),
        'items' => productions_all(),
    ]);

    exit;
}

if ($method === 'GET' && $path === '/producoes/criar') {
    view('productions/create', [
        'title' => 'Nova Produção',
        'workshops' => workshops_all(),
        'products' => products_all(),
        'users' => users_all(),
    ]);

    exit;
}

if ($method === 'POST' && $path === '/producoes') {
    required_fields(['product_id', 'workshop_id', 'quantity', 'produced_at', 'purpose'], '/producoes/criar');

    action_attempt(function () {
        activity_log('Produção registrada', 'Uma produção artesanal foi cadastrada.');
        return production_create($_POST);
    }, 'Produção registrada com sucesso.', '/producoes');
}

if ($method === 'GET' && ($params = route_params('/producoes/{id}/editar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    view('productions/edit', [
        'title' => 'Editar Produção',
        'production' => production_find($id),
        'workshops' => workshops_all(),
        'products' => products_all(),
        'users' => users_all(),
    ]);

    exit;
}

if ($method === 'POST' && ($params = route_params('/producoes/{id}/atualizar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    required_fields(['product_id', 'workshop_id', 'quantity', 'produced_at', 'purpose'], '/producoes/' . $id . '/editar');

    action_attempt(function () use ($id) {
        activity_log('Produção atualizada', 'Uma produção artesanal foi atualizada.');
        return production_update($id, $_POST);
    }, 'Produção atualizada com sucesso.', '/producoes');
}

if ($method === 'POST' && ($params = route_params('/producoes/{id}/excluir', $path))) {
    require_professor();

    $id = (int) $params['id'];

    action_attempt(function () use ($id) {
        activity_log('Produção excluída', 'Uma produção artesanal foi removida.');
        return production_delete($id);
    }, 'Produção excluída com sucesso.', '/producoes');
}

/*
|--------------------------------------------------------------------------
| Materiais
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && ($path === '/materiais' || $path === '/materials')) {
    view('materials/index', [
        'title' => 'Materiais',
        'materials' => materials_all(),
        'items' => materials_all(),
    ]);

    exit;
}

if ($method === 'GET' && $path === '/materiais/criar') {
    require_professor();

    view('materials/create', [
        'title' => 'Novo Material',
    ]);

    exit;
}

if ($method === 'POST' && $path === '/materiais') {
    require_professor();

    required_fields(['name', 'category', 'unit'], '/materiais/criar');

    action_attempt(function () {
        activity_log('Material criado', 'Um material foi cadastrado.');
        return material_create($_POST);
    }, 'Material criado com sucesso.', '/materiais');
}

if ($method === 'GET' && ($params = route_params('/materiais/{id}/editar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    view('materials/edit', [
        'title' => 'Editar Material',
        'material' => material_find($id),
    ]);

    exit;
}

if ($method === 'POST' && ($params = route_params('/materiais/{id}/atualizar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    required_fields(['name', 'category', 'unit'], '/materiais/' . $id . '/editar');

    action_attempt(function () use ($id) {
        activity_log('Material atualizado', 'Um material foi atualizado.');
        return material_update($id, $_POST);
    }, 'Material atualizado com sucesso.', '/materiais');
}

if ($method === 'POST' && ($params = route_params('/materiais/{id}/excluir', $path))) {
    require_professor();

    $id = (int) $params['id'];

    action_attempt(function () use ($id) {
        activity_log('Material excluído', 'Um material foi removido.');
        return material_delete($id);
    }, 'Material excluído com sucesso.', '/materiais');
}

/*
|--------------------------------------------------------------------------
| Estoque
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && ($path === '/estoque' || $path === '/stock')) {
    view('stock/index', [
        'title' => 'Estoque',
        'materials' => materials_all(),
        'movements' => stock_movements_all(),
        'items' => stock_movements_all(),
    ]);

    exit;
}

if ($method === 'GET' && $path === '/estoque/criar') {
    require_professor();

    view('stock/create', [
        'title' => 'Nova Movimentação',
        'materials' => materials_all(),
    ]);

    exit;
}

if ($method === 'POST' && $path === '/estoque') {
    require_professor();

    required_fields(['material_id', 'movement_type', 'quantity', 'movement_date'], '/estoque/criar');

    action_attempt(function () {
        activity_log('Estoque movimentado', 'Uma movimentação de estoque foi registrada.');
        return stock_create_movement($_POST);
    }, 'Movimentação registrada com sucesso.', '/estoque');
}

if ($method === 'POST' && ($params = route_params('/estoque/{id}/excluir', $path))) {
    require_professor();

    $id = (int) $params['id'];

    action_attempt(function () use ($id) {
        activity_log('Movimentação excluída', 'Uma movimentação de estoque foi removida.');
        return stock_delete_movement($id);
    }, 'Movimentação excluída com sucesso.', '/estoque');
}

/*
|--------------------------------------------------------------------------
| Relatórios
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && ($path === '/relatorios' || $path === '/reports')) {
    require_professor();

    view('reports/index', [
        'title' => 'Relatórios',
        'ranking' => report_production_by_workshop(),
        'materialConsumption' => report_material_consumption(),
        'activities' => report_recent_activities(),
        'lowStock' => dashboard_low_stock(),
        'stats' => dashboard_stats(),
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Administração
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && $path === '/admin') {
    require_professor();

    view('admin/index', [
        'title' => 'Painel Administrativo',
        'users' => users_all(),
        'items' => users_all(),
    ]);

    exit;
}

if ($method === 'GET' && $path === '/admin/usuarios/criar') {
    require_professor();

    view('admin/create_user', [
        'title' => 'Novo Usuário',
    ]);

    exit;
}

if ($method === 'POST' && $path === '/admin/usuarios') {
    require_professor();

    required_fields(['name', 'email', 'password', 'role'], '/admin/usuarios/criar');

    action_attempt(function () {
        activity_log('Usuário criado', 'Um usuário foi cadastrado pelo administrador.');
        return user_create($_POST);
    }, 'Usuário criado com sucesso.', '/admin');
}

if ($method === 'GET' && ($params = route_params('/admin/usuarios/{id}/editar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    view('admin/edit_user', [
        'title' => 'Editar Usuário',
        'userItem' => user_find($id),
        'user' => user_find($id),
    ]);

    exit;
}

if ($method === 'POST' && ($params = route_params('/admin/usuarios/{id}/atualizar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    required_fields(['name', 'email', 'role'], '/admin/usuarios/' . $id . '/editar');

    action_attempt(function () use ($id) {
        activity_log('Usuário atualizado', 'Um usuário foi atualizado pelo administrador.');
        return user_update($id, $_POST);
    }, 'Usuário atualizado com sucesso.', '/admin');
}

if ($method === 'POST' && ($params = route_params('/admin/usuarios/{id}/excluir', $path))) {
    require_professor();

    $id = (int) $params['id'];

    if ($id === user_id()) {
        flash('error', 'Você não pode excluir o próprio usuário logado.');
        redirect_to('/admin');
    }

    action_attempt(function () use ($id) {
        activity_log('Usuário excluído', 'Um usuário foi removido pelo administrador.');
        return user_delete($id);
    }, 'Usuário excluído com sucesso.', '/admin');
}

/*
|--------------------------------------------------------------------------
| Página não encontrada
|--------------------------------------------------------------------------
*/

http_response_code(404);

view('errors/404', [
    'title' => 'Página não encontrada',
]);