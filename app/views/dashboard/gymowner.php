<?php
declare(strict_types=1);
$pageTitle = 'Gym Owner Dashboard';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Gym Owner Dashboard</h1>
    <p class="text-muted mb-0">Manage your gym operations — budget, equipment, staff, members & revenue.</p>
</div>

<!-- Financial Summary Row -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-25 text-success"><i class="bi bi-wallet2"></i></div>
                <div><div class="text-muted small">Total Budget</div><div class="fw-bold">₱<?= number_format($budget, 2) ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-25 text-danger"><i class="bi bi-cash-stack"></i></div>
                <div><div class="text-muted small">Expenses</div><div class="fw-bold">₱<?= number_format($totalExpenses, 2) ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-25 text-primary"><i class="bi bi-graph-up-arrow"></i></div>
                <div><div class="text-muted small">Total Revenue</div><div class="fw-bold text-success">₱<?= number_format($totalRevenue ?? 0, 2) ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-25 text-info"><i class="bi bi-piggy-bank"></i></div>
                <div><div class="text-muted small">Net Profit</div><div class="fw-bold <?= ($monthlyProfit ?? 0) < 0 ? 'text-danger' : '' ?>">₱<?= number_format($monthlyProfit ?? 0, 2) ?></div></div>
            </div>
        </div>
    </div>
</div>

<!-- Members & Revenue Row -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-25 text-success"><i class="bi bi-people-fill"></i></div>
                <div><div class="text-muted small">Active Members</div><div class="fw-bold"><?= count($activeMembers ?? []) ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-25 text-warning"><i class="bi bi-person-plus"></i></div>
                <div><div class="text-muted small">Pending Applications</div><div class="fw-bold"><?= count($pendingMemberApps ?? []) ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-25 text-info"><i class="bi bi-calendar-month"></i></div>
                <div><div class="text-muted small">This Month Revenue</div><div class="fw-bold text-success">₱<?= number_format($monthlyMemberRevenue ?? 0, 2) ?></div></div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Revenue Breakdown -->
<?php if (!empty($revenueByMonth)): ?>
<div class="card mb-4">
    <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-bar-chart me-2"></i>Monthly Revenue Breakdown</h2></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Month</th><th>New Members</th><th>Revenue</th></tr></thead>
                <tbody>
                    <?php foreach ($revenueByMonth as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['month']) ?></td>
                        <td><?= (int)$r['member_count'] ?></td>
                        <td class="fw-bold text-success">₱<?= number_format((float)$r['total'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Revenue Tracking (Category Breakdown) -->
<?php if (!empty($revenueBreakdown)): ?>
<div class="card mb-4">
    <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-graph-up me-2"></i>Revenue Tracking</h2></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-3 border rounded">
                    <div class="text-muted small mb-1">Membership Revenue</div>
                    <div class="h4 mb-0 text-success">₱<?= number_format($revenueBreakdown['Membership Revenue'] ?? 0, 2) ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded">
                    <div class="text-muted small mb-1">Trainer Sessions</div>
                    <div class="h4 mb-0 text-primary">₱<?= number_format($revenueBreakdown['Trainer Sessions'] ?? 0, 2) ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded">
                    <div class="text-muted small mb-1">Others</div>
                    <div class="h4 mb-0 text-info">₱<?= number_format($revenueBreakdown['Others'] ?? 0, 2) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="row g-4">
    <div class="col-lg-3">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-wallet2 me-2"></i>Budget & Expenses</h2></div>
            <div class="card-body">
                <p class="small text-muted">Set your total budget and track operational expenses.</p>
                <a href="index.php?r=equipment/budget" class="btn btn-primary btn-sm"><i class="bi bi-arrow-right"></i> Manage</a>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-cart3 me-2"></i>Equipment Shop</h2></div>
            <div class="card-body">
                <p class="small text-muted">Browse and purchase gym equipment from suppliers.</p>
                <a href="index.php?r=equipment/shop" class="btn btn-warning btn-sm text-dark"><i class="bi bi-arrow-right"></i> Shop</a>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-people me-2"></i>Staff Applications</h2></div>
            <div class="card-body">
                <p class="small text-muted">Review applications from fitness enthusiasts wanting to join as staff.</p>
                <span class="badge bg-info mb-2"><?= count($staffApps) ?> pending</span><br>
                <a href="index.php?r=staff/applications" class="btn btn-success btn-sm"><i class="bi bi-arrow-right"></i> Review</a>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-person-plus me-2"></i>Memberships</h2></div>
            <div class="card-body">
                <p class="small text-muted">Review membership applications, view approved members, and track attendance.</p>
                <div class="d-flex flex-column gap-1">
                    <a href="index.php?r=gymowner/memberships" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-right"></i> Applications</a>
                    <a href="index.php?r=gymowner/members" class="btn btn-outline-success btn-sm"><i class="bi bi-arrow-right"></i> Members</a>
                    <a href="index.php?r=gymowner/attendance" class="btn btn-outline-info btn-sm"><i class="bi bi-arrow-right"></i> Attendance</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Plans & Services Quick Links -->
<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-tags me-2"></i>Gym Services</h2></div>
            <div class="card-body">
                <p class="small text-muted">Add services (Personal Training, Fitness Sessions) with separate member/non-member pricing.</p>
                <a href="index.php?r=gymowner/services" class="btn btn-warning btn-sm text-dark"><i class="bi bi-arrow-right"></i> Manage Services</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100 border-primary">
            <div class="card-header px-3 py-2 bg-primary text-white">
                <h2 class="h6 mb-0"><i class="bi bi-credit-card-2-front me-2"></i>PayMongo Setup</h2>
            </div>
            <div class="card-body">
                <p class="small text-muted">Configure PayMongo API keys to accept online payments from members.</p>
                <a href="index.php?r=gymowner/paymongo" class="btn btn-primary btn-sm">
                    <i class="bi bi-gear"></i> Configure PayMongo
                </a>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> Required for online payments
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
