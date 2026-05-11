<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\MembershipApplication;
use App\Models\GymMember;
use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Models\FinancialRecord;
use App\Models\Notification;

final class AdmofficerController extends Controller
{
    private function requireOfficer(): array
    {
        if (!isset($_SESSION['user_id'])) { $this->redirect('auth/login'); }
        $user = (new User())->findById((int)$_SESSION['user_id']);
        if (!$user || $user['role'] !== 'administrative_officer') { $this->redirect('home/index'); }
        return $user;
    }

    private function notify(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): void
    {
        $n = new Notification();
        if ($n->tableExists()) { $n->create($userId, $title, $message, $type, $link); }
    }

    /** List all membership applications */
    public function membershipsAction(): void
    {
        $user = $this->requireOfficer();
        $apps = (new MembershipApplication())->findAll();
        $trainers = (new Employee())->findAvailableTrainers();
        $this->view('admofficer/memberships', ['user' => $user, 'apps' => $apps, 'trainers' => $trainers]);
    }

    /** Review a single membership application */
    public function reviewAction(): void
    {
        $user = $this->requireOfficer();
        $id = (int)($_GET['id'] ?? 0);
        $appModel = new MembershipApplication();
        $app = $appModel->findById($id);
        if (!$app) { $this->redirect('admofficer/memberships'); }

        $error = ''; $success = '';
        $trainers = (new Employee())->findAvailableTrainers();
        $employees = (new Employee())->findAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $feedback = trim((string)($_POST['feedback'] ?? ''));
            $trainerId = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;

            if ($action === 'verify') {
                $appModel->updateStatus($id, 'verified', $feedback, (int)$user['id']);
                // Admin officer assigns trainer at this step
                if ($trainerId) {
                    $appModel->assignTrainer($id, $trainerId);
                    $trainer = (new Employee())->findById($trainerId);
                    $trainerName = $trainer ? $trainer['fullname'] : 'a fitness trainer';
                    $this->notify((int)$app['user_id'], 'Trainer Assigned',
                        "A fitness trainer ($trainerName) has been assigned to your membership.",
                        'info', 'membership/apply');
                }
                $this->notify((int)$app['user_id'], 'Application Verified',
                    'Your membership application has been verified. Please proceed with payment.',
                    'success', 'membership/apply');
                $success = 'Application verified!';
            } elseif ($action === 'paid') {
                $paymentType = $app['payment_type'] ?? 'regular_monthly';
                $paymentAmount = round((float)($app['payment_amount'] ?? 0), 2);
                $code = GymMember::generateCode();
                (new GymMember())->create((int)$app['user_id'], $id, $code, $trainerId, $paymentType, $paymentAmount);
                $appModel->updateStatus($id, 'approved', 'Payment confirmed. Code: ' . $code, (int)$user['id']);
                
                // Record revenue in financial_records for gym owner
                if (!empty($app['gym_owner_id'])) {
                    $finModel = new FinancialRecord();
                    $finModel->addRevenue(
                        (int)$app['gym_owner_id'],
                        'Membership Payment - ' . $app['first_name'] . ' ' . $app['last_name'],
                        $paymentAmount,
                        'Payment for ' . $paymentType . ' membership',
                        'Membership Revenue'
                    );
                }
                
                if ($trainerId) {
                    $appModel->assignTrainer($id, $trainerId);
                    (new Employee())->setAvailability($trainerId, false);
                    $trainer = (new Employee())->findById($trainerId);
                    $trainerName = $trainer ? $trainer['fullname'] : 'a fitness trainer';
                    $this->notify((int)$app['user_id'], 'Trainer Assigned',
                        "A fitness trainer ($trainerName) has been assigned to your membership.",
                        'info', 'membership/verifycode');
                }
                $this->notify((int)$app['user_id'], 'Membership Approved!',
                    'Payment confirmed. Your membership code: ' . $code,
                    'success', 'membership/verifycode');
                $success = 'Payment confirmed! Code: ' . $code;
            } elseif ($action === 'assign_trainer') {
                // Assign trainer to an existing approved member
                if ($trainerId) {
                    $appModel->assignTrainer($id, $trainerId);
                    // Also update gym_members record
                    $gymMember = (new GymMember())->findByUserId((int)$app['user_id']);
                    if ($gymMember) {
                        (new GymMember())->assignTrainer((int)$gymMember['id'], $trainerId);
                    }
                    (new Employee())->setAvailability($trainerId, false);
                    $trainer = (new Employee())->findById($trainerId);
                    $trainerName = $trainer ? $trainer['fullname'] : 'a fitness trainer';
                    $this->notify((int)$app['user_id'], 'Trainer Assigned',
                        "A fitness trainer ($trainerName) has been assigned to your membership.",
                        'success', 'membership/apply');
                    $success = "Trainer assigned successfully.";
                } else { $error = 'Select a trainer.'; }
            } elseif ($action === 'reject') {
                if ($feedback === '') { $error = 'Feedback is required.'; }
                else {
                    $appModel->updateStatus($id, 'rejected', $feedback, (int)$user['id']);
                    $this->notify((int)$app['user_id'], 'Membership Rejected', $feedback, 'danger', 'membership/apply');
                    $success = 'Application rejected.';
                }
            } elseif ($action === 'resubmit') {
                if ($feedback === '') { $error = 'Feedback is required.'; }
                else {
                    $appModel->updateStatus($id, 'resubmit', $feedback, (int)$user['id']);
                    $this->notify((int)$app['user_id'], 'Membership — Resubmission Required', $feedback, 'warning', 'membership/apply');
                    $success = 'Resubmission requested.';
                }
            }
            $app = $appModel->findById($id);
        }

        $this->view('admofficer/review', [
            'user' => $user, 'app' => $app, 'trainers' => $trainers,
            'employees' => $employees, 'error' => $error, 'success' => $success,
        ]);
    }

    public function membersAction(): void
    {
        $user = $this->requireOfficer();
        $members = (new GymMember())->findAll();
        $this->view('admofficer/members', ['user' => $user, 'members' => $members]);
    }

    public function attendanceAction(): void
    {
        $user = $this->requireOfficer();
        $logs = (new AttendanceLog())->findAll();
        $this->view('admofficer/attendance', ['user' => $user, 'logs' => $logs]);
    }

    public function employeesAction(): void
    {
        $user = $this->requireOfficer();
        $employees = (new Employee())->findAll();
        $this->view('admofficer/employees', ['user' => $user, 'employees' => $employees]);
    }
}
