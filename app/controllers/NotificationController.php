<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;

/**
 * Notification Controller
 * Handles in-app notifications for users
 */
final class NotificationController extends Controller
{
    /**
     * Require authentication
     */
    private function requireAuth(): array
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
        
        $user = (new User())->findById((int)$_SESSION['user_id']);
        if (!$user) {
            unset($_SESSION['user_id']);
            $this->redirect('auth/login');
        }
        
        return ['user' => $user];
    }
    
    /**
     * Display notifications page
     * GET: index.php?r=notification/index
     */
    public function indexAction(): void
    {
        $data = $this->requireAuth();
        $user = $data['user'];
        
        // Get all notifications for this user
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT 50'
        );
        $stmt->execute([':user_id' => $user['id']]);
        $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Render view
        require __DIR__ . '/../views/notifications/index.php';
    }
    
    /**
     * Get unread notification count (AJAX)
     * GET: index.php?r=notification/getUnreadCount
     */
    public function getUnreadCountAction(): void
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            return;
        }
        
        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) as count
                 FROM notifications
                 WHERE user_id = :user_id AND is_read = 0'
            );
            $stmt->execute([':user_id' => $_SESSION['user_id']]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'count' => (int)($result['count'] ?? 0)
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Mark notification as read (AJAX)
     * POST: index.php?r=notification/markAsRead
     */
    public function markAsReadAction(): void
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            return;
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $notificationId = (int)($data['notification_id'] ?? 0);
            
            if ($notificationId === 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
                return;
            }
            
            $pdo = Database::pdo();
            $stmt = $pdo->prepare(
                'UPDATE notifications
                 SET is_read = 1, read_at = NOW()
                 WHERE id = :id AND user_id = :user_id'
            );
            $stmt->execute([
                ':id' => $notificationId,
                ':user_id' => $_SESSION['user_id']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Mark all notifications as read (AJAX)
     * POST: index.php?r=notification/markAllAsRead
     */
    public function markAllAsReadAction(): void
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            return;
        }
        
        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare(
                'UPDATE notifications
                 SET is_read = 1, read_at = NOW()
                 WHERE user_id = :user_id AND is_read = 0'
            );
            $stmt->execute([':user_id' => $_SESSION['user_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
}
