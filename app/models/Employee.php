<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class Employee extends Model
{
    public function create(int $userId, string $position, int $hiredBy): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO employees (user_id, position, hired_by) VALUES (:uid, :pos, :hb)'
        );
        $stmt->execute([':uid' => $userId, ':pos' => $position, ':hb' => $hiredBy]);
        return (int)$this->db()->lastInsertId();
    }

    public function findAll(): array
    {
        return $this->db()->query(
            'SELECT e.*, u.fullname, u.email FROM employees e JOIN users u ON u.id = e.user_id ORDER BY e.hired_at DESC'
        )->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findByGymOwner(int $gymOwnerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT e.*, u.fullname, u.email FROM employees e 
             JOIN users u ON u.id = e.user_id 
             WHERE e.hired_by = :gid 
             ORDER BY e.hired_at DESC'
        );
        $stmt->execute([':gid' => $gymOwnerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAvailableTrainers(): array
    {
        return $this->db()->query(
            'SELECT e.*, u.fullname, u.email FROM employees e
             JOIN users u ON u.id = e.user_id
             WHERE e.position = "trainer" ORDER BY u.fullname'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT e.*, u.fullname, u.email FROM employees e JOIN users u ON u.id = e.user_id WHERE e.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT e.*, u.fullname, u.email, u.role FROM employees e 
             JOIN users u ON u.id = e.user_id 
             WHERE e.user_id = :uid 
             LIMIT 1'
        );
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function setAvailability(int $id, bool $available): void
    {
        $stmt = $this->db()->prepare('UPDATE employees SET is_available = :a WHERE id = :id');
        $stmt->execute([':a' => $available ? 1 : 0, ':id' => $id]);
    }
}
