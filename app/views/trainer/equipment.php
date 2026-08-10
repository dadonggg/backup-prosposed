<?php
declare(strict_types=1);
$pageTitle = 'Gym Equipment';
require __DIR__ . '/../partials/header.php';

$displayName = htmlspecialchars($user['fullname'] ?? 'Trainer', ENT_QUOTES, 'UTF-8');
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
  --bg-page:           #f0f2f0;
  --bg-card:           #ffffff;
  --bg-section-header: #e8f5f0;
  --border-card:       #e2e8f0;
  --border-teal:       #0d9488;
  --border-trainer:    #99f6e4;
  --accent-teal:       #0d9488;
  --accent-blue:       #06b6d4;
  --text-primary:      #1e293b;
  --text-secondary:    #64748b;
  --shadow-card: 0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.05);
  --shadow-sm:   0 1px 2px rgba(0,0,0,0.06);
}

body {
  background: var(--bg-page) !important;
  font-family: 'Inter', system-ui, sans-serif !important;
  color: var(--text-primary) !important;
}

/* ── Mini Stat ── */
.mini-stat {
  background: var(--bg-card);
  border: 1px solid var(--border-card);
  border-radius: 12px;
  padding: 1.2rem 1rem;
  text-align: center;
  box-shadow: var(--shadow-sm);
}
.mini-num {
  font-weight: 800;
  font-size: 1.8rem;
  line-height: 1;
}
.mini-lbl {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-top: 6px;
}

/* ── Cards ── */
.equip-card {
  background: var(--bg-card);
  border: 1px solid var(--border-card);
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  transition: all .25s ease;
  overflow: hidden;
}
.equip-card:hover {
  border-color: var(--accent-teal);
  box-shadow: 0 4px 16px rgba(13,148,136,0.1);
  transform: translateY(-2px);
}

/* ── Filter Buttons ── */
.btn-filter {
  background: #ffffff;
  border: 1px solid var(--border-card);
  color: var(--text-secondary);
  border-radius: 8px;
  font-weight: 600;
  font-size: 13px;
  padding: 6px 14px;
  transition: all .2s;
  cursor: pointer;
}
.btn-filter:hover, .btn-filter.active {
  background: var(--bg-section-header);
  border-color: var(--accent-teal);
  color: var(--accent-teal);
}

.btn-back {
  background: #ffffff;
  border: 1px solid var(--border-card);
  border-radius: 8px;
  padding: 8px 14px;
  color: var(--text-secondary);
  font-size: 13px;
  transition: all .2s;
}
.btn-back:hover {
  border-color: var(--accent-teal);
  color: var(--accent-teal);
  background: #f0fdf9;
}

/* ── Badges ── */
.badge-category {
  display: inline-block;
  background: var(--bg-section-header);
  color: var(--accent-teal);
  border: 1px solid var(--border-trainer);
  border-radius: 50px;
  padding: 3px 12px;
  font-size: 11px;
  font-weight: 600;
}
.badge-quantity {
  display: inline-block;
  background: #dcfce7;
  color: #16a34a;
  border-radius: 50px;
  padding: 3px 12px;
  font-size: 11px;
  font-weight: 600;
}
</style>

<div class="p-1">
  <div class="d-flex justify-content-between align-items-start mb-4">
    <div>
      <h1 class="fw-extrabold mb-1" style="color: var(--text-primary); font-size: 26px; font-weight: 800;">
        <i class="bi bi-tools me-2" style="color: var(--accent-teal)"></i>Gym Equipment
      </h1>
      <p style="color: var(--text-secondary); font-size: 14px;">Browse equipment available at your gym for training clients</p>
    </div>
    <a href="index.php?r=home/index" class="btn-back btn text-decoration-none">
      <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
    </a>
  </div>

  <!-- Equipment Stats -->
  <div class="row g-3 mb-4">
    <div class="col-4">
      <div class="mini-stat">
        <div class="mini-num" style="color: var(--accent-teal)"><?= $totalEquipment ?></div>
        <div class="mini-lbl">Total Equipment</div>
      </div>
    </div>
    <div class="col-4">
      <div class="mini-stat">
        <div class="mini-num" style="color: var(--accent-blue)"><?= count($categories) ?></div>
        <div class="mini-lbl">Categories</div>
      </div>
    </div>
    <div class="col-4">
      <div class="mini-stat">
        <div class="mini-num" style="color: #16a34a">Active</div>
        <div class="mini-lbl">Status</div>
      </div>
    </div>
  </div>

  <?php if (empty($equipment)): ?>
  <div class="equip-card p-5 text-center">
    <i class="bi bi-tools" style="font-size:3rem; color: var(--text-secondary); opacity:.3;"></i>
    <h5 class="mt-3 mb-2 fw-bold" style="color: var(--text-primary);">No Equipment Available</h5>
    <p style="color: var(--text-secondary); font-size: 14px;">Your gym hasn't added any equipment to the inventory yet.</p>
  </div>
  <?php else: ?>

  <!-- Category Filter Tabs -->
  <div class="equip-card p-3 mb-4">
    <div class="d-flex flex-wrap gap-2">
      <button class="btn-filter active" data-filter="all">
        All Equipment (<?= $totalEquipment ?>)
      </button>
      <?php foreach ($categories as $category => $count): ?>
      <button class="btn-filter" data-filter="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $category))) ?>">
        <?= htmlspecialchars($category) ?> (<?= $count ?>)
      </button>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Equipment Grid -->
  <div class="row g-3" id="equipmentGrid">
    <?php foreach ($equipment as $item): 
        $categoryClass = strtolower(str_replace(' ', '-', $item['category'] ?? 'other'));
        $quantity = (int)($item['quantity'] ?? 0);
        $equipmentName = htmlspecialchars($item['equipment_name'] ?? 'Unknown Equipment');
        $category = htmlspecialchars($item['category'] ?? 'Other');
        $supplierName = htmlspecialchars($item['supplier_name'] ?? 'Unknown Supplier');
        $purchasedDate = date('M j, Y', strtotime($item['purchased_at'] ?? 'now'));
    ?>
    <div class="col-md-6 col-lg-4 equipment-item" data-category="<?= $categoryClass ?>">
      <div class="equip-card h-100 p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div class="flex-grow-1">
            <h5 class="fw-bold mb-1" style="font-size: 1.1rem; color: var(--text-primary);"><?= $equipmentName ?></h5>
            <span class="badge-category"><?= $category ?></span>
          </div>
          <span class="badge-quantity"><?= $quantity ?> Available</span>
        </div>

        <div class="mb-3" style="font-size: 13px; color: var(--text-secondary);">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-building me-2"></i>
            <span>Supplier: <?= $supplierName ?></span>
          </div>
          <div class="d-flex align-items-center">
            <i class="bi bi-calendar3 me-2"></i>
            <span>Added: <?= $purchasedDate ?></span>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-3" style="border-top: 1px solid var(--border-card); font-size: 13px; color: #16a34a; font-weight: 600;">
          <span>
            <i class="bi bi-check-circle-fill me-1"></i>Ready for training
          </span>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
      const filterButtons = document.querySelectorAll('[data-filter]');
      const equipmentItems = document.querySelectorAll('.equipment-item');

      filterButtons.forEach(button => {
          button.addEventListener('click', function() {
              const filter = this.getAttribute('data-filter');

              filterButtons.forEach(btn => {
                  btn.classList.remove('active');
              });
              this.classList.add('active');

              equipmentItems.forEach(item => {
                  if (filter === 'all' || item.getAttribute('data-category') === filter) {
                      item.style.display = 'block';
                      setTimeout(() => {
                          item.style.opacity = '1';
                          item.style.transform = 'scale(1)';
                      }, 10);
                  } else {
                      item.style.opacity = '0';
                      item.style.transform = 'scale(0.9)';
                      setTimeout(() => {
                          item.style.display = 'none';
                      }, 300);
                  }
              });
          });
      });

      equipmentItems.forEach(item => {
          item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
      });
  });
  </script>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
