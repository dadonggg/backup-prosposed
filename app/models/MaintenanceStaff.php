<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class MaintenanceStaff extends Model
{
    /** Check if the maintenance_staff table exists */
    public function tableExists(): bool
    {
        try {
            $this->db()->query('SELECT 1 FROM maintenance_staff LIMIT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /** Find maintenance staff record by user_id */
    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT ms.*, u.fullname, u.email, ld.gym_name, ld.gym_address
             FROM maintenance_staff ms
             JOIN users u ON u.id = ms.user_id
             LEFT JOIN legal_documents ld ON ld.user_id = ms.gym_id
             WHERE ms.user_id = :uid
             ORDER BY ms.id DESC LIMIT 1'
        );
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Find by id */
    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT ms.*, u.fullname, u.email
             FROM maintenance_staff ms
             JOIN users u ON u.id = ms.user_id
             WHERE ms.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Create a new maintenance staff record (when gym owner approves) */
    public function create(int $userId, int $gymId, int $assignedBy): int
    {
        // Avoid duplicates — update if exists
        $stmt = $this->db()->prepare(
            'INSERT INTO maintenance_staff (user_id, gym_id, status, assigned_by)
             VALUES (:uid, :gid, "active", :ab)
             ON DUPLICATE KEY UPDATE status="active", assigned_by=:ab2, gym_id=:gid2'
        );
        $stmt->execute([
            ':uid' => $userId,
            ':gid' => $gymId,
            ':ab'  => $assignedBy,
            ':ab2' => $assignedBy,
            ':gid2' => $gymId,
        ]);
        return (int)$this->db()->lastInsertId();
    }

    /** Update status */
    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db()->prepare('UPDATE maintenance_staff SET status = :s WHERE id = :id');
        $stmt->execute([':s' => $status, ':id' => $id]);
    }

    /** Get all active maintenance staff for a specific gym owner */
    public function findByGymOwner(int $gymOwnerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT ms.*, u.fullname, u.email
             FROM maintenance_staff ms
             JOIN users u ON u.id = ms.user_id
             WHERE ms.gym_id = :gid
             ORDER BY ms.created_at DESC'
        );
        $stmt->execute([':gid' => $gymOwnerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
