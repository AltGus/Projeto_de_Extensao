<?php

/*
|--------------------------------------------------------------------------
| Compatibilidade com versões antigas do PHP
|--------------------------------------------------------------------------
*/

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }
}

if (!function_exists('text_length')) {
    function text_length(string $text): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($text);
        }

        return strlen($text);
    }
}

if (!function_exists('config_value')) {
    function config_value(string $key, $default = null)
    {
        if (strpos($key, 'app.') === 0) {
            $key = substr($key, 4);
            $config = config_app();
        } elseif (strpos($key, 'database.') === 0) {
            $key = substr($key, 9);
            $config = config_database();
        } else {
            $config = config_app();
        }

        return $config[$key] ?? $default;
    }
}

/*
|--------------------------------------------------------------------------
| Caminhos e configurações
|--------------------------------------------------------------------------
*/

function root_path(string $path = ''): string
{
    $root = dirname(__DIR__);

    if ($path === '') {
        return $root;
    }

    return $root . '/' . ltrim($path, '/');
}

function config_app(): array
{
    static $config = null;

    if ($config === null) {
        $path = root_path('config/app.php');

        if (file_exists($path)) {
            $config = require $path;
        } else {
            $config = [
                'name' => 'Gestão Artesanal',
                'base_url' => '',
                'timezone' => 'America/Sao_Paulo',
            ];
        }
    }

    return $config;
}

function config_database(): array
{
    static $config = null;

    if ($config === null) {
        $path = root_path('config/database.php');

        if (!file_exists($path)) {
            die('Arquivo config/database.php não encontrado.');
        }

        $config = require $path;
    }

    return $config;
}

function app_name(): string
{
    $config = config_app();
    return $config['name'] ?? 'Gestão Artesanal';
}

function base_url(): string
{
    $config = config_app();
    return rtrim($config['base_url'] ?? '', '/');
}

function url(string $path = ''): string
{
    $base = base_url();
    $path = '/' . ltrim($path, '/');

    if ($path === '/') {
        return $base ?: '/';
    }

    return $base . $path;
}

function asset(string $path): string
{
    return url('/assets/' . ltrim($path, '/'));
}

/*
|--------------------------------------------------------------------------
| Banco de dados
|--------------------------------------------------------------------------
*/

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $config = config_database();

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $dbname = $config['dbname'] ?? 'gestao_artesanal';
        $charset = $config['charset'] ?? 'utf8mb4';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die('Erro ao conectar no banco de dados: ' . $e->getMessage());
        }
    }

    return $pdo;
}

function query_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function query_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();

    return $result ?: null;
}

function execute_query(string $sql, array $params = []): bool
{
    $stmt = db()->prepare($sql);
    return $stmt->execute($params);
}

function last_insert_id(): int
{
    return (int) db()->lastInsertId();
}

/*
|--------------------------------------------------------------------------
| Segurança, sessão e mensagens
|--------------------------------------------------------------------------
*/

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $token = $_POST['_token'] ?? '';
    $sessionToken = $_SESSION['_csrf_token'] ?? '';

    if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
        die('Token de segurança inválido.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][$type][] = $message;
}

function flashes(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);

    return $messages;
}

function redirect_to(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    if (!$path) {
        return '/';
    }

    $base = base_url();

    if ($base && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }

    $path = '/' . trim($path, '/');

    return $path === '/' ? '/' : rtrim($path, '/');
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function is_get(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET';
}

/*
|--------------------------------------------------------------------------
| Views
|--------------------------------------------------------------------------
*/

function view(string $view, array $data = [], string $layout = 'app'): void
{
    extract($data);

    $viewFile = root_path('resources/views/pages/' . $view . '.php');

    if (!file_exists($viewFile)) {
        die('View não encontrada: ' . $viewFile);
    }

    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    $layoutFile = root_path('resources/views/layouts/' . $layout . '.php');

    if (!file_exists($layoutFile)) {
        die('Layout não encontrado: ' . $layoutFile);
    }

    require $layoutFile;
}

function component(string $component, array $data = []): void
{
    extract($data);

    $file = root_path('resources/views/components/' . $component . '.php');

    if (file_exists($file)) {
        require $file;
    }
}

/*
|--------------------------------------------------------------------------
| Autenticação
|--------------------------------------------------------------------------
*/

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function user_id(): ?int
{
    return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
}

function is_logged(): bool
{
    return isset($_SESSION['user']);
}

function is_professor(): bool
{
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'professor';
}

function is_aluno(): bool
{
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'aluno';
}

function require_login(): void
{
    if (!is_logged()) {
        flash('error', 'Faça login para acessar o sistema.');
        redirect_to('/login');
    }
}

function require_professor(): void
{
    require_login();

    if (!is_professor()) {
        http_response_code(403);

        view('errors/403', [
            'title' => 'Acesso negado',
        ], 'app');

        exit;
    }
}

function guest_only(): void
{
    if (is_logged()) {
        redirect_to('/dashboard');
    }
}

function auth_attempt(string $email, string $password): bool
{
    $user = query_one(
        "SELECT * FROM users WHERE email = ? LIMIT 1",
        [strtolower(trim($email))]
    );

    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        return false;
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    return true;
}

function auth_register(array $data): bool
{
    $name = trim($data['name'] ?? '');
    $email = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';
    $passwordConfirmation = $data['password_confirmation'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        flash('error', 'Preencha todos os campos obrigatórios.');
        return false;
    }

    if ($password !== $passwordConfirmation) {
        flash('error', 'As senhas não coincidem.');
        return false;
    }

    $exists = query_one("SELECT id FROM users WHERE email = ?", [$email]);

    if ($exists) {
        flash('error', 'Já existe uma conta com este e-mail.');
        return false;
    }

    execute_query(
        "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)",
        [
            $name,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            'aluno',
        ]
    );

    flash('success', 'Cadastro realizado com sucesso. Faça login para continuar.');
    return true;
}

function auth_logout(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

function dashboard_stats(): array
{
    $workshops = query_one("SELECT COUNT(*) AS total FROM workshops");
    $participants = query_one("SELECT COUNT(*) AS total FROM users WHERE role = 'aluno'");
    $products = query_one("SELECT COUNT(*) AS total FROM products");
    $materials = query_one("SELECT COUNT(*) AS total FROM materials");
    $production = query_one("SELECT COALESCE(SUM(quantity), 0) AS total FROM productions");

    return [
        'workshops' => (int) ($workshops['total'] ?? 0),
        'participants' => (int) ($participants['total'] ?? 0),
        'products' => (int) ($products['total'] ?? 0),
        'materials' => (int) ($materials['total'] ?? 0),
        'production' => (int) ($production['total'] ?? 0),
    ];
}

function dashboard_recent_productions(int $limit = 6): array
{
    $limit = max(1, $limit);

    return query_all(
        "SELECT 
            p.*,
            pr.name AS product_name,
            w.name AS workshop_name,
            u.name AS responsible_name
        FROM productions p
        INNER JOIN products pr ON pr.id = p.product_id
        INNER JOIN workshops w ON w.id = p.workshop_id
        LEFT JOIN users u ON u.id = p.responsible_user_id
        ORDER BY p.produced_at DESC, p.id DESC
        LIMIT {$limit}"
    );
}

function dashboard_workshop_ranking(): array
{
    return query_all(
        "SELECT 
            w.id,
            w.name,
            COALESCE(SUM(p.quantity), 0) AS total
        FROM workshops w
        LEFT JOIN productions p ON p.workshop_id = w.id
        GROUP BY w.id, w.name
        ORDER BY total DESC, w.name ASC"
    );
}

function dashboard_low_stock(): array
{
    return query_all(
        "SELECT *
        FROM materials
        WHERE current_quantity <= min_quantity
        ORDER BY current_quantity ASC, name ASC"
    );
}

/*
|--------------------------------------------------------------------------
| Usuários
|--------------------------------------------------------------------------
*/

function users_all(): array
{
    return query_all("SELECT * FROM users ORDER BY name ASC");
}

function users_participants(): array
{
    return query_all("SELECT * FROM users WHERE role = 'aluno' ORDER BY name ASC");
}

function user_find(int $id): ?array
{
    return query_one("SELECT * FROM users WHERE id = ?", [$id]);
}

function user_create(array $data): bool
{
    return execute_query(
        "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)",
        [
            trim($data['name']),
            strtolower(trim($data['email'])),
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 'aluno',
        ]
    );
}

function user_update(int $id, array $data): bool
{
    $user = user_find($id);

    if (!$user) {
        return false;
    }

    $password = trim($data['password'] ?? '');

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $hash = $user['password'];
    }

    return execute_query(
        "UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?",
        [
            trim($data['name']),
            strtolower(trim($data['email'])),
            $hash,
            $data['role'],
            $id,
        ]
    );
}

function user_delete(int $id): bool
{
    return execute_query("DELETE FROM users WHERE id = ?", [$id]);
}

/*
|--------------------------------------------------------------------------
| Oficinas
|--------------------------------------------------------------------------
*/

function workshops_all(): array
{
    return query_all(
        "SELECT 
            w.*,
            COUNT(DISTINCT wu.user_id) AS participants_count,
            COALESCE(SUM(p.quantity), 0) AS total_production
        FROM workshops w
        LEFT JOIN workshop_user wu ON wu.workshop_id = w.id
        LEFT JOIN productions p ON p.workshop_id = w.id
        GROUP BY w.id
        ORDER BY w.name ASC"
    );
}

function workshop_find(int $id): ?array
{
    return query_one("SELECT * FROM workshops WHERE id = ?", [$id]);
}

function workshop_create(array $data): bool
{
    return execute_query(
        "INSERT INTO workshops (name, description, color) VALUES (?, ?, ?)",
        [
            trim($data['name']),
            trim($data['description'] ?? ''),
            $data['color'] ?? '#4f46e5',
        ]
    );
}

function workshop_update(int $id, array $data): bool
{
    return execute_query(
        "UPDATE workshops SET name = ?, description = ?, color = ? WHERE id = ?",
        [
            trim($data['name']),
            trim($data['description'] ?? ''),
            $data['color'] ?? '#4f46e5',
            $id,
        ]
    );
}

function workshop_delete(int $id): bool
{
    return execute_query("DELETE FROM workshops WHERE id = ?", [$id]);
}

function workshop_participants(int $workshopId): array
{
    return query_all(
        "SELECT u.*
        FROM users u
        INNER JOIN workshop_user wu ON wu.user_id = u.id
        WHERE wu.workshop_id = ?
        ORDER BY u.name ASC",
        [$workshopId]
    );
}

function workshop_available_participants(int $workshopId): array
{
    return query_all(
        "SELECT *
        FROM users
        WHERE role = 'aluno'
        AND id NOT IN (
            SELECT user_id FROM workshop_user WHERE workshop_id = ?
        )
        ORDER BY name ASC",
        [$workshopId]
    );
}

function workshop_add_participant(int $workshopId, int $userId): bool
{
    $exists = query_one(
        "SELECT id FROM workshop_user WHERE workshop_id = ? AND user_id = ?",
        [$workshopId, $userId]
    );

    if ($exists) {
        return true;
    }

    return execute_query(
        "INSERT INTO workshop_user (workshop_id, user_id) VALUES (?, ?)",
        [$workshopId, $userId]
    );
}

function workshop_remove_participant(int $workshopId, int $userId): bool
{
    return execute_query(
        "DELETE FROM workshop_user WHERE workshop_id = ? AND user_id = ?",
        [$workshopId, $userId]
    );
}

/*
|--------------------------------------------------------------------------
| Produtos Artesanais
|--------------------------------------------------------------------------
*/

function products_all(): array
{
    return query_all(
        "SELECT 
            p.*,
            w.name AS workshop_name
        FROM products p
        LEFT JOIN workshops w ON w.id = p.workshop_id
        ORDER BY p.name ASC"
    );
}

function product_find(int $id): ?array
{
    return query_one("SELECT * FROM products WHERE id = ?", [$id]);
}

function product_create(array $data): bool
{
    $workshopId = !empty($data['workshop_id']) ? (int) $data['workshop_id'] : null;

    return execute_query(
        "INSERT INTO products (name, description, category, workshop_id) VALUES (?, ?, ?, ?)",
        [
            trim($data['name']),
            trim($data['description'] ?? ''),
            trim($data['category']),
            $workshopId,
        ]
    );
}

function product_update(int $id, array $data): bool
{
    $workshopId = !empty($data['workshop_id']) ? (int) $data['workshop_id'] : null;

    return execute_query(
        "UPDATE products SET name = ?, description = ?, category = ?, workshop_id = ? WHERE id = ?",
        [
            trim($data['name']),
            trim($data['description'] ?? ''),
            trim($data['category']),
            $workshopId,
            $id,
        ]
    );
}

function product_delete(int $id): bool
{
    return execute_query("DELETE FROM products WHERE id = ?", [$id]);
}

/*
|--------------------------------------------------------------------------
| Produções
|--------------------------------------------------------------------------
*/

function productions_all(): array
{
    return query_all(
        "SELECT
            p.*,
            pr.name AS product_name,
            w.name AS workshop_name,
            u.name AS responsible_name
        FROM productions p
        INNER JOIN products pr ON pr.id = p.product_id
        INNER JOIN workshops w ON w.id = p.workshop_id
        LEFT JOIN users u ON u.id = p.responsible_user_id
        ORDER BY p.produced_at DESC, p.id DESC"
    );
}

function production_find(int $id): ?array
{
    return query_one("SELECT * FROM productions WHERE id = ?", [$id]);
}

function production_create(array $data): bool
{
    $description = trim($data['description'] ?? '');

    if (text_length($description) < 50) {
        flash('error', 'A descrição da produção deve ter no mínimo 50 caracteres.');
        return false;
    }

    return execute_query(
        "INSERT INTO productions 
        (product_id, workshop_id, quantity, produced_at, responsible_user_id, purpose, description)
        VALUES (?, ?, ?, ?, ?, ?, ?)",
        [
            (int) $data['product_id'],
            (int) $data['workshop_id'],
            (int) $data['quantity'],
            $data['produced_at'],
            !empty($data['responsible_user_id']) ? (int) $data['responsible_user_id'] : user_id(),
            trim($data['purpose']),
            $description,
        ]
    );
}

function production_update(int $id, array $data): bool
{
    $description = trim($data['description'] ?? '');

    if (text_length($description) < 50) {
        flash('error', 'A descrição da produção deve ter no mínimo 50 caracteres.');
        return false;
    }

    return execute_query(
        "UPDATE productions 
        SET product_id = ?, workshop_id = ?, quantity = ?, produced_at = ?, responsible_user_id = ?, purpose = ?, description = ?
        WHERE id = ?",
        [
            (int) $data['product_id'],
            (int) $data['workshop_id'],
            (int) $data['quantity'],
            $data['produced_at'],
            !empty($data['responsible_user_id']) ? (int) $data['responsible_user_id'] : user_id(),
            trim($data['purpose']),
            $description,
            $id,
        ]
    );
}

function production_delete(int $id): bool
{
    return execute_query("DELETE FROM productions WHERE id = ?", [$id]);
}

function productions_by_workshop(int $workshopId): array
{
    return query_all(
        "SELECT
            p.*,
            pr.name AS product_name,
            u.name AS responsible_name
        FROM productions p
        INNER JOIN products pr ON pr.id = p.product_id
        LEFT JOIN users u ON u.id = p.responsible_user_id
        WHERE p.workshop_id = ?
        ORDER BY p.produced_at DESC",
        [$workshopId]
    );
}

/*
|--------------------------------------------------------------------------
| Materiais
|--------------------------------------------------------------------------
*/

function materials_all(): array
{
    return query_all(
        "SELECT 
            m.*,
            CASE
                WHEN m.applies_to_all = 1 THEN 'Todas as oficinas'
                ELSE COALESCE(
                    (
                        SELECT GROUP_CONCAT(w.name ORDER BY w.name SEPARATOR ', ')
                        FROM material_workshop mw
                        INNER JOIN workshops w ON w.id = mw.workshop_id
                        WHERE mw.material_id = m.id
                    ),
                    'Nenhuma oficina'
                )
            END AS workshop_destinations
        FROM materials m
        ORDER BY m.category ASC, m.name ASC"
    );
}

function material_find(int $id): ?array
{
    return query_one("SELECT * FROM materials WHERE id = ?", [$id]);
}

function material_selected_workshops(int $materialId): array
{
    $rows = query_all(
        "SELECT workshop_id 
        FROM material_workshop 
        WHERE material_id = ?",
        [$materialId]
    );

    return array_map(function ($row) {
        return (int) $row['workshop_id'];
    }, $rows);
}

function material_create(array $data): bool
{
    $appliesToAll = isset($data['applies_to_all']) ? 1 : 0;
    $workshopIds = $data['workshop_ids'] ?? [];

    if ($appliesToAll === 0 && empty($workshopIds)) {
        flash('error', 'Selecione pelo menos uma oficina ou marque a opção "todas as oficinas".');
        return false;
    }

    try {
        db()->beginTransaction();

        execute_query(
            "INSERT INTO materials (name, category, unit, current_quantity, min_quantity, applies_to_all)
            VALUES (?, ?, ?, ?, ?, ?)",
            [
                trim($data['name']),
                trim($data['category']),
                trim($data['unit'] ?? 'un'),
                (int) ($data['current_quantity'] ?? 0),
                (int) ($data['min_quantity'] ?? 0),
                $appliesToAll,
            ]
        );

        $materialId = last_insert_id();

        if ($appliesToAll === 0) {
            foreach ($workshopIds as $workshopId) {
                execute_query(
                    "INSERT IGNORE INTO material_workshop (material_id, workshop_id)
                    VALUES (?, ?)",
                    [$materialId, (int) $workshopId]
                );
            }
        }

        db()->commit();
        return true;
    } catch (Exception $e) {
        db()->rollBack();
        flash('error', 'Erro ao cadastrar material: ' . $e->getMessage());
        return false;
    }
}

function material_update(int $id, array $data): bool
{
    $appliesToAll = isset($data['applies_to_all']) ? 1 : 0;
    $workshopIds = $data['workshop_ids'] ?? [];

    if ($appliesToAll === 0 && empty($workshopIds)) {
        flash('error', 'Selecione pelo menos uma oficina ou marque a opção "todas as oficinas".');
        return false;
    }

    try {
        db()->beginTransaction();

        execute_query(
            "UPDATE materials
            SET name = ?, category = ?, unit = ?, current_quantity = ?, min_quantity = ?, applies_to_all = ?
            WHERE id = ?",
            [
                trim($data['name']),
                trim($data['category']),
                trim($data['unit'] ?? 'un'),
                (int) ($data['current_quantity'] ?? 0),
                (int) ($data['min_quantity'] ?? 0),
                $appliesToAll,
                $id,
            ]
        );

        execute_query(
            "DELETE FROM material_workshop WHERE material_id = ?",
            [$id]
        );

        if ($appliesToAll === 0) {
            foreach ($workshopIds as $workshopId) {
                execute_query(
                    "INSERT IGNORE INTO material_workshop (material_id, workshop_id)
                    VALUES (?, ?)",
                    [$id, (int) $workshopId]
                );
            }
        }

        db()->commit();
        return true;
    } catch (Exception $e) {
        db()->rollBack();
        flash('error', 'Erro ao atualizar material: ' . $e->getMessage());
        return false;
    }
}

function material_delete(int $id): bool
{
    return execute_query("DELETE FROM materials WHERE id = ?", [$id]);
}

/*
|--------------------------------------------------------------------------
| Estoque
|--------------------------------------------------------------------------
*/

function stock_movements_all(): array
{
    return query_all(
        "SELECT
            sm.*,
            m.name AS material_name,
            m.unit,
            u.name AS user_name
        FROM stock_movements sm
        INNER JOIN materials m ON m.id = sm.material_id
        LEFT JOIN users u ON u.id = sm.user_id
        ORDER BY sm.movement_date DESC, sm.id DESC"
    );
}

function stock_movement_find(int $id): ?array
{
    return query_one("SELECT * FROM stock_movements WHERE id = ?", [$id]);
}

function stock_create_movement(array $data): bool
{
    $materialId = (int) $data['material_id'];
    $type = $data['movement_type'];
    $quantity = (int) $data['quantity'];

    $material = material_find($materialId);

    if (!$material) {
        flash('error', 'Material não encontrado.');
        return false;
    }

    if ($quantity <= 0) {
        flash('error', 'A quantidade precisa ser maior que zero.');
        return false;
    }

    $newQuantity = (int) $material['current_quantity'];

    if ($type === 'entrada') {
        $newQuantity += $quantity;
    } elseif ($type === 'saida') {
        $newQuantity -= $quantity;
    } else {
        flash('error', 'Tipo de movimentação inválido.');
        return false;
    }

    if ($newQuantity < 0) {
        flash('error', 'Não é possível deixar o estoque negativo.');
        return false;
    }

    try {
        db()->beginTransaction();

        execute_query(
            "INSERT INTO stock_movements 
            (material_id, movement_type, quantity, notes, movement_date, user_id)
            VALUES (?, ?, ?, ?, ?, ?)",
            [
                $materialId,
                $type,
                $quantity,
                trim($data['notes'] ?? ''),
                $data['movement_date'],
                user_id(),
            ]
        );

        execute_query(
            "UPDATE materials SET current_quantity = ? WHERE id = ?",
            [$newQuantity, $materialId]
        );

        db()->commit();
        return true;
    } catch (Exception $e) {
        db()->rollBack();
        flash('error', 'Erro ao movimentar estoque: ' . $e->getMessage());
        return false;
    }
}

function stock_update_movement(int $id, array $data): bool
{
    $oldMovement = stock_movement_find($id);

    if (!$oldMovement) {
        flash('error', 'Movimentação não encontrada.');
        return false;
    }

    $oldMaterial = material_find((int) $oldMovement['material_id']);

    if (!$oldMaterial) {
        flash('error', 'Material antigo não encontrado.');
        return false;
    }

    $newMaterialId = (int) $data['material_id'];
    $newMaterial = material_find($newMaterialId);

    if (!$newMaterial) {
        flash('error', 'Novo material não encontrado.');
        return false;
    }

    $newType = $data['movement_type'];
    $newQuantity = (int) $data['quantity'];

    if ($newQuantity <= 0) {
        flash('error', 'A quantidade precisa ser maior que zero.');
        return false;
    }

    try {
        db()->beginTransaction();

        $oldMaterialQuantity = (int) $oldMaterial['current_quantity'];
        $oldQuantity = (int) $oldMovement['quantity'];

        if ($oldMovement['movement_type'] === 'entrada') {
            $oldMaterialQuantity -= $oldQuantity;
        } else {
            $oldMaterialQuantity += $oldQuantity;
        }

        if ($oldMaterialQuantity < 0) {
            throw new Exception('A reversão da movimentação antiga deixaria o estoque negativo.');
        }

        execute_query(
            "UPDATE materials SET current_quantity = ? WHERE id = ?",
            [$oldMaterialQuantity, $oldMovement['material_id']]
        );

        $newMaterialQuantity = (int) $newMaterial['current_quantity'];

        if ((int) $oldMovement['material_id'] === $newMaterialId) {
            $newMaterialQuantity = $oldMaterialQuantity;
        }

        if ($newType === 'entrada') {
            $newMaterialQuantity += $newQuantity;
        } elseif ($newType === 'saida') {
            $newMaterialQuantity -= $newQuantity;
        } else {
            throw new Exception('Tipo de movimentação inválido.');
        }

        if ($newMaterialQuantity < 0) {
            throw new Exception('A alteração deixaria o estoque negativo.');
        }

        execute_query(
            "UPDATE materials SET current_quantity = ? WHERE id = ?",
            [$newMaterialQuantity, $newMaterialId]
        );

        execute_query(
            "UPDATE stock_movements
            SET material_id = ?, movement_type = ?, quantity = ?, notes = ?, movement_date = ?
            WHERE id = ?",
            [
                $newMaterialId,
                $newType,
                $newQuantity,
                trim($data['notes'] ?? ''),
                $data['movement_date'],
                $id,
            ]
        );

        db()->commit();
        return true;
    } catch (Exception $e) {
        db()->rollBack();
        flash('error', 'Erro ao atualizar movimentação: ' . $e->getMessage());
        return false;
    }
}

function stock_delete_movement(int $id): bool
{
    $movement = stock_movement_find($id);

    if (!$movement) {
        return false;
    }

    try {
        db()->beginTransaction();

        $material = material_find((int) $movement['material_id']);
        $current = (int) $material['current_quantity'];
        $quantity = (int) $movement['quantity'];

        if ($movement['movement_type'] === 'entrada') {
            $newQuantity = $current - $quantity;
        } else {
            $newQuantity = $current + $quantity;
        }

        if ($newQuantity < 0) {
            throw new Exception('A exclusão deixaria o estoque negativo.');
        }

        execute_query("DELETE FROM stock_movements WHERE id = ?", [$id]);

        execute_query(
            "UPDATE materials SET current_quantity = ? WHERE id = ?",
            [$newQuantity, $movement['material_id']]
        );

        db()->commit();
        return true;
    } catch (Exception $e) {
        db()->rollBack();
        flash('error', 'Erro ao excluir movimentação: ' . $e->getMessage());
        return false;
    }
}

/*
|--------------------------------------------------------------------------
| Relatórios
|--------------------------------------------------------------------------
*/

function report_production_by_workshop(): array
{
    return dashboard_workshop_ranking();
}

function report_material_consumption(): array
{
    return query_all(
        "SELECT
            m.name AS material_name,
            COALESCE(SUM(CASE WHEN sm.movement_type = 'entrada' THEN sm.quantity ELSE 0 END), 0) AS total_entrada,
            COALESCE(SUM(CASE WHEN sm.movement_type = 'saida' THEN sm.quantity ELSE 0 END), 0) AS total_saida
        FROM materials m
        LEFT JOIN stock_movements sm ON sm.material_id = m.id
        GROUP BY m.id, m.name
        ORDER BY m.name ASC"
    );
}

function report_recent_activities(): array
{
    return query_all(
        "SELECT
            al.*,
            u.name AS user_name
        FROM activity_logs al
        LEFT JOIN users u ON u.id = al.user_id
        ORDER BY al.created_at DESC
        LIMIT 20"
    );
}

/*
|--------------------------------------------------------------------------
| Logs do sistema
|--------------------------------------------------------------------------
*/

function activity_log(string $action, string $description): void
{
    execute_query(
        "INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)",
        [
            user_id(),
            $action,
            $description,
        ]
    );
}