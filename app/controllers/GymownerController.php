<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\LegalDocument;
use App\Models\MembershipApplication;
use App\Models\GymMember;
use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Models\FinancialRecord;
use App\Models\Notification;
use App\Models\MembershipPlan;
use App\Models\GymService;
use App\Models\PayMongoConfig;

final class GymownerController extends Controller
{
    private function requireLogin(): array
    {
        if (!isset($_SESSION['user_id'])) { $this->redirect('auth/login'); }
        $user = (new User())->findById((int)$_SESSION['user_id']);
        if (!$user) { unset($_SESSION['user_id']); $this->redirect('auth/login'); }
        return $user;
    }

    private function requireGymOwner(): array
    {
        $user = $this->requireLogin();
        if ($user['role'] !== 'gym_owner') { $this->redirect('home/index'); }
        return $user;
    }

    private function notify(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): void
    {
        $n = new Notification();
        if ($n->tableExists()) { $n->create($userId, $title, $message, $type, $link); }
    }

    /** Customer applies to become gym owner – upload legal documents */
    public function applyAction(): void
    {
        // Prevent browser caching to ensure fresh data
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $user = $this->requireLogin();
        if ($user['role'] !== 'customer') { $this->redirect('home/index'); }

        $error = ''; $success = '';
        $docModel = new LegalDocument();
        
        // Always fetch fresh data from database
        $existing = $docModel->findByUserId((int)$user['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? 'submit_all';

            if ($action === 'resubmit_doc') {
                // Per-document resubmission: only re-upload the flagged file
                $docField = $_POST['doc_field'] ?? '';
                $allowed = ['cert_registration','mayors_permit','business_name_cert','fire_safety_cert'];
                if (!in_array($docField, $allowed, true) || !$existing) {
                    $error = 'Invalid document field.';
                } elseif (empty($_FILES[$docField]['tmp_name'])) {
                    $error = 'Please select a file to upload.';
                } else {
                    $uploadDir = BASE_PATH . '/public/uploads/legal_documents/';
                    if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
                    $ext = strtolower(pathinfo($_FILES[$docField]['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, ['pdf','jpg','jpeg','png'], true)) {
                        $error = 'Only PDF, JPG, PNG files are allowed.';
                    } else {
                        $filename = $docField . '_' . $user['id'] . '_' . time() . '.' . $ext;
                        if (move_uploaded_file($_FILES[$docField]['tmp_name'], $uploadDir . $filename)) {
                            $path = 'uploads/legal_documents/' . $filename;
                            $docModel->resubmitSingleDoc((int)$existing['id'], $docField, $path);
                            $docModel->recomputeOverallStatus((int)$existing['id']);
                            $success = 'Document resubmitted. Waiting for admin review.';
                        } else {
                            $error = 'Failed to upload file.';
                        }
                    }
                    $existing = $docModel->findByUserId((int)$user['id']);
                }
            } else {
                // Full initial submission with gym details
                $gymName = trim((string)($_POST['gym_name'] ?? ''));
                $gymAddress = trim((string)($_POST['gym_address'] ?? ''));
                $maintenanceCount = (int)($_POST['maintenance_count'] ?? 0);
                $trainerCount = (int)($_POST['trainer_count'] ?? 0);

                // Validate gym details
                if ($gymName === '' || $gymAddress === '') {
                    $error = 'Gym name and address are required.';
                } else {
                    $uploadDir = BASE_PATH . '/public/uploads/legal_documents/';
                    if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

                    // Handle gym logo upload
                    $gymLogo = '';
                    if (!empty($_FILES['gym_logo']['tmp_name'])) {
                        $ext = strtolower(pathinfo($_FILES['gym_logo']['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg','jpeg','png'], true)) {
                            $logoFilename = 'gym_logo_' . $user['id'] . '_' . time() . '.' . $ext;
                            if (move_uploaded_file($_FILES['gym_logo']['tmp_name'], $uploadDir . $logoFilename)) {
                                $gymLogo = 'uploads/legal_documents/' . $logoFilename;
                            }
                        }
                    }

                    $fields = ['cert_registration','mayors_permit','business_name_cert','fire_safety_cert'];
                    $paths = [];

                    foreach ($fields as $f) {
                        if (empty($_FILES[$f]['tmp_name'])) { $error = 'All four documents are required.'; break; }
                        $ext = strtolower(pathinfo($_FILES[$f]['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, ['pdf','jpg','jpeg','png'], true)) { $error = 'Only PDF, JPG, PNG allowed.'; break; }
                        $filename = $f . '_' . $user['id'] . '_' . time() . '.' . $ext;
                        if (!move_uploaded_file($_FILES[$f]['tmp_name'], $uploadDir . $filename)) { $error = 'Failed to upload ' . $f; break; }
                        $paths[$f] = 'uploads/legal_documents/' . $filename;
                    }

                    if ($error === '') {
                        if ($existing && in_array($existing['status'], ['resubmit', 'rejected'], true)) {
                            // Allowed: update the existing record with new files
                            $docModel->updateDocuments((int)$existing['id'], $paths['cert_registration'], $paths['mayors_permit'], $paths['business_name_cert'], $paths['fire_safety_cert']);
                            $success = 'Documents resubmitted successfully. Waiting for admin review.';
                        } elseif (!$existing) {
                            // Allowed: first-time submission — create a single new record
                            $docModel->create((int)$user['id'], $paths['cert_registration'], $paths['mayors_permit'], $paths['business_name_cert'], $paths['fire_safety_cert'], $gymName, $gymLogo, $gymAddress, $maintenanceCount, $trainerCount);
                            $success = 'Application submitted! Waiting for admin review.';
                        } else {
                            // Block: already has an active application (pending or verified)
                            $error = 'You already have an active application (Status: ' . ucfirst($existing['status']) . '). Please wait for the admin to review your existing submission.';
                        }
                        $existing = $docModel->findByUserId((int)$user['id']);
                    }
                }
            }
        }

        // POST-Redirect-GET: after successful submission, redirect to prevent duplicate on refresh
        if ($success !== '') {
            $_SESSION['flash_success'] = $success;
            $this->redirect('gymowner/apply');
        }

        // Restore flash message if redirected
        if (isset($_SESSION['flash_success'])) {
            $success = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
        }

        $this->view('gymowner/apply', ['user' => $user, 'error' => $error, 'success' => $success, 'legalDoc' => $existing]);
    }

    /* ─── Membership Applications ─── */
    public function membershipsAction(): void
    {
        $user = $this->requireGymOwner();
        $apps = (new MembershipApplication())->findAll();
        $trainers = (new Employee())->findAvailableTrainers();
        $this->view('gymowner/memberships', ['user' => $user, 'apps' => $apps, 'trainers' => $trainers]);
    }

    public function reviewmembershipAction(): void
    {
        $user = $this->requireGymOwner();
        $id = (int)($_GET['id'] ?? 0);
        $appModel = new MembershipApplication();
        $app = $appModel->findById($id);
        if (!$app) { $this->redirect('gymowner/memberships'); }

        $error = ''; $success = '';
        $trainers = (new Employee())->findAvailableTrainers();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $feedback = trim((string)($_POST['feedback'] ?? ''));
            $trainerId = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;

            if ($action === 'approve') {
                $paymentType = $app['payment_type'] ?? 'regular_monthly';
                $paymentAmount = round((float)($app['payment_amount'] ?? 0), 2);
                $code = GymMember::generateCode();
                (new GymMember())->create((int)$app['user_id'], $id, $code, $trainerId, $paymentType, $paymentAmount);
                $appModel->updateStatus($id, 'approved', 'Membership approved. Code: ' . $code, (int)$user['id']);
                $this->notify((int)$app['user_id'], 'Membership Approved!', 'Your membership code: ' . $code, 'success', 'membership/verifycode');
                if ($trainerId) { 
                    (new Employee())->setAvailability($trainerId, false); 
                    // Record trainer session revenue
                    (new FinancialRecord())->addRevenue((int)$user['id'], 'Trainer Session: ' . ($app['fullname'] ?? 'N/A'), $paymentAmount, 'Member: ' . ($app['fullname'] ?? 'N/A'), 'Trainer Sessions');
                } else {
                    // Record membership revenue
                    (new FinancialRecord())->addRevenue((int)$user['id'], 'Membership: ' . ucfirst(str_replace('_', ' ', $paymentType)), $paymentAmount, 'Member: ' . ($app['fullname'] ?? 'N/A'), 'Membership Revenue');
                }
                $success = 'Membership approved! Code: ' . $code;
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

        $this->view('gymowner/review_membership', ['user' => $user, 'app' => $app, 'trainers' => $trainers, 'error' => $error, 'success' => $success]);
    }

    public function membersAction(): void
    {
        $user = $this->requireGymOwner();
        $members = (new GymMember())->findAll();
        $this->view('gymowner/members', ['user' => $user, 'members' => $members]);
    }

    public function attendanceAction(): void
    {
        $user = $this->requireGymOwner();
        $logs = (new AttendanceLog())->findAll();
        $this->view('gymowner/attendance', ['user' => $user, 'logs' => $logs]);
    }

    /* ─── Membership Plans Management ─── */
    public function plansAction(): void
    {
        $user = $this->requireGymOwner();
        $planModel = new MembershipPlan();
        $error = ''; $success = '';

        if (!$planModel->tableExists()) {
            $this->view('gymowner/plans', ['user'=>$user, 'plans'=>[], 'error'=>'Please run migration_v5_features.sql first.', 'success'=>'']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $name = trim((string)($_POST['plan_name'] ?? ''));
            $desc = trim((string)($_POST['plan_desc'] ?? ''));
            $price = round((float)($_POST['plan_price'] ?? 0), 2);
            $duration = (int)($_POST['plan_duration'] ?? 30);

            if ($action === 'create') {
                if ($name === '' || $price <= 0) { $error = 'Name and price are required.'; }
                else { $planModel->create((int)$user['id'], $name, $desc, $price, $duration); $success = 'Plan created.'; }
            } elseif ($action === 'update') {
                $pid = (int)($_POST['plan_id'] ?? 0);
                if ($pid > 0 && $name !== '' && $price > 0) { $planModel->update($pid, $name, $desc, $price, $duration); $success = 'Plan updated.'; }
                else { $error = 'Invalid data.'; }
            } elseif ($action === 'delete') {
                $pid = (int)($_POST['plan_id'] ?? 0);
                if ($pid > 0) { $planModel->delete($pid); $success = 'Plan deleted.'; }
            }
        }

        $plans = $planModel->findByOwnerId((int)$user['id']);
        $this->view('gymowner/plans', ['user' => $user, 'plans' => $plans, 'error' => $error, 'success' => $success]);
    }

    /* ─── Gym Services Management ─── */
    public function servicesAction(): void
    {
        $user = $this->requireGymOwner();
        $svcModel = new GymService();
        $error = ''; $success = '';

        if (!$svcModel->tableExists()) {
            $this->view('gymowner/services', ['user'=>$user, 'services'=>[], 'error'=>'Please run migration_v5_features.sql first.', 'success'=>'']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $name = trim((string)($_POST['svc_name'] ?? ''));
            $desc = trim((string)($_POST['svc_desc'] ?? ''));
            // Fix: Use number_format to prevent rounding issues
            $memberPrice = (float)($_POST['svc_member_price'] ?? 0);
            $nonMemberPrice = (float)($_POST['svc_nonmember_price'] ?? 0);

            if ($action === 'create') {
                if ($name === '' || $memberPrice <= 0) { $error = 'Name and member price required.'; }
                else { $svcModel->create((int)$user['id'], $name, $desc, $memberPrice, $nonMemberPrice); $success = 'Service added.'; }
            } elseif ($action === 'update') {
                $sid = (int)($_POST['svc_id'] ?? 0);
                if ($sid > 0 && $name !== '' && $memberPrice > 0) { $svcModel->update($sid, $name, $desc, $memberPrice, $nonMemberPrice); $success = 'Service updated.'; }
                else { $error = 'Invalid data.'; }
            } elseif ($action === 'delete') {
                $sid = (int)($_POST['svc_id'] ?? 0);
                if ($sid > 0) { $svcModel->delete($sid); $success = 'Service deleted.'; }
            }
        }

        $services = $svcModel->findByOwnerId((int)$user['id']);
        $this->view('gymowner/services', ['user' => $user, 'services' => $services, 'error' => $error, 'success' => $success]);
    }

    /* ─── PayMongo Configuration ─── */
    public function paymongoAction(): void
    {
        $user = $this->requireGymOwner();
        $configModel = new PayMongoConfig();
        $error = ''; $success = '';

        if (!$configModel->tableExists()) {
            $this->view('gymowner/paymongo', [
                'user' => $user,
                'config' => null,
                'error' => 'Please run RUN_THIS_SQL_FIRST.sql to create the paymongo_config table.',
                'success' => ''
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'save') {
                $publicKey = trim((string)($_POST['public_key'] ?? ''));
                $secretKey = trim((string)($_POST['secret_key'] ?? ''));
                $isActive = isset($_POST['is_active']) && $_POST['is_active'] === '1';

                if ($publicKey === '' || $secretKey === '') {
                    $error = 'Both public key and secret key are required.';
                } elseif (!str_starts_with($publicKey, 'pk_')) {
                    $error = 'Invalid public key format. Should start with "pk_"';
                } elseif (!str_starts_with($secretKey, 'sk_')) {
                    $error = 'Invalid secret key format. Should start with "sk_"';
                } else {
                    $configModel->upsert((int)$user['id'], $publicKey, $secretKey, $isActive);
                    $success = 'PayMongo configuration saved successfully!';
                }
            } elseif ($action === 'toggle') {
                $config = $configModel->findByOwnerId((int)$user['id']);
                if ($config) {
                    $newStatus = !((bool)$config['is_active']);
                    $configModel->toggleActive((int)$config['id'], $newStatus);
                    $success = $newStatus ? 'PayMongo enabled.' : 'PayMongo disabled.';
                }
            } elseif ($action === 'delete') {
                $config = $configModel->findByOwnerId((int)$user['id']);
                if ($config) {
                    $configModel->delete((int)$config['id']);
                    $success = 'PayMongo configuration deleted.';
                }
            }
        }

        $config = $configModel->findByOwnerId((int)$user['id']);
        $this->view('gymowner/paymongo', [
            'user' => $user,
            'config' => $config,
            'error' => $error,
            'success' => $success
        ]);
    }

    /* ─── Manage Users (Assign / Revoke Administrative Officer) ─── */

    /**
     * List all users so the gym owner can assign the Administrative Officer role.
     */
    public function usersAction(): void
    {
        $user     = $this->requireGymOwner();
        $allUsers = (new User())->findAllExcept('admin');

        $success = $_SESSION['assign_success'] ?? '';
        $error   = $_SESSION['assign_error']   ?? '';
        unset($_SESSION['assign_success'], $_SESSION['assign_error']);

        $this->view('gymowner/users', [
            'user'     => $user,
            'allUsers' => $allUsers,
            'success'  => $success,
            'error'    => $error,
        ]);
    }

    /**
     * Assign or revoke the Administrative Officer role.
     * POST  index.php?r=gymowner/assignofficer
     *   id     — target user ID
     *   action — 'assign' | 'revoke'
     */
    public function assignofficerAction(): void
    {
        $owner = $this->requireGymOwner();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('gymowner/users');
        }

        $targetId = (int)($_POST['id'] ?? 0);
        $action   = $_POST['action'] ?? '';

        if ($targetId <= 0) {
            $_SESSION['assign_error'] = 'Invalid user ID.';
            $this->redirect('gymowner/users');
        }

        $userModel  = new User();
        $targetUser = $userModel->findById($targetId);

        if (!$targetUser) {
            $_SESSION['assign_error'] = 'User not found.';
            $this->redirect('gymowner/users');
        }

        // Safety: never touch admin or another gym owner
        if (in_array($targetUser['role'], ['admin', 'gym_owner'], true)) {
            $_SESSION['assign_error'] = 'Cannot modify an admin or gym owner account.';
            $this->redirect('gymowner/users');
        }

        if ($action === 'assign') {
            $ok = $userModel->updateRole($targetId, 'administrative_officer');
            if ($ok) {
                $this->notify(
                    $targetId,
                    'You Have Been Assigned as Administrative Officer',
                    'The gym owner has assigned you the Administrative Officer role. Please log out and log back in to access the new dashboard.',
                    'success',
                    'home/index'
                );
                $_SESSION['assign_success'] = htmlspecialchars($targetUser['fullname']) . ' has been assigned as Administrative Officer.';
            } else {
                $_SESSION['assign_error'] = 'Failed to assign role. Please try again.';
            }
        } elseif ($action === 'revoke') {
            $ok = $userModel->updateRole($targetId, 'fitness_enthusiast');
            if ($ok) {
                $this->notify(
                    $targetId,
                    'Administrative Officer Role Revoked',
                    'Your Administrative Officer role has been revoked.',
                    'warning',
                    'home/index'
                );
                $_SESSION['assign_success'] = htmlspecialchars($targetUser['fullname']) . "'s Administrative Officer role has been revoked.";
            } else {
                $_SESSION['assign_error'] = 'Failed to revoke role. Please try again.';
            }
        } else {
            $_SESSION['assign_error'] = 'Unknown action.';
        }

        $this->redirect('gymowner/users');
    }
}

