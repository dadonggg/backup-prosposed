<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class GymAnnouncement extends Model
{
    public function create(int $gymOwnerId, string $title, string $content, string $type = 'general',
                          string $priority = 'normal', ?string $publishDate = null, ?string $expiryDate = null,
                          string $targetAudience = 'all_members'): int
    {
        try {
            \App\Core\Database::beginTransaction();

            if ($publishDate === null) {
                $publishDate = date('Y-m-d');
            }

            $stmt = $this->db()->prepare(
                'INSERT INTO gym_announcements (gym_owner_id, title, content, announcement_type, priority, publish_date, expiry_date, target_audience)
                 VALUES (:owner, :title, :content, :type, :priority, :publish, :expiry, :audience)'
            );
            $stmt->execute([
                ':owner' => $gymOwnerId,
                ':title' => $title,
                ':content' => $content,
                ':type' => $type,
                ':priority' => $priority,
                ':publish' => $publishDate,
                ':expiry' => $expiryDate,
                ':audience' => $targetAudience
            ]);

            $announcementId = (int)$this->db()->lastInsertId();
            \App\Core\Database::commit();
            return $announcementId;

        } catch (\Exception $e) {
            \App\Core\Database::rollback();
            $this->logError("Failed to create announcement: " . $e->getMessage());
            return 0;
        }
    }

    public function findActiveAnnouncements(?int $gymOwnerId = null): array
    {
        $sql = 'SELECT * FROM gym_announcements 
                WHERE is_active = 1 
                  AND publish_date <= CURDATE() 
                  AND (expiry_date IS NULL OR expiry_date >= CURDATE())';
        
        $params = [];
        
        if ($gymOwnerId !== null) {
            $sql .= ' AND gym_owner_id = :owner';
            $params[':owner'] = $gymOwnerId;
        }

        $sql .= ' ORDER BY priority DESC, publish_date DESC';

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findForMember(int $memberId): array
    {
        // Get member's gym owner ID
        $stmt = $this->db()->prepare(
            'SELECT gm.*, ma.gym_owner_id 
             FROM gym_members gm 
             JOIN membership_applications ma ON ma.id = gm.application_id 
             WHERE gm.id = :mid'
        );
        $stmt->execute([':mid' => $memberId]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) return [];

        // Get announcements for this member's gym
        $stmt = $this->db()->prepare(
            'SELECT ga.*, 
                    CASE WHEN mav.id IS NOT NULL THEN 1 ELSE 0 END as is_viewed
             FROM gym_announcements ga
             LEFT JOIN member_announcement_views mav ON ga.id = mav.announcement_id AND mav.member_id = :mid
             WHERE ga.gym_owner_id = :owner
               AND ga.is_active = 1
               AND ga.publish_date <= CURDATE()
               AND (ga.expiry_date IS NULL OR ga.expiry_date >= CURDATE())
               AND ga.target_audience IN ("all_members", "active_members")
             ORDER BY ga.priority DESC, ga.publish_date DESC'
        );
        $stmt->execute([':mid' => $memberId, ':owner' => $member['gym_owner_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsViewed(int $memberId, int $announcementId): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'INSERT IGNORE INTO member_announcement_views (member_id, announcement_id) 
                 VALUES (:mid, :aid)'
            );
            $stmt->execute([':mid' => $memberId, ':aid' => $announcementId]);
            return true;
        } catch (\Exception $e) {
            $this->logError("Failed to mark announcement as viewed: " . $e->getMessage());
            return false;
        }
    }

    public function getUnreadCount(int $memberId): int
    {
        // Get member's gym owner ID
        $stmt = $this->db()->prepare(
            'SELECT ma.gym_owner_id 
             FROM gym_members gm 
             JOIN membership_applications ma ON ma.id = gm.application_id 
             WHERE gm.id = :mid'
        );
        $stmt->execute([':mid' => $memberId]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) return 0;

        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) as unread_count
             FROM gym_announcements ga
             LEFT JOIN member_announcement_views mav ON ga.id = mav.announcement_id AND mav.member_id = :mid
             WHERE ga.gym_owner_id = :owner
               AND ga.is_active = 1
               AND ga.publish_date <= CURDATE()
               AND (ga.expiry_date IS NULL OR ga.expiry_date >= CURDATE())
               AND ga.target_audience IN ("all_members", "active_members")
               AND mav.id IS NULL'
        );
        $stmt->execute([':mid' => $memberId, ':owner' => $member['gym_owner_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['unread_count'] ?? 0);
    }

    public function findByGymOwner(int $gymOwnerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT ga.*, 
                    COUNT(mav.id) as view_count,
                    (SELECT COUNT(*) FROM gym_members gm 
                     JOIN membership_applications ma ON ma.id = gm.application_id 
                     WHERE ma.gym_owner_id = ga.gym_owner_id AND gm.membership_status = "active") as total_members
             FROM gym_announcements ga
             LEFT JOIN member_announcement_views mav ON ga.id = mav.announcement_id
             WHERE ga.gym_owner_id = :owner
             GROUP BY ga.id
             ORDER BY ga.created_at DESC'
        );
        $stmt->execute([':owner' => $gymOwnerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $announcementId, bool $isActive): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'UPDATE gym_announcements SET is_active = :active, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $stmt->execute([':active' => $isActive ? 1 : 0, ':id' => $announcementId]);
            return true;
        } catch (\Exception $e) {
            $this->logError("Failed to update announcement status: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $announcementId): bool
    {
        try {
            \App\Core\Database::beginTransaction();

            // Delete views first (foreign key constraint)
            $stmt = $this->db()->prepare('DELETE FROM member_announcement_views WHERE announcement_id = :id');
            $stmt->execute([':id' => $announcementId]);

            // Delete announcement
            $stmt = $this->db()->prepare('DELETE FROM gym_announcements WHERE id = :id');
            $stmt->execute([':id' => $announcementId]);

            \App\Core\Database::commit();
            return true;

        } catch (\Exception $e) {
            \App\Core\Database::rollback();
            $this->logError("Failed to delete announcement: " . $e->getMessage());
            return false;
        }
    }

    private function logError(string $message): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/announcements.log';
        $logMessage = sprintf(
            "[%s] GymAnnouncement: %s\n",
            date('Y-m-d H:i:s'),
            $message
        );

        @error_log($logMessage, 3, $logFile);
    }
}