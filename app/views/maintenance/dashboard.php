<?php
declare(strict_types=1);
$pageTitle = 'Maintenance Dashboard';
require __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-tools me-2"></i>Maintenance Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($user['fullname'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <a href="index.php?r=maintenance/equipment" class="btn btn-primary">
        <i class="bi bi-search me-1"></i> Inspect Equipment
    </a>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(27,107,42,.1)">
                    <i class="bi bi-box-seam" style="color:var(--nf-green)"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalEquipment ?></div>
                    <div class="small text-muted">Total Equipment</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(13,110,253,.1)">
                    <i class="bi bi-clipboard2-check" style="color:#0d6efd"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $inspectedToday ?></div>
                    <div class="small text-muted">Inspected Today</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(255,193,7,.1)">
                    <i class="bi bi-clock-history" style="color:#ffc107"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $pendingReports ?></div>
                    <div class="small text-muted">Pending Reports</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(25,135,84,.1)">
                    <i class="bi bi-send-check" style="color:#198754"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $submittedReports ?></div>
                    <div class="small text-muted">Submitted Reports</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5></div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3">
                    <a href="index.php?r=maintenance/equipment" class="btn btn-primary px-4">
                        <i class="bi bi-search me-2"></i>Inspect Equipment
                    </a>
                    <a href="index.php?r=maintenance/reports" class="btn btn-outline-primary px-4">
                        <i class="bi bi-file-earmark-text me-2"></i>View My Reports
                    </a>
                    <a href="index.php?r=maintenance/history" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-clock-history me-2"></i>View History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Inspections -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Recent Inspections</h5>
        <a href="index.php?r=maintenance/reports" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentInspections)): ?>
            <div class="text-center py-5">
                <i class="bi bi-clipboard2 display-2 text-muted"></i>
                <p class="text-muted mt-2">No inspections yet. Start by inspecting equipment.</p>
                <a href="index.php?r=maintenance/equipment" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> View Equipment
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Equipment</th>
                            <th>Date</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentInspections as $r):
                            $__cond = $r['overall_condition'] ?? '';
                            if ($__cond === 'good') { $condClass = 'success'; }
                            elseif ($__cond === 'needs_repair') { $condClass = 'warning'; }
                            elseif ($__cond === 'condemned') { $condClass = 'danger'; }
                            else { $condClass = 'secondary'; }

                            if ($__cond === 'good') { $condLabel = '✅ Good'; }
                            elseif ($__cond === 'needs_repair') { $condLabel = '⚠️ Needs Repair'; }
                            elseif ($__cond === 'condemned') { $condLabel = '🔴 Condemned'; }
                            else { $condLabel = '—'; }

                            $__st = $r['status'] ?? '';
                            if ($__st === 'draft') { $statusClass = 'secondary'; }
                            elseif ($__st === 'submitted') { $statusClass = 'primary'; }
                            elseif ($__st === 'verified') { $statusClass = 'success'; }
                            else { $statusClass = 'secondary'; }
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['equipment_name'] ?? '') ?></strong></td>
                            <td><?= htmlspecialchars($r['inspection_date'] ?? '') ?></td>
                            <td><span class="badge bg-<?= $condClass ?>"><?= $condLabel ?></span></td>
                            <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst($r['status'] ?? '') ?></span></td>
                            <td>
                                <a href="index.php?r=maintenance/reportdetail&id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
