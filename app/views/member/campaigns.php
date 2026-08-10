<?php
declare(strict_types=1);
$pageTitle = 'Events & Promotions';
require __DIR__ . '/../partials/header.php';
?>

<style>
.text-purple { color: #7c3aed !important; }
.border-purple { border-color: #7c3aed !important; }
.btn-outline-purple { border-color: #7c3aed !important; color: #7c3aed !important; background: transparent; }
.btn-outline-purple:hover { background-color: #7c3aed !important; color: #fff !important; }
.interest-btn {
    font-size: 0.82rem; font-weight: 500; border-radius: 20px;
    padding: 5px 14px; transition: all 0.2s ease; cursor: pointer; border: 1.5px solid;
}
.interest-btn.interested { background: #16a34a; color: #fff; border-color: #16a34a; }
.interest-btn.interested.selected { background: #15803d; border-color: #15803d; box-shadow: 0 0 0 3px rgba(22,163,74,0.2); }
.interest-btn.not-interested { background: transparent; color: #6b7280; border-color: #d1d5db; }
.interest-btn.not-interested.selected { background: #f3f4f6; border-color: #9ca3af; color: #374151; box-shadow: 0 0 0 3px rgba(156,163,175,0.2); }
.interest-btn.selected { transform: scale(1.04); }
</style>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-calendar-event me-2 text-success"></i>Events & Promotions</h1>
    <p class="text-muted mb-0">Browse active campaigns and promotions from your gym.</p>
</div>

<?php if (empty($activeCampaigns) && empty($activePromotions)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-megaphone text-muted fs-1 d-block mb-3"></i>
        <h5 class="text-muted">No active promotions right now</h5>
        <p class="text-muted small">Check back later for exciting offers and events from your gym!</p>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($activeCampaigns)): ?>
<h5 class="fw-bold mb-3"><i class="bi bi-megaphone-fill text-purple me-2"></i>Active Campaigns</h5>
<div class="row g-4 mb-4">
    <?php foreach ($activeCampaigns as $c): ?>
    <?php $myInterest = $campaignInterestMap[(int)$c['id']] ?? null; ?>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius: 12px;">
            <?php if (!empty($c['image_path'])): ?>
                <img src="public/<?= htmlspecialchars($c['image_path']) ?>" class="card-img-top" style="max-height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($c['title']) ?>">
            <?php endif; ?>
            <div class="card-body">
                <h5 class="fw-bold mb-2"><?= htmlspecialchars($c['title']) ?></h5>
                <p class="text-muted small mb-2"><?= nl2br(htmlspecialchars($c['description'] ?? '')) ?></p>
                <div class="text-muted small mb-3">
                    <i class="bi bi-calendar-event me-1"></i> Valid: <?= htmlspecialchars($c['start_date']) ?> to <?= htmlspecialchars($c['end_date']) ?>
                </div>
                <div class="d-flex gap-2 campaign-interest-btns" data-campaign-id="<?= (int)$c['id'] ?>">
                    <button type="button"
                        class="interest-btn interested <?= $myInterest === 'interested' ? 'selected' : '' ?>"
                        onclick="saveInterest('campaign', <?= (int)$c['id'] ?>, 'interested', this)">
                        <?= $myInterest === 'interested' ? '✓ ' : '✅ ' ?>I'm Interested
                    </button>
                    <button type="button"
                        class="interest-btn not-interested <?= $myInterest === 'not_interested' ? 'selected' : '' ?>"
                        onclick="saveInterest('campaign', <?= (int)$c['id'] ?>, 'not_interested', this)">
                        <?= $myInterest === 'not_interested' ? '✓ ' : '❌ ' ?>Not Interested
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($activePromotions)): ?>
<h5 class="fw-bold mb-3"><i class="bi bi-tags-fill text-purple me-2"></i>Gym Promotions</h5>
<div class="row g-4 mb-4">
    <?php foreach ($activePromotions as $p): ?>
    <?php $myPromoInterest = $promotionInterestMap[(int)$p['id']] ?? null; ?>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-purple" style="border-radius: 8px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($p['title']) ?></h6>
                    <span class="badge bg-success">
                        <?php if ($p['discount_type'] === 'percentage'): ?>
                            <?= number_format((float)$p['discount_value'], 0) ?>% Off
                        <?php else: ?>
                            ₱<?= number_format((float)$p['discount_value'], 2) ?> Off
                        <?php endif; ?>
                    </span>
                </div>
                <?php if (!empty($p['image_path'])): ?>
                    <div class="mb-2">
                        <img src="public/<?= htmlspecialchars($p['image_path']) ?>" class="img-fluid rounded" style="max-height: 120px; width: 100%; object-fit: cover;">
                    </div>
                <?php endif; ?>
                <p class="text-muted small mb-2"><?= nl2br(htmlspecialchars($p['description'] ?? '')) ?></p>
                <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded border mb-2">
                    <code class="text-purple fw-bold fs-6" id="promoCode-<?= $p['id'] ?>"><?= htmlspecialchars($p['promo_code']) ?></code>
                    <button class="btn btn-sm btn-outline-purple py-0 px-2" onclick="copyPromoCode('<?= htmlspecialchars($p['promo_code']) ?>', this)">
                        <i class="bi bi-copy"></i> Copy
                    </button>
                </div>
                <small class="text-muted d-block mb-2" style="font-size: 0.75rem;">
                    <i class="bi bi-calendar-check me-1"></i> Expires: <?= htmlspecialchars($p['valid_until']) ?>
                </small>
                <div class="d-flex gap-2 promo-interest-btns" data-promotion-id="<?= (int)$p['id'] ?>">
                    <button type="button"
                        class="interest-btn interested <?= $myPromoInterest === 'interested' ? 'selected' : '' ?>"
                        onclick="saveInterest('promotion', <?= (int)$p['id'] ?>, 'interested', this)">
                        <?= $myPromoInterest === 'interested' ? '✓ ' : '✅ ' ?>I'm Interested
                    </button>
                    <button type="button"
                        class="interest-btn not-interested <?= $myPromoInterest === 'not_interested' ? 'selected' : '' ?>"
                        onclick="saveInterest('promotion', <?= (int)$p['id'] ?>, 'not_interested', this)">
                        <?= $myPromoInterest === 'not_interested' ? '✓ ' : '❌ ' ?>Not Interested
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
function copyPromoCode(code, btn) {
    navigator.clipboard.writeText(code).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
        btn.classList.remove('btn-outline-purple');
        btn.classList.add('btn-success', 'text-white');
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success', 'text-white');
            btn.classList.add('btn-outline-purple');
        }, 2000);
    });
}

function saveInterest(type, id, status, clickedBtn) {
    const container = clickedBtn.closest('.campaign-interest-btns, .promo-interest-btns');
    const allBtns = container ? container.querySelectorAll('.interest-btn') : [];
    const url = (type === 'campaign')
        ? 'index.php?r=member/savecampaigninterest'
        : 'index.php?r=member/savepromotioninterest';
    const body = new FormData();
    body.append((type === 'campaign' ? 'campaign_id' : 'promotion_id'), id);
    body.append('status', status);
    allBtns.forEach(btn => {
        btn.classList.remove('selected');
        if (btn.classList.contains('interested'))     btn.innerHTML = btn.innerHTML.replace(/^✓ /, '✅ ');
        if (btn.classList.contains('not-interested')) btn.innerHTML = btn.innerHTML.replace(/^✓ /, '❌ ');
    });
    clickedBtn.classList.add('selected');
    if (status === 'interested')     clickedBtn.innerHTML = clickedBtn.innerHTML.replace(/^✅ /, '✓ ');
    if (status === 'not_interested') clickedBtn.innerHTML = clickedBtn.innerHTML.replace(/^❌ /, '✓ ');
    fetch(url, { method: 'POST', body }).then(r => r.json()).catch(() => null);
}
</script>
