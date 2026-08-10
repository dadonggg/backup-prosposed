<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class MarketingOfficer extends Model
{
    /** Check if table exists */
    public function tableExists(): bool
    {
        try {
            $this->db()->query('SELECT 1 FROM marketing_officers LIMIT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /** Create or re-activate a marketing officer record */
    public function create(int $userId, int $gymId, int $assignedBy): int
    {
        $stmt = $this->db()->prepare('SELECT id FROM marketing_officers WHERE user_id = :uid AND gym_id = :gid LIMIT 1');
        $stmt->execute([':uid' => $userId, ':gid' => $gymId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $stmt = $this->db()->prepare('UPDATE marketing_officers SET status = "active", assigned_by = :ab WHERE id = :id');
            $stmt->execute([':ab' => $assignedBy, ':id' => (int)$row['id']]);
            return (int)$row['id'];
        } else {
            $stmt = $this->db()->prepare(
                'INSERT INTO marketing_officers (user_id, gym_id, assigned_by, status)
                 VALUES (:uid, :gid, :ab, "active")'
            );
            $stmt->execute([':uid' => $userId, ':gid' => $gymId, ':ab' => $assignedBy]);
            return (int)$this->db()->lastInsertId();
        }
    }

    /** Deactivate a marketing officer */
    public function deactivate(int $userId, int $gymId): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE marketing_officers SET status = "inactive" WHERE user_id = :uid AND gym_id = :gid'
        );
        $stmt->execute([':uid' => $userId, ':gid' => $gymId]);
    }

    /** Find an active marketing officer record by user_id */
    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM marketing_officers WHERE user_id = :uid AND status = "active" LIMIT 1'
        );
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
