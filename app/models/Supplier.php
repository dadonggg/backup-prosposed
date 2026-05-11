<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class Supplier extends Model
{
    public function findAll(): array
    {
        return $this->db()->query('SELECT * FROM suppliers ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM suppliers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
