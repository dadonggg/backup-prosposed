<?php
declare(strict_types=1);
$pageTitle = 'Request Fitness Training';
require __DIR__ . '/../partials/header.php';

$displayName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
if ($displayName === '') $displayName = $user['fullname'] ?? 'Member';

/* ── Assigned trainer name ───────────────────────────────────── */
$assignedTrainerName = $trainerName ?? null;

/* Pre-filled address values (if re-rendering after error) */
$preFilledStreet    = $_POST['street']            ?? '';
$preFilledProvince  = $_POST['province']           ?? '';
$preFilledProvinceC = $_POST['province_code']      ?? '';
$preFilledCity      = $_POST['city_municipality']  ?? '';
$preFilledCityC     = $_POST['city_code']          ?? '';
$preFilledBarangay  = $_POST['barangay']           ?? '';
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
  /* ── Green palette (matches My Fitness Program) ── */
  --bg-page:           #f0f2f0;
  --bg-card:           #ffffff;
  --bg-section-header: #ecfdf5;
  --bg-input:          #ffffff;
  --bg-trainer-box:    #f0fdf4;
  --bg-tag:            #f1f5f9;

  --border-card:       #e2e8f0;
  --border-input:      #cbd5e1;
  --border-green:      #16a34a;
  --border-trainer:    #bbf7d0;

  --accent-green:      #16a34a;
  --accent-green-mid:  #22c55e;
  --accent-green-dark: #15803d;
  --accent-green-deep: #065f46;

  --text-primary:      #1e293b;
  --text-secondary:    #64748b;
  --text-white:        #ffffff;

  --shadow-card: 0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.05);
  --shadow-sm:   0 1px 2px rgba(0,0,0,0.06);
}

body {
  background: var(--bg-page) !important;
  font-family: 'Inter', system-ui, sans-serif !important;
  color: var(--text-primary) !important;
}

/* ── Wrapper ── */
.fr-wrap {
  max-width: 780px;
  margin: 0 auto;
  padding: 1.5rem 1rem 4rem;
}

/* ══════════════════════════════════════════
   HERO BANNER
══════════════════════════════════════════ */
.fr-hero {
  background: linear-gradient(135deg, #15803d 0%, #16a34a 55%, #22c55e 100%);
  border-radius: 14px;
  padding: 28px 28px 24px;
  margin-bottom: 1.25rem;
  position: relative;
  overflow: hidden;
}
.fr-hero::before,
.fr-hero::after {
  content: '';
  position: absolute;
  border-radius: 50%;
  background: rgba(255,255,255,0.08);
  pointer-events: none;
}
.fr-hero::before { width: 220px; height: 220px; top: -60px; right: -50px; }
.fr-hero::after  { width: 140px; height: 140px; bottom: -50px; right: 80px; }
.fr-hero-title {
  color: #fff;
  font-size: 24px;
  font-weight: 800;
  margin: 0 0 4px;
  line-height: 1.25;
}
.fr-hero-sub {
  color: rgba(255,255,255,0.88);
  font-size: 13.5px;
  margin: 0;
}
.fr-hero-back {
  position: absolute;
  top: 20px;
  right: 20px;
  background: rgba(255,255,255,0.18);
  border: 1px solid rgba(255,255,255,0.35);
  border-radius: 8px;
  padding: 7px 13px;
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  transition: background .2s;
}
.fr-hero-back:hover {
  background: rgba(255,255,255,0.28);
  color: #fff;
  text-decoration: none;
}

/* ══════════════════════════════════════════
   SECTION CARDS
══════════════════════════════════════════ */
.fr-card {
  background: var(--bg-card);
  border: 1px solid var(--border-card);
  border-radius: 12px;
  box-shadow: var(--shadow-card);
  margin-bottom: 1.25rem;
  overflow: hidden;
}
.fr-card-head {
  background: var(--bg-section-header);
  border-left: 4px solid var(--accent-green);
  border-bottom: 1px solid var(--border-card);
  padding: 12px 18px;
  display: flex;
  align-items: center;
  gap: .6rem;
}
.fr-card-head h2 {
  color: var(--accent-green-deep) !important;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin: 0;
}
.fr-card-body {
  padding: 1.5rem;
}

/* ══════════════════════════════════════════
   INPUTS & LABELS
══════════════════════════════════════════ */
.fr-label {
  color: var(--text-secondary);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  margin-bottom: 6px;
  display: block;
}
.fr-label .req { color: #ef4444; }

.fr-input {
  background: var(--bg-input) !important;
  border: 1px solid var(--border-input) !important;
  color: var(--text-primary) !important;
  border-radius: 8px !important;
  padding: 10px 14px !important;
  font-size: 14px !important;
  width: 100%;
  transition: border-color .2s, box-shadow .2s;
  outline: none;
  font-family: inherit;
}
.fr-input:focus {
  border-color: var(--accent-green) !important;
  box-shadow: 0 0 0 3px rgba(22,163,74,.14) !important;
}
.fr-input:disabled {
  background: #f1f5f9 !important;
  color: var(--text-secondary) !important;
  cursor: not-allowed;
}
select.fr-input { appearance: auto; cursor: pointer; }

.fr-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
@media(max-width:600px) {
  .fr-grid-2 { grid-template-columns: 1fr; }
}

/* ══════════════════════════════════════════
   TRAINING TYPE — PILL BUTTONS
══════════════════════════════════════════ */
.training-pills {
  display: flex;
  flex-wrap: wrap;
  gap: .6rem;
}
.tt-option { position: relative; }
.tt-option input[type=checkbox] {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}
.tt-pill {
  display: inline-flex;
  align-items: center;
  gap: .45rem;
  padding: 9px 18px;
  border: 1.5px solid var(--border-card);
  border-radius: 50px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  transition: all .2s;
  background: #f8fafc;
  color: var(--text-secondary);
  user-select: none;
  white-space: nowrap;
}
.tt-pill:hover {
  border-color: var(--accent-green);
  color: var(--accent-green);
  background: #f0fdf4;
}
.tt-option input:checked + .tt-pill {
  border: 2px solid var(--accent-green);
  background: #dcfce7;
  color: var(--accent-green-deep);
  font-weight: 700;
}

#others-text-wrap { display: none; margin-top: .8rem; }
#others-text-wrap.show { display: block; }

/* ══════════════════════════════════════════
   SESSION SUMMARY
══════════════════════════════════════════ */
.session-badge {
  display: flex;
  align-items: center;
  gap: .7rem;
  background: var(--bg-trainer-box);
  border: 1px solid var(--border-trainer);
  color: var(--text-primary);
  border-radius: 8px;
  padding: 12px 16px;
  font-weight: 600;
  font-size: 14px;
}
.session-badge .s-icon { font-size: 1.3rem; }
.session-helper { font-size: 12px; color: var(--text-secondary); margin-top: .4rem; }
.no-package-warn {
  background: #fff3cd;
  border: 1px solid #ffc107;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 13px;
  color: #856404;
}

/* Trainer info */
.trainer-info {
  display: flex;
  align-items: center;
  gap: .6rem;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
}
.trainer-info.assigned {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: var(--accent-green-deep);
}
.trainer-info.unassigned {
  background: var(--bg-tag);
  border: 1px solid var(--border-card);
  color: var(--text-secondary);
}

/* ══════════════════════════════════════════
   SCHEDULE — COMPACT DAY PILLS
══════════════════════════════════════════ */
.schedule-group-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.2px;
  color: var(--text-secondary);
  margin: .2rem 0 .5rem;
}
.day-pills-row {
  display: flex;
  flex-wrap: wrap;
  gap: .5rem;
  margin-bottom: .5rem;
}
.dp-option { position: relative; }
.dp-option input[type=checkbox] {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}
.dp-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 52px;
  padding: 8px 14px;
  border: 1.5px solid var(--border-card);
  border-radius: 50px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all .2s;
  background: #f8fafc;
  color: var(--text-secondary);
  user-select: none;
}
.dp-pill:hover {
  border-color: var(--accent-green);
  color: var(--accent-green);
  background: #f0fdf4;
}
.dp-option input:checked + .dp-pill {
  border: 2px solid var(--accent-green);
  background: #dcfce7;
  color: var(--accent-green-deep);
  font-weight: 700;
}

/* Time slot row (expands per checked day) */
.day-time-block {
  background: #f8fafc;
  border: 1px solid var(--border-card);
  border-radius: 10px;
  padding: .8rem 1rem;
  margin-top: .6rem;
  display: none;
}
.day-time-block.open { display: block; }
.day-time-block-title {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: .5rem;
}
.time-pills-row {
  display: flex;
  flex-wrap: wrap;
  gap: .45rem;
}
.tp-option { position: relative; }
.tp-option input[type=radio] {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}
.tp-pill {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  padding: 6px 13px;
  border: 1px solid var(--border-card);
  border-radius: 50px;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all .2s;
  background: #fff;
  color: var(--text-secondary);
}
.tp-pill:hover { border-color: var(--accent-green); }
.tp-option input:checked + .tp-pill {
  border: 1.5px solid var(--accent-green);
  background: #dcfce7;
  color: var(--accent-green-deep);
  font-weight: 600;
}

/* ══════════════════════════════════════════
   ERRORS & INFO
══════════════════════════════════════════ */
.fr-error-inline {
  font-size: 12px;
  color: #ef4444;
  margin-top: .3rem;
  display: none;
}
.fr-error-inline.show { display: block; }

.info-card {
  display: flex;
  gap: .8rem;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 10px;
  padding: 12px 16px;
  margin-bottom: 1.25rem;
  font-size: 13px;
  color: var(--accent-green-deep);
}

/* Address loading/error */
.addr-loading {
  font-size: 12px;
  color: var(--accent-green);
  margin-top: .3rem;
  display: none;
}
.addr-loading.show { display: flex; align-items: center; gap: .35rem; }
.addr-error {
  font-size: 12px;
  color: #dc2626;
  margin-top: .3rem;
  display: none;
}
.addr-error.show { display: block; }
.addr-retry {
  font-size: 12px;
  color: var(--accent-green);
  font-weight: 600;
  cursor: pointer;
  text-decoration: underline;
  background: none;
  border: none;
  padding: 0;
}

/* ══════════════════════════════════════════
   SUBMIT
══════════════════════════════════════════ */
.fr-submit {
  width: 100%;
  padding: 14px;
  background: var(--accent-green);
  color: #fff !important;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  font-family: inherit;
  transition: background .2s, transform .15s;
}
.fr-submit:hover  { background: var(--accent-green-dark); }
.fr-submit:active { transform: scale(.99); }

/* Address section sub-divider */
.addr-divider {
  border-top: 1px solid #e2e8f0;
  padding-top: 1.25rem;
  margin-top: 1.25rem;
}
.addr-heading {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: .4rem;
}
</style>

<div class="fr-wrap">

  <!-- ══ HERO BANNER ══ -->
  <div class="fr-hero">
    <h1 class="fr-hero-title">🏋️ Request Fitness Training</h1>
    <p class="fr-hero-sub">Tell us your goals and schedule — we'll match you with the right trainer.</p>
    <a href="index.php?r=member/dashboard" class="fr-hero-back">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="border-radius:8px;">
    <i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <?php if ($success): ?>
  <div class="alert alert-success d-flex align-items-center gap-2 mb-3" style="border-radius:8px;">
    <i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($success) ?>
  </div>
  <?php endif; ?>

  <div class="info-card">
    <i class="bi bi-info-circle-fill flex-shrink-0" style="font-size:1.1rem;margin-top:.05rem;"></i>
    <div>Submit this form → Admin assigns a trainer → Fill your fitness profile → Receive your personalised plan → Track your progress.</div>
  </div>

  <form method="POST" action="index.php?r=fitness/request" id="fitnessForm" novalidate>

    <!-- ══ SECTION 1: Training Details ══ -->
    <div class="fr-card">
      <div class="fr-card-head"><h2><i class="bi bi-lightning-charge me-1"></i>Training Details</h2></div>
      <div class="fr-card-body">

        <!-- Training Type -->
        <label class="fr-label">Training Type <span class="req">*</span></label>
        <p style="font-size:12px;color:var(--text-secondary);margin-bottom:.75rem;">Select at least one type of training you're interested in.</p>
        <div class="training-pills" id="trainingGrid">
          <?php
          $types = [
            'training_personal' => ['Personal Training', 'bi-person-arms-up'],
            'training_pilates'  => ['Pilates',           'bi-activity'],
            'training_yoga'     => ['Yoga',              'bi-peace'],
            'training_strength' => ['Strength',          'bi-lightning-charge'],
            'training_cardio'   => ['Cardio',            'bi-heart-pulse'],
            'training_others'   => ['Others',            'bi-three-dots'],
          ];
          foreach ($types as $name => [$label, $icon]):
          ?>
          <div class="tt-option">
            <input class="tt-cb" type="checkbox" name="<?= $name ?>" id="<?= $name ?>" value="1"
                   <?= $name === 'training_others' ? 'onchange="toggleOthers(this)"' : '' ?>>
            <label class="tt-pill" for="<?= $name ?>">
              <i class="bi <?= $icon ?>"></i><?= $label ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
        <div id="trainingTypeError" class="fr-error-inline">Please select at least one training type.</div>

        <!-- Others text -->
        <div id="others-text-wrap">
          <input type="text" name="training_type_custom" id="training_type_custom"
                 class="fr-input" placeholder="Please specify your training type" style="margin-top:.5rem;">
          <div id="othersTextError" class="fr-error-inline">Please specify your training type.</div>
        </div>

      </div>
    </div>

    <!-- ══ SECTION 2: Session Summary ══ -->
    <div class="fr-card">
      <div class="fr-card-head"><h2><i class="bi bi-calendar2-check me-1"></i>Session Summary</h2></div>
      <div class="fr-card-body">
        <?php if ($autoSessions !== null): ?>
          <input type="hidden" name="session_preference" value="<?= $autoSessions ?>">
          <div class="session-badge">
            <span class="s-icon">🏋️</span>
            <div>
              <div><?= $autoSessions ?> Session<?= $autoSessions > 1 ? 's' : '' ?></div>
              <div style="font-size:12px;font-weight:400;opacity:.8;">based on your registered package</div>
            </div>
          </div>
          <p class="session-helper">Based on your registered package (<?= htmlspecialchars($packageLabel) ?>). This field is set automatically and cannot be changed.</p>
        <?php else: ?>
          <div class="no-package-warn">
            <i class="bi bi-exclamation-triangle me-1"></i>
            No active package found. Please <a href="index.php?r=membership/apply" style="color:inherit;font-weight:700;">complete registration</a> first to auto-fill your sessions.
          </div>
          <input type="hidden" name="session_preference" value="1">
        <?php endif; ?>

        <!-- Trainer display -->
        <div style="margin-top:1rem;">
          <label class="fr-label">Assigned Trainer</label>
          <?php if (!empty($assignedTrainerName)): ?>
          <div class="trainer-info assigned">
            <i class="bi bi-person-check-fill me-2"></i>
            <span><?= htmlspecialchars($assignedTrainerName) ?></span>
            <span style="font-size:12px;margin-left:.3rem;opacity:.7;">(Assigned)</span>
          </div>
          <?php else: ?>
          <div class="trainer-info unassigned">
            <i class="bi bi-hourglass-split me-2"></i>
            Trainer: To be assigned by administrator
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ══ SECTION 3: Schedule Preference ══ -->
    <div class="fr-card">
      <div class="fr-card-head"><h2><i class="bi bi-calendar-week me-1"></i>Schedule Preference</h2></div>
      <div class="fr-card-body">
        <p style="font-size:12px;color:var(--text-secondary);margin-bottom:1rem;">
          Select the days you prefer, then choose a time slot for each selected day.
        </p>

        <!-- Weekday pills -->
        <div class="schedule-group-label">Weekdays</div>
        <div class="day-pills-row" id="weekdayPills">
          <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day): ?>
          <div class="dp-option">
            <input type="checkbox" id="day-<?= $day ?>" name="day[<?= $day ?>]" value="1"
                   onchange="onDayToggle('<?= $day ?>')">
            <label class="dp-pill" for="day-<?= $day ?>"><?= substr($day, 0, 3) ?></label>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Weekday time selectors -->
        <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day): ?>
        <div class="day-time-block" id="dtb-<?= $day ?>">
          <div class="day-time-block-title"><?= $day ?> — choose a time slot</div>
          <div class="time-pills-row">
            <?php foreach (['Morning' => '🌅 6:00 AM – 11:59 AM', 'Afternoon' => '☀️ 12:00 PM – 5:59 PM', 'Evening' => '🌙 6:00 PM – 9:00 PM'] as $t => $tLabel): ?>
            <div class="tp-option">
              <input type="radio" name="time[<?= $day ?>]" id="t-<?= $day ?>-<?= $t ?>" value="<?= $t ?>">
              <label class="tp-pill" for="t-<?= $day ?>-<?= $t ?>"><?= $tLabel ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>

        <!-- Weekend pills -->
        <div class="schedule-group-label" style="margin-top:1rem;">Weekend</div>
        <div class="day-pills-row" id="weekendPills">
          <?php foreach (['Saturday','Sunday'] as $day): ?>
          <div class="dp-option">
            <input type="checkbox" id="day-<?= $day ?>" name="day[<?= $day ?>]" value="1"
                   onchange="onDayToggle('<?= $day ?>')">
            <label class="dp-pill" for="day-<?= $day ?>"><?= substr($day, 0, 3) ?></label>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Weekend time selectors -->
        <?php foreach (['Saturday','Sunday'] as $day): ?>
        <div class="day-time-block" id="dtb-<?= $day ?>">
          <div class="day-time-block-title"><?= $day ?> — choose a time slot</div>
          <div class="time-pills-row">
            <?php foreach (['Morning' => '🌅 6:00 AM – 11:59 AM', 'Afternoon' => '☀️ 12:00 PM – 5:59 PM', 'Evening' => '🌙 6:00 PM – 9:00 PM'] as $t => $tLabel): ?>
            <div class="tp-option">
              <input type="radio" name="time[<?= $day ?>]" id="t-<?= $day ?>-<?= $t ?>" value="<?= $t ?>">
              <label class="tp-pill" for="t-<?= $day ?>-<?= $t ?>"><?= $tLabel ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>

        <div id="scheduleError" class="fr-error-inline">Please select at least one day with a time slot.</div>
        <div id="timeError"    class="fr-error-inline">Each selected day must have a time slot chosen.</div>
      </div>
    </div>

    <!-- ══ SECTION 4: Personal Information ══ -->
    <div class="fr-card">
      <div class="fr-card-head"><h2><i class="bi bi-person me-1"></i>Personal Information</h2></div>
      <div class="fr-card-body">
        <div class="fr-grid-2" style="margin-bottom:1rem;">
          <div>
            <label class="fr-label" for="full_name">Full Name <span class="req">*</span></label>
            <input type="text" id="full_name" name="full_name" class="fr-input"
                   value="<?= htmlspecialchars($displayName) ?>" required>
          </div>
          <div>
            <label class="fr-label" for="email">Email <span class="req">*</span></label>
            <input type="email" id="email" name="email" class="fr-input"
                   value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
          </div>
          <div>
            <label class="fr-label" for="phone">Phone Number <span class="req">*</span></label>
            <input type="tel" id="phone" name="phone" class="fr-input"
                   placeholder="+63 912 345 6789" required>
          </div>
        </div>

        <!-- ── ADDRESS (PSGC cascading dropdowns) ── -->
        <div class="addr-divider">
          <div class="addr-heading">
            <i class="bi bi-geo-alt" style="color:var(--accent-green);"></i>Address
          </div>

          <div class="fr-grid-2">
            <!-- Row 1: Street | Province -->
            <div>
              <label class="fr-label" for="street">Street Address / House No. <span class="req">*</span></label>
              <input type="text" id="street" name="street" class="fr-input"
                     placeholder="e.g. 123 Rizal St."
                     value="<?= htmlspecialchars($preFilledStreet) ?>" required>
            </div>
            <div>
              <label class="fr-label" for="addr_province">Province <span class="req">*</span></label>
              <select id="addr_province" name="province" class="fr-input" required>
                <option value="" disabled selected>Loading provinces…</option>
              </select>
              <input type="hidden" name="province_code" id="province_code" value="<?= htmlspecialchars($preFilledProvinceC) ?>">
              <div class="addr-loading" id="prov-loading">
                <span class="spinner-border spinner-border-sm" style="width:.75rem;height:.75rem;border-width:2px;"></span>
                Loading…
              </div>
              <div class="addr-error" id="prov-error">
                Couldn't load provinces — <button type="button" class="addr-retry" onclick="initProvinces(true)">try again</button>
              </div>
            </div>

            <!-- Row 2: City | Barangay -->
            <div>
              <label class="fr-label" for="addr_city">City / Municipality <span class="req">*</span></label>
              <select id="addr_city" name="city_municipality" class="fr-input" required disabled>
                <option value="" disabled selected>Select Province first</option>
              </select>
              <input type="hidden" name="city_code" id="city_code" value="<?= htmlspecialchars($preFilledCityC) ?>">
              <div class="addr-loading" id="city-loading">
                <span class="spinner-border spinner-border-sm" style="width:.75rem;height:.75rem;border-width:2px;"></span>
                Loading…
              </div>
              <div class="addr-error" id="city-error">
                Couldn't load cities — <button type="button" class="addr-retry" onclick="reloadCities()">try again</button>
              </div>
            </div>
            <div>
              <label class="fr-label" for="addr_barangay">Barangay <span class="req">*</span></label>
              <select id="addr_barangay" name="barangay" class="fr-input" required disabled>
                <option value="" disabled selected>Select City first</option>
              </select>
              <div class="addr-loading" id="brgy-loading">
                <span class="spinner-border spinner-border-sm" style="width:.75rem;height:.75rem;border-width:2px;"></span>
                Loading…
              </div>
              <div class="addr-error" id="brgy-error">
                Couldn't load barangays — <button type="button" class="addr-retry" onclick="reloadBarangays()">try again</button>
              </div>
            </div>
          </div>
        </div><!-- /addr-divider -->

        <!-- Special requests -->
        <div style="margin-top:1.25rem;">
          <label class="fr-label" for="specific_trainer_request">Special Requests <span style="font-weight:400;text-transform:none;letter-spacing:0;">(Optional)</span></label>
          <textarea id="specific_trainer_request" name="specific_trainer_request"
                    class="fr-input" rows="2"
                    placeholder="Any preferences or special requirements for your trainer…"></textarea>
        </div>
      </div>
    </div>

    <!-- ══ Submit ══ -->
    <div class="fr-card">
      <div class="fr-card-body">
        <p style="font-size:12px;color:var(--text-secondary);margin-bottom:1rem;text-align:center;">
          <i class="bi bi-shield-check me-1" style="color:var(--accent-green);"></i>Your information is reviewed by our team.
          You'll be notified once a trainer is assigned.
        </p>
        <button type="submit" class="fr-submit" id="submitBtn">
          <i class="bi bi-send me-2"></i>Submit Training Request
        </button>
      </div>
    </div>

  </form>
</div><!-- /fr-wrap -->

<script>
/* ────────────────────────────────────────────────
   PRE-FILL VALUES (from PHP for repopulation)
──────────────────────────────────────────────── */
const PRE_PROVINCE  = <?= json_encode($preFilledProvince) ?>;
const PRE_PROV_CODE = <?= json_encode($preFilledProvinceC) ?>;
const PRE_CITY      = <?= json_encode($preFilledCity) ?>;
const PRE_CITY_CODE = <?= json_encode($preFilledCityC) ?>;
const PRE_BARANGAY  = <?= json_encode($preFilledBarangay) ?>;

/* ────────────────────────────────────────────────
   TRAINING TYPE — toggle "Others" text input
──────────────────────────────────────────────── */
function toggleOthers(cb) {
  const wrap = document.getElementById('others-text-wrap');
  wrap.classList.toggle('show', cb.checked);
  if (!cb.checked) {
    document.getElementById('training_type_custom').value = '';
    document.getElementById('othersTextError').classList.remove('show');
  }
}

/* ────────────────────────────────────────────────
   SCHEDULE — compact day pill toggle
──────────────────────────────────────────────── */
function onDayToggle(day) {
  const cb    = document.getElementById('day-' + day);
  const block = document.getElementById('dtb-' + day);
  if (cb.checked) {
    block.classList.add('open');
  } else {
    block.classList.remove('open');
    document.querySelectorAll('input[name="time[' + day + ']"]').forEach(r => r.checked = false);
  }
}

/* ────────────────────────────────────────────────
   PSGC ADDRESS DROPDOWNS
──────────────────────────────────────────────── */
const PSGC_BASE = 'https://psgc.gitlab.io/api';

const selProv = document.getElementById('addr_province');
const selCity = document.getElementById('addr_city');
const selBrgy = document.getElementById('addr_barangay');
const inpProvCode = document.getElementById('province_code');
const inpCityCode = document.getElementById('city_code');

function setLoading(which, on) {
  document.getElementById(which + '-loading').classList.toggle('show', on);
  document.getElementById(which + '-error').classList.remove('show');
}
function setError(which, on) {
  document.getElementById(which + '-error').classList.toggle('show', on);
  document.getElementById(which + '-loading').classList.remove('show');
}

/* ── Provinces ── */
async function initProvinces(retry = false) {
  setLoading('prov', true);
  selProv.innerHTML = '<option value="" disabled selected>Loading…</option>';
  selProv.disabled = true;
  try {
    const [provinces, districts] = await Promise.all([
      fetch(PSGC_BASE + '/provinces/').then(r => { if (!r.ok) throw new Error(); return r.json(); }),
      fetch(PSGC_BASE + '/districts/').then(r => { if (!r.ok) throw new Error(); return r.json(); })
    ]);

    const combined = [
      ...provinces.map(p => ({ ...p, _type: 'provinces' })),
      ...districts.map(d => ({ ...d, _type: 'districts', name: 'Metro Manila – ' + d.name }))
    ].sort((a, b) => a.name.localeCompare(b.name));

    selProv.innerHTML = '<option value="" disabled selected>Select Province</option>';
    combined.forEach(item => {
      const opt = document.createElement('option');
      opt.value = item.name;
      opt.textContent = item.name;
      opt.dataset.code = item.code;
      opt.dataset.type = item._type;
      if (PRE_PROVINCE && PRE_PROVINCE === item.name) opt.selected = true;
      selProv.appendChild(opt);
    });

    selProv.disabled = false;
    setLoading('prov', false);

    if (selProv.value) await loadCities(true);
  } catch (e) {
    setLoading('prov', false);
    setError('prov', true);
    selProv.innerHTML = '<option value="" disabled selected>Failed to load</option>';
    selProv.disabled = true;
  }
}

/* ── Cities ── */
let _lastProvinceCode = null;
let _lastProvinceType = null;

async function loadCities(isInitial = false) {
  const opt = selProv.options[selProv.selectedIndex];
  if (!opt || !opt.value) return;

  const code = opt.dataset.code;
  const type = opt.dataset.type;
  _lastProvinceCode = code;
  _lastProvinceType = type;

  // Store province code
  inpProvCode.value = code;

  selCity.innerHTML = '<option value="" disabled selected>Loading…</option>';
  selCity.disabled  = true;
  selBrgy.innerHTML = '<option value="" disabled selected>Select City first</option>';
  selBrgy.disabled  = true;
  inpCityCode.value = '';
  setLoading('city', true);

  try {
    const cities = await fetch(`${PSGC_BASE}/${type}/${code}/cities-municipalities/`)
      .then(r => { if (!r.ok) throw new Error(); return r.json(); });

    cities.sort((a, b) => a.name.localeCompare(b.name));

    selCity.innerHTML = '<option value="" disabled selected>Select City / Municipality</option>';
    cities.forEach(item => {
      const o = document.createElement('option');
      o.value = item.name;
      o.textContent = item.name;
      o.dataset.code = item.code;
      if (isInitial && PRE_CITY && PRE_CITY === item.name) o.selected = true;
      selCity.appendChild(o);
    });

    selCity.disabled = false;
    setLoading('city', false);

    if (selCity.value && isInitial) await loadBarangays(true);
  } catch (e) {
    setLoading('city', false);
    setError('city', true);
    selCity.innerHTML = '<option value="" disabled selected>Failed to load</option>';
  }
}

async function reloadCities() {
  if (_lastProvinceCode) await loadCities(false);
}

/* ── Barangays ── */
let _lastCityCode = null;

async function loadBarangays(isInitial = false) {
  const opt = selCity.options[selCity.selectedIndex];
  if (!opt || !opt.value) return;

  const code = opt.dataset.code;
  _lastCityCode = code;
  inpCityCode.value = code;

  selBrgy.innerHTML = '<option value="" disabled selected>Loading…</option>';
  selBrgy.disabled  = true;
  setLoading('brgy', true);

  try {
    const barangays = await fetch(`${PSGC_BASE}/cities-municipalities/${code}/barangays/`)
      .then(r => { if (!r.ok) throw new Error(); return r.json(); });

    barangays.sort((a, b) => a.name.localeCompare(b.name));

    selBrgy.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
    barangays.forEach(item => {
      const o = document.createElement('option');
      o.value = item.name;
      o.textContent = item.name;
      if (isInitial && PRE_BARANGAY && PRE_BARANGAY === item.name) o.selected = true;
      selBrgy.appendChild(o);
    });

    selBrgy.disabled = false;
    setLoading('brgy', false);
  } catch (e) {
    setLoading('brgy', false);
    setError('brgy', true);
    selBrgy.innerHTML = '<option value="" disabled selected>Failed to load</option>';
  }
}

async function reloadBarangays() {
  if (_lastCityCode) await loadBarangays(false);
}

/* ── Wire events ── */
selProv.addEventListener('change', () => loadCities(false));
selCity.addEventListener('change', () => loadBarangays(false));

/* ── Boot ── */
document.addEventListener('DOMContentLoaded', () => initProvinces());

/* ────────────────────────────────────────────────
   FORM VALIDATION
──────────────────────────────────────────────── */
document.getElementById('fitnessForm').addEventListener('submit', function(e) {
  let valid = true;

  // 1) Training type
  const ttChecked = [...document.querySelectorAll('.tt-cb:checked')];
  const ttErr = document.getElementById('trainingTypeError');
  if (ttChecked.length === 0) { ttErr.classList.add('show'); valid = false; }
  else ttErr.classList.remove('show');

  // 2) Others text
  const othersCb   = document.getElementById('training_others');
  const othersText = document.getElementById('training_type_custom');
  const othersErr  = document.getElementById('othersTextError');
  if (othersCb && othersCb.checked && othersText.value.trim() === '') {
    othersErr.classList.add('show'); valid = false;
  } else othersErr.classList.remove('show');

  // 3) At least one day
  const days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
  const checkedDays = days.filter(d => document.getElementById('day-' + d)?.checked);
  const schedErr = document.getElementById('scheduleError');
  const timeErr  = document.getElementById('timeError');
  if (checkedDays.length === 0) { schedErr.classList.add('show'); valid = false; }
  else schedErr.classList.remove('show');

  // 4) Each checked day must have a time
  let missingTime = false;
  checkedDays.forEach(d => {
    if (!document.querySelector('input[name="time[' + d + ']"]:checked')) missingTime = true;
  });
  if (missingTime) { timeErr.classList.add('show'); valid = false; }
  else timeErr.classList.remove('show');

  if (!valid) e.preventDefault();
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
