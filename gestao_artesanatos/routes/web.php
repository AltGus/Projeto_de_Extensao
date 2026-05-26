<?php
// routes/web.php
$router->get('/', function (): void {
    redirect_to(Auth::check() ? '/dashboard' : '/login');
});

$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest']);
$router->get('/cadastro', [AuthController::class, 'showRegister'], ['guest']);
$router->post('/cadastro', [AuthController::class, 'register'], ['guest']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth']);

$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);

$router->get('/oficinas', [WorkshopController::class, 'index'], ['auth']);
$router->get('/oficinas/criar', [WorkshopController::class, 'create'], ['admin']);
$router->post('/oficinas', [WorkshopController::class, 'store'], ['admin']);
$router->get('/oficinas/{id}', [WorkshopController::class, 'show'], ['auth']);
$router->get('/oficinas/{id}/editar', [WorkshopController::class, 'edit'], ['admin']);
$router->post('/oficinas/{id}/atualizar', [WorkshopController::class, 'update'], ['admin']);
$router->post('/oficinas/{id}/excluir', [WorkshopController::class, 'delete'], ['admin']);
$router->post('/oficinas/{id}/participantes', [WorkshopController::class, 'addParticipant'], ['admin']);
$router->post('/oficinas/{id}/participantes/{userId}/remover', [WorkshopController::class, 'removeParticipant'], ['admin']);

$router->get('/produtos', [ProductController::class, 'index'], ['auth']);
$router->get('/produtos/criar', [ProductController::class, 'create'], ['admin']);
$router->post('/produtos', [ProductController::class, 'store'], ['admin']);
$router->get('/produtos/{id}/editar', [ProductController::class, 'edit'], ['admin']);
$router->post('/produtos/{id}/atualizar', [ProductController::class, 'update'], ['admin']);
$router->post('/produtos/{id}/excluir', [ProductController::class, 'delete'], ['admin']);

$router->get('/materiais', [MaterialController::class, 'index'], ['auth']);
$router->get('/materiais/criar', [MaterialController::class, 'create'], ['admin']);
$router->post('/materiais', [MaterialController::class, 'store'], ['admin']);
$router->get('/materiais/{id}/editar', [MaterialController::class, 'edit'], ['admin']);
$router->post('/materiais/{id}/atualizar', [MaterialController::class, 'update'], ['admin']);
$router->post('/materiais/{id}/excluir', [MaterialController::class, 'delete'], ['admin']);

$router->get('/producoes', [ProductionController::class, 'index'], ['auth']);
$router->get('/producoes/criar', [ProductionController::class, 'create'], ['auth']);
$router->post('/producoes', [ProductionController::class, 'store'], ['auth']);
$router->get('/producoes/{id}/editar', [ProductionController::class, 'edit'], ['admin']);
$router->post('/producoes/{id}/atualizar', [ProductionController::class, 'update'], ['admin']);
$router->post('/producoes/{id}/excluir', [ProductionController::class, 'delete'], ['admin']);

$router->get('/estoque', [StockController::class, 'index'], ['auth']);
$router->get('/estoque/criar', [StockController::class, 'create'], ['admin']);
$router->post('/estoque', [StockController::class, 'store'], ['admin']);
$router->get('/estoque/{id}/editar', [StockController::class, 'edit'], ['admin']);
$router->post('/estoque/{id}/atualizar', [StockController::class, 'update'], ['admin']);
$router->post('/estoque/{id}/excluir', [StockController::class, 'delete'], ['admin']);

$router->get('/relatorios', [ReportController::class, 'index'], ['admin']);

$router->get('/admin', [AdminController::class, 'index'], ['admin']);
$router->get('/admin/usuarios/criar', [AdminController::class, 'createUser'], ['admin']);
$router->post('/admin/usuarios', [AdminController::class, 'storeUser'], ['admin']);
$router->get('/admin/usuarios/{id}/editar', [AdminController::class, 'editUser'], ['admin']);
$router->post('/admin/usuarios/{id}/atualizar', [AdminController::class, 'updateUser'], ['admin']);
$router->post('/admin/usuarios/{id}/excluir', [AdminController::class, 'deleteUser'], ['admin']);
