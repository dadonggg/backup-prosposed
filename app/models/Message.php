<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Message extends Model
{
    /** Ensure messages table exists */
    private function ensureTable(): void
    {
        try {
            $this->db()->query('SELECT 1 FROM messages LIMIT 1');
        } catch (\Exception $e) {
            $this->db()->exec("
                CREATE TABLE IF NOT EXISTS `messages` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `sender_id` INT NOT NULL,
                    `receiver_id` INT NOT NULL,
                    `request_id` INT DEFAULT NULL,
                    `message_text` TEXT NOT NULL,
                    `read_at` DATETIME DEFAULT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    KEY `idx_msg_sender` (`sender_id`),
                    KEY `idx_msg_receiver` (`receiver_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ");
        }
    }

    /** Send a message after validating input */
    public function sendMessage(int $senderId, int $receiverId, string $text, ?int $requestId = null): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO messages (sender_id, receiver_id, request_id, message_text, created_at)
             VALUES (:sender_id, :receiver_id, :request_id, :message_text, NOW())'
        );
        $stmt->execute([
            ':sender_id'    => $senderId,
            ':receiver_id'  => $receiverId,
            ':request_id'   => $requestId,
            ':message_text' => trim($text),
        ]);
        return (int)$this->db()->lastInsertId();
    }

    /** Get message history between two users */
    public function getConversation(int $userId1, int $userId2): array
    {
        $stmt = $this->db()->prepare(
            'SELECT m.*,
                    s.fullname as sender_name, s.profile_picture_url as sender_avatar, s.role as sender_role,
                    r.fullname as receiver_name, r.profile_picture_url as receiver_avatar, r.role as receiver_role
             FROM messages m
             JOIN users s ON s.id = m.sender_id
             JOIN users r ON r.id = m.receiver_id
             WHERE (m.sender_id = :u1 AND m.receiver_id = :u2)
                OR (m.sender_id = :u3 AND m.receiver_id = :u4)
             ORDER BY m.created_at ASC'
        );
        $stmt->execute([
            ':u1' => $userId1,
            ':u2' => $userId2,
            ':u3' => $userId2,
            ':u4' => $userId1,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Mark unread messages sent by senderId to receiverId as read */
    public function markAsRead(int $receiverId, int $senderId): bool
    {
        $this->ensureTable();
        try {
            $stmt = $this->db()->prepare(
                'UPDATE messages
                 SET read_at = NOW()
                 WHERE receiver_id = :receiver_id AND sender_id = :sender_id AND read_at IS NULL'
            );
            return $stmt->execute([
                ':receiver_id' => $receiverId,
                ':sender_id'   => $senderId
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /** Get total unread messages count for a user */
    public function getUnreadCount(int $userId): int
    {
        $this->ensureTable();
        try {
            $stmt = $this->db()->prepare(
                'SELECT COUNT(*) as cnt FROM messages WHERE receiver_id = :u AND read_at IS NULL'
            );
            $stmt->execute([':u' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['cnt'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /** Validate if client and trainer have a valid assignment */
    public function validateAssignment(int $userId1, int $userId2): bool
    {
        // One must be trainer, one client
        $userModel = new User();
        $u1 = $userModel->findById($userId1);
        $u2 = $userModel->findById($userId2);

        if (!$u1 || !$u2) {
            return false;
        }

        $trainerUserId = null;
        $clientUserId = null;

        if ($u1['role'] === 'trainer' && $u2['role'] === 'customer') {
            $trainerUserId = $userId1;
            $clientUserId = $userId2;
        } elseif ($u2['role'] === 'trainer' && $u1['role'] === 'customer') {
            $trainerUserId = $userId2;
            $clientUserId = $userId1;
        } else {
            // Allow trainer to trainer or admin oversight if needed
            return true; 
        }

        // Check if client is assigned to trainer
        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) as cnt
             FROM fitness_service_requests fsr
             JOIN gym_members gm ON gm.id = fsr.member_id
             JOIN employees e ON e.id = fsr.assigned_trainer_id
             WHERE gm.user_id = :client_user_id
               AND e.user_id = :trainer_user_id
               AND fsr.status IN ("assigned", "completed")'
        );
        $stmt->execute([
            ':client_user_id' => $clientUserId,
            ':trainer_user_id' => $trainerUserId,
        ]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ((int)($res['cnt'] ?? 0) > 0) {
            return true;
        }

        // Fallback check: trainer_assignments table if present
        try {
            $stmt2 = $this->db()->prepare(
                'SELECT COUNT(*) as cnt
                 FROM trainer_assignments ta
                 JOIN gym_members gm ON gm.id = ta.client_id
                 JOIN employees e ON e.id = ta.trainer_id
                 WHERE gm.user_id = :client_user_id
                   AND e.user_id = :trainer_user_id
                   AND ta.status = "active"'
            );
            $stmt2->execute([
                ':client_user_id' => $clientUserId,
                ':trainer_user_id' => $trainerUserId,
            ]);
            $res2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ((int)($res2['cnt'] ?? 0) > 0) {
                return true;
            }
        } catch (\Throwable $e) {
            // ignore if table not present
        }

        return false;
    }

    /** Get assigned clients for a trainer with latest message and unread count */
    public function getTrainerClientThreads(int $trainerUserId): array
    {
        $this->ensureTable();
        // First get employee record for trainer
        $employeeModel = new Employee();
        $employee = $employeeModel->findByUserId($trainerUserId);
        if (!$employee) {
            return [];
        }

        try {
            $stmt = $this->db()->prepare(
                'SELECT DISTINCT 
                    u.id as client_user_id,
                    u.fullname as client_name,
                    u.email as client_email,
                    u.profile_picture_url as client_avatar,
                    fsr.id as request_id,
                    fsr.training_type,
                    (SELECT message_text FROM messages 
                     WHERE (sender_id = :tu1 AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = :tu2)
                     ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT created_at FROM messages 
                     WHERE (sender_id = :tu3 AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = :tu4)
                     ORDER BY created_at DESC LIMIT 1) as last_message_time,
                    (SELECT COUNT(*) FROM messages 
                     WHERE sender_id = u.id AND receiver_id = :tu5 AND read_at IS NULL) as unread_count
                 FROM fitness_service_requests fsr
                 JOIN gym_members gm ON gm.id = fsr.member_id
                 JOIN users u ON u.id = gm.user_id
                 WHERE fsr.assigned_trainer_id = :emp_id
                   AND fsr.status IN ("assigned", "completed")
                 ORDER BY last_message_time DESC, client_name ASC'
            );
            $stmt->execute([
                ':tu1' => $trainerUserId,
                ':tu2' => $trainerUserId,
                ':tu3' => $trainerUserId,
                ':tu4' => $trainerUserId,
                ':tu5' => $trainerUserId,
                ':emp_id' => $employee['id']
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /** Get active trainer for a client user */
    public function getClientTrainerInfo(int $clientUserId): ?array
    {
        $this->ensureTable();
        try {
            $stmt = $this->db()->prepare(
                'SELECT 
                    tu.id as trainer_user_id,
                    tu.fullname as trainer_name,
                    tu.email as trainer_email,
                    tu.profile_picture_url as trainer_avatar,
                    fsr.id as request_id,
                    fsr.training_type,
                    (SELECT COUNT(*) FROM messages 
                     WHERE sender_id = tu.id AND receiver_id = :cu1 AND read_at IS NULL) as unread_count
                 FROM fitness_service_requests fsr
                 JOIN gym_members gm ON gm.id = fsr.member_id
                 JOIN employees e ON e.id = fsr.assigned_trainer_id
                 JOIN users tu ON tu.id = e.user_id
                 WHERE gm.user_id = :cu2
                   AND fsr.status IN ("assigned", "completed")
                 ORDER BY fsr.assigned_at DESC
                 LIMIT 1'
            );
            $stmt->execute([
                ':cu1' => $clientUserId,
                ':cu2' => $clientUserId
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /** Get list of all trainer-client active conversations for Gym Owner oversight */
    public function getGymOwnerConversations(int $gymOwnerUserId): array
    {
        $this->ensureTable();
        try {
            $stmt = $this->db()->prepare(
                'SELECT DISTINCT
                    fsr.id as request_id,
                    cu.id as client_user_id,
                    cu.fullname as client_name,
                    cu.profile_picture_url as client_avatar,
                    tu.id as trainer_user_id,
                    tu.fullname as trainer_name,
                    tu.profile_picture_url as trainer_avatar,
                    fsr.training_type,
                    (SELECT message_text FROM messages 
                     WHERE (sender_id = tu.id AND receiver_id = cu.id) OR (sender_id = cu.id AND receiver_id = tu.id)
                     ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT created_at FROM messages 
                     WHERE (sender_id = tu.id AND receiver_id = cu.id) OR (sender_id = cu.id AND receiver_id = tu.id)
                     ORDER BY created_at DESC LIMIT 1) as last_message_time
                 FROM fitness_service_requests fsr
                 JOIN gym_members gm ON gm.id = fsr.member_id
                 JOIN users cu ON cu.id = gm.user_id
                 JOIN employees e ON e.id = fsr.assigned_trainer_id
                 JOIN users tu ON tu.id = e.user_id
                 WHERE e.hired_by = :owner_id
                 ORDER BY last_message_time DESC'
            );
            $stmt->execute([':owner_id' => $gymOwnerUserId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
}
