<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;
use App\Models\MarketingOfficer;
use App\Models\AdCampaign;
use App\Models\GymPromotion;
use App\Models\Notification;
use App\Models\CampaignInterest;
use PDO;

final class MarketingController extends Controller
{
    /* ─── Auth Guards ─── */

    private function requireLogin(): array
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
        $user = (new User())->findById((int)$_SESSION['user_id']);
        if (!$user) {
            unset($_SESSION['user_id']);
            $this->redirect('auth/login');
        }
        return $user;
    }

    private function requireMarketingOfficer(): array
    {
        $user = $this->requireLogin();
        if ($user['role'] !== 'marketing_officer') {
            $this->redirect('home/index');
        }
        return $user;
    }

    private function getGymId(int $userId): int
    {
        $mo = (new MarketingOfficer())->findByUserId($userId);
        return $mo ? (int)$mo['gym_id'] : 0;
    }

    private function getMarketingOfficerId(int $userId): int
    {
        $mo = (new MarketingOfficer())->findByUserId($userId);
        return $mo ? (int)$mo['id'] : 0;
    }

    private function notifyGymMembers(int $gymId, string $title, string $message, string $type = 'info', ?string $link = null): void
    {
        $n = new Notification();
        if (!$n->tableExists()) {
            return;
        }

        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare(
                'SELECT gm.user_id 
                 FROM gym_members gm 
                 JOIN membership_applications ma ON ma.id = gm.application_id 
                 WHERE ma.gym_owner_id = :gym_id'
            );
            $stmt->execute([':gym_id' => $gymId]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($members as $m) {
                $n->create((int)$m['user_id'], $title, $message, $type, $link);
            }
        } catch (\PDOException $e) {
            // Graceful degradation on notification errors
            error_log("notifyGymMembers failed: " . $e->getMessage());
        }
    }

    /* ─────────────────────────────────────────────────────────────
       PAGE 1 — DASHBOARD
       Route: index.php?r=marketing/dashboard
    ───────────────────────────────────────────────────────────── */
    public function dashboardAction(): void
    {
        $user = $this->requireMarketingOfficer();
        $userId = (int)$user['id'];
        $gymId = $this->getGymId($userId);
        $moId = $this->getMarketingOfficerId($userId);

        $activeCampaignsCount = (new AdCampaign())->countActiveByGym($gymId);
        $activePromotionsCount = (new GymPromotion())->countActiveByGym($gymId);

        $pdo = Database::pdo();
        
        // Gym Members Count
        $stmtMembers = $pdo->prepare(
            'SELECT COUNT(*) FROM gym_members gm
             JOIN membership_applications ma ON ma.id = gm.application_id
             WHERE ma.gym_owner_id = :gym_id'
        );
        $stmtMembers->execute([':gym_id' => $gymId]);
        $gymMembersCount = (int)$stmtMembers->fetchColumn();

        // Today's Attendance Count
        $stmtTodayAttendance = $pdo->prepare(
            'SELECT COUNT(*) FROM attendance_log al
             JOIN gym_members gm ON gm.id = al.member_id
             JOIN membership_applications ma ON ma.id = gm.application_id
             WHERE ma.gym_owner_id = :gym_id AND DATE(al.check_in) = CURDATE()'
        );
        $stmtTodayAttendance->execute([':gym_id' => $gymId]);
        $todayAttendanceCount = (int)$stmtTodayAttendance->fetchColumn();

        // Attendance past 7 days
        $past7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stmtDay = $pdo->prepare(
                'SELECT COUNT(*) FROM attendance_log al
                 JOIN gym_members gm ON gm.id = al.member_id
                 JOIN membership_applications ma ON ma.id = gm.application_id
                 WHERE ma.gym_owner_id = :gym_id AND DATE(al.check_in) = :date'
            );
            $stmtDay->execute([':gym_id' => $gymId, ':date' => $date]);
            $past7Days[date('D (m/d)', strtotime($date))] = (int)$stmtDay->fetchColumn();
        }

        // Recent Campaigns
        $campaigns = (new AdCampaign())->findByMarketingOfficer($moId);
        $recentCampaigns = array_slice($campaigns, 0, 5);
        foreach ($recentCampaigns as &$c) {
            $c['registration_count'] = 0;
            if (!empty($c['extra_json'])) {
                $extra = json_decode($c['extra_json'], true);
                if (isset($extra['registrations']) && is_array($extra['registrations'])) {
                    $c['registration_count'] = count($extra['registrations']);
                }
            }
        }
        unset($c);

        $this->view('marketing/dashboard', [
            'user'                 => $user,
            'activeCampaignsCount' => $activeCampaignsCount,
            'activePromotionsCount'=> $activePromotionsCount,
            'gymMembersCount'      => $gymMembersCount,
            'todayAttendanceCount' => $todayAttendanceCount,
            'past7Days'            => $past7Days,
            'recentCampaigns'      => $recentCampaigns,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
       PAGE 2 — ATTENDANCE LOG VIEW
       Route: index.php?r=marketing/attendance
    ───────────────────────────────────────────────────────────── */
    public function attendanceAction(): void
    {
        $user = $this->requireMarketingOfficer();
        $gymId = $this->getGymId((int)$user['id']);
        $pdo = Database::pdo();

        // Filters
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $membershipType = $_GET['membership_type'] ?? '';

        // Summary Stats
        // 1. Today
        $stmtToday = $pdo->prepare(
            'SELECT COUNT(*) FROM attendance_log al
             JOIN gym_members gm ON gm.id = al.member_id
             JOIN membership_applications ma ON ma.id = gm.application_id
             WHERE ma.gym_owner_id = :gym_id AND DATE(al.check_in) = CURDATE()'
        );
        $stmtToday->execute([':gym_id' => $gymId]);
        $todayVisits = (int)$stmtToday->fetchColumn();

        // 2. This Week
        $stmtWeek = $pdo->prepare(
            'SELECT COUNT(*) FROM attendance_log al
             JOIN gym_members gm ON gm.id = al.member_id
             JOIN membership_applications ma ON ma.id = gm.application_id
             WHERE ma.gym_owner_id = :gym_id AND al.check_in >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)'
        );
        $stmtWeek->execute([':gym_id' => $gymId]);
        $weekVisits = (int)$stmtWeek->fetchColumn();

        // 3. This Month
        $stmtMonth = $pdo->prepare(
            'SELECT COUNT(*) FROM attendance_log al
             JOIN gym_members gm ON gm.id = al.member_id
             JOIN membership_applications ma ON ma.id = gm.application_id
             WHERE ma.gym_owner_id = :gym_id AND al.check_in >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)'
        );
        $stmtMonth->execute([':gym_id' => $gymId]);
        $monthVisits = (int)$stmtMonth->fetchColumn();

        // Daily attendance for past 30 days
        $past30Days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stmtDay = $pdo->prepare(
                'SELECT COUNT(*) FROM attendance_log al
                 JOIN gym_members gm ON gm.id = al.member_id
                 JOIN membership_applications ma ON ma.id = gm.application_id
                 WHERE ma.gym_owner_id = :gym_id AND DATE(al.check_in) = :date'
            );
            $stmtDay->execute([':gym_id' => $gymId, ':date' => $date]);
            $past30Days[date('m/d', strtotime($date))] = (int)$stmtDay->fetchColumn();
        }

        // Attendance Table Query
        $sql = 'SELECT al.*, u.fullname, gm.membership_type 
                FROM attendance_log al
                JOIN gym_members gm ON gm.id = al.member_id
                JOIN membership_applications ma ON ma.id = gm.application_id
                JOIN users u ON u.id = gm.user_id
                WHERE ma.gym_owner_id = :gym_id';
        
        $params = [':gym_id' => $gymId];

        if ($startDate !== '') {
            $sql .= ' AND DATE(al.check_in) >= :start_date';
            $params[':start_date'] = $startDate;
        }
        if ($endDate !== '') {
            $sql .= ' AND DATE(al.check_in) <= :end_date';
            $params[':end_date'] = $endDate;
        }
        if ($membershipType !== '') {
            $sql .= ' AND gm.membership_type = :mem_type';
            $params[':mem_type'] = $membershipType;
        }

        $sql .= ' ORDER BY al.check_in DESC';

        $stmtTable = $pdo->prepare($sql);
        $stmtTable->execute($params);
        $logs = $stmtTable->fetchAll(PDO::FETCH_ASSOC);

        // Handle CSV Export
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=attendance_report_' . date('Ymd_His') . '.csv');
            $output = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($output, ['Date', 'Member Name', 'Check-in Time', 'Membership Type', 'Duration (Min)', 'Visit Type']);
            
            foreach ($logs as $log) {
                fputcsv($output, [
                    date('Y-m-d', strtotime($log['check_in'])),
                    $log['fullname'],
                    date('H:i:s', strtotime($log['check_in'])),
                    ucwords(str_replace('_', ' ', (string)($log['membership_type'] ?? ''))),
                    $log['duration_minutes'] ?? 'N/A',
                    ucfirst((string)($log['visit_type'] ?? 'regular'))
                ]);
            }
            fclose($output);
            exit;
        }

        // Get unique membership types for filtering
        $stmtTypes = $pdo->prepare(
            'SELECT DISTINCT gm.membership_type FROM gym_members gm
             JOIN membership_applications ma ON ma.id = gm.application_id
             WHERE ma.gym_owner_id = :gym_id AND gm.membership_type IS NOT NULL AND gm.membership_type != ""'
        );
        $stmtTypes->execute([':gym_id' => $gymId]);
        $membershipTypes = $stmtTypes->fetchAll(PDO::FETCH_COLUMN);

        $this->view('marketing/attendance', [
            'user'            => $user,
            'todayVisits'     => $todayVisits,
            'weekVisits'      => $weekVisits,
            'monthVisits'     => $monthVisits,
            'past30Days'      => $past30Days,
            'logs'            => $logs,
            'membershipTypes' => $membershipTypes,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'membershipType'  => $membershipType,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
       PAGE 3 — AD CAMPAIGNS CRUD
       Route: index.php?r=marketing/campaigns
    ───────────────────────────────────────────────────────────── */
    public function campaignsAction(): void
    {
        $user = $this->requireMarketingOfficer();
        $moId = $this->getMarketingOfficerId((int)$user['id']);
        $gymId = $this->getGymId((int)$user['id']);
        
        $campaignModel = new AdCampaign();
        $success = $_SESSION['campaign_success'] ?? '';
        $error = $_SESSION['campaign_error'] ?? '';
        unset($_SESSION['campaign_success'], $_SESSION['campaign_error']);

        // Handle status toggle / delete actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $id = (int)($_POST['id'] ?? 0);

            if ($id > 0) {
                $campaign = $campaignModel->findById($id);
                if ($campaign && (int)$campaign['marketing_officer_id'] === $moId) {
                    if ($action === 'delete') {
                        $campaignModel->delete($id);
                        $_SESSION['campaign_success'] = 'Campaign deleted successfully.';
                    } elseif ($action === 'activate') {
                        $campaignModel->updateStatus($id, 'active');
                        // Notify members
                        $this->notifyGymMembers($gymId, $campaign['title'] . ' — Check out our latest promotion!', 'A new ad campaign is now active: ' . $campaign['title'] . '.', 'info', 'member/dashboard');
                        $_SESSION['campaign_success'] = 'Campaign activated and members notified!';
                    } elseif ($action === 'end') {
                        $campaignModel->updateStatus($id, 'ended');
                        $_SESSION['campaign_success'] = 'Campaign ended successfully.';
                    }
                    $this->redirect('marketing/campaigns');
                } else {
                    $error = 'Campaign not found or unauthorized.';
                }
            }
        }

        $campaigns = $campaignModel->findByMarketingOfficer($moId);

        // Load bulk interest counts for all campaigns
        $campaignInterestCounts = [];
        $ciModel = new CampaignInterest();
        if ($ciModel->tableExists() && !empty($campaigns)) {
            $ids = array_column($campaigns, 'id');
            $campaignInterestCounts = $ciModel->getBulkCampaignInterestCounts($ids);
        }

        $this->view('marketing/campaigns', [
            'user'                   => $user,
            'campaigns'              => $campaigns,
            'campaignInterestCounts' => $campaignInterestCounts,
            'success'                => $success,
            'error'                  => $error,
        ]);
    }

    public function createcampaignAction(): void
    {
        $user = $this->requireMarketingOfficer();
        $moId = $this->getMarketingOfficerId((int)$user['id']);
        $gymId = $this->getGymId((int)$user['id']);

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim((string)($_POST['title'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $targetAudience = $_POST['target_audience'] ?? 'all_members';
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            $status = $_POST['status'] ?? 'draft';

            if ($title === '' || $startDate === '' || $endDate === '') {
                $error = 'Title, Start Date, and End Date are required.';
            } else {
                $imagePath = null;
                // Handle file upload
                if (!empty($_FILES['banner_image']['tmp_name'])) {
                    $uploadDir = BASE_PATH . '/public/uploads/campaigns/';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $ext = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
                        $filename = 'campaign_' . time() . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $uploadDir . $filename)) {
                            $imagePath = 'uploads/campaigns/' . $filename;
                        }
                    } else {
                        $error = 'Only JPG, PNG, GIF banners are allowed.';
                    }
                }

                if ($error === '') {
                    $campaignModel = new AdCampaign();
                    $cid = $campaignModel->create($moId, $gymId, $title, $description, $imagePath, $targetAudience, $status, $startDate, $endDate);
                    
                    if ($status === 'active') {
                        $this->notifyGymMembers($gymId, $title . ' — Check out our latest promotion!', 'A new ad campaign is now active: ' . $title . '.', 'info', 'member/dashboard');
                    }

                    $_SESSION['campaign_success'] = 'Campaign created successfully.';
                    $this->redirect('marketing/campaigns');
                }
            }
        }

        $this->view('marketing/campaign-form', [
            'user'     => $user,
            'campaign' => null,
            'error'    => $error,
        ]);
    }

    public function editcampaignAction(): void
    {
        $user = $this->requireMarketingOfficer();
        $moId = $this->getMarketingOfficerId((int)$user['id']);
        $gymId = $this->getGymId((int)$user['id']);

        $id = (int)($_GET['id'] ?? 0);
        $campaignModel = new AdCampaign();
        $campaign = $campaignModel->findById($id);

        if (!$campaign || (int)$campaign['marketing_officer_id'] !== $moId) {
            $this->redirect('marketing/campaigns');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim((string)($_POST['title'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $targetAudience = $_POST['target_audience'] ?? 'all_members';
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            $status = $_POST['status'] ?? 'draft';

            if ($title === '' || $startDate === '' || $endDate === '') {
                $error = 'Title, Start Date, and End Date are required.';
            } else {
                $imagePath = null;
                // Handle file upload
                if (!empty($_FILES['banner_image']['tmp_name'])) {
                    $uploadDir = BASE_PATH . '/public/uploads/campaigns/';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $ext = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
                        $filename = 'campaign_' . time() . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $uploadDir . $filename)) {
                            $imagePath = 'uploads/campaigns/' . $filename;
                        }
                    } else {
                        $error = 'Only JPG, PNG, GIF banners are allowed.';
                    }
                }

                if ($error === '') {
                    $campaignModel->update($id, $title, $description, $imagePath, $targetAudience, $status, $startDate, $endDate);
                    
                    if ($status === 'active' && $campaign['status'] !== 'active') {
                        $this->notifyGymMembers($gymId, $title . ' — Check out our latest promotion!', 'A new ad campaign is now active: ' . $title . '.', 'info', 'member/dashboard');
                    }

                    $_SESSION['campaign_success'] = 'Campaign updated successfully.';
                    $this->redirect('marketing/campaigns');
                }
            }
        }

        $this->view('marketing/campaign-form', [
            'user'     => $user,
            'campaign' => $campaign,
            'error'    => $error,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
       PAGE 4 — GYM PROMOTIONS CRUD
       Route: index.php?r=marketing/promotions
    ───────────────────────────────────────────────────────────── */
    public function promotionsAction(): void
    {
        $user = $this->requireMarketingOfficer();
        $moId = $this->getMarketingOfficerId((int)$user['id']);
        $gymId = $this->getGymId((int)$user['id']);

        $promotionModel = new GymPromotion();
        $success = $_SESSION['promotion_success'] ?? '';
        $error = $_SESSION['promotion_error'] ?? '';
        unset($_SESSION['promotion_success'], $_SESSION['promotion_error']);

        // Handle status toggle / delete actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $id = (int)($_POST['id'] ?? 0);

            if ($id > 0) {
                $promotion = $promotionModel->findById($id);
                if ($promotion && (int)$promotion['marketing_officer_id'] === $moId) {
                    if ($action === 'delete') {
                        $promotionModel->delete($id);
                        $_SESSION['promotion_success'] = 'Promotion deleted successfully.';
                    } elseif ($action === 'activate') {
                        $promotionModel->updateStatus($id, 'active');
                        // Notify members
                        $this->notifyGymMembers($gymId, 'New promo available: ' . $promotion['title'] . '. Use code ' . $promotion['promo_code'] . '!', 'A new promotion is now active: ' . $promotion['title'] . '.', 'success', 'member/dashboard');
                        $_SESSION['promotion_success'] = 'Promotion activated and members notified!';
                    }
                    $this->redirect('marketing/promotions');
                } else {
                    $error = 'Promotion not found or unauthorized.';
                }
            }
        }

        $promotions = $promotionModel->findByMarketingOfficer($moId);

        $this->view('marketing/promotions', [
            'user'       => $user,
            'promotions' => $promotions,
            'success'    => $success,
            'error'      => $error,
        ]);
    }

    public function createpromotionAction(): void
    {
        $user = $this->requireMarketingOfficer();
        $moId = $this->getMarketingOfficerId((int)$user['id']);
        $gymId = $this->getGymId((int)$user['id']);

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim((string)($_POST['title'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $discountType = $_POST['discount_type'] ?? 'percentage';
            $discountValue = (float)($_POST['discount_value'] ?? 0.0);
            $promoCode = trim((string)($_POST['promo_code'] ?? ''));
            $validFrom = $_POST['valid_from'] ?? '';
            $validUntil = $_POST['valid_until'] ?? '';
            $status = $_POST['status'] ?? 'draft';

            if ($title === '' || $promoCode === '' || $validFrom === '' || $validUntil === '') {
                $error = 'Title, Promo Code, Valid From, and Valid Until dates are required.';
            } else {
                $imagePath = null;
                // Handle file upload
                if (!empty($_FILES['promo_image']['tmp_name'])) {
                    $uploadDir = BASE_PATH . '/public/uploads/promotions/';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $ext = strtolower(pathinfo($_FILES['promo_image']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
                        $filename = 'promo_' . time() . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($_FILES['promo_image']['tmp_name'], $uploadDir . $filename)) {
                            $imagePath = 'uploads/promotions/' . $filename;
                        }
                    } else {
                        $error = 'Only JPG, PNG, GIF promotion images are allowed.';
                    }
                }

                if ($error === '') {
                    $promotionModel = new GymPromotion();
                    $promotionModel->create($moId, $gymId, $title, $description, $discountType, $discountValue, $promoCode, $imagePath, $validFrom, $validUntil, $status);
                    
                    if ($status === 'active') {
                        $this->notifyGymMembers($gymId, 'New promo available: ' . $title . '. Use code ' . $promoCode . '!', 'A new promotion is now active: ' . $title . '.', 'success', 'member/dashboard');
                    }

                    $_SESSION['promotion_success'] = 'Promotion created successfully.';
                    $this->redirect('marketing/promotions');
                }
            }
        }

        $this->view('marketing/promotion-form', [
            'user'      => $user,
            'promotion' => null,
            'error'     => $error,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
       CAMPAIGN BUILDER
       Route: index.php?r=marketing/campaignbuilder
    ───────────────────────────────────────────────────────────── */
    public function campaignbuilderAction(): void
    {
        $user = $this->requireMarketingOfficer();
        $moId = $this->getMarketingOfficerId((int)$user['id']);
        $trainers = (new \App\Models\Employee())->findAvailableTrainers();

        // Load existing campaign-builder campaign (if any)
        $campaignModel = new AdCampaign();
        $existingCampaign = null;
        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare(
                'SELECT * FROM ad_campaigns WHERE marketing_officer_id = :mo_id AND source = "campaign_builder" ORDER BY updated_at DESC LIMIT 1'
            );
            $stmt->execute([':mo_id' => $moId]);
            $existingCampaign = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            // source column may not exist yet; ignore
        }
        if (!$existingCampaign) {
            // Fallback: try without source filter
            try {
                $pdo = Database::pdo();
                $stmt = $pdo->prepare(
                    'SELECT * FROM ad_campaigns WHERE marketing_officer_id = :mo_id ORDER BY updated_at DESC LIMIT 1'
                );
                $stmt->execute([':mo_id' => $moId]);
                // Only use this if it looks like a builder campaign (optional)
            } catch (\PDOException $e) {}
        }

        $this->view('marketing/campaign-builder', [
            'user' => $user,
            'trainers' => $trainers,
            'existingCampaign' => $existingCampaign,
        ]);
    }

    /** AJAX — Save/Publish Campaign Builder data to the database */
    public function savecampaignbuilderAction(): void
    {
        header('Content-Type: application/json');
        $user = $this->requireMarketingOfficer();
        $moId = $this->getMarketingOfficerId((int)$user['id']);
        $gymId = $this->getGymId((int)$user['id']);

        $title       = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $coach       = trim((string)($_POST['coach'] ?? ''));
        $startDate   = $_POST['start_date'] ?? '';
        $endDate     = $_POST['end_date'] ?? '';
        $audience    = $_POST['target_audience'] ?? 'all_members';
        $status      = $_POST['status'] ?? 'active'; // published = active
        $campaignId  = (int)($_POST['campaign_id'] ?? 0);
        $extraJson   = $_POST['extra_json'] ?? '{}'; // pricing, schedules, tags, etc.

        if ($title === '' || $startDate === '' || $endDate === '') {
            echo json_encode(['success' => false, 'error' => 'Title, start date, and end date are required.']);
            exit;
        }

        // Map audience values from builder to DB
        $audienceMap = ['all' => 'all_members', 'new' => 'active_members', 'premium' => 'active_members', 'walkin' => 'all_members'];
        $dbAudience = $audienceMap[$audience] ?? 'all_members';

        // Prepend coach to description if provided
        $fullDesc = $description;
        if ($coach !== '') {
            $fullDesc = 'Coach: ' . $coach . "\n" . $description;
        }

        $pdo = Database::pdo();

        // Ensure 'source' column exists (for identifying builder campaigns)
        try {
            $pdo->exec("ALTER TABLE ad_campaigns ADD COLUMN source VARCHAR(50) DEFAULT 'manual'");
        } catch (\PDOException $e) { /* column already exists */ }

        // Ensure 'extra_json' column exists (for storing pricing, schedules, tags)
        try {
            $pdo->exec('ALTER TABLE ad_campaigns ADD COLUMN extra_json TEXT DEFAULT NULL');
        } catch (\PDOException $e) { /* column already exists */ }

        // Ensure 'updated_at' column exists
        try {
            $pdo->exec('ALTER TABLE ad_campaigns ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        } catch (\PDOException $e) { /* column already exists */ }

        try {
            if ($campaignId > 0) {
                // Fetch the existing extra_json to preserve registrations
                $existStmt = $pdo->prepare('SELECT extra_json FROM ad_campaigns WHERE id = :id');
                $existStmt->execute([':id' => $campaignId]);
                $existingExtraJson = $existStmt->fetchColumn();
                
                $newExtra = json_decode($extraJson, true) ?: [];
                if ($existingExtraJson) {
                    $existingExtra = json_decode($existingExtraJson, true) ?: [];
                    if (isset($existingExtra['registrations'])) {
                        $newExtra['registrations'] = $existingExtra['registrations'];
                    }
                }
                $extraJson = json_encode($newExtra);

                // Update existing
                $stmt = $pdo->prepare(
                    'UPDATE ad_campaigns SET title = :title, description = :desc, target_audience = :audience,
                            status = :status, start_date = :start, end_date = :end, source = "campaign_builder",
                            extra_json = :extra, updated_at = NOW()
                     WHERE id = :id AND marketing_officer_id = :mo_id'
                );
                $stmt->execute([
                    ':title'    => $title,
                    ':desc'     => $fullDesc,
                    ':audience' => $dbAudience,
                    ':status'   => $status,
                    ':start'    => $startDate,
                    ':end'      => $endDate,
                    ':extra'    => $extraJson,
                    ':id'       => $campaignId,
                    ':mo_id'    => $moId,
                ]);
            } else {
                // Insert new
                $stmt = $pdo->prepare(
                    'INSERT INTO ad_campaigns (marketing_officer_id, gym_id, title, description, target_audience, status, start_date, end_date, source, extra_json, created_at, updated_at)
                     VALUES (:mo_id, :gym_id, :title, :desc, :audience, :status, :start, :end, "campaign_builder", :extra, NOW(), NOW())'
                );
                $stmt->execute([
                    ':mo_id'    => $moId,
                    ':gym_id'   => $gymId,
                    ':title'    => $title,
                    ':desc'     => $fullDesc,
                    ':audience' => $dbAudience,
                    ':status'   => $status,
                    ':start'    => $startDate,
                    ':end'      => $endDate,
                    ':extra'    => $extraJson,
                ]);
                $campaignId = (int)$pdo->lastInsertId();
            }

            // Notify members when publishing
            if ($status === 'active') {
                $this->notifyGymMembers(
                    $gymId,
                    $title . ' — New enrollment campaign!',
                    'A new campaign is now live: ' . $title . '. Check it out!',
                    'info',
                    'member/campaigns'
                );
            }

            echo json_encode(['success' => true, 'campaign_id' => $campaignId]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }

    /** AJAX — Unpublish (set to draft) a Campaign Builder campaign */
    public function unpublishcampaignbuilderAction(): void
    {
        header('Content-Type: application/json');
        $user = $this->requireMarketingOfficer();
        $moId = $this->getMarketingOfficerId((int)$user['id']);

        $campaignId = (int)($_POST['campaign_id'] ?? 0);
        if ($campaignId <= 0) {
            echo json_encode(['success' => false, 'error' => 'No campaign ID provided.']);
            exit;
        }

        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare(
                'UPDATE ad_campaigns SET status = "draft", updated_at = NOW() WHERE id = :id AND marketing_officer_id = :mo_id'
            );
            $stmt->execute([':id' => $campaignId, ':mo_id' => $moId]);
            echo json_encode(['success' => true]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }

    public function editpromotionAction(): void
    {
        $user = $this->requireMarketingOfficer();
        $moId = $this->getMarketingOfficerId((int)$user['id']);
        $gymId = $this->getGymId((int)$user['id']);

        $id = (int)($_GET['id'] ?? 0);
        $promotionModel = new GymPromotion();
        $promotion = $promotionModel->findById($id);

        if (!$promotion || (int)$promotion['marketing_officer_id'] !== $moId) {
            $this->redirect('marketing/promotions');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim((string)($_POST['title'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $discountType = $_POST['discount_type'] ?? 'percentage';
            $discountValue = (float)($_POST['discount_value'] ?? 0.0);
            $promoCode = trim((string)($_POST['promo_code'] ?? ''));
            $validFrom = $_POST['valid_from'] ?? '';
            $validUntil = $_POST['valid_until'] ?? '';
            $status = $_POST['status'] ?? 'draft';

            if ($title === '' || $promoCode === '' || $validFrom === '' || $validUntil === '') {
                $error = 'Title, Promo Code, Valid From, and Valid Until dates are required.';
            } else {
                $imagePath = null;
                // Handle file upload
                if (!empty($_FILES['promo_image']['tmp_name'])) {
                    $uploadDir = BASE_PATH . '/public/uploads/promotions/';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $ext = strtolower(pathinfo($_FILES['promo_image']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
                        $filename = 'promo_' . time() . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($_FILES['promo_image']['tmp_name'], $uploadDir . $filename)) {
                            $imagePath = 'uploads/promotions/' . $filename;
                        }
                    } else {
                        $error = 'Only JPG, PNG, GIF promotion images are allowed.';
                    }
                }

                if ($error === '') {
                    $promotionModel->update($id, $title, $description, $discountType, $discountValue, $promoCode, $imagePath, $validFrom, $validUntil, $status);
                    
                    if ($status === 'active' && $promotion['status'] !== 'active') {
                        $this->notifyGymMembers($gymId, 'New promo available: ' . $title . '. Use code ' . $promoCode . '!', 'A new promotion is now active: ' . $title . '.', 'success', 'member/dashboard');
                    }

                    $_SESSION['promotion_success'] = 'Promotion updated successfully.';
                    $this->redirect('marketing/promotions');
                }
            }
        }

        $this->view('marketing/promotion-form', [
            'user'      => $user,
            'promotion' => $promotion,
            'error'     => $error,
        ]);
    }
}
