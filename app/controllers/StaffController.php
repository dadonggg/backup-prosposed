<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\StaffApplication;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\UserDocument;

final class StaffController extends Controller
{
    private function requireLogin(): array
    {
        if (!isset($_SESSION['user_id'])) { $this->redirect('auth/login'); }
        $user = (new User())->findById((int)$_SESSION['user_id']);
        if (!$user) { unset($_SESSION['user_id']); $this->redirect('auth/login'); }
        return $user;
    }

    private function notify(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): void
    {
        $n = new Notification();
        if ($n->tableExists()) { $n->create($userId, $title, $message, $type, $link); }
    }

    /** Customer views available gyms to apply as staff */
    public function gymsAction(): void
    {
        $user = $this->requireLogin();
        if ($user['role'] !== 'customer') { $this->redirect('home/index'); }

        $appModel = new StaffApplication();
        $gyms = $appModel->findAvailableGyms();

        $this->view('staff/gyms', ['user' => $user, 'gyms' => $gyms]);
    }

    /**
     * Customer applies as maintenance or trainer — position only, no file uploads.
     * Documents are uploaded separately from Profile & Settings.
     */
    public function applyAction(): void
    {
        $user = $this->requireLogin();
        if ($user['role'] !== 'customer') { $this->redirect('home/index'); }

        $gymOwnerId = (int)($_GET['gym_id'] ?? 0);
        if ($gymOwnerId <= 0) { $this->redirect('staff/gyms'); }

        $error = ''; $success = '';
        $appModel = new StaffApplication();

        // Get gym details
        $gyms = $appModel->findAvailableGyms();
        $selectedGym = null;
        foreach ($gyms as $g) {
            if ($g['gym_owner_id'] == $gymOwnerId) {
                $selectedGym = $g;
                break;
            }
        }
        if (!$selectedGym) { $this->redirect('staff/gyms'); }

        // Check for existing application for this specific gym
        $existing = $appModel->findByUserAndGym((int)$user['id'], $gymOwnerId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = trim($_POST['application_type'] ?? '');
            $positionLabels = ['maintenance' => 'Maintenance Officer', 'trainer' => 'Fitness Trainer'];

            if (!isset($positionLabels[$type])) {
                $error = 'Please select a valid position.';
            } elseif ($existing && in_array($existing['status'], ['pending', 'approved'], true)) {
                $error = 'You already have an active application for this gym (Status: ' . ucfirst($existing['status']) . '). Please wait for the gym owner to review it.';
            } else {
                // Create or re-create the application record (position-only, no files)
                if ($existing && $existing['status'] === 'rejected') {
                    // Update the existing rejected application to re-apply
                    $appModel->updateStatus((int)$existing['id'], 'pending', '', null);
                    $success = 'Application re-submitted for ' . $positionLabels[$type] . ' at ' . htmlspecialchars($selectedGym['gym_name']) . '.';
                } else {
                    $appModel->create((int)$user['id'], $type, $gymOwnerId);
                    $success = 'Application submitted! You applied for the ' . $positionLabels[$type] . ' role at ' . htmlspecialchars($selectedGym['gym_name']) . '.';
                }

                // Notify gym owner
                $this->notify(
                    $gymOwnerId,
                    'New Staff Application — ' . $positionLabels[$type],
                    htmlspecialchars($user['fullname']) . ' has applied for the ' . $positionLabels[$type] . ' position. Review their application in Staff Applications.',
                    'info',
                    'staff/applications'
                );

                $existing = $appModel->findByUserAndGym((int)$user['id'], $gymOwnerId);
            }
        }

        // PRG: redirect on success to prevent duplicate submission on refresh
        if ($success !== '') {
            $_SESSION['flash_success'] = $success;
            $this->redirect('staff/apply&gym_id=' . $gymOwnerId);
        }
        if (isset($_SESSION['flash_success'])) {
            $success = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
        }

        $this->view('staff/apply', [
            'user'     => $user,
            'error'    => $error,
            'success'  => $success,
            'staffApp' => $existing,
            'gym'      => $selectedGym,
        ]);
    }

    /** Gym owner reviews a staff application — shows applicant profile + their uploaded docs */
    public function reviewAction(): void
    {
        $user = $this->requireLogin();
        if ($user['role'] !== 'gym_owner') { $this->redirect('home/index'); }

        $id = (int)($_GET['id'] ?? 0);
        $appModel = new StaffApplication();
        $app = $appModel->findById($id);
        if (!$app) { $this->redirect('staff/applications'); }

        // Load applicant's profile and their uploaded documents
        $userModel     = new User();
        $applicantUser = $userModel->findById((int)$app['user_id']);

        $docModel  = new UserDocument();
        $userDocs  = $docModel->tableExists() ? $docModel->findByUserId((int)$app['user_id']) : [];

        $error = ''; $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action   = $_POST['action'] ?? '';
            $feedback = trim((string)($_POST['feedback'] ?? ''));

            if ($action === 'approve') {
                $appModel->updateStatus($id, 'approved', $feedback, (int)$user['id']);
                (new Employee())->create((int)$app['user_id'], $app['application_type'], (int)$user['id']);
                (new User())->updateRole((int)$app['user_id'], $app['application_type']);

                if ($app['application_type'] === 'maintenance') {
                    (new \App\Models\MaintenanceStaff())->create((int)$app['user_id'], (int)$user['id'], (int)$user['id']);
                }

                $legalDocModel = new \App\Models\LegalDocument();
                $legalDocModel->decrementStaffCount((int)$user['id'], $app['application_type']);

                $posLabel = $app['application_type'] === 'trainer' ? 'Fitness Trainer' : 'Maintenance Officer';
                $this->notify(
                    (int)$app['user_id'],
                    'Staff Application Approved — You are now hired!',
                    'Congratulations! You have been approved as ' . $posLabel . ' at ' . ($user['fullname'] ?? 'the gym') . '. Please log out and log back in to access your new dashboard.',
                    'success',
                    'home/index'
                );
                $success = 'Application approved! User is now a ' . $posLabel . '.';

            } elseif ($action === 'reject') {
                $appModel->updateStatus($id, 'rejected', $feedback, (int)$user['id']);
                $this->notify(
                    (int)$app['user_id'],
                    'Staff Application Rejected',
                    $feedback !== '' ? $feedback : 'Your staff application has been rejected. You may re-apply.',
                    'danger',
                    'staff/gyms'
                );
                $success = 'Application rejected.';
            }

            // Refresh data after action
            $app = $appModel->findById($id);
        }

        $this->view('staff/review', [
            'user'          => $user,
            'app'           => $app,
            'applicantUser' => $applicantUser,
            'userDocs'      => $userDocs,
            'error'         => $error,
            'success'       => $success,
        ]);
    }

    /** Gym owner views all staff applications */
    public function applicationsAction(): void
    {
        $user = $this->requireLogin();
        if ($user['role'] !== 'gym_owner') { $this->redirect('home/index'); }

        $appModel  = new StaffApplication();
        $apps      = $appModel->findByGymOwner((int)$user['id']);
        $employees = (new Employee())->findByGymOwner((int)$user['id']);
        $this->view('staff/applications', ['user' => $user, 'apps' => $apps, 'employees' => $employees]);
    }
}
