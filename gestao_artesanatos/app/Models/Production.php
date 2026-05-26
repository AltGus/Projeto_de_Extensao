<?php
// app/Models/Production.php
class Production extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            "SELECT p.*, w.name AS workshop_name, pr.name AS product_name, u.name AS responsible_name
             FROM productions p
             INNER JOIN workshops w ON w.id = p.workshop_id
             INNER JOIN products pr ON pr.id = p.product_id
             INNER JOIN users u ON u.id = p.responsible_id
             ORDER BY p.produced_at DESC, p.id DESC"
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM productions WHERE id = ?', [$id]);
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO productions (workshop_id, product_id, quantity, produced_at, responsible_id, purpose, notes, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                (int) $data['workshop_id'],
                (int) $data['product_id'],
                (int) $data['quantity'],
                $data['produced_at'],
                (int) $data['responsible_id'],
                trim($data['purpose']),
                trim($data['notes'] ?? ''),
            ]
        );

        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute(
            'UPDATE productions
             SET workshop_id = ?, product_id = ?, quantity = ?, produced_at = ?, responsible_id = ?, purpose = ?, notes = ?
             WHERE id = ?',
            [
                (int) $data['workshop_id'],
                (int) $data['product_id'],
                (int) $data['quantity'],
                $data['produced_at'],
                (int) $data['responsible_id'],
                trim($data['purpose']),
                trim($data['notes'] ?? ''),
                $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM productions WHERE id = ?', [$id]);
    }

    public function totalQuantity(): int
    {
        $row = $this->fetchOne('SELECT COALESCE(SUM(quantity), 0) AS total FROM productions');
        return (int) ($row['total'] ?? 0);
    }

    public function recent(int $limit = 6): array
    {
        $limit = max(1, (int) $limit);

        return $this->fetchAll(
            "SELECT p.*, w.name AS workshop_name, pr.name AS product_name, u.name AS responsible_name
             FROM productions p
             INNER JOIN workshops w ON w.id = p.workshop_id
             INNER JOIN products pr ON pr.id = p.product_id
             INNER JOIN users u ON u.id = p.responsible_id
             ORDER BY p.produced_at DESC, p.id DESC
             LIMIT {$limit}"
        );
    }

    public function byWorkshop(int $workshopId, int $limit = 20): array
    {
        $limit = max(1, (int) $limit);

        return $this->fetchAll(
            "SELECT p.*, pr.name AS product_name, u.name AS responsible_name
             FROM productions p
             INNER JOIN products pr ON pr.id = p.product_id
             INNER JOIN users u ON u.id = p.responsible_id
             WHERE p.workshop_id = ?
             ORDER BY p.produced_at DESC, p.id DESC
             LIMIT {$limit}",
            [$workshopId]
        );
    }

    public function monthlySummary(int $months = 6): array
    {
        $months = max(1, (int) $months);

        return $this->fetchAll(
            "SELECT DATE_FORMAT(produced_at, '%Y-%m') AS month_key,
                    DATE_FORMAT(produced_at, '%m/%Y') AS month_label,
                    COALESCE(SUM(quantity), 0) AS total
             FROM productions
             WHERE produced_at >= DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)
             GROUP BY month_key, month_label
             ORDER BY month_key"
        );
    }
}
