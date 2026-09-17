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
        db()->beginTransaction();
        $result = $callback();

        if ($result === false) {
            db()->rollBack();
            redirect_to($redirect);
        }

        db()->commit();
        flash('success', $successMessage);
        redirect_to($redirect);
    } catch (Throwable $e) {
        if (db()->inTransaction()) db()->rollBack();
        report_error($e);
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

if (($path === '/cadastro' || $path === '/register') && !config_value('app.allow_registration', false)) {
    http_response_code(403); exit('Solicite seu cadastro ao professor responsável.');
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

// Student data and production management are administrative, including read-only screens.
if (preg_match('#^/(alunos|criancas|producoes|productions|oficinas|workshops|produtos|products)(?:/|$)#',$path)) require_professor();

// Reject nonexistent or archived targets before edits or success audit messages.
if (preg_match('#^/(oficinas|produtos|materiais|producoes|estoque|alunos|admin/usuarios)/([0-9]+)(?:/|$)#', $path, $target)) {
    $finders = ['oficinas'=>'workshop_find','produtos'=>'product_find','materiais'=>'material_find',
        'producoes'=>'production_find','estoque'=>'stock_movement_find','alunos'=>'student_find','admin/usuarios'=>'user_find'];
    $record = $finders[$target[1]]((int)$target[2]);
    $historyRead=$method==='GET' && $path==='/'.$target[1].'/'.$target[2] && in_array($target[1],['alunos','produtos','oficinas'],true);
    if (!$record || (!$historyRead && isset($record['active']) && !$record['active'])) {
        http_response_code(404); exit('Registro não encontrado ou arquivado.');
    }
}

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
        'orientators' => users_orientators(),
    ]);

    exit;
}

if ($method === 'POST' && $path === '/oficinas') {
    require_professor();

    required_fields(['name'], '/oficinas/criar');

    action_attempt(function () {
        $result = workshop_create($_POST);
        if ($result) activity_log('Oficina criada', 'Uma nova oficina foi cadastrada.');
        return $result;
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
        'orientators' => users_orientators(),
    ]);

    exit;
}

if ($method === 'POST' && ($params = route_params('/oficinas/{id}/atualizar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    required_fields(['name'], '/oficinas/' . $id . '/editar');

    action_attempt(function () use ($id) {
        $result = workshop_update($id, $_POST);
        if ($result) activity_log('Oficina atualizada', 'Uma oficina foi atualizada.');
        return $result;
    }, 'Oficina atualizada com sucesso.', '/oficinas');
}

if ($method === 'POST' && ($params = route_params('/oficinas/{id}/excluir', $path))) {
    require_professor();

    $id = (int) $params['id'];

    action_attempt(function () use ($id) {
        $result = workshop_delete($id);
        if ($result) activity_log('Oficina arquivada', 'Uma oficina foi removida.');
        return $result;
    }, 'Oficina arquivada com sucesso.', '/oficinas');
}

if ($method === 'POST' && ($params = route_params('/oficinas/{id}/participantes', $path))) {
    require_professor();

    $id = (int) $params['id'];

    required_fields(['student_id'], '/oficinas/' . $id);

    action_attempt(function () use ($id) {
        return workshop_add_participant($id, (int) $_POST['student_id']);
    }, 'Participante adicionado à oficina.', '/oficinas/' . $id);
}

if ($method === 'POST' && ($params = route_params('/oficinas/{id}/participantes/{studentId}/remover', $path))) {
    require_professor();

    $id = (int) $params['id'];
    $studentId = (int) $params['studentId'];

    action_attempt(function () use ($id, $studentId) {
        return workshop_remove_participant($id, $studentId);
    }, 'Participante removido da oficina.', '/oficinas/' . $id);
}

/*
|--------------------------------------------------------------------------
| Alunos / Crianças (registros administrativos, sem login)
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && ($path === '/alunos' || $path === '/criancas')) {
    require_professor();
    $search = trim((string)($_GET['q'] ?? ''));
    view('students/index', ['title'=>'Alunos','students'=>students_all($search),'search'=>$search]);
    exit;
}
if ($method === 'GET' && $path === '/alunos/criar') {
    require_professor();
    view('students/create',['title'=>'Novo Aluno','workshops'=>workshops_all()]); exit;
}
if ($method === 'POST' && $path === '/alunos') {
    require_professor(); required_fields(['full_name','guardian_name'],'/alunos/criar');
    action_attempt(function(){ $ok=student_create($_POST); if($ok) activity_log('Aluno criado','Novo registro administrativo de aluno.'); return $ok;},'Aluno cadastrado com sucesso.','/alunos');
}
if ($method === 'GET' && ($params=route_params('/alunos/{id}',$path))) {
    require_professor(); $id=(int)$params['id']; $student=student_find($id); if(!$student){http_response_code(404);exit('Aluno não encontrado.');}
    $filterWorkshop=!empty($_GET['workshop_id'])?(int)$_GET['workshop_id']:null;
    view('students/show',['title'=>$student['full_name'],'student'=>$student,'workshops'=>student_workshops($id),'productions'=>student_productions($id,$filterWorkshop),'deliveryState'=>student_delivery_state($id),'deliveryCandidates'=>student_delivery_candidates($id),'filterWorkshop'=>$filterWorkshop,'historyWorkshops'=>student_history_workshops($id)]); exit;
}
if ($method === 'GET' && ($params=route_params('/alunos/{id}/editar',$path))) {
    require_professor(); $id=(int)$params['id']; view('students/edit',['title'=>'Editar Aluno','student'=>student_find($id),'workshops'=>workshops_all(),'selectedWorkshops'=>student_selected_workshops($id)]); exit;
}
if ($method === 'POST' && ($params=route_params('/alunos/{id}/atualizar',$path))) {
    require_professor(); $id=(int)$params['id']; required_fields(['full_name','guardian_name'],'/alunos/'.$id.'/editar');
    action_attempt(function()use($id){$ok=student_update($id,$_POST);if($ok)activity_log('Aluno atualizado','Registro de aluno atualizado.');return $ok;},'Aluno atualizado com sucesso.','/alunos/'.$id);
}
if ($method === 'POST' && ($params=route_params('/alunos/{id}/excluir',$path))) {
    require_professor(); $id=(int)$params['id']; action_attempt(function()use($id){$ok=student_delete($id);if($ok)activity_log('Aluno arquivado','Registro de aluno arquivado.');return $ok;},'Aluno arquivado com sucesso.','/alunos');
}
if ($method === 'POST' && ($params=route_params('/alunos/{id}/entregar',$path))) {
    require_professor(); $id=(int)$params['id']; required_fields(['production_id'],'/alunos/'.$id);
    action_attempt(function()use($id){$ok=delivery_mark($id,(int)$_POST['production_id'],$_POST['notes']??'');if($ok)activity_log('Produção entregue','Entrega registrada para aluno ID '.$id);return $ok;},'Entrega registrada com sucesso.','/alunos/'.$id);
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
        $result = product_create($_POST);
        if ($result) activity_log('Produto criado', 'Um produto artesanal foi cadastrado.');
        return $result;
    }, 'Produto criado com sucesso.', '/produtos');
}

if ($method === 'GET' && ($params = route_params('/produtos/{id}', $path))) {
    $id=(int)$params['id']; $product=product_find($id); if(!$product){http_response_code(404);exit('Produto não encontrado.');}
    view('products/show',['title'=>'Detalhes do Produto','product'=>$product,'productions'=>productions_by_product($id)]); exit;
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
        $result = product_update($id, $_POST);
        if ($result) activity_log('Produto atualizado', 'Um produto artesanal foi atualizado.');
        return $result;
    }, 'Produto atualizado com sucesso.', '/produtos');
}

if ($method === 'POST' && ($params = route_params('/produtos/{id}/excluir', $path))) {
    require_professor();

    $id = (int) $params['id'];

    action_attempt(function () use ($id) {
        $result = product_delete($id);
        if ($result) activity_log('Produto arquivado', 'Um produto artesanal foi removido.');
        return $result;
    }, 'Produto arquivado com sucesso.', '/produtos');
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
        'users' => users_orientators(),
        'students' => students_all(),
    ]);

    exit;
}

if ($method === 'POST' && $path === '/producoes') {
    required_fields(['product_id', 'workshop_id', 'student_id', 'quantity', 'produced_at', 'purpose'], '/producoes/criar');

    action_attempt(function () {
        $result = production_create($_POST);
        if ($result) activity_log('Produção registrada', 'Uma produção artesanal foi cadastrada.');
        return $result;
    }, 'Produção registrada com sucesso.', '/producoes');
}

if ($method === 'GET' && ($params = route_params('/producoes/{id}/editar', $path))) {
    require_professor();

    $id = (int) $params['id'];
    $production = production_find($id);

    if (production_is_delivered($id)) {
        flash('error', 'Produções já entregues ficam bloqueadas para edição a fim de preservar o histórico.');
        redirect_to('/producoes');
    }

    view('productions/edit', [
        'title' => 'Editar Produção',
        'production' => $production,
        'workshops' => workshops_all(),
        'products' => products_all(),
        'users' => users_orientators(),
        'students' => students_all(),
    ]);

    exit;
}

if ($method === 'POST' && ($params = route_params('/producoes/{id}/atualizar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    required_fields(['product_id', 'workshop_id', 'student_id', 'quantity', 'produced_at', 'purpose'], '/producoes/' . $id . '/editar');

    action_attempt(function () use ($id) {
        $result = production_update($id, $_POST);
        if ($result) activity_log('Produção atualizada', 'Uma produção artesanal foi atualizada.');
        return $result;
    }, 'Produção atualizada com sucesso.', '/producoes');
}

if ($method === 'POST' && ($params = route_params('/producoes/{id}/excluir', $path))) {
    require_professor();

    $id = (int) $params['id'];

    action_attempt(function () use ($id) {
        $result = production_delete($id);
        if ($result) activity_log('Produção excluída', 'Uma produção artesanal foi removida.');
        return $result;
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
        'workshops' => workshops_all(),
    ]);

    exit;
}

if ($method === 'POST' && $path === '/materiais') {
    require_professor();

    required_fields(['name', 'category', 'unit'], '/materiais/criar');

    action_attempt(function () {
        $result = material_create($_POST);
        if ($result) activity_log('Material criado', 'Um material foi cadastrado.');
        return $result;
    }, 'Material criado com sucesso.', '/materiais');
}

if ($method === 'GET' && ($params = route_params('/materiais/{id}/editar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    view('materials/edit', [
        'title' => 'Editar Material',
        'material' => material_find($id),
        'workshops' => workshops_all(),
        'selectedWorkshops' => material_selected_workshops($id),
    ]);

    exit;
}

if ($method === 'POST' && ($params = route_params('/materiais/{id}/atualizar', $path))) {
    require_professor();

    $id = (int) $params['id'];

    required_fields(['name', 'category', 'unit'], '/materiais/' . $id . '/editar');

    action_attempt(function () use ($id) {
        $result = material_update($id, $_POST);
        if ($result) activity_log('Material atualizado', 'Um material foi atualizado.');
        return $result;
    }, 'Material atualizado com sucesso.', '/materiais');
}

if ($method === 'POST' && ($params = route_params('/materiais/{id}/excluir', $path))) {
    require_professor();

    $id = (int) $params['id'];

    action_attempt(function () use ($id) {
        $result = material_delete($id);
        if ($result) activity_log('Material arquivado', 'Um material foi removido.');
        return $result;
    }, 'Material arquivado com sucesso.', '/materiais');
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
        $result = stock_create_movement($_POST);
        if ($result) activity_log('Estoque movimentado', 'Uma movimentação de estoque foi registrada.');
        return $result;
    }, 'Movimentação registrada com sucesso.', '/estoque');
}

if ($method === 'GET' && ($params = route_params('/estoque/{id}/editar', $path))) {
    require_professor();
    $movement = stock_movement_find((int)$params['id']);
    if (!$movement) { http_response_code(404); exit('Movimentação não encontrada.'); }
    view('stock/edit', ['title' => 'Editar movimentação', 'movement' => $movement, 'materials' => materials_all()]);
    exit;
}
if ($method === 'POST' && ($params = route_params('/estoque/{id}/atualizar', $path))) {
    require_professor();
    $id = (int)$params['id'];
    action_attempt(function () use ($id) {
        $result = stock_update_movement($id, $_POST);
        if ($result) activity_log('Movimentação atualizada', 'Movimentação ID ' . $id);
        return $result;
    }, 'Movimentação atualizada.', '/estoque');
}

if ($method === 'POST' && ($params = route_params('/estoque/{id}/excluir', $path))) {
    require_professor();

    $id = (int) $params['id'];

    action_attempt(function () use ($id) {
        $result = stock_delete_movement($id);
        if ($result) activity_log('Movimentação excluída', 'Uma movimentação de estoque foi removida.');
        return $result;
    }, 'Movimentação excluída com sucesso.', '/estoque');
}

/*
|--------------------------------------------------------------------------
| Relatórios
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && ($path === '/relatorios' || $path === '/reports')) {
    require_professor();

    try {
        $rows = report_filtered_productions($_GET);

        if (($_GET['export'] ?? '') === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="producoes.csv"');
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Data','Aluno','Trabalho','Oficina','Produto','Responsável','Quantidade','Finalidade','Entrega'], ';', '"', '');
            foreach ($rows as $row) {
                fputcsv($out, array_map('csv_safe', [
                    $row['produced_at'],
                    $row['student_name'] ?? 'Registro antigo',
                    $row['work_number'] ?? '',
                    $row['workshop_name'],
                    $row['product_name'],
                    $row['responsible_name'],
                    $row['quantity'],
                    $row['purpose'],
                    $row['delivered_at'] ?? '',
                ]), ';', '"', '');
            }
            fclose($out);
            exit;
        }

        $reportData = [
            'materialConsumption' => report_material_consumption($_GET),
            'stockMovements' => report_stock_movements($_GET),
            'productionByWorkshop' => report_production_by_workshop_filtered($_GET),
            'productionByStudent' => report_production_by_student($_GET),
            'deliveriesInPeriod' => report_deliveries($_GET),
            'summary' => report_summary($_GET),
        ];
    } catch (DomainException $e) {
        flash('error', $e->getMessage());
        redirect_to('/relatorios');
    }

    view('reports/filtered', array_merge([
        'title' => 'Relatórios',
        'rows' => $rows,
        // Inclui registros arquivados para manter o histórico pesquisável.
        'workshops' => query_all('SELECT id,name FROM workshops ORDER BY name'),
        'products' => query_all('SELECT id,name FROM products ORDER BY name'),
        'users' => query_all('SELECT id,name FROM users ORDER BY name'),
        'students' => query_all('SELECT id,full_name AS name FROM students ORDER BY full_name'),
    ], $reportData));
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
        $result = user_create($_POST);
        if ($result) activity_log('Usuário criado', 'Um usuário foi cadastrado pelo administrador.');
        return $result;
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
        $result = user_update($id, $_POST);
        if ($result) activity_log('Usuário atualizado', 'Um usuário foi atualizado pelo administrador.');
        return $result;
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
        $result = user_delete($id);
        if ($result) activity_log('Usuário arquivado', 'Um usuário foi removido pelo administrador.');
        return $result;
    }, 'Usuário arquivado com sucesso.', '/admin');
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