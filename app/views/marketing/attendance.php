<?php
declare(strict_types=1);
$pageTitle = 'Attendance Log';
require __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-calendar3 me-2 text-purple"></i>Attendance Logs & Analytics</h1>
        <p class="text-muted mb-0">Track daily visits, membership types checking in, and historical patterns.</p>
    </div>
    <div>
        <!-- Export to CSV Link with current filters -->
        <a href="index.php?r=marketing/attendance&export=csv&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&membership_type=<?= urlencode($membershipType) ?>" class="btn btn-purple">
            <i class="bi bi-file-earmark-excel me-1"></i> Export to CSV
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted small uppercase mb-1">Today's Visits</h6>
                        <h3 class="mb-0 fw-bold"><?= $todayVisits ?></h3>
                    </div>
                    <div class="bg-purple-light text-purple p-3 rounded-circle">
                        <i class="bi bi-calendar2-day fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted small uppercase mb-1">Visits (Past 7 Days)</h6>
                        <h3 class="mb-0 fw-bold"><?= $weekVisits ?></h3>
                    </div>
                    <div class="bg-primary-light text-primary p-3 rounded-circle">
                        <i class="bi bi-calendar2-week fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted small uppercase mb-1">Visits (Past 30 Days)</h6>
                        <h3 class="mb-0 fw-bold"><?= $monthVisits ?></h3>
                    </div>
                    <div class="bg-success-light text-success p-3 rounded-circle">
                        <i class="bi bi-calendar2-month fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 30-Day Bar Chart -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-graph-up-arrow me-2 text-purple"></i>Visits Over Time (Past 30 Days)</h5>
    </div>
    <div class="card-body">
        <div style="height: 250px; position: relative;">
            <canvas id="attendance30DaysChart"></canvas>
        </div>
    </div>
</div>

<!-- Filter Panel -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3 align-items-end">
            <input type="hidden" name="r" value="marketing/attendance">
            
            <div class="col-md-3">
                <label class="form-label small fw-bold">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label small fw-bold">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label small fw-bold">Membership Type</label>
                <select name="membership_type" class="form-select">
                    <option value="">All Membership Types</option>
                    <?php foreach ($membershipTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= $membershipType === $type ? 'selected' : '' ?>>
                            <?= ucwords(str_replace('_', ' ', htmlspecialchars($type))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-purple flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="index.php?r=marketing/attendance" class="btn btn-outline-secondary">
                    Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Attendance Logs Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-columns-reverse me-2 text-purple"></i>Visits Record Log</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Member Name</th>
                        <th>Check-in Time</th>
                        <th>Membership Type</th>
                        <th>Duration (Min)</th>
                        <th>Visit Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No visits found matching your filters.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="fw-semibold"><?= date('Y-m-d', strtotime($log['check_in'])) ?></td>
                                <td><?= htmlspecialchars($log['fullname']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-clock me-1"></i><?= date('h:i A', strtotime($log['check_in'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-outline-purple">
                                        <?= ucwords(str_replace('_', ' ', (string)($log['membership_type'] ?? 'regular'))) ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?= $log['duration_minutes'] ?? 'In Progress' ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= ucfirst((string)($log['visit_type'] ?? 'regular')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js setup -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('attendance30DaysChart').getContext('2d');
    const chartData = <?= json_encode($past30Days) ?>;
    
    const labels = Object.keys(chartData);
    const data = Object.values(chartData);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Daily Check-ins',
                data: data,
                backgroundColor: 'rgba(124, 58, 237, 0.7)',
                borderColor: '#7c3aed',
                borderWidth: 1,
                borderRadius: 3
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
.text-purple { color: #7c3aed !important; }
.btn-purple { background-color: #7c3aed !important; border-color: #7c3aed !important; color: #fff !important; }
.btn-purple:hover { background-color: #6d28d9 !important; border-color: #6d28d9 !important; color: #fff !important; }
.bg-outline-purple { border: 1px solid #7c3aed; color: #7c3aed; background: transparent; }
</style>

<?php require __DIR__ . '/../partials/footer.php'; ?>
