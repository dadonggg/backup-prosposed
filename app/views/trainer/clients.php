<?php
declare(strict_types=1);
$pageTitle = 'My Clients — Trainer Dashboard';
require __DIR__ . '/../partials/header.php';
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
  --bg-page:           #f0f2f0;
  --bg-card:           #ffffff;
  --bg-section-header: #e8f5f0;
  --bg-trainer-box:    #f0fdf9;
  --border-card:       #e2e8f0;
  --border-teal:       #0d9488;
  --border-trainer:    #99f6e4;
  --accent-teal:       #0d9488;
  --accent-blue:       #06b6d4;
  --accent-green-btn:  #166534;
  --accent-green-hover:#15803d;
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

/* ── Client Card ── */
.client-card {
  background: var(--bg-card);
  border: 1px solid var(--border-card);
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  transition: all .25s;
  overflow: hidden;
  cursor: pointer;
}
.client-card:hover {
  border-color: var(--accent-teal);
  box-shadow: 0 4px 16px rgba(13,148,136,0.12);
  transform: translateY(-2px);
}

/* ── Avatar ── */
.client-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--accent-teal), var(--accent-blue));
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 1.2rem;
  color: #fff;
  flex-shrink: 0;
}

/* ── Status Badges ── */
.badge-fit {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 50px;
  font-size: 12px;
  font-weight: 600;
}
.badge-assigned { background: #e0f2fe; color: #0369a1; }
.badge-pending { background: #fef9c3; color: #854d0e; }
.badge-completed { background: #ede9fe; color: #6d28d9; }

/* ── Expandable Profile Panel ── */
.profile-panel {
  background: #f8fafc;
  border-top: 1px solid var(--border-card);
  max-height: 0;
  overflow: hidden;
  transition: max-height .4s cubic-bezier(0.4, 0, 0.2, 1);
}
.profile-panel.open { max-height: 800px; }

.profile-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: .6rem 0;
  border-bottom: 1px solid var(--border-card);
}
.profile-row:last-child { border-bottom: none; }
.profile-key { color: var(--text-secondary); font-size: 12px; font-weight: 500; }
.profile-val { color: var(--text-primary); font-size: 13px; font-weight: 600; text-align: right; max-width: 200px; }

/* ── Goal Tags ── */
.goal-tag {
  display: inline-block;
  background: var(--bg-section-header);
  color: var(--accent-teal);
  border-radius: 50px;
  padding: 3px 12px;
  font-size: 12px;
  font-weight: 600;
  margin: 2px;
}

/* ── Action Buttons ── */
.btn-action-teal {
  border: 1.5px solid var(--accent-teal);
  color: var(--accent-teal);
  background: transparent;
  border-radius: 8px;
  padding: 7px 14px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all .2s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: .3rem;
}
.btn-action-teal:hover { background: rgba(13,148,136,0.08); color: var(--accent-teal); }

.btn-action-amber {
  border: none;
  color: #fff;
  background: #d97706;
  border-radius: 8px;
  padding: 7px 16px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all .2s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: .3rem;
}
.btn-action-amber:hover { background: #b45309; color: #fff; }
</style>

<div class="p-1">
  <div class="d-flex justify-content-between align-items-start mb-4">
    <div>
      <h1 class="fw-extrabold mb-1" style="color: var(--text-primary); font-size: 26px; font-weight: 800;">
        <i class="bi bi-people me-2" style="color: var(--accent-teal)"></i>My Clients
      </h1>
      <p style="color: var(--text-secondary); font-size: 14px;">Fitness Trainer: <strong style="color: var(--text-primary)"><?= htmlspecialchars($user['fullname'] ?? '') ?></strong></p>
    </div>
    <a href="index.php?r=trainer/progress" class="btn-action-amber text-decoration-none">
      <i class="bi bi-graph-up me-1"></i>Progress Reviews
    </a>
  </div>

  <!-- Stats Row -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="mini-stat">
        <div class="mini-num" style="color: var(--accent-teal)"><?= count($assignedClients) ?></div>
        <div class="mini-lbl">Total Clients</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="mini-stat">
        <div class="mini-num" style="color: #3b82f6"><?= count(array_filter($assignedClients, fn($c) => $c['status'] === 'assigned')) ?></div>
        <div class="mini-lbl">Active</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="mini-stat">
        <div class="mini-num" style="color: #7c3aed"><?= count(array_filter($assignedClients, fn($c) => $c['status'] === 'completed')) ?></div>
        <div class="mini-lbl">Completed</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="mini-stat">
        <div class="mini-num" style="color: #d97706"><?= count(array_filter($assignedClients, fn($c) => !isset($c['profile_id']) || !$c['profile_id'])) ?></div>
        <div class="mini-lbl">Awaiting Profile</div>
      </div>
    </div>
  </div>

  <!-- Client Cards -->
  <?php if (empty($assignedClients)): ?>
  <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 12px; box-shadow: var(--shadow-card);">
    <div class="p-5 text-center">
      <i class="bi bi-people" style="font-size:3rem; color: var(--text-secondary); opacity:.3;"></i>
      <h5 class="mt-3 mb-2 fw-bold" style="color: var(--text-primary);">No Clients Assigned Yet</h5>
      <p style="color: var(--text-secondary); font-size: 14px;">The administrative officer will assign clients to you.</p>
    </div>
  </div>
  <?php else: ?>
  <div class="d-flex flex-column gap-3">
    <?php foreach ($assignedClients as $idx => $client):
      $initials = strtoupper(substr($client['member_name'] ?? $client['full_name'] ?? 'C', 0, 1));
      $name = $client['member_name'] ?? $client['full_name'] ?? 'Client';
      $trainingTypes = array_map('trim', explode(',', $client['training_type'] ?? ''));
      $goals = !empty($client['fitness_goals']) ? array_map('trim', explode(',', $client['fitness_goals'])) : [];
      $statusClass = $client['status'] === 'assigned' ? 'badge-assigned' : ($client['status'] === 'completed' ? 'badge-completed' : 'badge-pending');
    ?>
    <div class="client-card" id="card<?= $idx ?>">
      <!-- Client Header (clickable) -->
      <div class="p-4 d-flex align-items-center gap-3" onclick="toggleProfile(<?= $idx ?>)" style="user-select:none;">
        <?php if (!empty($client['profile_picture_url'])): ?>
          <img src="public/<?= htmlspecialchars(ltrim($client['profile_picture_url'], '/')) ?>" class="client-avatar" style="object-fit:cover;" alt="Avatar">
        <?php else: ?>
          <div class="client-avatar"><?= $initials ?></div>
        <?php endif; ?>
        <div class="flex-grow-1">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <h5 class="mb-0 fw-bold" style="font-size: 1.1rem; color: var(--text-primary);"><?= htmlspecialchars($name) ?></h5>
            <span class="badge-fit <?= $statusClass ?>"><?= ucfirst($client['status']) ?></span>
          </div>
          <div class="mt-1 d-flex gap-2 flex-wrap">
            <?php foreach ($trainingTypes as $t): ?>
            <span style="font-size: 12px; color: var(--text-secondary)"><i class="bi bi-dot me-0"></i><?= ucwords(str_replace('_',' ',$t)) ?></span>
            <?php endforeach; ?>
            <span style="font-size: 12px; color: var(--text-secondary);">· <?= $client['session_preference'] ?> sessions/wk</span>
          </div>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <?php if (!empty($client['client_user_id'])): ?>
          <a href="index.php?r=message/index&with=<?= (int)$client['client_user_id'] ?>" class="btn btn-sm btn-outline-success rounded-pill fw-semibold" onclick="event.stopPropagation()">
            <i class="bi bi-chat-dots-fill me-1"></i>Message
          </a>
          <?php endif; ?>
          <a href="index.php?r=trainer/createPlan&request_id=<?= $client['id'] ?>" class="btn-action-teal" onclick="event.stopPropagation()">
            <i class="bi bi-pencil-square me-1"></i>Create Plan
          </a>
          <i class="bi bi-chevron-down" id="chevron<?= $idx ?>" style="color: var(--text-secondary); transition: transform .3s; font-size: .9rem;"></i>
        </div>
      </div>

      <!-- Expandable Profile Panel -->
      <div class="profile-panel" id="panel<?= $idx ?>">
        <div class="p-4">
          <?php if (!empty($client['fitness_goals']) || !empty($client['age'])): ?>
          <div class="row g-4">
            <div class="col-md-6">
              <h6 style="color: var(--text-secondary); font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 1rem;">CLIENT PROFILE</h6>
              <div class="profile-row"><span class="profile-key">Age</span><span class="profile-val"><?= htmlspecialchars((string)($client['age'] ?? '—')) ?></span></div>
              <div class="profile-row"><span class="profile-key">Gender</span><span class="profile-val"><?= ucfirst(str_replace('_',' ',$client['gender'] ?? '—')) ?></span></div>
              <div class="profile-row"><span class="profile-key">Height</span><span class="profile-val"><?= $client['height_cm'] ?? '—' ?> cm</span></div>
              <div class="profile-row"><span class="profile-key">Weight</span><span class="profile-val"><?= $client['weight_kg'] ?? '—' ?> kg</span></div>
              <div class="profile-row"><span class="profile-key">Activity Level</span><span class="profile-val"><?= ucwords(str_replace('_',' ',$client['activity_level'] ?? '—')) ?></span></div>
              <?php
              $addrParts = array_filter([
                  $client['street'] ?? '',
                  $client['barangay'] ?? '',
                  $client['city'] ?? '',
                  $client['province'] ?? ''
              ]);
              $addrStr = $addrParts ? implode(', ', $addrParts) : ($client['address'] ?? '—');
              ?>
              <div class="profile-row"><span class="profile-key">Address</span><span class="profile-val"><?= htmlspecialchars($addrStr) ?></span></div>
              <?php if (!empty($client['medical_conditions'])): ?>
              <div class="profile-row"><span class="profile-key">Medical</span><span class="profile-val"><?= htmlspecialchars($client['medical_conditions']) ?></span></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <?php if (!empty($goals)): ?>
              <h6 style="color: var(--text-secondary); font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: .75rem;">FITNESS GOALS</h6>
              <div class="mb-3">
                <?php foreach ($goals as $g): ?>
                <span class="goal-tag"><?= ucwords(str_replace('_',' ',$g)) ?></span>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
              <?php if (!empty($client['dietary_preferences'])): ?>
              <h6 style="color: var(--text-secondary); font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: .5rem;">DIETARY</h6>
              <p style="font-size: 13px; color: var(--text-primary);"><?= htmlspecialchars($client['dietary_preferences']) ?></p>
              <?php endif; ?>
            </div>
          </div>
          <?php else: ?>
          <div class="text-center py-3">
            <i class="bi bi-person-x" style="font-size:2rem; color: var(--text-secondary); opacity:.3;"></i>
            <p class="mt-2 mb-0" style="color: var(--text-secondary); font-size: 14px;">Client has not filled their profile yet.</p>
          </div>
          <?php endif; ?>
          <div class="mt-3 pt-3 d-flex gap-2 justify-content-end" style="border-top: 1px solid var(--border-card);">
            <a href="index.php?r=trainer/createPlan&request_id=<?= $client['id'] ?>" class="btn-action-teal text-decoration-none">
              <i class="bi bi-pencil-square me-1"></i>Write Plan
            </a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
function toggleProfile(idx) {
  const panel = document.getElementById('panel' + idx);
  const chev = document.getElementById('chevron' + idx);
  panel.classList.toggle('open');
  chev.style.transform = panel.classList.contains('open') ? 'rotate(180deg)' : '';
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
