<?php
// app/Models/StockMovement.php
class StockMovement extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            "SELECT sm.*, m.name AS material_name, m.unit, u.name AS responsible_name
             FROM stock_movements sm
             INNER JOIN materials m ON m.id = sm.material_id
             INNER JOIN users u ON u.id = sm.responsible_id
             ORDER BY sm.happened_at DESC, sm.id DESC"
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM stock_movements WHERE id = ?', [$id]);
    }

    public function create(array $data): int
    {
        try {
            $this->db->beginTransaction();

            $this->execute(
                'INSERT INTO stock_movements (material_id, movement_type, quantity, note, happened_at, responsible_id)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    (int) $data['material_id'],
                    $data['movement_type'],
                    (int) $data['quantity'],
                    trim($data['note'] ?? ''),
                    $data['happened_at'],
                    (int) $data['responsible_id'],
                ]
            );

            $this->recalculateMaterialStock((int) $data['material_id']);

            $this->db->commit();
            return $this->lastInsertId();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function update(int $id, array $data): void
    {
        $old = $this->find($id);

        try {
            $this->db->beginTransaction();

            $this->execute(
                'UPDATE stock_movements
                 SET material_id = ?, movement_type = ?, quantity = ?, note = ?, happened_at = ?, responsible_id = ?
                 WHERE id = ?',
                [
                    (int) $data['material_id'],
                    $data['movement_type'],
                    (int) $data['quantity'],
                    trim($data['note'] ?? ''),
                    $data['happened_at'],
                    (int) $data['responsible_id'],
                    $id,
                ]
            );

            $this->recalculateMaterialStock((int) $old['material_id']);
            if ((int) $old['material_id'] !== (int) $data['material_id']) {
                $this->recalculateMaterialStock((int) $data['material_id']);
            }

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        $old = $this->find($id);

        try {
            $this->db->beginTransaction();
            $this->execute('DELETE FROM stock_movements WHERE id = ?', [$id]);
            $this->recalculateMaterialStock((int) $old['material_id']);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function totals(): array
    {
        return $this->fetchOne(
            "SELECT
                COALESCE(SUM(CASE WHEN movement_type = 'entrada' THEN quantity ELSE 0 END), 0) AS entradas,
                COALESCE(SUM(CASE WHEN movement_type = 'saida' THEN quantity ELSE 0 END), 0) AS saidas
             FROM stock_movements"
        ) ?? ['entradas' => 0, 'saidas' => 0];
    }

    private function recalculateMaterialStock(int $materialId): void
    {
        $row = $this->fetchOne(
            "SELECT
                COALESCE(SUM(CASE WHEN movement_type = 'entrada' THEN quantity ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN movement_type = 'saida' THEN quantity ELSE 0 END), 0) AS saldo
             FROM stock_movements
             WHERE material_id = ?",
            [$materialId]
        );

        $saldo = (int) ($row['saldo'] ?? 0);

        if ($saldo < 0) {
            throw new RuntimeException('A operação deixaria o estoque negativo.');
        }

        $this->execute('UPDATE materials SET current_stock = ? WHERE id = ?', [$saldo, $materialId]);
    }
}
