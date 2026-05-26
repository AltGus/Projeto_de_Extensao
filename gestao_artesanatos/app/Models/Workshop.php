<?php
// app/Models/Workshop.php
class Workshop extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            "SELECT
                w.*,
                (SELECT COUNT(*) FROM workshop_participants wp WHERE wp.workshop_id = w.id) AS participant_count,
                (SELECT COALESCE(SUM(p.quantity), 0) FROM productions p WHERE p.workshop_id = w.id) AS total_production
             FROM workshops w
             ORDER BY w.name"
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM workshops WHERE id = ?', [$id]);
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO workshops (name, description, created_at) VALUES (?, ?, NOW())',
            [trim($data['name']), trim($data['description'] ?? '')]
        );

        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute(
            'UPDATE workshops SET name = ?, description = ? WHERE id = ?',
            [trim($data['name']), trim($data['description'] ?? ''), $id]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM workshops WHERE id = ?', [$id]);
    }

    public function participants(int $workshopId): array
    {
        return $this->fetchAll(
            'SELECT u.* FROM users u
             INNER JOIN workshop_participants wp ON wp.user_id = u.id
             WHERE wp.workshop_id = ?
             ORDER BY u.name',
            [$workshopId]
        );
    }

    public function availableParticipants(int $workshopId): array
    {
        return $this->fetchAll(
            "SELECT * FROM users
             WHERE role = 'aluno'
               AND id NOT IN (
                    SELECT user_id FROM workshop_participants WHERE workshop_id = ?
               )
             ORDER BY name",
            [$workshopId]
        );
    }

    public function addParticipant(int $workshopId, int $userId): void
    {
        $this->execute(
            'INSERT IGNORE INTO workshop_participants (workshop_id, user_id, created_at) VALUES (?, ?, NOW())',
            [$workshopId, $userId]
        );
    }

    public function removeParticipant(int $workshopId, int $userId): void
    {
        $this->execute('DELETE FROM workshop_participants WHERE workshop_id = ? AND user_id = ?', [$workshopId, $userId]);
    }

    public function ranking(): array
    {
        return $this->fetchAll(
            "SELECT w.name, COALESCE(SUM(p.quantity), 0) AS total
             FROM workshops w
             LEFT JOIN productions p ON p.workshop_id = w.id
             GROUP BY w.id, w.name
             ORDER BY total DESC, w.name ASC"
        );
    }
}
