<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\MembershipApplication;
use App\Models\GymMember;
use App\Models\AttendanceLog;
use App\Models\MembershipPlan;
use App\Models\GymService;

final class MembershipController extends Controller
{
    private function requireLogin(): array
    {
        if (!isset($_SESSION['user_id'])) { $this->redirect('auth/login'); }
        $user = (new User())->findById((int)$_SESSION['user_id']);
        if (!$user) { unset($_SESSION['user_id']); $this->redirect('auth/login'); }
        return $user;
    }

    /** Customer applies for gym membership */
    public function applyAction(): void
    {
        $user = $this->requireLogin();
        
        // Get gym_id from URL parameter
        $gymId = (int)($_GET['gym_id'] ?? 0);
        if ($gymId === 0) {
            $this->redirect('membership/gyms');
        }
        
        // Get gym details from legal_documents
        $legalDocModel = new \App\Models\LegalDocument();
        $gym = $legalDocModel->findById($gymId);
        
        if (!$gym || $gym['status'] !== 'verified') {
            $this->redirect('membership/gyms');
        }
        
        $error = ''; $success = '';
        $appModel = new MembershipApplication();
        $existing = $appModel->findByUserId((int)$user['id']);

        // Load dynamic plans and services for this gym
        $planModel = new MembershipPlan();
        $svcModel = new GymService();
        $plans = $planModel->tableExists() ? $planModel->findByOwnerId((int)$gym['user_id']) : [];
        $services = $svcModel->tableExists() ? $svcModel->findByOwnerId((int)$gym['user_id']) : [];

        // Generate PayMongo payment link if application is verified and payment mode is online
        $paymongoLink = null;
        if ($existing && $existing['status'] === 'verified' && ($existing['payment_mode'] ?? 'cash') === 'online') {
            $paymongoLink = $this->generatePayMongoLink($existing, (int)$gym['user_id']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fn = trim((string)($_POST['first_name'] ?? ''));
            $ln = trim((string)($_POST['last_name'] ?? ''));
            $mi = trim((string)($_POST['middle_initial'] ?? ''));
            $phone = trim((string)($_POST['phone_number'] ?? ''));
            $serviceId = !empty($_POST['service_id']) ? (int)$_POST['service_id'] : null;
            $paymentMode = trim((string)($_POST['payment_mode'] ?? 'cash'));
            $action = $_POST['action'] ?? 'submit';

            // Get service details and calculate price
            $paymentAmount = 0.0;
            $paymentType = 'membership';
            if ($serviceId) {
                $service = $svcModel->findById($serviceId);
                if ($service) {
                    $paymentAmount = (float)$service['member_price'];
                    $paymentType = $service['name'];
                } else {
                    $error = 'Invalid service selected.';
                }
            } else {
                $error = 'Please select a service.';
            }

            if ($error === '') {
                if ($fn === '' || $ln === '') { $error = 'First and last name are required.'; }
                elseif ($phone === '') { $error = 'Phone number is required.'; }
                else {
                    // No trainer_id — admin officer assigns later
                    $trainerId = null;
                    if ($action === 'resubmit' && $existing && in_array($existing['status'], ['rejected', 'resubmit'], true)) {
                        $appModel->resubmitWithService((int)$existing['id'], $fn, $ln, $mi, $phone, $trainerId, $paymentType, $serviceId, $paymentAmount, $paymentMode, (int)$gym['user_id']);
                        $success = 'Application resubmitted! Waiting for review.';
                    } else {
                        if ($appModel->hasActiveApplication((int)$user['id'])) {
                            $error = 'You already have an active membership application.';
                        } else {
                            $appModel->createWithService((int)$user['id'], $fn, $ln, $mi, $phone, $trainerId, $paymentType, $serviceId, $paymentAmount, $paymentMode, (int)$gym['user_id']);
                            $success = 'Membership application submitted! Waiting for approval.';
                        }
                    }
                    $existing = $appModel->findByUserId((int)$user['id']);
                }
            }
        }

        $this->view('membership/apply', [
            'user' => $user, 'error' => $error, 'success' => $success,
            'memberApp' => $existing, 'plans' => $plans, 'services' => $services,
            'gym' => $gym, 'paymongoLink' => $paymongoLink,
        ]);
    }

    /**
     * Generate PayMongo payment link for verified application
     */
    private function generatePayMongoLink(array $application, int $gymOwnerId): ?string
    {
        // Check if PayMongo is configured for this gym owner
        $paymongoModel = new \App\Models\PayMongoConfig();
        if (!$paymongoModel->tableExists()) {
            return null;
        }

        $config = $paymongoModel->findByOwnerId($gymOwnerId);
        if (!$config || !$config['is_active']) {
            return null;
        }

        $secretKey = $config['secret_key'];
        $amount = (float)($application['payment_amount'] ?? 0);
        $description = 'Gym Membership - ' . ($application['payment_type'] ?? 'Membership');
        $remarks = 'membership_app_' . $application['id'];

        // Create PayMongo payment link
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.paymongo.com/v1/links",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "accept: application/json",
                    "authorization: Basic " . base64_encode($secretKey . ":"),
                    "content-type: application/json"
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    "data" => [
                        "attributes" => [
                            "amount" => (int)($amount * 100), // Convert to centavos
                            "description" => $description,
                            "remarks" => $remarks
                        ]
                    ]
                ])
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode === 200 || $httpCode === 201) {
                $data = json_decode($response, true);
                if (isset($data['data']['attributes']['checkout_url'])) {
                    // Save payment reference
                    $paymentId = $data['data']['id'] ?? null;
                    if ($paymentId) {
                        $db = \App\Core\Database::pdo();
                        $stmt = $db->prepare(
                            'UPDATE membership_applications SET paymongo_payment_id = ? WHERE id = ?'
                        );
                        $stmt->execute([$paymentId, $application['id']]);
                    }
                    return $data['data']['attributes']['checkout_url'];
                }
            }
        } catch (\Exception $e) {
            // Log error but don't show to user
            error_log('PayMongo API Error: ' . $e->getMessage());
        }

        return null;
    }

    /** List all verified gyms for membership application */
    public function gymsAction(): void
    {
        $user = $this->requireLogin();
        
        $legalDocModel = new \App\Models\LegalDocument();
        $gyms = $legalDocModel->findAllVerified();
        
        $this->view('membership/gyms', [
            'user' => $user,
            'gyms' => $gyms,
        ]);
    }

    /** Customer verifies membership code */
    public function verifycodeAction(): void
    {
        $user = $this->requireLogin();
        $error = ''; $success = '';
        $gymMember = (new GymMember())->findByUserId((int)$user['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim((string)($_POST['membership_code'] ?? ''));
            if ($code === '') { $error = 'Enter your membership code.'; }
            else {
                $member = (new GymMember())->findByMembershipCode($code);
                if (!$member || (int)$member['user_id'] !== (int)$user['id']) {
                    $error = 'Invalid membership code.';
                } else {
                    (new AttendanceLog())->create((int)$member['id'], $code);
                    $success = 'Membership verified! Welcome, ' . htmlspecialchars($member['fullname']) . '!';
                    $gymMember = $member;
                }
            }
        }

        $logs = [];
        if ($gymMember) { $logs = (new AttendanceLog())->findByMemberId((int)$gymMember['id']); }

        $this->view('membership/verify_code', [
            'user' => $user, 'error' => $error, 'success' => $success,
            'gymMember' => $gymMember, 'logs' => $logs,
        ]);
    }

    public function notifypaymentAction(): void
    {
        $user = $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $appModel = new MembershipApplication();
            $app = $appModel->findById($id);
            
            if ($app && (int)$app['user_id'] === (int)$user['id'] && $app['status'] === 'verified') {
                $appModel->markPaymentSubmitted($id);
                
                // Notify administrative_officer and gym_owner
                $n = new \App\Models\Notification();
                if ($n->tableExists()) {
                    $adminStmt = \App\Core\Database::pdo()->query("SELECT id FROM users WHERE role = 'administrative_officer' LIMIT 1");
                    $admin = $adminStmt->fetch(\PDO::FETCH_ASSOC);
                    if ($admin) {
                        $n->create((int)$admin['id'], 'Payment Submitted', 'Applicant ' . htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) . ' has submitted their online payment.', 'success', 'admofficer/review&id=' . $id);
                    }
                    if (!empty($app['gym_owner_id'])) {
                        $n->create((int)$app['gym_owner_id'], 'Payment Submitted', 'Applicant ' . htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) . ' has submitted their online payment.', 'success');
                    }
                }
                
                $_SESSION['flash_success'] = 'Payment marked as submitted! The Administrative Officer will verify and generate your code shortly.';
            }
        }
        $gymId = $app['gym_owner_id'] ?? 0;
        $this->redirect('membership/apply&gym_id=' . $gymId);
    }
}
