<?php
declare(strict_types=1);
$pageTitle = $pageTitle ?? 'Nutrify Management';
$currentUser = null;
$userRole = 'guest';
$_notifCount = 0;
$_notifItems = [];
$_msgUnreadCount = 0;
$_clientTrainerAssigned = false;

if (!empty($_SESSION['user_id'])) {
    $currentUser = (new \App\Models\User())->findById((int)$_SESSION['user_id']);
    $userRole = $currentUser['role'] ?? 'customer';
    // Notification bell data
    $__notifModel = new \App\Models\Notification();
    if ($__notifModel->tableExists()) {
        $_notifCount = $__notifModel->countUnread((int)$_SESSION['user_id']);
        $_notifItems = $__notifModel->getUnread((int)$_SESSION['user_id'], 8);
    }
    // Unread message count
    $__msgModel = new \App\Models\Message();
    $_msgUnreadCount = $__msgModel->getUnreadCount((int)$_SESSION['user_id']);
    if ($userRole === 'customer') {
        $_clientTrainerAssigned = !empty($__msgModel->getClientTrainerInfo((int)$_SESSION['user_id']));
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — Nutrify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/bootstrap-app.css">
    <style>
        :root {
            --nf-green: #1B6B2A;
            --nf-green-light: #2E8B3E;
            --nf-green-dark: #145420;
            --nf-accent: #4CAF50;
            --nf-accent-glow: rgba(76,175,80,.25);
            --nf-bg: #f5f7f4;
            --nf-sidebar: #122117;
            --nf-sidebar-hover: rgba(76,175,80,.18);
            --nf-card: #ffffff;
            --nf-text: #1a2e1a;
            --nf-text-secondary: #4a6a4a;
            --nf-muted: #6b8a6b;
            --nf-border: rgba(27,107,42,.12);
            --nf-topbar: #ffffff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--nf-bg);
            color: var(--nf-text);
            min-height: 100vh;
        }

        /* ─── Top navbar ─── */
        .top-navbar {
            background: var(--nf-topbar) !important;
            border-bottom: 1px solid var(--nf-border);
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }
        .top-navbar .navbar-brand {
            color: var(--nf-green) !important;
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: -0.02em;
        }
        .top-navbar .navbar-brand img {
            height: 36px;
            width: auto;
        }

        /* Header Avatar */
        .nav-avatar-img {
            width: 34px; height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--nf-green);
        }
        .nav-avatar-circle {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1B6B2A, #2E8B3E);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .85rem;
            border: 2px solid var(--nf-green);
        }

        /* ─── Sidebar ─── */
        .sidebar {
            position: sticky;
            top: 56px;
            height: calc(100vh - 56px);
            overflow-y: auto;
            background: var(--nf-sidebar);
            border-right: 1px solid rgba(255,255,255,.06);
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.2) transparent;
        }
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.2);
            border-radius: 4px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,.4);
        }
        .sidebar .nav-link, 
        .sidebar .nav-link:link, 
        .sidebar .nav-link:visited {
            color: rgba(255, 255, 255, 0.95) !important;
            padding: .65rem 1.2rem;
            border-radius: .5rem;
            margin: 2px 8px;
            font-size: .875rem;
            font-weight: 600;
            transition: all .2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }
        .sidebar .nav-link span {
            color: rgba(255, 255, 255, 0.95) !important;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #ffffff !important;
            background: linear-gradient(90deg, #1B6B2A, #2E8B3E) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
            color: #4CAF50 !important;
            font-size: 1.05rem;
        }
        .sidebar .nav-section {
            color: #a5d6a7 !important;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: .6rem 1.2rem .3rem;
            margin-top: .75rem;
            font-weight: 700 !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.6);
        }

        /* ─── Main content ─── */
        .main-content {
            background: var(--nf-bg);
            min-height: calc(100vh - 56px);
        }

        /* ─── Cards ─── */
        .card {
            background: var(--nf-card);
            border: 1px solid var(--nf-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .card-header {
            background: rgba(27,107,42,.04);
            border-bottom: 1px solid var(--nf-border);
            color: var(--nf-text);
        }

        /* ─── Stat cards ─── */
        .stat-card {
            background: var(--nf-card);
            border: 1px solid var(--nf-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(27,107,42,.1);
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* ─── Badges ─── */
        .badge-role {
            font-size: .7rem;
            padding: 4px 10px;
            border-radius: 20px;
        }

        /* ─── Tables ─── */
        .table { color: var(--nf-text); }
        .table thead th {
            border-color: var(--nf-border);
            color: var(--nf-muted);
            font-size: .8rem;
            text-transform: uppercase;
            font-weight: 600;
            background: rgba(27,107,42,.03);
        }
        .table td {
            border-color: var(--nf-border);
            color: var(--nf-text);
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background: rgba(27,107,42,.04);
        }

        /* ─── Forms ─── */
        .form-control, .form-select {
            background: #fff;
            border: 1px solid rgba(27,107,42,.2);
            color: var(--nf-text);
        }
        .form-control:focus, .form-select:focus {
            background: #fff;
            border-color: var(--nf-accent);
            color: var(--nf-text);
            box-shadow: 0 0 0 .2rem var(--nf-accent-glow);
        }
        .form-label {
            color: var(--nf-text-secondary);
            font-size: .875rem;
            font-weight: 500;
        }

        /* ─── Text visibility fixes ─── */
        h1, h2, h3, h4, h5, h6 { color: var(--nf-text); }
        .text-muted { color: var(--nf-muted) !important; }
        p, span, label, li, dt, dd, td, th { color: var(--nf-text); }
        .small, small { color: var(--nf-text-secondary); }
        a { color: var(--nf-green); text-decoration: none; }
        a:hover { color: var(--nf-green-dark); text-decoration: underline; }
        code { color: var(--nf-green); }
        .form-text { color: var(--nf-muted) !important; }

        /* ─── Alerts ─── */
        .alert { border-radius: 10px; }
        .alert-danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .alert-success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .alert-warning { background: #fffbeb; border-color: #fde68a; color: #92400e; }
        .alert-info { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }

        /* ─── Buttons ─── */
        .btn-primary {
            background: linear-gradient(135deg, var(--nf-green), var(--nf-green-light));
            border: none;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--nf-green-dark), var(--nf-green));
        }
        .btn-outline-primary {
            border-color: var(--nf-green);
            color: var(--nf-green);
        }
        .btn-outline-primary:hover {
            background: var(--nf-green);
            border-color: var(--nf-green);
            color: #fff;
        }
        .btn-outline-secondary {
            border-color: var(--nf-border);
            color: var(--nf-text-secondary);
        }
        .btn-outline-secondary:hover {
            background: var(--nf-bg);
            color: var(--nf-text);
        }

        /* ─── Navbar user badge ─── */
        .nav-user-name { color: var(--nf-text-secondary); }
        .nav-badge-role {
            background: rgba(27,107,42,.1);
            color: var(--nf-green);
            font-size: .7rem;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        /* ─── HR ─── */
        hr { border-color: var(--nf-border); opacity: .5; }

        /* ─── Selection & option overrides ─── */
        option { color: #1a2e1a; background: #fff; }
        textarea { color: var(--nf-text) !important; }

        /* ─── Description list ─── */
        dl.row dt { color: var(--nf-muted); font-weight: 600; }
        dl.row dd { color: var(--nf-text); }

        /* ─── Notification Bell ─── */
        .notif-bell { position: relative; cursor: pointer; display: flex; align-items: center; padding: 6px; }
        .notif-bell .badge { position: absolute; top: 0; right: 0; font-size: .65rem; border-radius: 50px; }
        .notif-dropdown {
            display: none; position: absolute; top: 100%; right: 0; width: 320px;
            background: #fff; border: 1px solid var(--nf-border); border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12); z-index: 1050; margin-top: 8px; overflow: hidden;
        }
        .notif-dropdown.show { display: block; }
        .notif-dropdown .notif-header {
            padding: 10px 14px; background: rgba(27,107,42,.04); border-bottom: 1px solid var(--nf-border);
            font-weight: 600; font-size: .85rem; display: flex; justify-content: space-between; align-items: center;
        }
        .notif-dropdown .notif-item {
            padding: 10px 14px; border-bottom: 1px solid rgba(0,0,0,.04);
            font-size: .82rem; text-decoration: none; display: block;
            color: var(--nf-text); transition: background .15s;
        }
        .notif-dropdown .notif-item:hover { background: rgba(27,107,42,.04); text-decoration: none; }
        .notif-dropdown .notif-item .notif-title { font-weight: 600; margin-bottom: 2px; }
        .notif-dropdown .notif-item .notif-msg { color: var(--nf-muted); font-size: .78rem; }
        .notif-dropdown .notif-item .notif-time { color: var(--nf-muted); font-size: .7rem; margin-top: 3px; }
        .notif-dropdown .notif-empty { padding: 20px; text-align: center; color: var(--nf-muted); font-size: .85rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg top-navbar sticky-top">
    <div class="container-fluid px-3">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php?r=home/index">
            <img src="public/assets/logo.png" alt="Nutrify" style="height:36px;width:auto;">
            Nutrify
        </a>
        <div class="ms-auto d-flex gap-2 align-items-center">
            <?php if ($currentUser): ?>
                <a href="index.php?r=account/settings" class="d-flex align-items-center gap-2 text-decoration-none me-1">
                    <?php if (!empty($currentUser['profile_picture_url'])): ?>
                        <img src="public/<?= htmlspecialchars(ltrim($currentUser['profile_picture_url'], '/')) ?>" class="nav-avatar-img" alt="Profile Picture">
                    <?php else: ?>
                        <?php
                            $nameParts = explode(' ', trim($currentUser['fullname'] ?? 'User'));
                            $inits = strtoupper(substr($nameParts[0] ?? 'U', 0, 1) . substr($nameParts[count($nameParts)-1] ?? '', 0, 1));
                        ?>
                        <div class="nav-avatar-circle"><?= htmlspecialchars($inits) ?></div>
                    <?php endif; ?>
                    <span class="nav-user-name small d-none d-md-inline fw-semibold text-dark"><?= htmlspecialchars($currentUser['fullname'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </a>
                <?php
                $roleLabels = [
                    'customer'=>'Fitness Enthusiast',
                    'gym_owner'=>'Gym Owner',
                    'admin'=>'Admin',
                    'administrative_officer'=>'Administrative Officer',
                    'trainer'=>'Fitness Trainer',
                    'fitness_trainer'=>'Fitness Trainer',
                    'maintenance'=>'Maintenance Officer',
                    'maintenance_officer'=>'Maintenance Officer',
                    'marketing_officer'=>'Marketing Officer'
                ];
                $roleLabel = $roleLabels[$userRole] ?? ucwords(str_replace('_',' ',$userRole));
                ?>
                <span class="nav-badge-role"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span>
                <!-- Notification Bell -->
                <div class="notif-bell" onclick="document.getElementById('notifDrop').classList.toggle('show')" id="notifBellBtn">
                    <i class="bi bi-bell" style="font-size:1.15rem;color:var(--nf-text-secondary)"></i>
                    <?php if ($_notifCount > 0): ?>
                        <span class="badge bg-danger"><?= $_notifCount > 9 ? '9+' : $_notifCount ?></span>
                    <?php endif; ?>
                    <div class="notif-dropdown" id="notifDrop" onclick="event.stopPropagation()">
                        <div class="notif-header">
                            <span><i class="bi bi-bell me-1"></i>Notifications</span>
                            <?php if ($_notifCount > 0): ?>
                                <a href="index.php?r=notification/markallread" style="font-size:.75rem">Mark all read</a>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($_notifItems)): ?>
                            <div class="notif-empty"><i class="bi bi-check-circle me-1"></i>No new notifications</div>
                        <?php else: ?>
                            <?php foreach ($_notifItems as $__n):
                                $__nt = $__n['type'] ?? 'info';
                                if ($__nt === 'success') { $__icon = 'bi-check-circle-fill text-success'; }
                                elseif ($__nt === 'warning') { $__icon = 'bi-exclamation-triangle-fill text-warning'; }
                                elseif ($__nt === 'danger') { $__icon = 'bi-x-circle-fill text-danger'; }
                                else { $__icon = 'bi-info-circle-fill text-info'; }
                                $__link = $__n['link'] ? 'index.php?r=notification/markread&id='.$__n['id'].'&link='.urlencode($__n['link']) : '#';
                            ?>
                            <a class="notif-item" href="<?= $__link ?>">
                                <div class="notif-title"><i class="bi <?= $__icon ?> me-1"></i><?= htmlspecialchars($__n['title']) ?></div>
                                <div class="notif-msg"><?= htmlspecialchars(mb_substr($__n['message'], 0, 80)) ?><?= mb_strlen($__n['message']) > 80 ? '…' : '' ?></div>
                                <div class="notif-time"><?= htmlspecialchars($__n['created_at']) ?></div>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <a href="index.php?r=notification/index" class="notif-item" style="text-align:center;font-weight:600;color:var(--nf-green)">View All Notifications</a>
                    </div>
                </div>
                <a class="btn btn-outline-secondary btn-sm" href="index.php?r=account/settings" title="Account Settings"><i class="bi bi-gear"></i> Settings</a>
                <a class="btn btn-outline-secondary btn-sm" href="index.php?r=home/logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
            <?php else: ?>
                <a class="btn btn-outline-primary btn-sm" href="index.php?r=auth/register">Register</a>
                <a class="btn btn-primary btn-sm" href="index.php?r=auth/login">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if ($currentUser): ?>
<div class="d-flex">
    <aside class="sidebar d-none d-md-block" style="width:240px;flex-shrink:0">
        <nav class="nav flex-column py-3">
            <a class="nav-link" href="index.php?r=home/index"><span><i class="bi bi-speedometer2"></i> Dashboard</span></a>

            <?php if ($userRole === 'customer'): ?>
                <div class="nav-section">Gym Owner</div>
                <a class="nav-link" href="index.php?r=gymowner/apply"><span><i class="bi bi-building"></i> Apply as Gym Owner</span></a>
                <div class="nav-section">Staff</div>
                <a class="nav-link" href="index.php?r=staff/apply"><span><i class="bi bi-person-badge"></i> Apply as Staff</span></a>
                <div class="nav-section">Membership</div>
                <a class="nav-link" href="index.php?r=membership/apply"><span><i class="bi bi-card-checklist"></i> Apply for Membership</span></a>
                <a class="nav-link" href="index.php?r=membership/verifycode"><span><i class="bi bi-qr-code"></i> Verify Membership</span></a>
                <div class="nav-section">Fitness Training</div>
                <a class="nav-link" href="index.php?r=membership/fitnessprogram"><span><i class="bi bi-lightning-charge-fill"></i> My Fitness Program</span></a>
                <a class="nav-link" href="index.php?r=fitness/profile"><span><i class="bi bi-file-earmark-person-fill"></i> Fill Fitness Profile</span></a>
                <a class="nav-link" href="index.php?r=fitness/directory"><span><i class="bi bi-search-heart-fill"></i> Find a Coach</span></a>
                <a class="nav-link" href="index.php?r=fitness/status"><span><i class="bi bi-clipboard-check"></i> My Training Status</span></a>
                
                <?php if ($_clientTrainerAssigned): ?>
                    <a class="nav-link" href="index.php?r=message/index">
                        <span><i class="bi bi-chat-dots-fill"></i> Messages</span>
                        <?php if ($_msgUnreadCount > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?= $_msgUnreadCount ?></span>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <a class="nav-link opacity-50" href="index.php?r=message/index" title="Trainer assignment required">
                        <span><i class="bi bi-chat-dots"></i> Messages</span>
                        <span class="badge bg-secondary rounded-pill" style="font-size:.65rem;">Locked</span>
                    </a>
                <?php endif; ?>

                <div class="nav-section">Gym</div>
                <a class="nav-link" href="index.php?r=member/equipment"><span><i class="bi bi-tools"></i> Gym Equipment</span></a>
                <a class="nav-link" href="index.php?r=member/campaigns"><span><i class="bi bi-calendar-event"></i> Events &amp; Promotions</span></a>
                <div class="nav-section">My Coaching</div>
                <a class="nav-link" href="index.php?r=member/coaching"><span><i class="bi bi-person-arms-up"></i> My Coach &amp; Plan</span></a>
                <a class="nav-link" href="index.php?r=member/goals"><span><i class="bi bi-bullseye"></i> My Fitness Goals</span></a>

            <?php elseif ($userRole === 'gym_owner'): ?>
                <div class="nav-section">Gym Profile</div>
                <a class="nav-link" href="index.php?r=gymowner/managegym"><span><i class="bi bi-building-gear"></i> Manage Gym Profile</span></a>
                <div class="nav-section">Finance</div>
                <a class="nav-link" href="index.php?r=equipment/budget"><span><i class="bi bi-wallet2"></i> Financial Dashboard</span></a>
                <div class="nav-section">Equipment</div>
                <a class="nav-link" href="index.php?r=equipment/inventory"><span><i class="bi bi-box-seam"></i> Equipment Inventory</span></a>
                <div class="nav-section">Staff & Roles</div>
                <a class="nav-link" href="index.php?r=staff/applications"><span><i class="bi bi-people"></i> Staff Applications</span></a>
                <a class="nav-link" href="index.php?r=gymowner/users"><span><i class="bi bi-person-gear"></i> Manage Users</span></a>
                <a class="nav-link" href="index.php?r=message/index"><span><i class="bi bi-chat-square-text-fill"></i> Message Logs</span></a>
                <div class="nav-section">Memberships</div>
                <a class="nav-link" href="index.php?r=gymowner/memberships"><span><i class="bi bi-person-plus"></i> Membership Applications</span></a>
                <a class="nav-link" href="index.php?r=gymowner/members"><span><i class="bi bi-people-fill"></i> Gym Members</span></a>
                <a class="nav-link" href="index.php?r=gymowner/attendance"><span><i class="bi bi-calendar-check"></i> Attendance Log</span></a>
                <div class="nav-section">Plans & Services</div>
                <a class="nav-link" href="index.php?r=gymowner/plans"><span><i class="bi bi-card-list"></i> Membership Plans</span></a>
                <a class="nav-link" href="index.php?r=gymowner/services"><span><i class="bi bi-tags"></i> Gym Services</span></a>
                <a class="nav-link" href="index.php?r=gymowner/trainerpricing"><span><i class="bi bi-person-badge"></i> Training Pricing</span></a>
                <div class="nav-section">Maintenance</div>
                <a class="nav-link" href="index.php?r=gymowner/maintenancereports"><span><i class="bi bi-clipboard2-check"></i> Maintenance Reports</span></a>

            <?php elseif ($userRole === 'admin'): ?>
                <div class="nav-section">Reviews</div>
                <a class="nav-link" href="index.php?r=admin/legalreviews"><span><i class="bi bi-file-earmark-check"></i> Legal Documents</span></a>
                <div class="nav-section">Security</div>
                <a class="nav-link" href="index.php?r=admin/loginactivities"><span><i class="bi bi-shield-check"></i> Login Activity</span></a>

            <?php elseif ($userRole === 'administrative_officer'): ?>
                <div class="nav-section">Memberships</div>
                <a class="nav-link" href="index.php?r=admofficer/memberships"><span><i class="bi bi-person-plus"></i> Membership Applications</span></a>
                <a class="nav-link" href="index.php?r=admofficer/members"><span><i class="bi bi-people-fill"></i> Gym Members</span></a>
                <a class="nav-link" href="index.php?r=admofficer/attendance"><span><i class="bi bi-calendar-check"></i> Attendance Log</span></a>
                <div class="nav-section">Fitness Training</div>
                <a class="nav-link" href="index.php?r=admofficer/fitnessRequests"><span><i class="bi bi-person-hearts"></i> Fitness Requests</span></a>
                <div class="nav-section">Staff</div>
                <a class="nav-link" href="index.php?r=admofficer/employees"><span><i class="bi bi-person-badge"></i> Employee List</span></a>

            <?php elseif ($userRole === 'trainer' || $userRole === 'fitness_trainer'): ?>
                <div class="nav-section">My Coaching</div>
                <a class="nav-link" href="index.php?r=trainer/requests"><span><i class="bi bi-inbox"></i> Booking Requests</span></a>
                <a class="nav-link" href="index.php?r=trainer/clients"><span><i class="bi bi-people"></i> My Clients</span></a>
                <a class="nav-link" href="index.php?r=message/index">
                    <span><i class="bi bi-chat-dots-fill"></i> Messages</span>
                    <?php if ($_msgUnreadCount > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= $_msgUnreadCount ?></span>
                    <?php endif; ?>
                </a>
                <a class="nav-link" href="index.php?r=trainer/progress"><span><i class="bi bi-graph-up-arrow"></i> Progress Reviews</span></a>
                <a class="nav-link" href="index.php?r=trainer/equipment"><span><i class="bi bi-tools"></i> View Equipment</span></a>
                <div class="nav-section">My Profile</div>
                <a class="nav-link" href="index.php?r=trainer/manageprofile"><span><i class="bi bi-person-workspace"></i> Profile & Availability</span></a>

            <?php elseif ($userRole === 'maintenance' || $userRole === 'maintenance_officer'): ?>
                <div class="nav-section">Dashboard</div>
                <a class="nav-link" href="index.php?r=maintenance/dashboard"><span><i class="bi bi-speedometer2"></i> Dashboard</span></a>
                <div class="nav-section">Equipment</div>
                <a class="nav-link" href="index.php?r=maintenance/equipment"><span><i class="bi bi-box-seam"></i> View Equipment</span></a>
                <a class="nav-link" href="index.php?r=maintenance/equipment"><span><i class="bi bi-clipboard2-plus"></i> Inspect Equipment</span></a>
                <div class="nav-section">Reports</div>
                <a class="nav-link" href="index.php?r=maintenance/reports"><span><i class="bi bi-file-earmark-text"></i> My Reports</span></a>
                <a class="nav-link" href="index.php?r=maintenance/history"><span><i class="bi bi-clock-history"></i> Inspection History</span></a>

            <?php elseif ($userRole === 'marketing_officer'): ?>
                <div class="nav-section">Marketing</div>
                <a class="nav-link" href="index.php?r=marketing/dashboard"><span><i class="bi bi-speedometer2"></i> Dashboard</span></a>
                <a class="nav-link" href="index.php?r=marketing/campaignbuilder"><span><i class="bi bi-layout-text-window-reverse"></i> Campaign Builder</span></a>
                <a class="nav-link" href="index.php?r=marketing/campaigns"><span><i class="bi bi-megaphone"></i> Ad Campaigns</span></a>
                <a class="nav-link" href="index.php?r=marketing/promotions"><span><i class="bi bi-tags"></i> Gym Promotions</span></a>
                <div class="nav-section">Analytics</div>
                <a class="nav-link" href="index.php?r=marketing/attendance"><span><i class="bi bi-graph-up-arrow"></i> Attendance Log</span></a>
            <?php endif; ?>

            <div class="nav-section">Account</div>
            <a class="nav-link" href="index.php?r=account/settings"><span><i class="bi bi-person-circle"></i> Profile & Settings</span></a>

        </nav>
    </aside>
    <div class="main-content flex-grow-1 p-4">
<?php else: ?>
<main class="container py-4 py-md-5">
<?php endif; ?>
