<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Notification;

final class NotificationController extends Controller
{
    private function requireLogin(): array
    {
        if (!isset($_SESSION['user_id'])) { $this->redirect('auth/login'); }
        $user = (new User())->findById((int)$_SESSION['user_id']);
        if (!$user) { unset($_SESSION['user_id']); $this->redirect('auth/login'); }
        return $user;
    }

    /** View all notifications */
    public function indexAction(): void
    {
        $user = $this->requireLogin();
        $notifModel = new Notification();
        if (!$notifModel->tableExists()) { $this->redirect('home/index'); }
        $notifications = $notifModel->getAll((int)$user['id']);
        $this->view('notifications/index', ['user' => $user, 'notifications' => $notifications]);
    }

    /** Mark all as read (AJAX or redirect) */
    public function markallreadAction(): void
    {
        $user = $this->requireLogin();
        $notifModel = new Notification();
        if ($notifModel->tableExists()) {
            $notifModel->markAllRead((int)$user['id']);
        }
        $this->redirect('notification/index');
    }

    /** Mark single as read */
    public function markreadAction(): void
    {
        $user = $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $notifModel = new Notification();
        if ($notifModel->tableExists() && $id > 0) {
            $notifModel->markRead($id);
        }
        // Redirect to the notification link if provided
        $link = trim((string)($_GET['link'] ?? ''));
        if ($link !== '') {
            $this->redirect($link);
        } else {
            $this->redirect('notification/index');
        }
    }
}
