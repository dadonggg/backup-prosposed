<?php
declare(strict_types=1);
$pageTitle = 'Equipment List';
require __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-box-seam me-2"></i>Gym Equipment</h1>
        <p class="text-muted mb-0">Select equipment to begin an inspection</p>
    </div>
    <a href="index.php?r=maintenance/dashboard" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>
</div>

<?php if (empty($equipment)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox display-2 text-muted"></i>
            <h5 class="text-muted mt-3">No Equipment Found</h5>
            <p class="text-muted">Your gym owner has not added any equipment yet.</p>
        </div>
    </div>
<?php else: ?>
    <!-- Search/Filter -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-6">
                    <input type="text" id="searchEquipment" class="form-control" placeholder="🔍 Search equipment name or category…">
                </div>
                <div class="col-md-4">
                    <select id="filterCondition" class="form-select">
                        <option value="">All Conditions</option>
                        <option value="good">✅ Good</option>
                        <option value="needs_repair">⚠️ Needs Repair</option>
                        <option value="condemned">🔴 Condemned</option>
                        <option value="not_inspected">🕐 Not Inspected</option>
                    </select>
                </div>
                <div class="col-md-2 text-muted small">
                    <span id="equipmentCount"><?= count($equipment) ?></span> items
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3" id="equipmentGrid">
        <?php foreach ($equipment as $eq):
            $lastInsp = $lastInspections[$eq['id']] ?? null;
            $condition = $lastInsp['overall_condition'] ?? null;
            $badgeClass = 'secondary';
            if ($condition === 'good') { $badgeClass = 'success'; }
            elseif ($condition === 'needs_repair') { $badgeClass = 'warning'; }
            elseif ($condition === 'condemned') { $badgeClass = 'danger'; }

            $badgeLabel = '🕐 Not Inspected';
            if ($condition === 'good') { $badgeLabel = '✅ Good'; }
            elseif ($condition === 'needs_repair') { $badgeLabel = '⚠️ Needs Repair'; }
            elseif ($condition === 'condemned') { $badgeLabel = '🔴 Condemned'; }
            $lastDate = $lastInsp ? $lastInsp['inspection_date'] : null;
            $imgSrc = !empty($eq['image_path']) ? 'public/' . $eq['image_path'] : null;
        ?>
        <div class="col-md-4 col-lg-3 equipment-card"
             data-name="<?= strtolower(htmlspecialchars($eq['name'] ?? '')) ?>"
             data-category="<?= strtolower(htmlspecialchars($eq['category'] ?? '')) ?>"
             data-condition="<?= $condition ?? 'not_inspected' ?>">
            <div class="card h-100" style="transition:transform .2s,box-shadow .2s">
                <!-- Equipment Image -->
                <div style="height:140px;background:linear-gradient(135deg,var(--nf-green-dark),var(--nf-green));border-radius:12px 12px 0 0;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                    <?php if ($imgSrc): ?>
                        <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($eq['name']) ?>"
                             style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <i class="bi bi-bicycle" style="font-size:3rem;color:rgba(255,255,255,.6)"></i>
                    <?php endif; ?>
                </div>

                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title mb-0 fw-bold"><?= htmlspecialchars($eq['name'] ?? '') ?></h6>
                        <span class="badge bg-<?= $badgeClass ?> ms-1" style="font-size:.7rem"><?= $badgeLabel ?></span>
                    </div>

                    <div class="small text-muted mb-1">
                        <i class="bi bi-tag me-1"></i><?= htmlspecialchars($eq['category'] ?? '—') ?>
                    </div>
                    <?php if (!empty($eq['brand'])): ?>
                    <div class="small text-muted mb-1">
                        <i class="bi bi-building me-1"></i><?= htmlspecialchars($eq['brand']) ?>
                    </div>
                    <?php endif; ?>
                    <div class="small text-muted mb-1">
                        <i class="bi bi-123 me-1"></i>Qty: <?= (int)($eq['quantity'] ?? 1) ?>
                        <?php if (!empty($eq['weight_kg'])): ?>
                        &nbsp;|&nbsp;<i class="bi bi-speedometer me-1"></i><?= $eq['weight_kg'] ?> kg
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted mb-3">
                        <i class="bi bi-calendar me-1"></i>Last Inspected:
                        <?= $lastDate ? htmlspecialchars($lastDate) : '<span class="text-warning">Never</span>' ?>
                    </div>

                    <div class="mt-auto">
                        <a href="index.php?r=maintenance/inspect&equipment_id=<?= $eq['id'] ?>"
                           class="btn btn-primary w-100">
                            <i class="bi bi-clipboard2-plus me-1"></i>Inspect Equipment
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
const searchInput = document.getElementById('searchEquipment');
const filterSelect = document.getElementById('filterCondition');
const cards = document.querySelectorAll('.equipment-card');
const countEl = document.getElementById('equipmentCount');

function filterCards() {
    const q = (searchInput?.value || '').toLowerCase();
    const cond = filterSelect?.value || '';
    let visible = 0;
    cards.forEach(card => {
        const name = card.dataset.name || '';
        const cat = card.dataset.category || '';
        const cardCond = card.dataset.condition || '';
        const matchQ = !q || name.includes(q) || cat.includes(q);
        const matchCond = !cond || cardCond === cond;
        card.style.display = (matchQ && matchCond) ? '' : 'none';
        if (matchQ && matchCond) visible++;
    });
    if (countEl) countEl.textContent = visible;
}

searchInput?.addEventListener('input', filterCards);
filterSelect?.addEventListener('change', filterCards);
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
