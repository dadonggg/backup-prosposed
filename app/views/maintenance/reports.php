<?php
declare(strict_types=1);
$pageTitle = isset($isHistory) && $isHistory ? 'Inspection History' : 'My Reports';
require __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-file-earmark-text me-2"></i>
            <?= isset($isHistory) && $isHistory ? 'Inspection History' : 'My Reports' ?>
        </h1>
        <p class="text-muted mb-0">All your submitted and drafted inspection reports</p>
    </div>
    <a href="index.php?r=maintenance/equipment" class="btn btn-primary">
        <i class="bi bi-clipboard2-plus me-1"></i> New Inspection
    </a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="bi bi-list-ul me-2"></i>Reports</h5>
        <div class="d-flex gap-2">
            <input type="text" id="reportSearch" class="form-control form-control-sm" placeholder="Search…" style="width:180px">
            <select id="reportFilter" class="form-select form-select-sm" style="width:150px">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="submitted">Submitted</option>
                <option value="verified">Verified</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($reports)): ?>
            <div class="text-center py-5">
                <i class="bi bi-clipboard2 display-2 text-muted"></i>
                <h5 class="text-muted mt-3">No Reports Yet</h5>
                <p class="text-muted">Start an inspection to create your first report.</p>
                <a href="index.php?r=maintenance/equipment" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> View Equipment
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="reportsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Equipment</th>
                            <th>Category</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $r):
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
                            $statusLabel = ucfirst($r['status'] ?? '');
                        ?>
                        <tr data-status="<?= $r['status'] ?>"
                            data-search="<?= strtolower(htmlspecialchars($r['equipment_name'] ?? '') . ' ' . htmlspecialchars($r['category'] ?? '')) ?>">
                            <td><?= htmlspecialchars($r['inspection_date'] ?? '') ?></td>
                            <td><strong><?= htmlspecialchars($r['equipment_name'] ?? '') ?></strong></td>
                            <td><span class="badge" style="background:rgba(27,107,42,.1);color:var(--nf-green)"><?= htmlspecialchars($r['category'] ?? '') ?></span></td>
                            <td><span class="badge bg-<?= $condClass ?>"><?= $condLabel ?></span></td>
                            <td><span class="badge bg-<?= $statusClass ?>"><?= $statusLabel ?></span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="index.php?r=maintenance/reportdetail&id=<?= $r['id'] ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                    <?php if ($r['status'] === 'draft'): ?>
                                    <a href="index.php?r=maintenance/inspect&equipment_id=<?= $r['equipment_id'] ?>&inspection_id=<?= $r['id'] ?>"
                                       class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil me-1"></i>Edit
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const searchEl = document.getElementById('reportSearch');
const filterEl = document.getElementById('reportFilter');
const rows = document.querySelectorAll('#reportsTable tbody tr');

function filterReports() {
    const q = (searchEl?.value || '').toLowerCase();
    const status = filterEl?.value || '';
    rows.forEach(row => {
        const matchQ = !q || (row.dataset.search || '').includes(q);
        const matchS = !status || row.dataset.status === status;
        row.style.display = (matchQ && matchS) ? '' : 'none';
    });
}
searchEl?.addEventListener('input', filterReports);
filterEl?.addEventListener('change', filterReports);
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
