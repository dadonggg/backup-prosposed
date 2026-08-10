<?php
declare(strict_types=1);
$pageTitle = 'Marketing Dashboard';
require __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-speedometer2 me-2 text-purple"></i>Marketing Dashboard</h1>
        <p class="text-muted mb-0">Overview of active campaigns, promotions, and gym attendance metrics.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php?r=marketing/createcampaign" class="btn btn-purple">
            <i class="bi bi-plus-circle me-1"></i> Create Campaign
        </a>
        <a href="index.php?r=marketing/createpromotion" class="btn btn-outline-purple">
            <i class="bi bi-tag me-1"></i> Create Promotion
        </a>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 bg-purple-light text-purple p-3 rounded-3 me-3">
                    <i class="bi bi- megaphone fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted small uppercase mb-1">Active Campaigns</h6>
                    <h3 class="mb-0 fw-bold"><?= $activeCampaignsCount ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 bg-success-light text-success p-3 rounded-3 me-3">
                    <i class="bi bi-tag-fill fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted small uppercase mb-1">Active Promotions</h6>
                    <h3 class="mb-0 fw-bold"><?= $activePromotionsCount ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 bg-primary-light text-primary p-3 rounded-3 me-3">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted small uppercase mb-1">Gym Members</h6>
                    <h3 class="mb-0 fw-bold"><?= $gymMembersCount ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 bg-warning-light text-warning p-3 rounded-3 me-3">
                    <i class="bi bi-calendar-check-fill fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted small uppercase mb-1">Visits Today</h6>
                    <h3 class="mb-0 fw-bold"><?= $todayAttendanceCount ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Chart Section -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-bar-chart-line me-2 text-purple"></i>Attendance Overview (Past 7 Days)</h5>
            </div>
            <div class="card-body">
                <div style="height: 300px; position: relative;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4 h-100">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-lightning-charge me-2 text-purple"></i>Quick Actions</h5>
            </div>
            <div class="card-body d-flex flex-column gap-3">
                <a href="index.php?r=marketing/createcampaign" class="btn btn-purple py-2 text-start">
                    <i class="bi bi-plus-circle-dotted me-2"></i> Create Ad Campaign
                </a>
                <a href="index.php?r=marketing/createpromotion" class="btn btn-outline-purple py-2 text-start">
                    <i class="bi bi-tag-fill me-2"></i> Create Gym Promotion
                </a>
                <a href="index.php?r=marketing/attendance" class="btn btn-light py-2 text-start">
                    <i class="bi bi-calendar3 me-2"></i> View Attendance Log
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Campaigns Table -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-newspaper me-2 text-purple"></i>Recent Campaigns</h5>
        <a href="index.php?r=marketing/campaigns" class="btn btn-sm btn-link text-purple p-0">View All Campaigns <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Target Audience</th>
                        <th>Registrations</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Views</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentCampaigns)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No recent campaigns. Click "Create Campaign" to get started.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentCampaigns as $c): ?>
                            <?php
                                $__st = $c['status'];
                                if ($__st === 'active') { $statusBadge = 'bg-success text-white'; }
                                elseif ($__st === 'ended') { $statusBadge = 'bg-danger text-white'; }
                                else { $statusBadge = 'bg-secondary text-white'; }
                            ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($c['title']) ?></td>
                                <td class="small"><?= ucfirst(str_replace('_', ' ', $c['target_audience'])) ?></td>
                                <td class="text-center">
                                    <?php if (($c['registration_count'] ?? 0) > 0): ?>
                                        <span class="badge bg-warning text-dark px-2 rounded-pill"><?= $c['registration_count'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small"><?= htmlspecialchars($c['start_date']) ?></td>
                                <td class="small"><?= htmlspecialchars($c['end_date']) ?></td>
                                <td class="fw-semibold text-purple"><?= (int)$c['views_count'] ?></td>
                                <td>
                                    <span class="badge <?= $statusBadge ?>"><?= ucfirst($c['status']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js and custom code -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const past7DaysData = <?= json_encode($past7Days) ?>;
    
    const labels = Object.keys(past7DaysData);
    const data = Object.values(past7DaysData);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Gym Visits',
                data: data,
                backgroundColor: '#7c3aed',
                borderColor: '#6d28d9',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>

<style>
.bg-purple-light { background-color: rgba(124, 58, 237, 0.1); }
.bg-success-light { background-color: rgba(22, 163, 74, 0.1); }
.bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
.bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
.text-purple { color: #7c3aed !important; }
.btn-purple { background-color: #7c3aed !important; border-color: #7c3aed !important; color: #fff !important; }
.btn-purple:hover { background-color: #6d28d9 !important; border-color: #6d28d9 !important; color: #fff !important; }
.btn-outline-purple { border-color: #7c3aed !important; color: #7c3aed !important; }
.btn-outline-purple:hover { background-color: #7c3aed !important; color: #fff !important; }
</style>

<?php require __DIR__ . '/../partials/footer.php'; ?>
