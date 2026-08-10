<?php
declare(strict_types=1);
$pageTitle = 'Fitness & Nutrition Plan — Step 3';
require __DIR__ . '/../partials/header.php';
$displayName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
if ($displayName === '') $displayName = $user['fullname'] ?? 'Member';
$requestId = $request['id'];

// New schema workout schedule query
$db_conn = \App\Core\Database::pdo();
$client_id = (int)($member['id'] ?? 0);

$new_plan_stmt = $db_conn->prepare("SELECT * FROM fitness_plans WHERE client_id = ? AND status = 'active' LIMIT 1");
$new_plan_stmt->execute([$client_id]);
$new_plan = $new_plan_stmt->fetch();
$new_plan_id = $new_plan ? (int)$new_plan['id'] : 0;

$new_schedule = [
    'monday' => [], 'tuesday' => [], 'wednesday' => [],
    'thursday' => [], 'friday' => [], 'saturday' => [], 'sunday' => []
];
if ($new_plan_id > 0) {
    $ex_stmt = $db_conn->prepare("SELECT * FROM plan_exercises WHERE plan_id = ? ORDER BY exercise_order ASC");
    $ex_stmt->execute([$new_plan_id]);
    $exercises = $ex_stmt->fetchAll();
    foreach ($exercises as $ex) {
        $new_schedule[strtolower($ex['day_of_week'])][] = $ex;
    }
    
    // Log client view in plan_view_log
    $logStmt = $db_conn->prepare("INSERT INTO plan_view_log (plan_id, client_id) VALUES (?, ?)");
    $logStmt->execute([$new_plan_id, $client_id]);
}
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
  /* Backgrounds */
  --bg-page:           #f0f2f0;
  --bg-card:           #ffffff;
  --bg-section-header: #e8f5f0;
  --bg-input:          #ffffff;
  --bg-info-banner:    #eff6ff;
  --bg-trainer-box:    #f0fdf9;
  --bg-tag:            #f1f5f9;

  /* Borders */
  --border-card:       #e2e8f0;
  --border-input:      #cbd5e1;
  --border-teal:       #0d9488;
  --border-info:       #bfdbfe;
  --border-trainer:    #99f6e4;

  /* Accent Colors */
  --accent-teal:       #0d9488;
  --accent-teal-light: #14b8a6;
  --accent-blue:       #06b6d4;
  --accent-green-btn:  #166534;
  --accent-green-hover:#15803d;

  /* Text */
  --text-primary:      #1e293b;
  --text-secondary:    #64748b;
  --text-teal:         #0d9488;
  --text-blue:         #06b6d4;
  --text-white:        #ffffff;

  /* Badges */
  --badge-assigned-bg:   #06b6d4;
  --badge-assigned-text: #ffffff;
  --badge-personal-bg:   #3b82f6;
  --badge-personal-text: #ffffff;
  --tag-bg:              #e2e8f0;
  --tag-text:            #475569;

  /* Shadows */
  --shadow-card: 0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.05);
  --shadow-sm:   0 1px 2px rgba(0,0,0,0.06);
}

body {
  background: var(--bg-page) !important;
  font-family: 'Inter', system-ui, sans-serif !important;
  color: var(--text-primary) !important;
}

/* ── Step Bar ── */
.step-bar {
  display: flex;
  margin-bottom: 2rem;
  position: relative;
}
.step-bar::before {
  content: '';
  position: absolute;
  top: 18px;
  left: 10%;
  right: 10%;
  height: 2px;
  background: var(--border-card);
  z-index: 0;
}
.step-item {
  flex: 1;
  text-align: center;
  position: relative;
  z-index: 1;
}
.step-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 6px;
  font-weight: 700;
  font-size: .85rem;
  border: 2px solid var(--border-card);
  background: var(--bg-card);
  color: var(--text-secondary);
  transition: all .3s;
}
.step-item.done .step-circle {
  background: #22c55e;
  border-color: #22c55e;
  color: #fff;
}
.step-item.active .step-circle {
  background: var(--accent-teal);
  border-color: var(--accent-teal);
  color: #fff;
  box-shadow: 0 0 12px rgba(13,148,136,0.3);
}
.step-label {
  font-size: .75rem;
  color: var(--text-secondary);
  font-weight: 500;
}
.step-item.active .step-label {
  color: var(--accent-teal);
  font-weight: 600;
}
.step-item.done .step-label {
  color: #22c55e;
  font-weight: 600;
}

/* ── Cards ── */
.fit-card {
  background: var(--bg-card);
  border: 1px solid var(--border-card);
  border-radius: 12px;
  box-shadow: var(--shadow-card);
  margin-bottom: 1.5rem;
  overflow: hidden;
}

/* ── Section Headers ── */
.fit-card-hd {
  background: var(--bg-section-header);
  border-left: 4px solid var(--accent-teal);
  border-bottom: 1px solid var(--border-card);
  padding: 12px 18px;
}
.fit-card-hd.nutrition-hd {
  background: #fffbeb;
  border-left: 4px solid #f59e0b;
}
.fit-card-hd.logs-hd {
  background: #f8fafc;
  border-left: 4px solid var(--accent-blue);
}
.fit-card-hd .fit-heading {
  color: var(--accent-teal) !important;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin: 0;
}
.fit-card-hd.nutrition-hd .fit-heading {
  color: #d97706 !important;
}
.fit-card-hd.logs-hd .fit-heading {
  color: var(--text-primary) !important;
}

/* ── Workout Schedule Subcards ── */
.day-card {
  background: #f8fafc;
  border: 1px solid var(--border-card);
  border-radius: 10px;
  padding: 1rem;
  height: 100%;
}
.day-label {
  color: var(--accent-teal);
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 6px;
}
.day-content {
  color: var(--text-primary);
  font-size: 13px;
  white-space: pre-wrap;
}

/* ── Macro Box Subcards ── */
.macro-box {
  background: #f8fafc;
  border: 1px solid var(--border-card);
  border-radius: 10px;
  padding: 1.2rem 0.5rem;
  text-align: center;
}
.macro-val {
  font-size: 2rem;
  font-weight: 800;
  line-height: 1.2;
}
.macro-lbl {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-top: 4px;
}

/* ── Form Inputs ── */
.fit-input {
  background: var(--bg-input) !important;
  border: 1px solid var(--border-input) !important;
  color: var(--text-primary) !important;
  border-radius: 8px !important;
  padding: 10px 14px !important;
  font-size: 14px !important;
  transition: border-color .2s, box-shadow .2s;
}
.fit-input:focus {
  border-color: var(--accent-teal) !important;
  box-shadow: 0 0 0 3px rgba(13,148,136,0.12) !important;
  outline: none;
}
.fit-label {
  color: var(--text-secondary);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  margin-bottom: 6px;
  display: block;
}

/* ── Buttons ── */
.btn-fit {
  background: var(--accent-green-btn);
  color: #fff !important;
  border: none;
  border-radius: 8px;
  padding: 12px 20px;
  font-weight: 600;
  font-size: 14px;
  transition: all .2s;
}
.btn-fit:hover {
  background: var(--accent-green-hover);
  transform: translateY(-1px);
}
.btn-accent {
  background: #ffffff;
  border: 1px solid var(--border-card);
  color: var(--text-secondary) !important;
  border-radius: 8px;
  padding: 12px 20px;
  font-weight: 600;
  font-size: 14px;
  transition: all .2s;
}
.btn-accent:hover {
  border-color: var(--accent-teal);
  color: var(--accent-teal) !important;
  background: #f0fdf9;
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

/* ── Tables ── */
.log-table {
  color: var(--text-primary);
  width: 100%;
}
.log-table thead th {
  color: var(--text-secondary);
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  border-bottom: 2px solid var(--border-card);
  padding: 12px 10px;
}
.log-table td {
  border-bottom: 1px solid var(--border-card);
  vertical-align: middle;
  font-size: 14px;
  padding: 12px 10px;
}
.log-table tbody tr:hover {
  background: #f8fafc;
}

/* ── Meal Badges ── */
.meal-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}
.badge-breakfast {
  background: #fef3c7;
  color: #d97706;
}
.badge-lunch {
  background: #dcfce7;
  color: #16a34a;
}
.badge-dinner {
  background: #e0e7ff;
  color: #4f46e5;
}
.badge-snack {
  background: #ffedd5;
  color: #ea580c;
}

/* ── Tab Navigation ── */
.tab-nav {
  display: flex;
  gap: 8px;
  margin-bottom: 1.5rem;
  background: #f1f5f9;
  border-radius: 12px;
  padding: 6px;
}
.tab-btn {
  flex: 1;
  padding: 10px;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: var(--text-secondary);
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all .2s;
}
.tab-btn.active {
  background: #ffffff;
  color: var(--accent-teal);
  box-shadow: var(--shadow-sm);
}
.tab-pane {
  display: none;
}
.tab-pane.active {
  display: block;
  animation: fadeIn .3s ease;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: none; }
}

/* ── Toast Notification ── */
.toast-notif {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  background: var(--bg-card);
  border: 1px solid var(--accent-teal);
  border-radius: 12px;
  padding: 1rem 1.5rem;
  box-shadow: var(--shadow-card);
  display: none;
  animation: slideUp .3s ease;
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: none; }
}
</style>

<div class="p-1">
  <!-- Step Progress Bar -->
  <div class="step-bar">
    <div class="step-item done"><div class="step-circle"><i class="bi bi-check-lg"></i></div><div class="step-label">Request</div></div>
    <div class="step-item done"><div class="step-circle"><i class="bi bi-check-lg"></i></div><div class="step-label">Profile</div></div>
    <div class="step-item active"><div class="step-circle">3</div><div class="step-label">Your Plan</div></div>
    <div class="step-item"><div class="step-circle">4</div><div class="step-label">Progress</div></div>
  </div>

  <div class="d-flex justify-content-between align-items-start mb-4">
    <div>
      <h1 class="fw-extrabold mb-1" style="color: var(--text-primary); font-size: 26px; font-weight: 800;">
        <i class="bi bi-file-earmark-check me-2" style="color: var(--accent-teal)"></i>Your Fitness & Nutrition Plan
      </h1>
      <p style="color: var(--text-secondary); font-size: 14px;">Created by <strong style="color: var(--text-primary)"><?= htmlspecialchars($plan['trainer_name'] ?? 'Your Trainer') ?></strong> · <?= $plan['fitness_level'] ? '<span style="color: var(--accent-teal); text-transform: capitalize; font-weight: 600;">'.htmlspecialchars($plan['fitness_level']).' Level</span>' : '' ?></p>
    </div>
    <div class="d-flex gap-2">
      <a href="index.php?r=fitness/progress&request_id=<?= $requestId ?>" class="btn-accent btn text-decoration-none">
        <i class="bi bi-graph-up me-1"></i>Track Progress
      </a>
      <a href="index.php?r=fitness/status" class="btn-back btn text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i>Back
      </a>
    </div>
  </div>

  <!-- Weekly Fitness Schedule -->
  <div class="fit-card">
    <div class="fit-card-hd d-flex align-items-center justify-content-between flex-wrap gap-2">
      <h5 class="fit-heading">
        <i class="bi bi-calendar-week me-2"></i>WEEKLY WORKOUT SCHEDULE
      </h5>
      <span style="color: var(--text-secondary); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
        <?= $new_plan_id > 0 ? htmlspecialchars((string)$new_plan['sessions_week']) : $plan['recommended_sessions_per_week'] ?> sessions/week
      </span>
    </div>
    
    <?php if ($new_plan_id > 0): ?>
    <!-- Premium Interactive Exercise Schedule Tab Interface -->
    <div class="fit-card-hd d-flex overflow-auto border-0" style="background-color: #fafafa; padding: 8px 16px;">
        <?php 
        $days = ['monday'=>'Mon', 'tuesday'=>'Tue', 'wednesday'=>'Wed', 'thursday'=>'Thu', 'friday'=>'Fri', 'saturday'=>'Sat', 'sunday'=>'Sun'];
        $first_active = true;
        foreach ($days as $day_key => $day_lbl): 
            $has_exercises = count($new_schedule[$day_key]) > 0;
        ?>
            <button class="day-tab btn py-2 px-3 fw-bold <?= $first_active ? 'active text-teal border-bottom border-3 border-teal' : 'text-secondary' ?>" 
                    id="tab-btn-<?= $day_key ?>" 
                    onclick="switchDayTab('<?= $day_key ?>')"
                    style="border-radius: 0; border: none; font-size: 13px; outline: none; box-shadow: none;">
                <?= $day_lbl ?> 
                <?php if ($has_exercises): ?>
                    <span class="badge rounded-pill bg-teal ms-1" style="background-color: #0d9488; font-size: 9px;"><?= count($new_schedule[$day_key]) ?></span>
                <?php endif; ?>
            </button>
        <?php 
            $first_active = false;
        endforeach; ?>
    </div>

    <div class="p-4">
        <?php 
        $first_pane = true;
        foreach ($days as $day_key => $day_lbl): 
            $exercises_list = $new_schedule[$day_key];
        ?>
        <div class="day-pane" id="pane-<?= $day_key ?>" style="display: <?= $first_pane ? 'block' : 'none' ?>;">
            <?php if (count($exercises_list) === 0): ?>
                <div class="text-center py-5 text-secondary">
                    <i class="bi bi-calendar-check" style="font-size: 48px; color: #0d9488; opacity: 0.5;"></i>
                    <h5 class="mt-3 fw-bold">Active Recovery / Rest Day</h5>
                    <p class="m-0 text-secondary" style="font-size: 13px;">Allow your muscle tissue to heal and rebuild. Hydrate and rest well!</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($exercises_list as $ex): 
                        $inst = $ex['instructions'];
                    ?>
                    <div class="col-12 col-md-6 col-lg-4 d-flex">
                        <div class="day-card w-100 d-flex flex-column justify-content-between p-3" style="background: #ffffff; border: 1px solid var(--border-card); border-radius: 12px;">
                            <div>
                                <div class="text-center mb-3">
                                    <img src="public/api/exercises.php?action=image&id=<?= urlencode($ex['exercise_id']) ?>" alt="<?= htmlspecialchars($ex['exercise_name']) ?>" class="img-fluid rounded" style="max-height: 180px; border: 1px solid var(--border-card); background: #f8fafc;" onerror="this.src='https://placehold.co/200?text=Workout+Demo'">
                                </div>
                                <h4 class="h6 fw-extrabold mb-1" style="font-weight: 800; text-transform: capitalize; color: var(--text-primary);"><?= htmlspecialchars($ex['exercise_name']) ?></h4>
                                
                                <div class="mt-2 mb-3">
                                    <span class="info-badge me-1" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-size: 10px;"><?= htmlspecialchars($ex['body_part']) ?></span>
                                    <span class="info-badge" style="background: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe; font-size: 10px;"><?= htmlspecialchars($ex['equipment']) ?></span>
                                </div>

                                <!-- Sets/Reps/Rest -->
                                <div class="row g-2 text-center py-2 bg-light rounded mb-3" style="margin-left: 0; margin-right: 0;">
                                    <div class="col-4 border-end">
                                        <span class="text-secondary d-block" style="font-size: 9px; font-weight: 700; text-transform: uppercase;">Sets</span>
                                        <strong style="font-size: 14px; color: #0d9488;"><?= htmlspecialchars((string)$ex['sets']) ?></strong>
                                    </div>
                                    <div class="col-4 border-end">
                                        <span class="text-secondary d-block" style="font-size: 9px; font-weight: 700; text-transform: uppercase;">Reps</span>
                                        <strong style="font-size: 14px;"><?= htmlspecialchars($ex['reps']) ?></strong>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-secondary d-block" style="font-size: 9px; font-weight: 700; text-transform: uppercase;">Rest</span>
                                        <strong style="font-size: 14px;"><?= htmlspecialchars($ex['rest_time']) ?></strong>
                                    </div>
                                </div>

                                <?php if (!empty($ex['trainer_notes'])): ?>
                                <div class="p-2 mb-3 rounded" style="background-color: #fffbeb; border: 1px solid #fef3c7;">
                                    <span class="d-block text-secondary" style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #d97706;"><i class="bi bi-chat-left-dots-fill me-1"></i>Coach Notes</span>
                                    <p class="m-0 text-dark" style="font-size: 12px; font-style: italic;"><?= htmlspecialchars($ex['trainer_notes']) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Dynamic Instructions Toggle -->
                            <?php if (!empty($inst)): ?>
                            <div>
                                <button class="btn btn-link p-0 text-decoration-none fw-bold" id="toggle-btn-<?= $ex['id'] ?>" onclick="toggleInstructions(<?= $ex['id'] ?>)" style="color: #0d9488; font-size: 12px;">
                                    <i class="bi bi-plus-circle me-1"></i>View Guide
                                </button>
                                <div class="instructions-content mt-2" id="instructions-<?= $ex['id'] ?>" style="display: none;">
                                    <ol style="font-size: 11px; padding-left: 16px; margin: 0; color: var(--text-secondary);">
                                        <?php foreach ($inst as $step): ?>
                                            <li class="mb-1"><?= htmlspecialchars($step) ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php 
            $first_pane = false;
        endforeach; ?>
        
        <?php if (!empty($new_plan['additional_notes'])): ?>
        <div class="mt-4 p-3" style="background: var(--bg-trainer-box); border-radius:10px; border:1px solid var(--border-trainer);">
            <p class="mb-0" style="font-size: 13px; color: var(--text-primary);"><strong style="color: var(--accent-teal);">Trainer Notes:</strong> <?= nl2br(htmlspecialchars($new_plan['additional_notes'])) ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    function switchDayTab(day) {
        // Hide all day panes
        const panes = document.querySelectorAll('.day-pane');
        panes.forEach(pane => pane.style.display = 'none');
        
        // Deactivate all day tabs
        const tabs = document.querySelectorAll('.day-tab');
        tabs.forEach(tab => {
            tab.classList.remove('active', 'text-teal', 'border-bottom', 'border-3', 'border-teal');
            tab.classList.add('text-secondary');
        });
        
        // Show active day pane & set active tab
        document.getElementById('pane-' + day).style.display = 'block';
        const activeTab = document.getElementById('tab-btn-' + day);
        activeTab.classList.add('active', 'text-teal', 'border-bottom', 'border-3', 'border-teal');
        activeTab.classList.remove('text-secondary');
    }

    function toggleInstructions(id) {
        const el = document.getElementById('instructions-' + id);
        const btn = document.getElementById('toggle-btn-' + id);
        
        if (el.style.display === 'none') {
            el.style.display = 'block';
            btn.innerHTML = `<i class="bi bi-dash-circle me-1"></i>Hide Guide`;
        } else {
            el.style.display = 'none';
            btn.innerHTML = `<i class="bi bi-plus-circle me-1"></i>View Guide`;
        }
    }
    </script>
    
    <?php else: ?>
    <!-- Fallback to original schedule format -->
    <div class="p-4">
      <div class="row g-3">
        <?php
        $days = ['monday'=>'MON','tuesday'=>'TUE','wednesday'=>'WED','thursday'=>'THU','friday'=>'FRI','saturday'=>'SAT','sunday'=>'SUN'];
        foreach ($days as $day => $label): $content = $plan['fitness_plan_' . $day] ?? ''; ?>
        <div class="col-lg-3 col-md-4 col-6">
          <div class="day-card">
            <div class="day-label"><?= $label ?></div>
            <?php if (trim($content)): ?>
            <div class="day-content"><?= htmlspecialchars($content) ?></div>
            <?php else: ?>
            <div style="color: var(--text-secondary); font-size: 13px; font-style: italic;">Rest day</div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (!empty($plan['fitness_plan_notes'])): ?>
      <div class="mt-3 p-3" style="background: var(--bg-trainer-box); border-radius:10px; border:1px solid var(--border-trainer);">
        <p class="mb-0" style="font-size: 13px; color: var(--text-primary);"><strong style="color: var(--accent-teal);">Trainer Notes:</strong> <?= nl2br(htmlspecialchars($plan['fitness_plan_notes'])) ?></p>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Nutrition Plan -->
  <div class="fit-card">
    <div class="fit-card-hd nutrition-hd">
      <h5 class="fit-heading">
        <i class="bi bi-egg-fried me-2"></i>NUTRITION TARGETS
      </h5>
    </div>
    <div class="p-4">
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="macro-box">
            <div class="macro-val" style="color: #ef4444"><?= $plan['target_calories'] ?? '—' ?></div>
            <div class="macro-lbl">DAILY CALORIES</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="macro-box">
            <div class="macro-val" style="color: #f59e0b"><?= $plan['target_protein_g'] ?? '—' ?><small style="font-size:1rem; font-weight: 600;">g</small></div>
            <div class="macro-lbl">PROTEIN</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="macro-box">
            <div class="macro-val" style="color: #3b82f6"><?= $plan['target_carbs_g'] ?? '—' ?><small style="font-size:1rem; font-weight: 600;">g</small></div>
            <div class="macro-lbl">CARBOHYDRATES</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="macro-box">
            <div class="macro-val" style="color: #0d9488"><?= $plan['target_fats_g'] ?? '—' ?><small style="font-size:1rem; font-weight: 600;">g</small></div>
            <div class="macro-lbl">FATS</div>
          </div>
        </div>
      </div>
      <?php if (!empty($plan['meal_suggestions'])): ?>
      <div class="p-3" style="background: #fffbeb; border-radius:10px; border:1px solid #fef3c7;">
        <p class="mb-1" style="color: #d97706; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">Meal Suggestions</p>
        <p class="mb-0" style="font-size:13px; color: var(--text-primary);"><?= nl2br(htmlspecialchars($plan['meal_suggestions'])) ?></p>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Logging Tabs -->
  <div class="fit-card">
    <div class="fit-card-hd logs-hd">
      <h5 class="fit-heading">
        <i class="bi bi-journal-check me-2" style="color: var(--accent-teal)"></i>DAILY LOGS
      </h5>
    </div>
    <div class="p-4">
      <div class="tab-nav">
        <button class="tab-btn active" onclick="switchTab('workout',this)"><i class="bi bi-lightning me-1"></i>Workout Log</button>
        <button class="tab-btn" onclick="switchTab('nutrition',this)"><i class="bi bi-egg-fried me-1"></i>Nutrition Log</button>
      </div>

      <!-- Workout Tab -->
      <div id="tab-workout" class="tab-pane active">
        <form id="workoutForm" class="mb-4">
          <input type="hidden" name="request_id" value="<?= $requestId ?>">
          <div class="row g-2 align-items-end">
            <div class="col-md-2 col-6">
              <label class="fit-label">Date</label>
              <input type="date" name="log_date" class="form-control fit-input" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-3 col-6">
              <label class="fit-label">Exercise</label>
              <input type="text" name="exercise_name" class="form-control fit-input" placeholder="e.g. Bench Press" required>
            </div>
            <div class="col-md-1 col-3">
              <label class="fit-label">Sets</label>
              <input type="number" name="sets" class="form-control fit-input" min="0" value="3">
            </div>
            <div class="col-md-1 col-3">
              <label class="fit-label">Reps</label>
              <input type="number" name="reps" class="form-control fit-input" min="0" value="10">
            </div>
            <div class="col-md-1 col-3">
              <label class="fit-label">Wt (kg)</label>
              <input type="number" name="weight_kg" class="form-control fit-input" min="0" step="0.5" value="0">
            </div>
            <div class="col-md-1 col-3">
              <label class="fit-label">Mins</label>
              <input type="number" name="duration_minutes" class="form-control fit-input" min="0" value="30">
            </div>
            <div class="col-md-2 col-8">
              <label class="fit-label">Notes</label>
              <input type="text" name="notes" class="form-control fit-input" placeholder="Optional">
            </div>
            <div class="col-md-1 col-4">
              <button type="submit" class="btn-fit btn w-100" style="padding: 10px 5px;">
                <i class="bi bi-plus-lg"></i>
              </button>
            </div>
          </div>
        </form>
        <div class="table-responsive">
          <table class="table log-table" id="workoutTable">
            <thead><tr>
              <th>Date</th><th>Exercise</th><th>Sets</th><th>Reps</th><th>Weight</th><th>Duration</th><th>Notes</th>
            </tr></thead>
            <tbody>
              <?php foreach ($workoutLogs as $log): ?>
              <tr>
                <td><?= htmlspecialchars($log['log_date']) ?></td>
                <td><strong><?= htmlspecialchars($log['exercise_name']) ?></strong></td>
                <td><?= $log['sets'] ?></td>
                <td><?= $log['reps'] ?></td>
                <td><?= $log['weight_kg'] ?> kg</td>
                <td><?= $log['duration_minutes'] ?> min</td>
                <td style="color: var(--text-secondary)"><?= htmlspecialchars($log['notes'] ?? '') ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($workoutLogs)): ?>
              <tr><td colspan="7" class="text-center" style="color: var(--text-secondary); padding: 2rem;">No workout logs yet. Add your first entry above!</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Nutrition Tab -->
      <div id="tab-nutrition" class="tab-pane">
        <form id="nutritionForm" class="mb-4">
          <input type="hidden" name="request_id" value="<?= $requestId ?>">
          <div class="row g-2 align-items-end">
            <div class="col-md-2 col-6">
              <label class="fit-label">Date</label>
              <input type="date" name="log_date" class="form-control fit-input" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-2 col-6">
              <label class="fit-label">Meal Type</label>
              <select name="meal_type" class="form-select fit-input" required>
                <option value="breakfast">Breakfast</option>
                <option value="lunch">Lunch</option>
                <option value="dinner">Dinner</option>
                <option value="snack">Snack</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="fit-label">Food Item</label>
              <input type="text" name="food_item" class="form-control fit-input" placeholder="e.g. Grilled Chicken" required>
            </div>
            <div class="col-md-1 col-3">
              <label class="fit-label">Cal</label>
              <input type="number" name="calories" class="form-control fit-input" min="0" value="0">
            </div>
            <div class="col-md-1 col-3">
              <label class="fit-label">Prot (g)</label>
              <input type="number" name="protein_g" class="form-control fit-input" min="0" step="0.1" value="0">
            </div>
            <div class="col-md-1 col-3">
              <label class="fit-label">Carbs (g)</label>
              <input type="number" name="carbs_g" class="form-control fit-input" min="0" step="0.1" value="0">
            </div>
            <div class="col-md-1 col-3">
              <label class="fit-label">Fats (g)</label>
              <input type="number" name="fats_g" class="form-control fit-input" min="0" step="0.1" value="0">
            </div>
            <div class="col-md-1">
              <button type="submit" class="btn-fit btn w-100" style="padding: 10px 5px;">
                <i class="bi bi-plus-lg"></i>
              </button>
            </div>
          </div>
        </form>
        <div class="table-responsive">
          <table class="table log-table" id="nutritionTable">
            <thead><tr>
              <th>Date</th><th>Meal</th><th>Food Item</th><th>Calories</th><th>Protein</th><th>Carbs</th><th>Fats</th>
            </tr></thead>
            <tbody>
              <?php foreach ($nutritionLogs as $log): ?>
              <tr>
                <td><?= htmlspecialchars($log['log_date']) ?></td>
                <td><span class="meal-badge badge-<?= $log['meal_type'] ?>"><?= ucfirst($log['meal_type']) ?></span></td>
                <td><strong><?= htmlspecialchars($log['food_item']) ?></strong></td>
                <td><?= $log['calories'] ?></td>
                <td><?= $log['protein_g'] ?>g</td>
                <td><?= $log['carbs_g'] ?>g</td>
                <td><?= $log['fats_g'] ?>g</td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($nutritionLogs)): ?>
              <tr><td colspan="7" class="text-center" style="color: var(--text-secondary); padding: 2rem;">No nutrition logs yet. Track your first meal!</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Toast Notification -->
<div class="toast-notif" id="toastNotif">
  <div class="d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill" style="color: var(--accent-teal); font-size:1.1rem;"></i>
    <span id="toastMsg" style="color: var(--text-primary); font-size:.88rem;"></span>
  </div>
</div>

<script>
function switchTab(name, btn) {
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  btn.classList.add('active');
}

function showToast(msg, success = true) {
  const toast = document.getElementById('toastNotif');
  document.getElementById('toastMsg').textContent = msg;
  toast.style.borderColor = success ? 'var(--accent-teal)' : '#ef4444';
  toast.style.display = 'block';
  setTimeout(() => toast.style.display = 'none', 3500);
}

function addWorkoutRow(data) {
  const tbody = document.querySelector('#workoutTable tbody');
  const emptyRow = tbody.querySelector('td[colspan="7"]');
  if (emptyRow) emptyRow.closest('tr').remove();
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${data.log_date}</td>
    <td><strong>${data.exercise_name}</strong></td>
    <td>${data.sets}</td><td>${data.reps}</td>
    <td>${data.weight_kg} kg</td><td>${data.duration_minutes} min</td>
    <td style="color: var(--text-secondary)">${data.notes || ''}</td>`;
  tbody.insertBefore(tr, tbody.firstChild);
}

function addNutritionRow(data) {
  const tbody = document.querySelector('#nutritionTable tbody');
  const emptyRow = tbody.querySelector('td[colspan="7"]');
  if (emptyRow) emptyRow.closest('tr').remove();
  const badges = {breakfast:'badge-breakfast',lunch:'badge-lunch',dinner:'badge-dinner',snack:'badge-snack'};
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${data.log_date}</td>
    <td><span class="meal-badge ${badges[data.meal_type]||''}">${data.meal_type.charAt(0).toUpperCase()+data.meal_type.slice(1)}</span></td>
    <td><strong>${data.food_item}</strong></td>
    <td>${data.calories}</td><td>${data.protein_g}g</td><td>${data.carbs_g}g</td><td>${data.fats_g}g</td>`;
  tbody.insertBefore(tr, tbody.firstChild);
}

document.getElementById('workoutForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const fd = new FormData(this);
  const captured = Object.fromEntries(fd);
  try {
    const r = await fetch('index.php?r=fitness/addWorkout', {method:'POST', body: fd});
    const d = await r.json();
    if (d.success) {
      addWorkoutRow(captured);
      this.reset();
      this.querySelector('[name="log_date"]').value = new Date().toISOString().slice(0,10);
      this.querySelector('[name="request_id"]').value = '<?= $requestId ?>';
      showToast('Workout logged successfully!');
    } else showToast(d.error || 'Failed to log workout', false);
  } catch(e) { showToast('Network error', false); }
});

document.getElementById('nutritionForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const fd = new FormData(this);
  const captured = Object.fromEntries(fd);
  try {
    const r = await fetch('index.php?r=fitness/addNutrition', {method:'POST', body: fd});
    const d = await r.json();
    if (d.success) {
      addNutritionRow(captured);
      this.reset();
      this.querySelector('[name="log_date"]').value = new Date().toISOString().slice(0,10);
      this.querySelector('[name="request_id"]').value = '<?= $requestId ?>';
      showToast('Nutrition logged successfully!');
    } else showToast(d.error || 'Failed to log nutrition', false);
  } catch(e) { showToast('Network error', false); }
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
