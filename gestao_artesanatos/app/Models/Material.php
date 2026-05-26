<?php
// app/Models/Material.php
class Material extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM materials ORDER BY category, name');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM materials WHERE id = ?', [$id]);
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO materials (name, category, unit, current_stock, min_stock, description, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [
                trim($data['name']),
                $data['category'],
                trim($data['unit']),
                (int) $data['current_stock'],
                (int) $data['min_stock'],
                trim($data['description'] ?? ''),
            ]
        );

        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute(
            'UPDATE materials
             SET name = ?, category = ?, unit = ?, current_stock = ?, min_stock = ?, description = ?
             WHERE id = ?',
            [
                trim($data['name']),
                $data['category'],
                trim($data['unit']),
                (int) $data['current_stock'],
                (int) $data['min_stock'],
                trim($data['description'] ?? ''),
                $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM materials WHERE id = ?', [$id]);
    }

    public function lowStock(): array
    {
        return $this->fetchAll('SELECT * FROM materials WHERE current_stock <= min_stock ORDER BY name');
    }
}
