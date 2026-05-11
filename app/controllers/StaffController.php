<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\StaffApplication;
use App\Models\Employee;
use App\Models\Notification;

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

    /** Customer applies as maintenance or trainer */
    public function applyAction(): void
    {
        $user = $this->requireLogin();
        if ($user['role'] !== 'customer') { $this->redirect('home/index'); }

        $gymOwnerId = (int)($_GET['gym_id'] ?? 0);
        if ($gymOwnerId <= 0) { $this->redirect('staff/gyms'); }

        $error = ''; $success = '';
        $appModel = new StaffApplication();
        $existing = $appModel->findByUserId((int)$user['id']);
        
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? 'submit';

            if ($action === 'resubmit_doc') {
                // Resubmit a single flagged document
                $docField = $_POST['doc_field'] ?? '';
                if (!in_array($docField, ['medical_certificate', 'resume'], true)) {
                    $error = 'Invalid document field.';
                } elseif (!$existing || $existing['status'] !== 'resubmit') {
                    $error = 'No application to resubmit.';
                } elseif (empty($_FILES[$docField]['tmp_name'])) {
                    $error = 'Please select a file to upload.';
                } else {
                    $uploadDir = BASE_PATH . '/public/uploads/staff_applications/';
                    if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
                    $ext = strtolower(pathinfo($_FILES[$docField]['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, ['pdf','jpg','jpeg','png','doc','docx'], true)) {
                        $error = 'Only PDF, JPG, PNG, DOC files allowed.';
                    } else {
                        $filename = $docField . '_' . $user['id'] . '_' . time() . '.' . $ext;
                        if (!move_uploaded_file($_FILES[$docField]['tmp_name'], $uploadDir . $filename)) {
                            $error = 'Failed to upload file.';
                        } else {
                            $path = 'uploads/staff_applications/' . $filename;
                            $appModel->updateSingleDocument((int)$existing['id'], $docField, $path);

                            // Check if all flagged docs are now fixed → set back to pending
                            $updated = $appModel->findByUserId((int)$user['id']);
                            $allFixed = true;
                            foreach (['medical_certificate_status', 'resume_status'] as $sf) {
                                if (($updated[$sf] ?? 'pending') === 'flagged') { $allFixed = false; }
                            }
                            if ($allFixed) {
                                $appModel->updateStatus((int)$existing['id'], 'pending', '', null);
                            }

                            $success = 'Document resubmitted successfully.';
                            $existing = $appModel->findByUserId((int)$user['id']);
                        }
                    }
                }
            } else {
                // Original full application submit
                $type = $_POST['application_type'] ?? '';
                if (!in_array($type, ['maintenance','trainer'], true)) {
                    $error = 'Select a valid position.';
                } else {
                    $uploadDir = BASE_PATH . '/public/uploads/staff_applications/';
                    if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

                    $fields = ['medical_certificate','resume'];
                    $paths = [];
                    foreach ($fields as $f) {
                        if (empty($_FILES[$f]['tmp_name'])) { $error = 'Both medical certificate and resume are required.'; break; }
                        $ext = strtolower(pathinfo($_FILES[$f]['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, ['pdf','jpg','jpeg','png','doc','docx'], true)) { $error = 'Only PDF, JPG, PNG, DOC files allowed.'; break; }
                        $filename = $f . '_' . $user['id'] . '_' . time() . '.' . $ext;
                        if (!move_uploaded_file($_FILES[$f]['tmp_name'], $uploadDir . $filename)) { $error = 'Failed to upload ' . $f; break; }
                        $paths[$f] = 'uploads/staff_applications/' . $filename;
                    }
                    if ($error === '') {
                        if ($existing && in_array($existing['status'], ['rejected', 'resubmit'], true)) {
                            // Allowed: update existing record with new files
                            $appModel->updateDocuments((int)$existing['id'], $paths['medical_certificate'], $paths['resume']);
                            $success = 'Documents resubmitted. Waiting for review.';
                        } elseif (!$existing) {
                            // Allowed: first-time application — create single record
                            $appModel->create((int)$user['id'], $type, $paths['medical_certificate'], $paths['resume'], $gymOwnerId);
                            $success = 'Application submitted to ' . htmlspecialchars($selectedGym['gym_name']) . '. Waiting for gym owner review.';
                        } else {
                            // Block: active application already exists (pending/approved)
                            $error = 'You already have an active application (Status: ' . ucfirst($existing['status']) . '). Please wait for the gym owner to review it.';
                        }
                        $existing = $appModel->findByUserId((int)$user['id']);
                    }
                }
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
            'user' => $user, 
            'error' => $error, 
            'success' => $success, 
            'staffApp' => $existing,
            'gym' => $selectedGym
        ]);
    }

    /** Gym owner reviews staff applications */
    public function reviewAction(): void
    {
        $user = $this->requireLogin();
        if ($user['role'] !== 'gym_owner') { $this->redirect('home/index'); }

        $id = (int)($_GET['id'] ?? 0);
        $appModel = new StaffApplication();
        $app = $appModel->findById($id);
        if (!$app) { $this->redirect('home/index'); }

        $docLabels = [
            'medical_certificate' => 'Medical Certificate',
            'resume'              => 'Resume / CV',
        ];

        $error = ''; $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action   = $_POST['action'] ?? '';
            $feedback = trim((string)($_POST['feedback'] ?? ''));

            if ($action === 'approve') {
                $appModel->updateStatus($id, 'approved', $feedback, (int)$user['id']);
                (new Employee())->create((int)$app['user_id'], $app['application_type'], (int)$user['id']);
                (new User())->updateRole((int)$app['user_id'], $app['application_type']);
                $legalDocModel = new \App\Models\LegalDocument();
                $legalDocModel->decrementStaffCount((int)$user['id'], $app['application_type']);
                // Notify applicant
                $this->notify(
                    (int)$app['user_id'],
                    'Staff Application Approved — You are now hired!',
                    'Congratulations! You have been approved as ' . ucfirst($app['application_type']) . ' at ' . ($user['fullname'] ?? 'the gym') . '.',
                    'success',
                    'home/index'
                );
                $success = 'Application approved! User is now a ' . ucfirst($app['application_type']) . '.';

            } elseif ($action === 'reject') {
                $appModel->updateStatus($id, 'rejected', $feedback, (int)$user['id']);
                // Notify applicant
                $this->notify(
                    (int)$app['user_id'],
                    'Staff Application Rejected',
                    $feedback !== '' ? $feedback : 'Your staff application has been rejected. You may re-apply.',
                    'danger',
                    'staff/gyms'
                );
                $success = 'Application rejected.';

            } elseif ($action === 'update_doc_status') {
                // Per-document status update
                $docField  = $_POST['doc_field']  ?? '';
                $docStatus = $_POST['doc_status']  ?? 'pending';
                $docComment = trim((string)($_POST['doc_comment'] ?? ''));
                $docChecked = !empty($_POST['doc_checked']);

                if (in_array($docField, array_keys($docLabels), true)) {
                    $appModel->updateDocStatus($id, $docField, $docStatus, $docComment, $docChecked);
                    $appModel->recomputeOverallStatus($id, (int)$user['id']);

                    // Refresh app data after recompute
                    $app = $appModel->findById($id);
                    $label = $docLabels[$docField];

                    if ($docStatus === 'approved') {
                        $msg = "Your $label has been approved.";
                        if ($docComment !== '') { $msg .= " Note: $docComment"; }
                        $this->notify(
                            (int)$app['user_id'],
                            "$label Approved — Staff Application",
                            $msg,
                            'success',
                            'staff/apply&gym_id=' . ($app['gym_owner_id'] ?? 0)
                        );
                        $success = "$label approved and applicant notified.";
                    } elseif ($docStatus === 'flagged') {
                        $msg = $docComment !== ''
                            ? $docComment
                            : "Your $label was flagged. Please review and upload a corrected copy.";
                        $this->notify(
                            (int)$app['user_id'],
                            "$label Flagged — Action Required",
                            $msg,
                            'danger',
                            'staff/apply&gym_id=' . ($app['gym_owner_id'] ?? 0)
                        );
                        $success = "$label flagged and applicant notified.";
                    } else {
                        $success = 'Document status updated.';
                    }
                } else {
                    $error = 'Invalid document field.';
                }
            }
            $app = $appModel->findById($id);
        }

        $this->view('staff/review', ['user' => $user, 'app' => $app, 'error' => $error, 'success' => $success]);
    }

    /** Gym owner views all staff applications */
    public function applicationsAction(): void
    {
        $user = $this->requireLogin();
        if ($user['role'] !== 'gym_owner') { $this->redirect('home/index'); }

        $appModel = new StaffApplication();
        $apps = $appModel->findByGymOwner((int)$user['id']);
        $employees = (new Employee())->findByGymOwner((int)$user['id']);
        $this->view('staff/applications', ['user' => $user, 'apps' => $apps, 'employees' => $employees]);
    }
}
