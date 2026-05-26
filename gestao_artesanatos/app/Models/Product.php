<?php
// app/Models/Product.php
class Product extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM products ORDER BY name');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM products WHERE id = ?', [$id]);
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO products (name, category, description, created_at) VALUES (?, ?, ?, NOW())',
            [trim($data['name']), trim($data['category']), trim($data['description'] ?? '')]
        );

        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute(
            'UPDATE products SET name = ?, category = ?, description = ? WHERE id = ?',
            [trim($data['name']), trim($data['category']), trim($data['description'] ?? ''), $id]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM products WHERE id = ?', [$id]);
    }
}
