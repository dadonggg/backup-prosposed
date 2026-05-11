<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class GymEquipment extends Model
{
    /** List all equipment (with optional supplier join) */
    public function findAll(): array
    {
        return $this->db()->query(
            'SELECT e.*, s.name as supplier_name FROM gym_equipment e
             LEFT JOIN suppliers s ON s.id = e.supplier_id
             WHERE e.is_active = 1
             ORDER BY e.category, e.name'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT e.*, s.name as supplier_name FROM gym_equipment e
             LEFT JOIN suppliers s ON s.id = e.supplier_id WHERE e.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findBySupplierId(int $supplierId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT e.*, s.name as supplier_name FROM gym_equipment e
             LEFT JOIN suppliers s ON s.id = e.supplier_id WHERE e.supplier_id = :sid ORDER BY e.name'
        );
        $stmt->execute([':sid' => $supplierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Find all equipment listed by a specific gym owner */
    public function findByOwnerId(int $ownerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT e.*, s.name as supplier_name FROM gym_equipment e
             LEFT JOIN suppliers s ON s.id = e.supplier_id
             WHERE e.listed_by = :oid ORDER BY e.created_at DESC'
        );
        $stmt->execute([':oid' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** List (add) new equipment to inventory */
    public function listEquipment(
        int $ownerId,
        string $name,
        string $category,
        string $brand,
        string $dimensions,
        ?float $weightKg,
        int $quantity,
        float $price,
        string $description,
        ?string $imagePath
    ): int {
        $stmt = $this->db()->prepare(
            'INSERT INTO gym_equipment
                (supplier_id, name, category, brand, dimensions, weight_kg, quantity, price, description, image_path, listed_by, is_active)
             VALUES
                (NULL, :name, :category, :brand, :dimensions, :weight_kg, :quantity, :price, :description, :image_path, :listed_by, 1)'
        );
        $stmt->execute([
            ':name' => $name,
            ':category' => $category,
            ':brand' => $brand,
            ':dimensions' => $dimensions,
            ':weight_kg' => $weightKg,
            ':quantity' => $quantity,
            ':price' => $price,
            ':description' => $description,
            ':image_path' => $imagePath,
            ':listed_by' => $ownerId,
        ]);
        return (int)$this->db()->lastInsertId();
    }

    /** Update equipment listing */
    public function updateEquipment(
        int $id,
        string $name,
        string $category,
        string $brand,
        string $dimensions,
        ?float $weightKg,
        int $quantity,
        float $price,
        string $description,
        ?string $imagePath = null
    ): void {
        $sql = 'UPDATE gym_equipment SET
            name = :name, category = :category, brand = :brand,
            dimensions = :dimensions, weight_kg = :weight_kg, quantity = :quantity,
            price = :price, description = :description';
        $params = [
            ':name' => $name, ':category' => $category, ':brand' => $brand,
            ':dimensions' => $dimensions, ':weight_kg' => $weightKg,
            ':quantity' => $quantity, ':price' => $price, ':description' => $description,
            ':id' => $id,
        ];
        if ($imagePath !== null) {
            $sql .= ', image_path = :image_path';
            $params[':image_path'] = $imagePath;
        }
        $sql .= ' WHERE id = :id';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
    }

    /** Soft-delete equipment */
    public function deactivate(int $id): void
    {
        $stmt = $this->db()->prepare('UPDATE gym_equipment SET is_active = 0 WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
