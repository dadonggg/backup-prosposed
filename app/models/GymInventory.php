<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class GymInventory extends Model
{
    public function create(int $ownerId, int $equipmentId, int $qty, float $totalCost): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO gym_inventory (gym_owner_id, equipment_id, quantity, total_cost)
             VALUES (:oid, :eid, :qty, :cost)'
        );
        $stmt->execute([':oid' => $ownerId, ':eid' => $equipmentId, ':qty' => $qty, ':cost' => $totalCost]);
        return (int)$this->db()->lastInsertId();
    }

    public function findByOwnerId(int $ownerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT gi.*, ge.name as equipment_name, ge.category, s.name as supplier_name
             FROM gym_inventory gi
             JOIN gym_equipment ge ON ge.id = gi.equipment_id
             JOIN suppliers s ON s.id = ge.supplier_id
             WHERE gi.gym_owner_id = :oid ORDER BY gi.purchased_at DESC'
        );
        $stmt->execute([':oid' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalSpent(int $ownerId): float
    {
        $stmt = $this->db()->prepare(
            'SELECT COALESCE(SUM(total_cost),0) as total FROM gym_inventory WHERE gym_owner_id = :oid'
        );
        $stmt->execute([':oid' => $ownerId]);
        return (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
