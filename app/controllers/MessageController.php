<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Message;

final class MessageController extends Controller
{
    private function requireAuth(): array
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
        $user = (new User())->findById((int)$_SESSION['user_id']);
        if (!$user) {
            $this->redirect('auth/login');
        }
        return $user;
    }

    /** Renders the main chat UI based on user role */
    public function indexAction(): void
    {
        $user = $this->requireAuth();
        $messageModel = new Message();

        $threads = [];
        $activeThread = null;
        $activePartner = null;
        $activeMessages = [];
        $errorNotice = null;

        $targetUserId = isset($_GET['with']) ? (int)$_GET['with'] : 0;

        if ($user['role'] === 'trainer') {
            $threads = $messageModel->getTrainerClientThreads((int)$user['id']);
            if ($targetUserId > 0) {
                $userModel = new User();
                $activePartner = $userModel->findById($targetUserId);
            } elseif (!empty($threads)) {
                $targetUserId = (int)$threads[0]['client_user_id'];
                $activePartner = (new User())->findById($targetUserId);
            }

            if ($activePartner) {
                // Mark thread read
                $messageModel->markAsRead((int)$user['id'], $targetUserId);
                $activeMessages = $messageModel->getConversation((int)$user['id'], $targetUserId);
            }
        } elseif ($user['role'] === 'customer') {
            $trainerInfo = $messageModel->getClientTrainerInfo((int)$user['id']);
            if ($trainerInfo) {
                $targetUserId = (int)$trainerInfo['trainer_user_id'];
                $activePartner = (new User())->findById($targetUserId);
                $messageModel->markAsRead((int)$user['id'], $targetUserId);
                $activeMessages = $messageModel->getConversation((int)$user['id'], $targetUserId);
            } else {
                $errorNotice = 'You do not currently have an assigned Fitness Trainer. A trainer will be assigned once your fitness request is approved.';
            }
        } elseif ($user['role'] === 'gym_owner') {
            $threads = $messageModel->getGymOwnerConversations((int)$user['id']);
            if ($targetUserId > 0 && isset($_GET['client'])) {
                $clientUserId = (int)$_GET['client'];
                $trainerUserId = $targetUserId;
                $activePartner = (new User())->findById($clientUserId);
                $activeMessages = $messageModel->getConversation($trainerUserId, $clientUserId);
            } elseif (!empty($threads)) {
                $targetUserId = (int)$threads[0]['trainer_user_id'];
                $clientUserId = (int)$threads[0]['client_user_id'];
                $activePartner = (new User())->findById($clientUserId);
                $activeMessages = $messageModel->getConversation($targetUserId, $clientUserId);
            }
        } else {
            // Admin or other staff fallback
            $this->redirect('home/index');
        }

        $this->view('message/index', [
            'user'           => $user,
            'threads'        => $threads,
            'activePartner'  => $activePartner,
            'activeMessages' => $activeMessages,
            'errorNotice'    => $errorNotice,
            'targetUserId'   => $targetUserId,
        ]);
    }

    /** AJAX Fetch Messages for active thread */
    public function fetchAction(): void
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $partnerId = isset($_GET['partner_id']) ? (int)$_GET['partner_id'] : 0;
        $trainerId = isset($_GET['trainer_id']) ? (int)$_GET['trainer_id'] : 0;

        if ($partnerId <= 0 && $trainerId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid partner ID']);
            return;
        }

        $messageModel = new Message();

        if ($trainerId > 0 && $partnerId > 0) {
            // Gym owner read-only view
            $messages = $messageModel->getConversation($trainerId, $partnerId);
        } else {
            $messageModel->markAsRead($userId, $partnerId);
            $messages = $messageModel->getConversation($userId, $partnerId);
        }

        echo json_encode([
            'success'  => true,
            'messages' => $messages,
            'user_id'  => $userId,
        ]);
    }

    /** AJAX Send Message */
    public function sendAction(): void
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $senderId = (int)$_SESSION['user_id'];
        $receiverId = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
        $text = isset($_POST['message_text']) ? trim((string)$_POST['message_text']) : '';
        $requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : null;

        if ($receiverId <= 0 || $text === '') {
            echo json_encode(['success' => false, 'error' => 'Receiver and message text are required.']);
            return;
        }

        $messageModel = new Message();

        // Validate assignment before allowing message
        if (!$messageModel->validateAssignment($senderId, $receiverId)) {
            echo json_encode(['success' => false, 'error' => 'Messaging is only permitted between a client and their assigned trainer.']);
            return;
        }

        $messageId = $messageModel->sendMessage($senderId, $receiverId, $text, $requestId);

        echo json_encode([
            'success'    => true,
            'message_id' => $messageId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** AJAX Total unread count for navbar badge polling */
    public function unreadCountAction(): void
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'unread_count' => 0]);
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $count = (new Message())->getUnreadCount($userId);

        echo json_encode([
            'success'      => true,
            'unread_count' => $count,
        ]);
    }
}
