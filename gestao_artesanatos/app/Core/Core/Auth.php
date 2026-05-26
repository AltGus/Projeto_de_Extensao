<?php
// app/Core/Auth.php
class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['auth']);
    }

    public static function user(): ?array
    {
        return $_SESSION['auth'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['auth']['id']) ? (int) $_SESSION['auth']['id'] : null;
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['auth']['role'] ?? null) === 'professor';
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION['auth'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION['auth']);
        session_regenerate_id(true);
    }
}
