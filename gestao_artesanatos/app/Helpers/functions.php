<?php
// app/Helpers/functions.php
function root_path(string $path = ''): string
{
    $root = dirname(__DIR__, 2);
    return $path ? $root . '/' . ltrim($path, '/') : $root;
}

function view_path(string $path): string
{
    return root_path('resources/views/' . ltrim($path, '/') . '.php');
}

function config_value(string $key, $default = null)
{
    static $configs = [];
    [$file, $item] = array_pad(explode('.', $key, 2), 2, null);

    if (!isset($configs[$file])) {
        $path = root_path('config/' . $file . '.php');
        $configs[$file] = file_exists($path) ? require $path : [];
    }

    return $item ? ($configs[$file][$item] ?? $default) : ($configs[$file] ?? $default);
}

function url(string $path = ''): string
{
    $base = rtrim((string) config_value('app.base_url', ''), '/');
    $path = '/' . ltrim($path, '/');
    return $base . ($path === '/' ? '' : $path);
}

function asset(string $path): string
{
    return url('/assets/' . ltrim($path, '/'));
}

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = rtrim($path, '/');
    return $path === '' ? '/' : $path;
}

function is_active(string $path): bool
{
    $current = request_path();
    $path = rtrim($path, '/');
    $path = $path === '' ? '/' : $path;

    if ($path === '/') {
        return $current === '/';
    }

    return $current === $path || str_starts_with($current, $path . '/');
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][$type][] = $message;
}

function consume_flash(): array
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

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['_token'] ?? '';
    $stored = $_SESSION['_csrf'] ?? '';

    if (!$stored || !hash_equals($stored, $sent)) {
        http_response_code(419);
        exit('Token CSRF inválido.');
    }
}

function include_with_data(string $file, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require $file;
}

function component(string $path, array $data = []): void
{
    include_with_data(view_path('components/' . $path), $data);
}

function template_part(string $path, array $data = []): void
{
    include_with_data(view_path('templates/' . $path), $data);
}

function abort(int $status): void
{
    http_response_code($status);
    $title = $status === 403 ? 'Acesso negado' : 'Página não encontrada';

    ob_start();
    require view_path('pages/errors/' . $status);
    $content = ob_get_clean();

    require view_path('layouts/guest');
    exit;
}
