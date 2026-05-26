<?php
// app/Models/User.php
class User extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM users ORDER BY name');
    }

    public function participants(): array
    {
        return $this->fetchAll("SELECT * FROM users WHERE role = 'aluno' ORDER BY name");
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE email = ?', [strtolower(trim($email))]);
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())',
            [
                trim($data['name']),
                strtolower(trim($data['email'])),
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'] ?? 'aluno',
            ]
        );

        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $user = $this->find($id);
        $password = trim((string) ($data['password'] ?? ''));
        $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : $user['password'];

        $this->execute(
            'UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?',
            [
                trim($data['name']),
                strtolower(trim($data['email'])),
                $hash,
                $data['role'],
                $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM users WHERE id = ?', [$id]);
    }
}
