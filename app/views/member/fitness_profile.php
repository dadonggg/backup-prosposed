<?php
declare(strict_types=1);
$pageTitle = 'Client Profile — Step 2';
require __DIR__ . '/../partials/header.php';
$displayName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
if ($displayName === '') $displayName = $user['fullname'] ?? 'Member';
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

.fit-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
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
.fit-card-header {
  background: var(--bg-section-header);
  border-left: 4px solid var(--accent-teal);
  border-bottom: 1px solid var(--border-card);
  padding: 12px 18px;
}
.fit-card-header .fit-heading {
  color: var(--accent-teal) !important;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin: 0;
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

/* ── Goal Pills ── */
.fit-check {
  display: none;
}
.goal-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #f1f5f9;
  border: 1px solid var(--border-card);
  color: var(--text-secondary);
  border-radius: 24px;
  padding: 8px 18px;
  cursor: pointer;
  transition: all .2s;
  font-size: .85rem;
  font-weight: 500;
  user-select: none;
}
.goal-chip:hover {
  border-color: var(--accent-teal);
  color: var(--accent-teal);
}
.fit-check:checked + .goal-chip {
  background: #f0fdf9;
  border: 1.5px solid var(--accent-teal);
  color: var(--accent-teal);
  font-weight: 600;
}

/* ── Activity Level Cards ── */
.activity-btn {
  display: none;
}
.activity-label {
  display: block;
  background: #f8fafc;
  border: 1px solid var(--border-card);
  border-radius: 10px;
  padding: 12px 10px;
  cursor: pointer;
  text-align: center;
  transition: all .2s;
  color: var(--text-secondary);
}
.activity-label:hover {
  border-color: var(--accent-teal);
  color: var(--text-primary);
}
.activity-btn:checked + label {
  background: #f0fdf9;
  border: 1.5px solid var(--accent-teal);
  color: var(--accent-teal);
}
.activity-btn:checked + label i {
  color: var(--accent-teal);
}
.activity-label i {
  color: var(--text-secondary);
  transition: color .2s;
}

/* ── Buttons ── */
.btn-fit {
  background: var(--accent-green-btn);
  color: #fff !important;
  border: none;
  border-radius: 8px;
  padding: 13px 20px;
  font-weight: 600;
  font-size: 14px;
  transition: all .2s;
  box-shadow: var(--shadow-sm);
}
.btn-fit:hover {
  background: var(--accent-green-hover);
  transform: translateY(-1px);
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

/* ── Trainer Box ── */
.trainer-assigned-box {
  background: var(--bg-trainer-box);
  border: 1px solid var(--border-trainer);
  border-radius: 12px;
  padding: 1rem 1.5rem;
}
</style>

<div class="fit-page">
  <!-- Step Progress Bar -->
  <div class="step-bar">
    <div class="step-item done">
      <div class="step-circle"><i class="bi bi-check-lg"></i></div>
      <div class="step-label">Service Request</div>
    </div>
    <div class="step-item active">
      <div class="step-circle">2</div>
      <div class="step-label">Client Profile</div>
    </div>
    <div class="step-item">
      <div class="step-circle">3</div>
      <div class="step-label">Fitness Plan</div>
    </div>
    <div class="step-item">
      <div class="step-circle">4</div>
      <div class="step-label">Progress</div>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-start mb-4">
    <div>
      <h1 class="fw-extrabold mb-1" style="color: var(--text-primary); font-size: 26px; font-weight: 800;">
        <i class="bi bi-person-circle me-2" style="color: var(--accent-teal)"></i>Client Profile
      </h1>
      <p style="color: var(--text-secondary); font-size: 14px;">Tell your trainer about yourself — this unlocks your personalized plan.</p>
    </div>
    <a href="index.php?r=fitness/status" class="btn-back btn text-decoration-none">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger mb-4" style="border-radius: 12px;">
    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <!-- Assigned Trainer Info -->
  <?php if (!empty($request['trainer_name'])): ?>
  <div class="trainer-assigned-box mb-4">
    <div class="d-flex align-items-center gap-3">
      <div style="width:48px;height:48px;background:rgba(13,148,136,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid var(--accent-teal);">
        <i class="bi bi-person-badge" style="color: var(--accent-teal);font-size:1.2rem;"></i>
      </div>
      <div>
        <p class="mb-0" style="color: var(--text-secondary); font-size:.78rem; text-transform:uppercase; letter-spacing:.08em; font-weight: 600;">Your Assigned Trainer</p>
        <h5 class="mb-0 fw-bold" style="color: var(--accent-blue); font-size: 1.1rem;"><?= htmlspecialchars($request['trainer_name']) ?></h5>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <form method="POST" action="index.php?r=fitness/profile&request_id=<?= $request['id'] ?>">
    <!-- Personal Stats -->
    <div class="fit-card">
      <div class="fit-card-header">
        <h5 class="fit-heading">
          <i class="bi bi-body-text me-2"></i>PERSONAL STATS
        </h5>
      </div>
      <div class="p-4">
        <div class="row g-3">
          <div class="col-md-3 col-6">
            <label class="fit-label">Age</label>
            <input type="number" name="age" class="form-control fit-input" min="10" max="100"
                   value="<?= htmlspecialchars((string)($profile['age'] ?? $user['age'] ?? '')) ?>" required>
          </div>
          <div class="col-md-3 col-6">
            <label class="fit-label">Gender</label>
            <select name="gender" class="form-select fit-input" required>
              <option value="">Select</option>
              <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other','prefer_not_to_say'=>'Prefer not to say'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($profile['gender'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3 col-6">
            <label class="fit-label">Height (cm)</label>
            <input type="number" name="height_cm" class="form-control fit-input" min="50" max="250" step="0.1"
                   value="<?= htmlspecialchars((string)($profile['height_cm'] ?? $user['height_cm'] ?? '')) ?>" placeholder="e.g. 170" required>
          </div>
          <div class="col-md-3 col-6">
            <label class="fit-label">Weight (kg)</label>
            <input type="number" name="weight_kg" class="form-control fit-input" min="20" max="300" step="0.1"
                   value="<?= htmlspecialchars((string)($profile['weight_kg'] ?? $user['weight_kg'] ?? '')) ?>" placeholder="e.g. 65" required>
          </div>
        </div>
      </div>
    </div>

    <!-- Fitness Goals -->
    <div class="fit-card">
      <div class="fit-card-header">
        <h5 class="fit-heading">
          <i class="bi bi-trophy me-2"></i>FITNESS GOALS
        </h5>
      </div>
      <div class="p-4">
        <p style="color: var(--text-secondary); font-size:.85rem; margin-bottom:1rem;">Select all that apply:</p>
        <?php
        $savedGoals = explode(',', $profile['fitness_goals'] ?? '');
        $goals = [
          'weight_loss' => ['icon'=>'bi-graph-down-arrow','label'=>'Weight Loss'],
          'muscle_gain' => ['icon'=>'bi-lightning-charge','label'=>'Muscle Gain'],
          'endurance'   => ['icon'=>'bi-heart-pulse','label'=>'Endurance'],
          'flexibility' => ['icon'=>'bi-person-arms-up','label'=>'Flexibility'],
          'general_wellness' => ['icon'=>'bi-stars','label'=>'General Wellness'],
        ];
        foreach ($goals as $val => $g): ?>
        <input type="checkbox" class="fit-check" id="goal_<?= $val ?>" name="goal_<?= str_replace('_loss','_loss',explode('_',$val)[0] === 'general' ? 'wellness' : $val) ?>"
               id_extra="<?= $val ?>" value="1" <?= in_array($val, $savedGoals) ? 'checked' : '' ?>>
        <label class="goal-chip me-2 mb-2" for="goal_<?= $val ?>">
          <i class="bi <?= $g['icon'] ?> me-1"></i><?= $g['label'] ?>
        </label>
        <?php endforeach; ?>
        <!-- Hidden checkboxes with proper names -->
        <div style="display:none;">
          <?php foreach ($goals as $val => $g):
            if ($val === 'weight_loss') { $name = 'goal_weight_loss'; }
            elseif ($val === 'muscle_gain') { $name = 'goal_muscle_gain'; }
            elseif ($val === 'endurance') { $name = 'goal_endurance'; }
            elseif ($val === 'flexibility') { $name = 'goal_flexibility'; }
            else { $name = 'goal_wellness'; }
          ?>
          <input type="checkbox" name="<?= $name ?>" id="hid_<?= $val ?>" value="1" <?= in_array($val, $savedGoals) ? 'checked' : '' ?>>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Activity Level -->
    <div class="fit-card">
      <div class="fit-card-header">
        <h5 class="fit-heading">
          <i class="bi bi-activity me-2"></i>CURRENT ACTIVITY LEVEL
        </h5>
      </div>
      <div class="p-4">
        <div class="row g-2">
          <?php
          $levels = [
            'sedentary'        => ['icon'=>'bi-person','label'=>'Sedentary','desc'=>'Little to no exercise'],
            'lightly_active'   => ['icon'=>'bi-person-walking','label'=>'Lightly Active','desc'=>'1-3 days/week'],
            'moderately_active'=> ['icon'=>'bi-bicycle','label'=>'Moderately Active','desc'=>'3-5 days/week'],
            'very_active'      => ['icon'=>'bi-lightning-fill','label'=>'Very Active','desc'=>'6-7 days/week'],
          ];
          foreach ($levels as $val => $l): ?>
          <div class="col-md-3 col-6">
            <input type="radio" class="activity-btn" name="activity_level" id="act_<?= $val ?>" value="<?= $val ?>"
                   <?= ($profile['activity_level'] ?? '') === $val ? 'checked' : '' ?> required>
            <label class="activity-label" for="act_<?= $val ?>">
              <i class="bi <?= $l['icon'] ?> d-block mb-1" style="font-size:1.4rem;"></i>
              <strong style="font-size:.85rem; font-weight: 600;"><?= $l['label'] ?></strong>
              <br><span style="font-size:.72rem; color: var(--text-secondary);"><?= $l['desc'] ?></span>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Medical & Dietary -->
    <div class="fit-card">
      <div class="fit-card-header">
        <h5 class="fit-heading">
          <i class="bi bi-clipboard2-pulse me-2"></i>HEALTH & DIET
        </h5>
      </div>
      <div class="p-4">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="fit-label">Medical Conditions / Injuries</label>
            <textarea name="medical_conditions" class="form-control fit-input" rows="4"
                      placeholder="List any injuries, chronic conditions, medications, or physical limitations..."><?= htmlspecialchars($profile['medical_conditions'] ?? '') ?></textarea>
            <small style="color: var(--text-secondary); font-size:.72rem; margin-top: 4px; display: block;">Leave blank if none.</small>
          </div>
          <div class="col-md-6">
            <label class="fit-label">Dietary Preferences</label>
            <textarea name="dietary_preferences" class="form-control fit-input" rows="4"
                      placeholder="e.g., vegetarian, vegan, gluten-free, allergies, foods to avoid..."><?= htmlspecialchars($profile['dietary_preferences'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Submit -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <a href="index.php?r=fitness/status" class="btn-back btn text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i>Back to Status
      </a>
      <button type="submit" class="btn-fit btn">
        <i class="bi bi-send me-2"></i><?= $profile ? 'Update Profile' : 'Submit Profile' ?>
      </button>
    </div>
  </form>
</div>

<script>
// Sync visible chips to hidden checkboxes
document.addEventListener('DOMContentLoaded', function() {
  const goalMap = {
    'weight_loss': 'goal_weight_loss',
    'muscle_gain': 'goal_muscle_gain',
    'endurance': 'goal_endurance',
    'flexibility': 'goal_flexibility',
    'general_wellness': 'goal_wellness'
  };
  Object.keys(goalMap).forEach(function(val) {
    const visCheck = document.getElementById('goal_' + val);
    const hidCheck = document.getElementById('hid_' + val);
    if (visCheck && hidCheck) {
      visCheck.checked = hidCheck.checked;
      visCheck.addEventListener('change', function() {
        hidCheck.checked = visCheck.checked;
      });
    }
  });
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
