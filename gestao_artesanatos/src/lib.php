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
            $pdo->exec("SET time_zone = '" . date('P') . "'");
        } catch (PDOException $e) {
            throw $e;
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

    if (!is_string($token) || !$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
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
        throw new RuntimeException('View ausente');
    }

    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    $layoutFile = root_path('resources/views/layouts/' . $layout . '.php');

    if (!file_exists($layoutFile)) {
        throw new RuntimeException('Layout ausente');
    }

    require $layoutFile;
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
    refresh_session_user();
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
        "SELECT * FROM users WHERE email = ? AND active = 1 LIMIT 1",
        [strtolower(trim($email))]
    );

    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        return false;
    }

    if (session_status() === PHP_SESSION_ACTIVE) session_regenerate_id(true);

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

    if (!validate_user($data + ['role' => 'aluno'])) return false;

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
    if (session_status() === PHP_SESSION_ACTIVE) session_regenerate_id(true);
}

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

function dashboard_stats(): array
{
    $workshops = query_one("SELECT COUNT(*) AS total FROM workshops WHERE active = 1");
    $participants = query_one("SELECT COUNT(*) AS total FROM students WHERE active = 1");
    $products = query_one("SELECT COUNT(*) AS total FROM products WHERE active = 1");
    $materials = query_one("SELECT COUNT(*) AS total FROM materials WHERE active = 1");
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
            u.name AS responsible_name,
            s.full_name AS student_name
        FROM productions p
        INNER JOIN products pr ON pr.id = p.product_id
        INNER JOIN workshops w ON w.id = p.workshop_id
        LEFT JOIN users u ON u.id = p.responsible_user_id
        LEFT JOIN students s ON s.id = p.student_id
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
        WHERE active = 1 AND current_quantity <= min_quantity
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
    return query_all("SELECT * FROM users WHERE active = 1 ORDER BY name ASC");
}

function users_participants(): array
{
    return query_all("SELECT * FROM users WHERE active = 1 AND role = 'aluno' ORDER BY name ASC");
}

function user_find(int $id): ?array
{
    return query_one("SELECT * FROM users WHERE id = ?", [$id]);
}

function user_create(array $data): bool
{
    if (!validate_user($data)) return false;
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
    if (!validate_user($data, $id)) return false;
    if ($id === user_id() && ($data['role'] ?? '') !== 'professor') {
        flash('error', 'Outro professor deve alterar seu perfil.');
        return false;
    }
    $user = user_find($id);

    if (!$user) {
        return false;
    }

    $password = $data['password'] ?? '';

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
    return execute_query("UPDATE users SET active = 0 WHERE id = ?", [$id]);
}

/*
|--------------------------------------------------------------------------
| Oficinas
|--------------------------------------------------------------------------
*/

function workshops_all(): array
{
    return query_all("SELECT w.*, u.name AS responsible_name,
        (SELECT COUNT(*) FROM student_workshop sw JOIN students s ON s.id=sw.student_id WHERE sw.workshop_id=w.id AND s.active=1) AS participants_count,
        (SELECT COALESCE(SUM(p.quantity),0) FROM productions p WHERE p.workshop_id=w.id) AS total_production
        FROM workshops w LEFT JOIN users u ON u.id=w.responsible_user_id
        WHERE w.active=1 ORDER BY w.name");
}

function workshop_find(int $id): ?array
{
    return query_one("SELECT w.*, u.name AS responsible_name FROM workshops w LEFT JOIN users u ON u.id=w.responsible_user_id WHERE w.id = ?", [$id]);
}

function users_orientators(): array
{
    return query_all("SELECT id,name,email,role FROM users WHERE active=1 AND role='professor' ORDER BY name");
}

function workshop_image_upload(?string $oldPath = null): ?string
{
    if (empty($_FILES['image']['name'])) return $oldPath;
    if (!isset($_FILES['image']['error']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) throw new DomainException('Não foi possível receber a imagem da oficina.');
    if (($_FILES['image']['size'] ?? 0) > 5 * 1024 * 1024) throw new DomainException('A imagem deve ter no máximo 5 MB.');
    $tmp = $_FILES['image']['tmp_name'];
    $info = @getimagesize($tmp);
    if (!$info || !in_array($info[2], [IMAGETYPE_JPEG], true)) throw new DomainException('A imagem da oficina deve ser JPEG/JPG.');
    if ($info[0] * $info[1] > 12000000) throw new DomainException('A imagem deve ter no máximo 12 megapixels.');
    if (!function_exists('imagecreatefromjpeg')) throw new DomainException('Ative a extensão GD do PHP para enviar imagens.');
    $dir = root_path('public/uploads/workshops');
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Falha ao preparar diretório de imagens.');
    $filename = bin2hex(random_bytes(12)) . '.jpg';
    $dest = $dir . '/' . $filename;
    if (function_exists('imagecreatefromjpeg') && function_exists('imagecreatetruecolor')) {
        $src = @imagecreatefromjpeg($tmp);
        if (!$src) throw new DomainException('JPEG inválido.');
        $sw=imagesx($src); $sh=imagesy($src); $tw=640; $th=360;
        $ratio=max($tw/$sw,$th/$sh); $rw=(int)ceil($tw/$ratio); $rh=(int)ceil($th/$ratio);
        $sx=max(0,(int)(($sw-$rw)/2)); $sy=max(0,(int)(($sh-$rh)/2));
        $dst=imagecreatetruecolor($tw,$th);
        imagecopyresampled($dst,$src,0,0,$sx,$sy,$tw,$th,$rw,$rh);
        $saved = imagejpeg($dst,$dest,86); imagedestroy($dst); imagedestroy($src);
        if (!$saved) throw new RuntimeException('Falha ao salvar imagem.');
    } else {
        throw new DomainException('Ative a extensão GD do PHP para enviar imagens.');
    }
    return '/uploads/workshops/' . $filename;
}

function workshop_create(array $data): bool
{
    $responsible = !empty($data['responsible_user_id']) ? (int)$data['responsible_user_id'] : null;
    if ($responsible) { $u=user_find($responsible); if (!$u || !$u['active'] || $u['role']!=='professor') throw new DomainException('Selecione um orientador responsável válido.'); }
    $image = workshop_image_upload();
    return execute_query("INSERT INTO workshops (name, description, responsible_user_id, image_path, color) VALUES (?, ?, ?, ?, ?)", [trim($data['name']), trim($data['description'] ?? ''), $responsible, $image, $data['color'] ?? '#4f46e5']);
}

function workshop_update(int $id, array $data): bool
{
    $current=workshop_find($id); if(!$current) return false;
    $responsible = !empty($data['responsible_user_id']) ? (int)$data['responsible_user_id'] : null;
    if ($responsible) { $u=user_find($responsible); if (!$u || !$u['active'] || $u['role']!=='professor') throw new DomainException('Selecione um orientador responsável válido.'); }
    $image = workshop_image_upload($current['image_path'] ?? null);
    return execute_query("UPDATE workshops SET name=?, description=?, responsible_user_id=?, image_path=?, color=? WHERE id=?", [trim($data['name']),trim($data['description']??''),$responsible,$image,$data['color']??'#4f46e5',$id]);
}

function workshop_delete(int $id): bool { return execute_query("UPDATE workshops SET active = 0 WHERE id = ?", [$id]); }
function workshop_participants(int $workshopId): array { return query_all("SELECT s.* FROM students s JOIN student_workshop sw ON sw.student_id=s.id WHERE s.active=1 AND sw.workshop_id=? ORDER BY s.full_name",[$workshopId]); }
function workshop_available_participants(int $workshopId): array { return query_all("SELECT * FROM students WHERE active=1 AND id NOT IN (SELECT student_id FROM student_workshop WHERE workshop_id=?) ORDER BY full_name",[$workshopId]); }
function workshop_add_participant(int $workshopId, int $studentId): bool { $s=student_find($studentId); if(!$s||!$s['active']){flash('error','Selecione um aluno ativo.');return false;} return execute_query("INSERT IGNORE INTO student_workshop(student_id,workshop_id) VALUES(?,?)",[$studentId,$workshopId]); }
function workshop_remove_participant(int $workshopId, int $studentId): bool { return execute_query("DELETE FROM student_workshop WHERE workshop_id=? AND student_id=?",[$workshopId,$studentId]); }

/* Alunos / crianças: registros administrativos sem login. */
function students_all(string $search=''): array {
    if ($search!=='') return query_all("SELECT s.*, (SELECT COUNT(*) FROM productions p WHERE p.student_id=s.id) total_works FROM students s WHERE s.active=1 AND s.full_name LIKE ? ORDER BY s.full_name",['%'.$search.'%']);
    return query_all("SELECT s.*, (SELECT COUNT(*) FROM productions p WHERE p.student_id=s.id) total_works FROM students s WHERE s.active=1 ORDER BY s.full_name");
}
function student_find(int $id): ?array { return query_one("SELECT * FROM students WHERE id=?",[$id]); }
function student_validate(array $data): bool
{
    if (trim($data['full_name']??'')==='' || text_length(trim($data['full_name']))>160 || trim($data['guardian_name']??'')==='' || text_length(trim($data['guardian_name']))>200) {
        flash('error','Informe nome do aluno e responsável.'); return false;
    }
    foreach (['father_phone','mother_phone'] as $field) {
        $phone=trim($data[$field]??''); $digits=preg_replace('/\D/','',$phone);
        if ($phone!=='' && (strlen($phone)>30 || strlen($digits)<8 || strlen($digits)>15)) { flash('error','Informe telefone válido ou deixe em branco.'); return false; }
    }
    return true;
}
function student_sync_workshops(int $id,array $ids): void
{
    $selected=array_unique(array_map('intval',$ids));
    foreach ($selected as $wid) {
        $workshop=workshop_find($wid);
        if (!$workshop || !$workshop['active']) throw new DomainException('Selecione apenas oficinas ativas.');
    }
    // Preserve existing archived memberships; the active form does not display them.
    execute_query('DELETE sw FROM student_workshop sw JOIN workshops w ON w.id=sw.workshop_id WHERE sw.student_id=? AND w.active=1',[$id]);
    foreach ($selected as $wid) execute_query('INSERT INTO student_workshop(student_id,workshop_id) VALUES(?,?) ON DUPLICATE KEY UPDATE student_id=VALUES(student_id)',[$id,$wid]);
}
function student_create(array $data): bool
{
    if (!student_validate($data)) return false;
    return atomic_change(function () use ($data) {
        execute_query('INSERT INTO students(full_name,guardian_name,father_phone,mother_phone,notes) VALUES(?,?,?,?,?)',[trim($data['full_name']),trim($data['guardian_name']),trim($data['father_phone']??'')?:null,trim($data['mother_phone']??'')?:null,trim($data['notes']??'')]);
        student_sync_workshops(last_insert_id(),$data['workshop_ids']??[]);return true;
    });
}
function student_update(int $id,array $data): bool
{
    if (!student_validate($data)) return false;
    return atomic_change(function () use ($id,$data) {
        if (!query_one('SELECT id FROM students WHERE id=? AND active=1 FOR UPDATE',[$id])) return false;
        execute_query('UPDATE students SET full_name=?,guardian_name=?,father_phone=?,mother_phone=?,notes=? WHERE id=?',[trim($data['full_name']),trim($data['guardian_name']),trim($data['father_phone']??'')?:null,trim($data['mother_phone']??'')?:null,trim($data['notes']??''),$id]);
        student_sync_workshops($id,$data['workshop_ids']??[]);return true;
    });
}
function student_delete(int $id): bool { return execute_query('UPDATE students SET active=0 WHERE id=?',[$id]); }
function student_workshops(int $id): array { return query_all('SELECT w.* FROM workshops w JOIN student_workshop sw ON sw.workshop_id=w.id WHERE sw.student_id=? ORDER BY w.name',[$id]); }
function student_selected_workshops(int $id): array { return array_map(fn($r)=>(int)$r['workshop_id'],query_all('SELECT workshop_id FROM student_workshop WHERE student_id=?',[$id])); }
function student_productions(int $id, ?int $workshopId=null): array { $sql="SELECT p.*,pr.name product_name,w.name workshop_name,d.id delivery_id,d.delivered_at FROM productions p JOIN products pr ON pr.id=p.product_id JOIN workshops w ON w.id=p.workshop_id LEFT JOIN deliveries d ON d.production_id=p.id WHERE p.student_id=?"; $v=[$id]; if($workshopId){$sql.=' AND p.workshop_id=?';$v[]=$workshopId;} return query_all($sql.' ORDER BY p.work_number ASC,p.produced_at ASC,p.id ASC',$v); }
function student_delivery_state(int $id, bool $locking=false): array
{
    $lock=$locking?' FOR UPDATE':'';
    $works=(int)query_one('SELECT COUNT(*) n FROM productions WHERE student_id=?'.$lock,[$id])['n'];
    $done=(int)query_one('SELECT COUNT(*) n FROM deliveries WHERE student_id=?'.$lock,[$id])['n'];
    return ['works'=>$works,'earned_cycles'=>intdiv($works,4),'delivered_cycles'=>$done,'pending_cycles'=>max(0,intdiv($works,4)-$done),'next_cycle'=>$done+1];
}
function student_delivery_candidates(int $id): array { return query_all("SELECT p.*,pr.name product_name FROM productions p JOIN products pr ON pr.id=p.product_id LEFT JOIN deliveries d ON d.production_id=p.id WHERE p.student_id=? AND p.availability_status='disponivel' AND d.id IS NULL ORDER BY p.work_number,p.id",[$id]); }
function delivery_mark(int $studentId, int $productionId, string $notes = ''): bool
{
    $pdo = db();
    $ownsTransaction = !$pdo->inTransaction();

    if ($ownsTransaction) {
        $pdo->beginTransaction();
    }

    try {
        // Serializa entregas do mesmo aluno. Assim o cálculo do ciclo ocorre
        // dentro da mesma transação que grava a entrega.
        $student = query_one('SELECT id FROM students WHERE id=? AND active=1 FOR UPDATE', [$studentId]);
        if (!$student) {
            throw new DomainException('Aluno não encontrado ou inativo.');
        }

        $state = student_delivery_state($studentId,true);
        if ($state['pending_cycles'] < 1) {
            throw new DomainException('Este aluno não possui ciclo de quatro trabalhos pendente.');
        }

        $production = query_one('SELECT * FROM productions WHERE id=? FOR UPDATE', [$productionId]);
        if (!$production || (int)$production['student_id'] !== $studentId) {
            throw new DomainException('A produção não pertence a este aluno.');
        }
        if ($production['produced_at'] > date('Y-m-d')) throw new DomainException('Não é possível entregar trabalho com data futura.');
        if ($production['availability_status'] !== 'disponivel') {
            throw new DomainException('Somente produção disponível pode ser entregue.');
        }
        if (query_one('SELECT id FROM deliveries WHERE production_id=? FOR UPDATE', [$productionId])) {
            throw new DomainException('Esta produção já foi entregue.');
        }

        $ok = execute_query(
            'INSERT INTO deliveries(student_id,production_id,cycle_number,delivered_at,delivered_by_user_id,notes) VALUES(?,?,?,NOW(),?,?)',
            [$studentId, $productionId, $state['next_cycle'], user_id(), trim($notes)]
        );

        if (!$ok) {
            if ($ownsTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }

        if ($ownsTransaction) {
            $pdo->commit();
        }

        return true;
    } catch (Throwable $e) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
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
        WHERE p.active = 1
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
    return execute_query("UPDATE products SET active = 0 WHERE id = ?", [$id]);
}

/*
|--------------------------------------------------------------------------
| Produções
|--------------------------------------------------------------------------
*/

function productions_all(): array
{
    return query_all("SELECT p.*,pr.name product_name,w.name workshop_name,u.name responsible_name,s.full_name student_name,d.id delivery_id,d.delivered_at
        FROM productions p JOIN products pr ON pr.id=p.product_id JOIN workshops w ON w.id=p.workshop_id
        LEFT JOIN users u ON u.id=p.responsible_user_id LEFT JOIN students s ON s.id=p.student_id LEFT JOIN deliveries d ON d.production_id=p.id
        ORDER BY p.produced_at DESC,p.id DESC");
}
function production_find(int $id): ?array { return query_one('SELECT * FROM productions WHERE id=?',[$id]); }
function production_is_delivered(int $id): bool { return query_one('SELECT id FROM deliveries WHERE production_id=? LIMIT 1',[$id]) !== null; }
function next_work_number(int $studentId): int
{
    $student=query_one('SELECT last_work_number FROM students WHERE id=? AND active=1 FOR UPDATE',[$studentId]);
    if (!$student) throw new DomainException('Aluno não encontrado ou inativo.');
    $next=(int)$student['last_work_number']+1;
    execute_query('UPDATE students SET last_work_number=? WHERE id=?',[$next,$studentId]);
    return $next;
}
function production_create(array $data): bool
{
    $description = trim($data['description'] ?? '');
    if (text_length($description) < 10) {
        flash('error', 'A descrição deve ter pelo menos 10 caracteres.');
        return false;
    }

    $pdo = db();
    $ownsTransaction = !$pdo->inTransaction();

    if ($ownsTransaction) {
        $pdo->beginTransaction();
    }

    try {
        $studentId = (int)($data['student_id'] ?? 0);
        $number = next_work_number($studentId);
        $data=validate_production($data);
        if ($data===null) { if ($ownsTransaction) $pdo->rollBack(); return false; }

        $ok = execute_query(
            'INSERT INTO productions(product_id,workshop_id,student_id,work_number,availability_status,quantity,produced_at,responsible_user_id,purpose,description) VALUES(?,?,?,?,?,?,?,?,?,?)',
            [
                (int)$data['product_id'],
                (int)$data['workshop_id'],
                $studentId,
                $number,
                $data['availability_status'],
                (int)$data['quantity'],
                $data['produced_at'],
                $data['responsible_user_id'],
                trim($data['purpose']),
                $description,
            ]
        );

        if (!$ok) {
            if ($ownsTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }

        if ($ownsTransaction) {
            $pdo->commit();
        }

        return true;
    } catch (Throwable $e) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
function production_update(int $id,array $data): bool
{
    return atomic_change(function () use ($id,$data) {
        $ref=production_find($id); if (!$ref) return false;
        $studentId=(int)($ref['student_id'] ?: ($data['student_id'] ?? 0));
        query_one('SELECT id FROM students WHERE id=? FOR UPDATE',[$studentId]);
        $old=query_one('SELECT * FROM productions WHERE id=? FOR UPDATE',[$id]);
        if (!$old) return false;
        if (query_one('SELECT id FROM deliveries WHERE production_id=? FOR UPDATE',[$id])) throw new DomainException('Não é possível editar uma produção já entregue.');
        if ($old['student_id']!==null && (int)$old['student_id']!==(int)$data['student_id']) throw new DomainException('O aluno de uma produção identificada não pode ser alterado.');
        if ($old['student_id']!==null && (int)$old['student_id']!==$studentId) throw new DomainException('A produção foi alterada por outro usuário. Reabra a página.');
        $data=validate_production($data); if ($data===null) return false;
        if (text_length(trim($data['description'] ?? ''))<10) throw new DomainException('A descrição deve ter pelo menos 10 caracteres.');
        $number=$old['work_number'] ?? next_work_number($studentId);
        return execute_query('UPDATE productions SET product_id=?,workshop_id=?,student_id=?,work_number=?,availability_status=?,quantity=?,produced_at=?,responsible_user_id=?,purpose=?,description=? WHERE id=?',[(int)$data['product_id'],(int)$data['workshop_id'],$studentId,$number,$data['availability_status'],(int)$data['quantity'],$data['produced_at'],$data['responsible_user_id'],trim($data['purpose']),trim($data['description']),$id]);
    });
}
function production_delete(int $id): bool
{
    return atomic_change(function () use ($id) {
        $ref=production_find($id); if (!$ref) return false;
        if ($ref['student_id']) query_one('SELECT id FROM students WHERE id=? FOR UPDATE',[$ref['student_id']]);
        $old=query_one('SELECT * FROM productions WHERE id=? FOR UPDATE',[$id]);
        if (!$old) return false;
        if ((string)$ref['student_id']!==(string)$old['student_id']) throw new DomainException('A produção mudou. Reabra a página antes de excluir.');
        if (query_one('SELECT id FROM deliveries WHERE production_id=? FOR UPDATE',[$id])) throw new DomainException('Não é possível excluir uma produção já entregue.');
        if ($old['student_id']) {
            $state=student_delivery_state((int)$old['student_id'],true);
            if (intdiv($state['works']-1,4)<$state['delivered_cycles']) throw new DomainException('Este trabalho sustenta uma entrega já realizada e não pode ser excluído.');
        }
        return execute_query('DELETE FROM productions WHERE id=?',[$id]);
    });
}
function productions_by_workshop(int $workshopId): array { return query_all("SELECT p.*,pr.name product_name,u.name responsible_name,s.full_name student_name FROM productions p JOIN products pr ON pr.id=p.product_id LEFT JOIN users u ON u.id=p.responsible_user_id LEFT JOIN students s ON s.id=p.student_id WHERE p.workshop_id=? ORDER BY p.produced_at DESC,p.id DESC",[$workshopId]); }
function productions_by_product(int $productId): array { return query_all("SELECT p.*,w.name workshop_name,s.full_name student_name,d.id delivery_id,d.delivered_at FROM productions p JOIN workshops w ON w.id=p.workshop_id LEFT JOIN students s ON s.id=p.student_id LEFT JOIN deliveries d ON d.production_id=p.id WHERE p.product_id=? ORDER BY p.produced_at DESC,p.id DESC",[$productId]); }

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
        WHERE m.active = 1
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
    $quantityMode = in_array($data['quantity_mode'] ?? 'decimal', ['integer','decimal'], true) ? ($data['quantity_mode'] ?? 'decimal') : 'decimal';
    if ($quantityMode === 'integer') {
        foreach (['current_quantity','min_quantity'] as $field) {
            if (isset($data[$field]) && quantity_milli($data[$field]) % 1000 !== 0) { flash('error', 'Materiais de quantidade inteira não aceitam valores fracionados.'); return false; }
        }
    }

    if ($appliesToAll === 0 && empty($workshopIds)) {
        flash('error', 'Selecione pelo menos uma oficina ou marque a opção "todas as oficinas".');
        return false;
    }

    try {
        $ownsTransaction = !db()->inTransaction();
        if ($ownsTransaction) db()->beginTransaction();

        execute_query(
            "INSERT INTO materials (name, category, unit, quantity_mode, current_quantity, min_quantity, applies_to_all)
            VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                trim($data['name']),
                trim($data['category']),
                trim($data['unit'] ?? 'un'),
                in_array($data['quantity_mode'] ?? 'decimal', ['integer','decimal'], true) ? ($data['quantity_mode'] ?? 'decimal') : 'decimal',
                decimal_quantity($data['current_quantity'] ?? '0'),
                decimal_quantity($data['min_quantity'] ?? '0'),
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

        if ($ownsTransaction) db()->commit();
        return true;
    } catch (Throwable $e) {
        if (($ownsTransaction ?? false) && db()->inTransaction()) db()->rollBack();
        report_error($e);
        return false;
    }
}

function material_update(int $id, array $data): bool
{
    $appliesToAll = isset($data['applies_to_all']) ? 1 : 0;
    $workshopIds = $data['workshop_ids'] ?? [];
    $quantityMode = in_array($data['quantity_mode'] ?? 'decimal', ['integer','decimal'], true) ? ($data['quantity_mode'] ?? 'decimal') : 'decimal';
    if ($appliesToAll === 0 && empty($workshopIds)) {
        flash('error', 'Selecione pelo menos uma oficina ou marque a opção "todas as oficinas".');
        return false;
    }

    try {
        $ownsTransaction = !db()->inTransaction();
        if ($ownsTransaction) db()->beginTransaction();

        $existing=query_one('SELECT * FROM materials WHERE id=? FOR UPDATE',[$id]);
        if (!$existing) throw new DomainException('Material não encontrado.');
        if (($data['unit'] ?? 'un')!==$existing['unit'] && (quantity_milli($existing['current_quantity'])!==0 || query_one('SELECT id FROM stock_movements WHERE material_id=? LIMIT 1',[$id]))) throw new DomainException('Um material com saldo ou movimentações deve manter sua unidade. Cadastre outro material para uma unidade diferente.');
        if ($quantityMode==='integer') {
            if (quantity_milli($existing['current_quantity'])%1000!==0 || quantity_milli($data['min_quantity'] ?? '0')%1000!==0 || query_one('SELECT id FROM stock_movements WHERE material_id=? AND MOD(quantity,1)<>0 LIMIT 1',[$id])) throw new DomainException('O saldo, o mínimo e as movimentações precisam ser inteiros para usar essa precisão.');
        }
        execute_query(
            "UPDATE materials
            SET name = ?, category = ?, unit = ?, quantity_mode = ?, min_quantity = ?, applies_to_all = ?
            WHERE id = ?",
            [
                trim($data['name']),
                trim($data['category']),
                trim($data['unit'] ?? 'un'),
                in_array($data['quantity_mode'] ?? 'decimal', ['integer','decimal'], true) ? ($data['quantity_mode'] ?? 'decimal') : 'decimal',
                decimal_quantity($data['min_quantity'] ?? '0'),
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

        if ($ownsTransaction) db()->commit();
        return true;
    } catch (Throwable $e) {
        if (($ownsTransaction ?? false) && db()->inTransaction()) db()->rollBack();
        report_error($e);
        return false;
    }
}

function material_delete(int $id): bool
{
    return execute_query("UPDATE materials SET active = 0 WHERE id = ?", [$id]);
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
    return stock_change(null, $data);
}

function stock_update_movement(int $id, array $data): bool
{
    return stock_change($id, $data);
}

function stock_delete_movement(int $id): bool
{
    return stock_change($id, null);
}

// Lock movement first, then all affected materials in ascending ID order.
function stock_change(?int $id, ?array $data): bool
{
    $owns = !db()->inTransaction();
    try {
        if ($owns) db()->beginTransaction();
        $old = $id ? query_one('SELECT * FROM stock_movements WHERE id = ? FOR UPDATE', [$id]) : null;
        if ($id && !$old) throw new DomainException('Movimentação não encontrada.');
        $ids = $old ? [(int)$old['material_id']] : [];
        if ($data !== null) {
            $quantity = quantity_milli($data['quantity'] ?? '');
            if ($quantity <= 0) throw new DomainException('A quantidade deve ser maior que zero.');
            $type = $data['movement_type'] ?? '';
            if (!in_array($type, ['entrada', 'saida'], true)) throw new DomainException('Tipo de movimentação inválido.');
            if (!valid_date($data['movement_date'] ?? '')) throw new DomainException('Data inválida.');
            $newId = (int)($data['material_id'] ?? 0);
            $ids[] = $newId;
        }
        $ids = array_unique($ids);
        sort($ids, SORT_NUMERIC);
        $balances = [];
        foreach ($ids as $materialId) {
            $material = query_one('SELECT * FROM materials WHERE id = ? FOR UPDATE', [$materialId]);
            if ($data !== null && $materialId === $newId && $material && ($material['quantity_mode'] ?? 'decimal') === 'integer' && $quantity % 1000 !== 0) { throw new DomainException('Este material aceita apenas quantidades inteiras.'); }
            if (!$material || ($data !== null && $materialId === $newId && !$material['active'])) {
                throw new DomainException('Material indisponível.');
            }
            $balances[$materialId] = quantity_milli($material['current_quantity']);
        }
        if ($old) $balances[$old['material_id']] -= ($old['movement_type'] === 'entrada' ? 1 : -1) * quantity_milli($old['quantity']);
        if ($data !== null) $balances[$newId] += ($type === 'entrada' ? 1 : -1) * $quantity;
        foreach ($balances as $materialId => $balance) {
            if ($balance < 0 || $balance > 999999999999) throw new DomainException('A operação deixaria o saldo negativo ou acima do limite.');
            execute_query('UPDATE materials SET current_quantity = ? WHERE id = ?', [milli_decimal($balance), $materialId]);
        }
        if ($data === null) {
            execute_query('DELETE FROM stock_movements WHERE id = ?', [$id]);
        } else {
            $values = [$newId, $type, milli_decimal($quantity), trim($data['notes'] ?? ''), $data['movement_date']];
            if ($id) {
                $values[] = $id;
                execute_query('UPDATE stock_movements SET material_id=?, movement_type=?, quantity=?, notes=?, movement_date=? WHERE id=?', $values);
            } else {
                $values[] = user_id();
                execute_query('INSERT INTO stock_movements (material_id, movement_type, quantity, notes, movement_date, user_id) VALUES (?, ?, ?, ?, ?, ?)', $values);
            }
        }
        if ($owns) db()->commit();
        return true;
    } catch (Throwable $e) {
        if ($owns && db()->inTransaction()) db()->rollBack();
        report_error($e);
        return false;
    }
}

/*
|--------------------------------------------------------------------------
| Relatórios
|--------------------------------------------------------------------------
*/


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
function refresh_session_user(): void
{
    if (!isset($_SESSION['user']['id'])) return;
    $user = query_one('SELECT id, name, email, role FROM users WHERE id = ? AND active = 1', [$_SESSION['user']['id']]);
    if (!$user) { auth_logout(); return; }
    $_SESSION['user'] = $user;
}

function report_error(Throwable $e): void
{
    if (!$e instanceof DomainException) error_log((string)$e);
    flash('error', $e instanceof DomainException ? $e->getMessage() : 'Não foi possível concluir a operação. Consulte o administrador.');
}

function valid_date(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
}

// Integer thousandths avoid floating-point drift in stock calculations.
function quantity_milli($value): int
{
    if (!is_scalar($value)) throw new DomainException('Quantidade inválida.');
    $value = str_replace(',', '.', trim((string)$value));
    if (!preg_match('/^([0-9]{1,9})(?:\.([0-9]{1,3}))?$/D', $value, $parts)) {
        throw new DomainException('Informe uma quantidade positiva, com até três casas decimais.');
    }
    return (int)$parts[1] * 1000 + (int)str_pad($parts[2] ?? '', 3, '0');
}

function milli_decimal(int $value): string
{
    return intdiv($value, 1000) . '.' . str_pad((string)($value % 1000), 3, '0', STR_PAD_LEFT);
}

function decimal_quantity($value): string
{
    return milli_decimal(quantity_milli($value));
}

function validate_user(array $data, ?int $id = null): bool
{
    $email = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';
    if (trim($data['name'] ?? '') === '' || text_length(trim($data['name'])) > 120 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150 ||
        !in_array($data['role'] ?? 'aluno', ['professor', 'aluno'], true)) {
        flash('error', 'Informe nome, e-mail e perfil válidos.'); return false;
    }
    if (($id === null || $password !== '') && (strlen($password) < 12 || strlen($password) > 72)) {
        flash('error', 'Use uma senha entre 12 e 72 bytes (prefira uma frase longa).'); return false;
    }
    if (query_one('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, $id ?? 0])) {
        flash('error', 'E-mail já cadastrado.'); return false;
    }
    return true;
}

function validate_production(array $data): ?array
{
    $quantity=filter_var($data['quantity']??null,FILTER_VALIDATE_INT); $product=product_find((int)($data['product_id']??0)); $workshop=workshop_find((int)($data['workshop_id']??0)); $student=student_find((int)($data['student_id']??0));
    $responsible=is_professor()?(int)(($data['responsible_user_id']??'')?:user_id()):user_id(); $user=$responsible?user_find($responsible):null; $availability=$data['availability_status']??'disponivel';
    if($quantity===false||$quantity<=0||$quantity>2147483647||!valid_date($data['produced_at']??'')||!$product||!$product['active']||!$workshop||!$workshop['active']||!$student||!$student['active']||!$user||!$user['active']||$user['role']!=='professor'||!in_array($availability,['disponivel','indisponivel'],true)||($product['workshop_id']!==null&&(int)$product['workshop_id']!==(int)$workshop['id'])||trim($data['purpose']??'')===''||text_length($data['purpose']??'')>150){flash('error','Verifique aluno, quantidade, data, produto, oficina, disponibilidade e responsável.');return null;}
    if(!query_one('SELECT student_id FROM student_workshop WHERE student_id=? AND workshop_id=? FOR UPDATE',[(int)$student['id'],(int)$workshop['id']])) { flash('error','O aluno precisa estar vinculado à oficina selecionada.'); return null; }
    if ($data['produced_at']>date('Y-m-d')) { flash('error','A data de produção não pode estar no futuro.'); return null; }
    $data['responsible_user_id']=$responsible; $data['availability_status']=$availability; return $data;
}

function report_period_dates(array $filters): array
{
    $mode = $filters['period_mode'] ?? 'custom';
    if (!in_array($mode,['custom','monthly','semester','annual'],true)) throw new DomainException('Tipo de período inválido.');
    $year = (int)($filters['year'] ?? date('Y'));

    if (in_array($mode, ['monthly', 'semester', 'annual'], true) && ($year < 2000 || $year > 2100)) {
        throw new DomainException('Ano do relatório inválido.');
    }

    if ($mode === 'monthly') {
        $month = (int)($filters['month'] ?? date('n'));
        if ($month < 1 || $month > 12) throw new DomainException('Mês do relatório inválido.');
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = (new DateTimeImmutable($start))->modify('last day of this month')->format('Y-m-d');
        return [$start, $end];
    }

    if ($mode === 'semester') {
        $semester = (int)($filters['semester'] ?? 1);
        if (!in_array($semester, [1, 2], true)) throw new DomainException('Semestre do relatório inválido.');
        return [
            $year . '-' . ($semester === 1 ? '01-01' : '07-01'),
            $year . '-' . ($semester === 1 ? '06-30' : '12-31'),
        ];
    }

    if ($mode === 'annual') {
        return [$year . '-01-01', $year . '-12-31'];
    }

    return [trim((string)($filters['start'] ?? '')), trim((string)($filters['end'] ?? ''))];
}

/**
 * Monta filtros compartilhados pelos relatórios.
 * $dateField define qual data representa o período (produção, entrega ou estoque).
 * Filtros de oficina/produto/aluno/responsável só são aplicados quando há uma
 * produção relacionada à consulta.
 */
function report_filter_parts(array $filters, string $dateField, bool $includeProductionFilters = true): array
{
    [$start, $end] = report_period_dates($filters);
    $where = [];
    $values = [];

    if ($start !== '') {
        if (!valid_date($start)) throw new DomainException('Data inicial inválida.');
        $where[] = "$dateField >= ?";
        $values[] = $start;
    }
    if ($end !== '') {
        if (!valid_date($end)) throw new DomainException('Data final inválida.');
        $where[] = "$dateField <= ?";
        $values[] = $end;
    }
    if ($start !== '' && $end !== '' && $start > $end) {
        throw new DomainException('O início deve preceder o fim.');
    }

    if ($includeProductionFilters) {
        foreach (['workshop_id', 'product_id', 'responsible_user_id', 'student_id'] as $key) {
            if (($filters[$key] ?? '') === '') continue;
            $id = filter_var($filters[$key], FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) throw new DomainException('Filtro inválido.');
            $where[] = "p.$key = ?";
            $values[] = $id;
        }
    }

    return [$where, $values];
}

function report_filtered_productions(array $filters): array
{
    [$where, $values] = report_filter_parts($filters, 'p.produced_at');

    return query_all(
        'SELECT p.*,pr.name product_name,w.name workshop_name,u.name responsible_name,s.full_name student_name,d.delivered_at
         FROM productions p
         JOIN products pr ON pr.id=p.product_id
         JOIN workshops w ON w.id=p.workshop_id
         LEFT JOIN users u ON u.id=p.responsible_user_id
         LEFT JOIN students s ON s.id=p.student_id
         LEFT JOIN deliveries d ON d.production_id=p.id' .
        ($where ? ' WHERE ' . implode(' AND ', $where) : '') .
        ' ORDER BY p.produced_at DESC,p.id DESC',
        $values
    );
}

function report_material_consumption(array $filters = []): array
{
    [$where, $values] = report_filter_parts($filters, 'sm.movement_date', false);

    return query_all(
        "SELECT m.id AS material_id,
                m.name AS material_name,
                m.unit,
                COALESCE(SUM(CASE WHEN sm.movement_type='entrada' THEN sm.quantity ELSE 0 END),0) AS total_entrada,
                COALESCE(SUM(CASE WHEN sm.movement_type='saida' THEN sm.quantity ELSE 0 END),0) AS total_saida,
                COUNT(sm.id) AS movement_count
         FROM stock_movements sm
         JOIN materials m ON m.id=sm.material_id" .
        ($where ? ' WHERE ' . implode(' AND ', $where) : '') .
        ' GROUP BY m.id,m.name,m.unit ORDER BY m.name ASC',
        $values
    );
}

function report_stock_movements(array $filters = []): array
{
    [$where, $values] = report_filter_parts($filters, 'sm.movement_date', false);

    return query_all(
        "SELECT sm.*,m.name material_name,m.unit,u.name user_name
         FROM stock_movements sm
         JOIN materials m ON m.id=sm.material_id
         LEFT JOIN users u ON u.id=sm.user_id" .
        ($where ? ' WHERE ' . implode(' AND ', $where) : '') .
        ' ORDER BY sm.movement_date DESC,sm.id DESC',
        $values
    );
}

function report_production_by_workshop_filtered(array $filters): array
{
    [$where, $values] = report_filter_parts($filters, 'p.produced_at');

    return query_all(
        'SELECT w.id,w.name,COUNT(p.id) production_records,COALESCE(SUM(p.quantity),0) production_units
         FROM productions p
         JOIN workshops w ON w.id=p.workshop_id' .
        ($where ? ' WHERE ' . implode(' AND ', $where) : '') .
        ' GROUP BY w.id,w.name ORDER BY production_records DESC,production_units DESC,w.name ASC',
        $values
    );
}

function report_production_by_student(array $filters): array
{
    [$where, $values] = report_filter_parts($filters, 'p.produced_at');

    return query_all(
        "SELECT p.student_id,COALESCE(s.full_name,'Registro antigo sem aluno') student_name,
                COUNT(p.id) work_count,COALESCE(SUM(p.quantity),0) production_units
         FROM productions p
         LEFT JOIN students s ON s.id=p.student_id" .
        ($where ? ' WHERE ' . implode(' AND ', $where) : '') .
        ' GROUP BY p.student_id,s.full_name ORDER BY work_count DESC,student_name ASC',
        $values
    );
}

function report_deliveries(array $filters): array
{
    // Para entregas, o intervalo selecionado é aplicado à data da entrega,
    // enquanto os demais filtros continuam usando a produção relacionada.
    [$where, $values] = report_filter_parts($filters, 'DATE(d.delivered_at)');

    return query_all(
        'SELECT d.*,p.work_number,p.product_id,p.workshop_id,p.responsible_user_id,
                s.full_name student_name,pr.name product_name,w.name workshop_name,
                u.name responsible_name,du.name delivered_by_name
         FROM deliveries d
         JOIN productions p ON p.id=d.production_id
         JOIN students s ON s.id=d.student_id
         JOIN products pr ON pr.id=p.product_id
         JOIN workshops w ON w.id=p.workshop_id
         LEFT JOIN users u ON u.id=p.responsible_user_id
         LEFT JOIN users du ON du.id=d.delivered_by_user_id' .
        ($where ? ' WHERE ' . implode(' AND ', $where) : '') .
        ' ORDER BY d.delivered_at DESC,d.id DESC',
        $values
    );
}

function report_summary(array $filters): array
{
    [$productionWhere, $productionValues] = report_filter_parts($filters, 'p.produced_at');
    $production = query_one(
        'SELECT COUNT(*) records,COALESCE(SUM(p.quantity),0) units,COUNT(DISTINCT p.student_id) students
         FROM productions p' . ($productionWhere ? ' WHERE ' . implode(' AND ', $productionWhere) : ''),
        $productionValues
    );

    [$deliveryWhere, $deliveryValues] = report_filter_parts($filters, 'DATE(d.delivered_at)');
    $deliveries = query_one(
        'SELECT COUNT(*) n FROM deliveries d JOIN productions p ON p.id=d.production_id' .
        ($deliveryWhere ? ' WHERE ' . implode(' AND ', $deliveryWhere) : ''),
        $deliveryValues
    );

    [$stockWhere, $stockValues] = report_filter_parts($filters, 'sm.movement_date', false);
    $stock = query_one(
        "SELECT COUNT(*) movements,
                COALESCE(SUM(CASE WHEN sm.movement_type='entrada' THEN 1 ELSE 0 END),0) entries,
                COALESCE(SUM(CASE WHEN sm.movement_type='saida' THEN 1 ELSE 0 END),0) exits
         FROM stock_movements sm" . ($stockWhere ? ' WHERE ' . implode(' AND ', $stockWhere) : ''),
        $stockValues
    );

    // Estes dois indicadores representam a situação atual, não o período histórico.
    $lowStock = (int)(query_one('SELECT COUNT(*) n FROM materials WHERE active=1 AND current_quantity<=min_quantity')['n'] ?? 0);
    $pending = (int)(query_one(
        'SELECT COALESCE(SUM(GREATEST(FLOOR(x.works/4)-x.deliveries,0)),0) n
         FROM (
             SELECT s.id,COUNT(DISTINCT p.id) works,COUNT(DISTINCT d.id) deliveries
             FROM students s
             LEFT JOIN productions p ON p.student_id=s.id
             LEFT JOIN deliveries d ON d.student_id=s.id
             WHERE s.active=1
             GROUP BY s.id
         ) x'
    )['n'] ?? 0);

    return [
        'production_records' => (int)($production['records'] ?? 0),
        'production_units' => (int)($production['units'] ?? 0),
        'students' => (int)($production['students'] ?? 0),
        'deliveries' => (int)($deliveries['n'] ?? 0),
        'pending_deliveries' => $pending,
        'stock_movements' => (int)($stock['movements'] ?? 0),
        'stock_entries' => $stock['entries'] ?? 0,
        'stock_exits' => $stock['exits'] ?? 0,
        'low_stock' => $lowStock,
    ];
}

function csv_safe($value): string
{
    $value = (string)$value;
    return preg_match('/^[\s]*[=+@-]/u', $value) ? "'" . $value : $value;
}

/** Mutations join the HTTP transaction or own an atomic transaction for CLI/tests. */
function atomic_change(callable $operation): bool
{
    $pdo=db(); $owns=!$pdo->inTransaction();
    if ($owns) $pdo->beginTransaction();
    try {
        $result=$operation();
        if ($owns) { if ($result===false) $pdo->rollBack(); else $pdo->commit(); }
        return $result !== false;
    } catch (Throwable $e) {
        if ($owns && $pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
function student_history_workshops(int $id): array
{
    return query_all('SELECT id,name FROM workshops WHERE id IN (SELECT workshop_id FROM student_workshop WHERE student_id=?) OR id IN (SELECT workshop_id FROM productions WHERE student_id=?) ORDER BY name',[$id,$id]);
}
