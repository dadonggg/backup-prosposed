<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class Notification extends Model
{
    /**
     * Create a notification for a user.
     * @param string $type One of: info, success, warning, danger
     */
    public function create(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO notifications (user_id, title, message, type, link)
             VALUES (:uid, :title, :msg, :type, :link)'
        );
        $stmt->execute([':uid'=>$userId, ':title'=>$title, ':msg'=>$message, ':type'=>$type, ':link'=>$link]);
        return (int)$this->db()->lastInsertId();
    }

    /** Get unread notifications for a user (most recent first). */
    public function getUnread(int $userId, int $limit = 20): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM notifications WHERE user_id = :uid AND is_read = 0
             ORDER BY created_at DESC LIMIT ' . $limit
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Get all notifications for a user (most recent first). */
    public function getAll(int $userId, int $limit = 50): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM notifications WHERE user_id = :uid
             ORDER BY created_at DESC LIMIT ' . $limit
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Count unread notifications. */
    public function countUnread(int $userId): int
    {
        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0'
        );
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /** Mark a single notification as read. */
    public function markRead(int $id): void
    {
        $stmt = $this->db()->prepare('UPDATE notifications SET is_read = 1 WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /** Mark all notifications for a user as read. */
    public function markAllRead(int $userId): void
    {
        $stmt = $this->db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0');
        $stmt->execute([':uid' => $userId]);
    }

    /** Check if notifications table exists (graceful degradation). */
    public function tableExists(): bool
    {
        try {
            $this->db()->query('SELECT 1 FROM notifications LIMIT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }
}
