<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * LoginActivity Model - Tracks user login/logout activities
 * 
 * Security Feature: Logging and Monitoring
 * Tracks all authentication activities for security auditing
 */
final class LoginActivity extends Model
{
    /**
     * Log a login/logout activity
     * 
     * @param int|null $userId User ID (null for failed attempts)
     * @param string $email User email
     * @param string $activityType Type: login_success, login_failed, logout, otp_sent, otp_failed
     * @param string|null $failureReason Reason for failure (if applicable)
     * @return int Activity ID
     */
    public function log(
        ?int $userId,
        string $email,
        string $activityType,
        ?string $failureReason = null
    ): int {
        $ipAddress = $this->getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $sessionId = session_id();
        
        $stmt = $this->db()->prepare(
            'INSERT INTO login_activity 
             (user_id, email, activity_type, ip_address, user_agent, session_id, failure_reason, created_at)
             VALUES (:uid, :email, :type, :ip, :ua, :sid, :reason, NOW())'
        );
        
        $stmt->execute([
            ':uid' => $userId,
            ':email' => $email,
            ':type' => $activityType,
            ':ip' => $ipAddress,
            ':ua' => $userAgent,
            ':sid' => $sessionId,
            ':reason' => $failureReason,
        ]);
        
        return (int)$this->db()->lastInsertId();
    }

    /**
     * Log successful login
     */
    public function logLoginSuccess(int $userId, string $email): int
    {
        return $this->log($userId, $email, 'login_success');
    }

    /**
     * Log failed login attempt
     */
    public function logLoginFailed(string $email, string $reason): int
    {
        return $this->log(null, $email, 'login_failed', $reason);
    }

    /**
     * Log logout
     */
    public function logLogout(int $userId, string $email): int
    {
        return $this->log($userId, $email, 'logout');
    }

    /**
     * Log OTP sent
     */
    public function logOtpSent(int $userId, string $email): int
    {
        return $this->log($userId, $email, 'otp_sent');
    }

    /**
     * Log OTP failed
     */
    public function logOtpFailed(int $userId, string $email, string $reason): int
    {
        return $this->log($userId, $email, 'otp_failed', $reason);
    }

    /**
     * Get all activities (paginated)
     */
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db()->prepare(
            'SELECT la.*, u.fullname, u.role 
             FROM login_activity la
             LEFT JOIN users u ON u.id = la.user_id
             ORDER BY la.created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get activities for specific user
     */
    public function findByUserId(int $userId, int $limit = 50): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM login_activity 
             WHERE user_id = :uid 
             ORDER BY created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get failed login attempts for email (last hour)
     */
    public function getFailedAttempts(string $email, int $hours = 1): int
    {
        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) as count 
             FROM login_activity 
             WHERE email = :email 
             AND activity_type = "login_failed"
             AND created_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)'
        );
        $stmt->execute([':email' => $email, ':hours' => $hours]);
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get recent activities (last 24 hours)
     */
    public function getRecentActivities(int $hours = 24, int $limit = 100): array
    {
        $stmt = $this->db()->prepare(
            'SELECT la.*, u.fullname, u.role 
             FROM login_activity la
             LEFT JOIN users u ON u.id = la.user_id
             WHERE la.created_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)
             ORDER BY la.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':hours', $hours, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get activity statistics
     */
    public function getStatistics(int $days = 7): array
    {
        $stmt = $this->db()->prepare(
            'SELECT 
                activity_type,
                COUNT(*) as count,
                DATE(created_at) as date
             FROM login_activity
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             GROUP BY activity_type, DATE(created_at)
             ORDER BY date DESC, activity_type'
        );
        $stmt->execute([':days' => $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get currently active sessions
     */
    public function getActiveSessions(): array
    {
        $sql = 'SELECT la.*, u.fullname, u.role, u.email as user_email
                FROM login_activity la
                JOIN users u ON u.id = la.user_id
                WHERE la.activity_type = "login_success"
                AND la.user_id NOT IN (
                    SELECT user_id FROM login_activity 
                    WHERE activity_type = "logout" 
                    AND user_id IS NOT NULL
                    AND created_at > la.created_at
                )
                ORDER BY la.created_at DESC';
        
        return $this->db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if table exists
     */
    public function tableExists(): bool
    {
        try {
            $this->db()->query('SELECT 1 FROM login_activity LIMIT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Get client IP address (handles proxies)
     */
    private function getClientIp(): string
    {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key]) && filter_var($_SERVER[$key], FILTER_VALIDATE_IP)) {
                return $_SERVER[$key];
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
}
