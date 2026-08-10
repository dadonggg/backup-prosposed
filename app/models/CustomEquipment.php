<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

final class CustomEquipment extends Model
{
    /** Create custom equipment */
    public function create(int $trainerId, string $equipmentName): int
    {
        try {
            $stmt = $this->db()->prepare(
                'INSERT INTO custom_equipment (trainer_id, equipment_name)
                 VALUES (:trainer_id, :equipment_name)'
            );
            
            $stmt->execute([
                ':trainer_id' => $trainerId,
                ':equipment_name' => trim($equipmentName)
            ]);
            
            return (int)$this->db()->lastInsertId();
        } catch (\PDOException $e) {
            // Handle duplicate entry
            if ($e->getCode() == 23000) {
                return 0; // Already exists
            }
            throw $e;
        }
    }

    /** Find all custom equipment by trainer */
    public function findByTrainerId(int $trainerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM custom_equipment 
             WHERE trainer_id = :trainer_id
             ORDER BY equipment_name ASC'
        );
        $stmt->execute([':trainer_id' => $trainerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Delete custom equipment */
    public function delete(int $id, int $trainerId): bool
    {
        $stmt = $this->db()->prepare(
            'DELETE FROM custom_equipment 
             WHERE id = :id AND trainer_id = :trainer_id'
        );
        return $stmt->execute([':id' => $id, ':trainer_id' => $trainerId]);
    }
}
