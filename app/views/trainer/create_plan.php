<?php
declare(strict_types=1);
$pageTitle = 'Create Workout Plan';
require __DIR__ . '/../partials/header.php';

$clientId   = (int)($clientRequest['id'] ?? 0);
$clientName = htmlspecialchars($clientRequest['member_name'] ?? 'Client');
$requestId  = (int)($clientRequest['id'] ?? 0);
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
  --bg-page:    #f0f2f0;
  --bg-card:    #ffffff;
  --bg-section: #ecfdf5;
  --border:     #e2e8f0;
  --border-in:  #cbd5e1;
  --green:      #16a34a;
  --green-mid:  #22c55e;
  --green-dark: #15803d;
  --green-deep: #065f46;
  --green-lite: #f0fdf4;
  --green-pale: #dcfce7;
  --txt-pri:    #1e293b;
  --txt-sec:    #64748b;
  --shadow:     0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.05);
}

body {
  background: var(--bg-page) !important;
  font-family: 'Inter', system-ui, sans-serif !important;
  color: var(--txt-pri) !important;
}

/* ── Wrapper ── */
.cp-wrap {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem 1rem 4rem;
}

/* ── Hero Banner ── */
.cp-hero {
  background: linear-gradient(135deg, #15803d 0%, #16a34a 55%, #22c55e 100%);
  border-radius: 14px;
  padding: 26px 28px 22px;
  margin-bottom: 1.25rem;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}
.cp-hero::before {
  content:'';
  position:absolute;
  width:200px;height:200px;
  border-radius:50%;
  background:rgba(255,255,255,.08);
  top:-60px;right:-40px;
  pointer-events:none;
}
.cp-hero-left h1 {
  color:#fff;
  font-size:22px;
  font-weight:800;
  margin:0 0 4px;
}
.cp-hero-left p {
  color:rgba(255,255,255,.88);
  font-size:13px;
  margin:0;
}
.cp-hero-badge {
  display:inline-flex;
  align-items:center;
  gap:.4rem;
  background:rgba(255,255,255,.18);
  border:1px solid rgba(255,255,255,.35);
  color:#fff;
  padding:5px 12px;
  border-radius:50px;
  font-size:12px;
  font-weight:600;
  margin-top:8px;
}
.cp-hero-back {
  background:rgba(255,255,255,.18);
  border:1px solid rgba(255,255,255,.35);
  border-radius:8px;
  padding:8px 14px;
  color:#fff;
  font-size:13px;
  font-weight:600;
  text-decoration:none;
  transition:background .2s;
  white-space:nowrap;
}
.cp-hero-back:hover {
  background:rgba(255,255,255,.28);
  color:#fff;
  text-decoration:none;
}

/* ── Cards ── */
.cp-card {
  background:var(--bg-card);
  border:1px solid var(--border);
  border-radius:12px;
  box-shadow:var(--shadow);
  margin-bottom:1.25rem;
  overflow:hidden;
}
.cp-card-head {
  background:var(--bg-section);
  border-left:4px solid var(--green);
  border-bottom:1px solid var(--border);
  padding:12px 20px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  flex-wrap:wrap;
  gap:0.5rem;
}
.cp-card-head h2 {
  color:var(--green-deep) !important;
  font-size:13px;
  font-weight:800;
  letter-spacing:2px;
  text-transform:uppercase;
  margin:0;
}
.cp-card-body { padding:1.5rem; }

/* ── Profile summary pills ── */
.profile-grid {
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
  gap:.75rem;
  margin-bottom:1rem;
}
.profile-pill {
  background:var(--green-lite);
  border:1px solid #bbf7d0;
  border-radius:10px;
  padding:10px 14px;
}
.profile-pill-lbl {
  font-size:10px;
  font-weight:700;
  text-transform:uppercase;
  letter-spacing:1.2px;
  color:var(--green-deep);
  margin-bottom:4px;
}
.profile-pill-val {
  font-size:14px;
  font-weight:600;
  color:var(--txt-pri);
}

/* ── Generate AI button ── */
.btn-ai-gen {
  background:linear-gradient(135deg,#15803d,#22c55e);
  color:#fff !important;
  border:none;
  border-radius:8px;
  padding:12px 22px;
  font-size:14px;
  font-weight:700;
  font-family:inherit;
  cursor:pointer;
  transition:all .2s;
  width:100%;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:.5rem;
}
.btn-ai-gen:hover {
  background:linear-gradient(135deg,#166534,#16a34a);
  transform:translateY(-1px);
  box-shadow:0 4px 14px rgba(22,163,74,.35);
}
.btn-ai-gen:active { transform:translateY(0); }
.btn-ai-gen:disabled {
  opacity:.65;
  cursor:not-allowed;
  transform:none;
}

.ai-status-bar {
  display:none;
  align-items:center;
  gap:.6rem;
  margin-top:.75rem;
  padding:10px 14px;
  background:var(--green-lite);
  border:1px solid #bbf7d0;
  border-radius:8px;
  font-size:13px;
  color:var(--green-deep);
}

/* ── Schedule Grid ── */
.schedule-grid {
  display: flex;
  gap: 0.5rem;
  padding: 1rem;
  overflow-x: auto;
}
.day-col {
  flex: 1;
  min-width: 155px;
}
.day-column {
  background: #f8fafc;
  border: 1px solid var(--border);
  border-radius: 10px;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.day-col-head {
  padding: 10px 12px;
  background: #ffffff;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-weight: 700;
  font-size: 0.85rem;
}
.exercise-list {
  padding: 8px;
  flex: 1;
  min-height: 220px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.empty-state {
  text-align: center;
  padding: 40px 10px;
  color: var(--txt-sec);
}
.empty-state i { font-size: 1.8rem; opacity: 0.3; }
.empty-state p { font-size: 0.78rem; margin-top: 4px; }
.btn-add-ex {
  margin: 8px;
  background: #ffffff;
  border: 1px dashed var(--green);
  color: var(--green-dark);
  font-weight: 600;
  font-size: 0.8rem;
  border-radius: 6px;
  padding: 6px 10px;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-add-ex:hover { background: var(--green-lite); }

.exercise-item {
  background: #ffffff;
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 8px 10px;
  font-size: 0.8rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.exercise-item-name { font-weight: 700; color: var(--txt-pri); line-height: 1.3; }
.exercise-item-meta { font-size: 0.72rem; color: var(--txt-sec); margin-top: 2px; }

/* Modal Header */
.modal-hd-green {
  background: linear-gradient(135deg, #15803d, #16a34a);
  color: #ffffff;
  padding: 14px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.modal-hd-green h5 { margin: 0; font-weight: 700; font-size: 1rem; color: #ffffff !important; }

/* Exercise Card */
.exercise-card {
  border: 1px solid var(--border);
  border-radius: 10px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
}
.exercise-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22,163,74,0.15);
  border-color: var(--green);
}
.custom-badge {
  position: absolute;
  top: 8px; right: 8px;
  background: #16a34a;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 50px;
}
.custom-ex-form {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin-bottom: 1.25rem;
}
.cp-save-card {
  background: #ffffff;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
  box-shadow: var(--shadow);
}
.btn-save-plan {
  background: linear-gradient(135deg, #15803d, #16a34a);
  color: #ffffff !important;
  font-weight: 700;
  font-size: 0.95rem;
  border: none;
  border-radius: 10px;
  padding: 10px 24px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  box-shadow: 0 4px 14px rgba(22,163,74,0.25);
  transition: transform 0.15s;
}
.btn-save-plan:hover { transform: translateY(-1px); }
</style>

<div class="cp-wrap">

  <!-- Hero Header -->
  <div class="cp-hero">
    <div class="cp-hero-left">
      <h1><i class="bi bi-calendar2-week me-2"></i>Create Workout Plan</h1>
      <p>Assigning tailored training routine for <strong><?= $clientName ?></strong></p>
      <div class="cp-hero-badge">
        <i class="bi bi-person-check-fill"></i> Client: <?= $clientName ?>
      </div>
    </div>
    <a href="index.php?r=trainer/clients" class="cp-hero-back">
      <i class="bi bi-arrow-left me-1"></i>Back to Clients
    </a>
  </div>

  <!-- Client Profile Overview & AI Assistant -->
  <div class="cp-card">
    <div class="cp-card-head">
      <h2><i class="bi bi-person-bounding-box me-1"></i>Client Overview &amp; AI Generator</h2>
    </div>
    <div class="cp-card-body">
      <div class="row g-4">
        <div class="col-lg-8">
          <?php if (!empty($clientProfile)): ?>
            <div class="profile-grid">
              <div class="profile-pill">
                <div class="profile-pill-lbl">Goals</div>
                <div class="profile-pill-val"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $clientProfile['fitness_goals'] ?? 'Not specified'))) ?></div>
              </div>
              <div class="profile-pill">
                <div class="profile-pill-lbl">Activity Level</div>
                <div class="profile-pill-val"><?= htmlspecialchars(ucfirst($clientProfile['activity_level'] ?? 'Not specified')) ?></div>
              </div>
              <div class="profile-pill">
                <div class="profile-pill-lbl">Medical Conditions</div>
                <div class="profile-pill-val"><?= htmlspecialchars($clientProfile['medical_conditions'] ?: 'None') ?></div>
              </div>
              <div class="profile-pill">
                <div class="profile-pill-lbl">Dietary Preferences</div>
                <div class="profile-pill-val"><?= htmlspecialchars($clientProfile['dietary_preferences'] ?: 'None') ?></div>
              </div>
            </div>
          <?php else: ?>
            <div class="alert alert-warning mb-0" style="font-size:13px;border-radius:10px;">
              <i class="bi bi-exclamation-triangle me-2"></i>
              No client profile found. Custom split templates and manual builder below can be used to set up the plan.
            </div>
          <?php endif; ?>
        </div>

        <div class="col-lg-4 d-flex flex-column justify-content-center" style="border-left:1px solid var(--border);padding-left:1.5rem;">
          <p style="font-size:13px;font-weight:700;color:var(--txt-pri);margin-bottom:.4rem;">🚀 Automate Workout Creation</p>
          <p style="font-size:12px;color:var(--txt-sec);margin-bottom:.9rem;line-height:1.5;">Generate a tailor-made workout plan based on the client's goals and profile.</p>
          <button id="generateAiBtn" onclick="generateAIPlan(<?= $requestId ?>)" class="btn-ai-gen">
            <i class="bi bi-stars"></i>Generate AI Plan
          </button>
          <div id="aiStatus" class="ai-status-bar">
            <span class="spinner-border spinner-border-sm" style="width:.8rem;height:.8rem;border-width:2px;" role="status"></span>
            Gemini is generating your plan… please wait
          </div>
          <div id="aiError" class="ai-error-box" style="display:none;margin-top:.75rem;padding:8px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:12px;color:#991b1b;">
            <div id="aiErrorMsg">Generation failed.</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ PROGRAM NAME & PRE-BUILT SPLIT TEMPLATES BAR ══ -->
  <div class="cp-card">
    <div class="cp-card-head">
      <h2><i class="bi bi-journal-bookmark-fill me-1"></i>Program Details &amp; Pre-Built Workout Splits</h2>
    </div>
    <div class="cp-card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-6">
          <label class="form-label fw-bold small text-dark">Program Title / Name</label>
          <input type="text" id="programNameInput" class="form-control rounded-3" placeholder="e.g., 12-Week Push Pull Legs Hypertrophy Plan" value="Custom Workout Program">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold small text-dark"><i class="bi bi-magic text-success me-1"></i>Load Pre-Built Split Template</label>
          <div class="input-group">
            <select class="form-select rounded-start-3" id="templateSelect">
              <option value="">— Select a Pre-Built Split —</option>
              <option value="ppl">🏋️ Push / Pull / Legs (PPL - 6 Day Hypertrophy)</option>
              <option value="bro_split">💪 Bro Split (5 Day Body Part Split)</option>
              <option value="upper_lower">⚡ Upper / Lower Split (4 Day Power Building)</option>
              <option value="full_body">🔥 Full Body Routine (3 Day Foundation)</option>
              <option value="arnold">🏆 Arnold Split (Chest/Back, Shoulders/Arms, Legs - 6 Day)</option>
              <option value="cardio_fatloss">🏃 Fat Loss &amp; Cardio Conditioning (5 Day)</option>
            </select>
            <button type="button" class="btn btn-success fw-bold px-3 rounded-end-3" onclick="applySelectedTemplate()">
              Load Template
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ WEEKLY SCHEDULE BUILDER ══ -->
  <div class="cp-card">
    <div class="cp-card-head">
      <h2><i class="bi bi-calendar-week me-1"></i>Weekly Workout Schedule</h2>
      <span style="font-size:11px;color:var(--txt-sec);">Click "+ Add Exercise" on any day to add movements</span>
    </div>
    <div class="schedule-grid">
      <?php
      $days     = ['MON','TUE','WED','THU','FRI','SAT','SUN'];
      $daysFull = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
      foreach ($days as $i => $day):
      ?>
      <div class="day-col">
        <div class="day-column" data-day="<?= strtolower($day) ?>">
          <div class="day-col-head">
            <span class="day-col-name">
              <i class="bi bi-calendar-day me-1"></i><?= $day ?>
            </span>
            <span class="badge day-count-badge zero exercise-count">0</span>
          </div>
          <div class="exercise-list">
            <div class="empty-state">
              <i class="bi bi-moon-stars"></i>
              <p>Rest day</p>
            </div>
          </div>
          <button class="btn-add-ex add-exercise-btn"
                  data-day="<?= strtolower($day) ?>"
                  data-day-full="<?= $daysFull[$i] ?>">
            <i class="bi bi-plus-circle me-1"></i>Add Exercise
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ══ SAVE CARD ══ -->
  <div class="cp-save-card">
    <div>
      <p style="font-size:15px;font-weight:700;margin:0 0 3px;color:var(--txt-pri);">Ready to save this workout plan?</p>
      <p style="font-size:13px;color:var(--txt-sec);margin:0;">After saving you'll be redirected to create the meal plan for <?= $clientName ?>.</p>
    </div>
    <button class="btn-save-plan" id="savePlanBtn">
      <i class="bi bi-check-circle-fill me-1"></i>Save &amp; Continue to Meal Plan
    </button>
  </div>

</div><!-- /cp-wrap -->

<!-- ══ EXERCISE SELECTION MODAL ══ -->
<div class="modal fade" id="exerciseModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-hd-green">
        <h5><i class="bi bi-search me-2"></i>Add Exercise — <span id="modalDayName">Monday</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <!-- Custom exercise toggle -->
        <div class="mb-3">
          <button class="btn btn-outline-success btn-sm fw-bold" id="toggleCustomFormBtn">
            <i class="bi bi-plus-circle me-1"></i>+ Add Custom Exercise
          </button>
        </div>

        <!-- Custom exercise form -->
        <div id="customExerciseForm" class="custom-ex-form" style="display:none;">
          <p style="font-size:12px;font-weight:700;color:var(--green-deep);text-transform:uppercase;letter-spacing:1px;margin-bottom:.75rem;">Create Your Own Custom Exercise</p>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small">Exercise Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="customExerciseName" placeholder="e.g., Bulgarian Split Squat">
            </div>
            <div class="col-md-3">
              <label class="form-label small">Body Part <span class="text-danger">*</span></label>
              <select class="form-select" id="customBodyPart">
                <option value="">Select...</option>
                <option value="Back">Back</option>
                <option value="Arms">Arms</option>
                <option value="Chest">Chest</option>
                <option value="Legs">Legs</option>
                <option value="Shoulders">Shoulders</option>
                <option value="Abs">Abs</option>
                <option value="Cardio">Cardio</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small">Equipment <span class="text-danger">*</span></label>
              <select class="form-select" id="customEquipment">
                <option value="">Select...</option>
                <option value="None/Bodyweight">None/Bodyweight</option>
                <option value="Dumbbell">Dumbbell</option>
                <option value="Barbell">Barbell</option>
                <option value="Machine">Machine</option>
                <option value="Cable">Cable</option>
                <option value="Kettlebell">Kettlebell</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small">Instructions (optional)</label>
              <textarea class="form-control" id="customInstructions" rows="2" placeholder="Step-by-step form guide..."></textarea>
            </div>
            <div class="col-md-3">
              <label class="form-label small">Default Sets</label>
              <input type="number" class="form-control" id="customSets" value="3" min="1">
            </div>
            <div class="col-md-3">
              <label class="form-label small">Default Reps</label>
              <input type="number" class="form-control" id="customReps" value="10" min="1">
            </div>
            <div class="col-12">
              <button class="btn btn-success btn-sm fw-bold" id="saveCustomExerciseBtn">
                <i class="bi bi-check-circle me-1"></i>Save Custom Exercise
              </button>
              <button class="btn btn-outline-secondary btn-sm ms-2" id="cancelCustomFormBtn">Cancel</button>
            </div>
          </div>
        </div>

        <!-- Search & Filter Controls -->
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Search Exercise</label>
            <input type="text" class="form-control" id="exerciseSearch" placeholder="Search Back, Arms, Cardio, Bench Press...">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Body Part / Category</label>
            <select class="form-select" id="categoryFilter">
              <option value="">All Body Parts</option>
              <option value="Back">Back</option>
              <option value="Arms">Arms (Biceps &amp; Triceps)</option>
              <option value="Chest">Chest</option>
              <option value="Legs">Legs (Quads, Glutes &amp; Calves)</option>
              <option value="Shoulders">Shoulders</option>
              <option value="Abs">Abs &amp; Core</option>
              <option value="Cardio">Cardio &amp; Conditioning</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Equipment Needed</label>
            <select class="form-select" id="equipmentFilter">
              <option value="">All Equipment</option>
              <option value="Barbell">Barbell</option>
              <option value="Dumbbell">Dumbbell</option>
              <option value="Cable">Cable</option>
              <option value="Machine">Machine</option>
              <option value="None/Bodyweight">None / Bodyweight</option>
              <option value="Kettlebell">Kettlebell</option>
            </select>
          </div>
        </div>

        <!-- Exercise grid -->
        <div id="exerciseGrid" class="row g-3 mt-1" style="max-height: 480px; overflow-y: auto;"></div>

      </div>
    </div>
  </div>
</div>

<script>
/* ── LOCAL COMPREHENSIVE EXERCISE LIBRARY (60+ EXERCISES) ── */
const LOCAL_EXERCISES = [
  // BACK
  { id: 'ex_b1', name: 'Barbell Bent-Over Row', category: 'Back', equipment: 'Barbell', muscles: 'Lats, Rhomboids, Upper Back', image: 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', sets: 4, reps: 8 },
  { id: 'ex_b2', name: 'Lat Pulldown', category: 'Back', equipment: 'Cable', muscles: 'Latissimus Dorsi, Biceps', image: 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=400&q=80', sets: 4, reps: 10 },
  { id: 'ex_b3', name: 'Seated Cable Row', category: 'Back', equipment: 'Cable', muscles: 'Mid Back, Lats, Biceps', image: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 12 },
  { id: 'ex_b4', name: 'Pull-Ups / Chin-Ups', category: 'Back', equipment: 'None/Bodyweight', muscles: 'Lats, Upper Back, Core', image: 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 8 },
  { id: 'ex_b5', name: 'Barbell Conventional Deadlift', category: 'Back', equipment: 'Barbell', muscles: 'Hamstrings, Glutes, Lower Back', image: 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', sets: 4, reps: 5 },
  { id: 'ex_b6', name: 'Dumbbell Single-Arm Row', category: 'Back', equipment: 'Dumbbell', muscles: 'Lats, Traps, Biceps', image: 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 10 },
  { id: 'ex_b7', name: 'T-Bar Row', category: 'Back', equipment: 'Barbell', muscles: 'Middle Back, Lats', image: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 10 },
  { id: 'ex_b8', name: 'Cable Face Pull', category: 'Back', equipment: 'Cable', muscles: 'Rear Delts, Upper Back, Rotator Cuff', image: 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 15 },

  // ARMS (BICEPS & TRICEPS)
  { id: 'ex_a1', name: 'Barbell Bicep Curl', category: 'Arms', equipment: 'Barbell', muscles: 'Biceps Brachii', image: 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 10 },
  { id: 'ex_a2', name: 'Dumbbell Alternate Hammer Curl', category: 'Arms', equipment: 'Dumbbell', muscles: 'Brachialis, Forearms', image: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 12 },
  { id: 'ex_a3', name: 'Tricep Rope Pushdown', category: 'Arms', equipment: 'Cable', muscles: 'Triceps Lateral & Medial Head', image: 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 12 },
  { id: 'ex_a4', name: 'Skull Crushers (EZ Bar Extension)', category: 'Arms', equipment: 'Barbell', muscles: 'Triceps Long Head', image: 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 10 },
  { id: 'ex_a5', name: 'Preacher Curl', category: 'Arms', equipment: 'Barbell', muscles: 'Biceps Short Head', image: 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 10 },
  { id: 'ex_a6', name: 'Tricep Bodyweight Dips', category: 'Arms', equipment: 'None/Bodyweight', muscles: 'Triceps, Lower Chest', image: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 10 },
  { id: 'ex_a7', name: 'Incline Dumbbell Curl', category: 'Arms', equipment: 'Dumbbell', muscles: 'Biceps Long Head', image: 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 12 },
  { id: 'ex_a8', name: 'Overhead Dumbbell Tricep Extension', category: 'Arms', equipment: 'Dumbbell', muscles: 'Triceps Long Head', image: 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 12 },

  // CHEST
  { id: 'ex_c1', name: 'Flat Barbell Bench Press', category: 'Chest', equipment: 'Barbell', muscles: 'Pectoralis Major, Anterior Deltoid', image: 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', sets: 4, reps: 8 },
  { id: 'ex_c2', name: 'Incline Dumbbell Bench Press', category: 'Chest', equipment: 'Dumbbell', muscles: 'Upper Chest, Shoulders', image: 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=400&q=80', sets: 4, reps: 10 },
  { id: 'ex_c3', name: 'Chest Cable Flyes', category: 'Chest', equipment: 'Cable', muscles: 'Inner Pectorals', image: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 12 },
  { id: 'ex_c4', name: 'Standard Bodyweight Push-Ups', category: 'Chest', equipment: 'None/Bodyweight', muscles: 'Chest, Triceps, Core', image: 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 15 },
  { id: 'ex_c5', name: 'Machine Chest Press', category: 'Chest', equipment: 'Machine', muscles: 'Mid Chest', image: 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 10 },

  // SHOULDERS
  { id: 'ex_s1', name: 'Standing Barbell Overhead Press (OHP)', category: 'Shoulders', equipment: 'Barbell', muscles: 'Anterior & Lateral Deltoids', image: 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', sets: 4, reps: 8 },
  { id: 'ex_s2', name: 'Seated Dumbbell Shoulder Press', category: 'Shoulders', equipment: 'Dumbbell', muscles: 'Front & Side Delts', image: 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 10 },
  { id: 'ex_s3', name: 'Dumbbell Lateral Raise', category: 'Shoulders', equipment: 'Dumbbell', muscles: 'Side Delts', image: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=400&q=80', sets: 4, reps: 15 },
  { id: 'ex_s4', name: 'Arnold Press', category: 'Shoulders', equipment: 'Dumbbell', muscles: 'Full Deltoid Complex', image: 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 10 },
  { id: 'ex_s5', name: 'Dumbbell Rear Delt Fly', category: 'Shoulders', equipment: 'Dumbbell', muscles: 'Posterior Deltoid', image: 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 12 },

  // LEGS
  { id: 'ex_l1', name: 'Barbell Back Squat', category: 'Legs', equipment: 'Barbell', muscles: 'Quadriceps, Glutes, Lower Back', image: 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', sets: 4, reps: 8 },
  { id: 'ex_l2', name: 'Leg Press Machine', category: 'Legs', equipment: 'Machine', muscles: 'Quads, Glutes', image: 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=400&q=80', sets: 4, reps: 10 },
  { id: 'ex_l3', name: 'Dumbbell Romanian Deadlift (RDL)', category: 'Legs', equipment: 'Dumbbell', muscles: 'Hamstrings, Glutes', image: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 10 },
  { id: 'ex_l4', name: 'Lying Leg Curl', category: 'Legs', equipment: 'Machine', muscles: 'Hamstrings', image: 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 12 },
  { id: 'ex_l5', name: 'Leg Extension', category: 'Legs', equipment: 'Machine', muscles: 'Quads Isolation', image: 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 12 },
  { id: 'ex_l6', name: 'Bulgarian Split Squat', category: 'Legs', equipment: 'Dumbbell', muscles: 'Quads, Glutes', image: 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 10 },
  { id: 'ex_l7', name: 'Standing Calf Raise', category: 'Legs', equipment: 'Machine', muscles: 'Calves (Gastrocnemius)', image: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=400&q=80', sets: 4, reps: 15 },

  // ABS & CORE
  { id: 'ex_core1', name: 'Hanging Leg Raise', category: 'Abs', equipment: 'None/Bodyweight', muscles: 'Lower Abs, Hip Flexors', image: 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 12 },
  { id: 'ex_core2', name: 'Ab Wheel Rollout', category: 'Abs', equipment: 'None/Bodyweight', muscles: 'Rectus Abdominis, Core', image: 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 10 },
  { id: 'ex_core3', name: 'Cable Woodchoppers', category: 'Abs', equipment: 'Cable', muscles: 'Obliques', image: 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 15 },
  { id: 'ex_core4', name: 'Plank Hold (Isometric)', category: 'Abs', equipment: 'None/Bodyweight', muscles: 'Transverse Abdominis, Core Stability', image: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 60 },

  // CARDIO
  { id: 'ex_card1', name: 'Treadmill Running (HIIT)', category: 'Cardio', equipment: 'Machine', muscles: 'Cardiovascular System', image: 'https://images.unsplash.com/photo-1538805060514-97d9cc17730c?auto=format&fit=crop&w=400&q=80', sets: 1, reps: 20 },
  { id: 'ex_card2', name: 'Stationary Bike Sprints', category: 'Cardio', equipment: 'Machine', muscles: 'Quads, Cardio', image: 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', sets: 1, reps: 15 },
  { id: 'ex_card3', name: 'Rowing Machine Ergometer', category: 'Cardio', equipment: 'Machine', muscles: 'Full Body, Cardio', image: 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=400&q=80', sets: 1, reps: 15 },
  { id: 'ex_card4', name: 'Jump Rope Conditioning', category: 'Cardio', equipment: 'None/Bodyweight', muscles: 'Calves, Cardio', image: 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 100 },
  { id: 'ex_card5', name: 'Full Body Burpees', category: 'Cardio', equipment: 'None/Bodyweight', muscles: 'Full Body, Cardio', image: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=400&q=80', sets: 3, reps: 15 }
];

/* ── PRE-BUILT SPLIT TEMPLATES DEFINITION ── */
const PREBUILT_TEMPLATES = {
  ppl: {
    title: '12-Week Push Pull Legs (PPL) Hypertrophy',
    plan: {
      mon: [
        { name: 'Flat Barbell Bench Press', category: 'Chest', equipment: 'Barbell', sets: 4, reps: 8 },
        { name: 'Standing Barbell Overhead Press (OHP)', category: 'Shoulders', equipment: 'Barbell', sets: 3, reps: 8 },
        { name: 'Incline Dumbbell Bench Press', category: 'Chest', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Tricep Rope Pushdown', category: 'Arms', equipment: 'Cable', sets: 3, reps: 12 },
        { name: 'Dumbbell Lateral Raise', category: 'Shoulders', equipment: 'Dumbbell', sets: 4, reps: 15 }
      ],
      tue: [
        { name: 'Barbell Bent-Over Row', category: 'Back', equipment: 'Barbell', sets: 4, reps: 8 },
        { name: 'Lat Pulldown', category: 'Back', equipment: 'Cable', sets: 3, reps: 10 },
        { name: 'Seated Cable Row', category: 'Back', equipment: 'Cable', sets: 3, reps: 12 },
        { name: 'Barbell Bicep Curl', category: 'Arms', equipment: 'Barbell', sets: 3, reps: 10 },
        { name: 'Dumbbell Alternate Hammer Curl', category: 'Arms', equipment: 'Dumbbell', sets: 3, reps: 12 }
      ],
      wed: [
        { name: 'Barbell Back Squat', category: 'Legs', equipment: 'Barbell', sets: 4, reps: 8 },
        { name: 'Dumbbell Romanian Deadlift (RDL)', category: 'Legs', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Leg Press Machine', category: 'Legs', equipment: 'Machine', sets: 3, reps: 10 },
        { name: 'Lying Leg Curl', category: 'Legs', equipment: 'Machine', sets: 3, reps: 12 },
        { name: 'Standing Calf Raise', category: 'Legs', equipment: 'Machine', sets: 4, reps: 15 }
      ],
      thu: [
        { name: 'Incline Dumbbell Bench Press', category: 'Chest', equipment: 'Dumbbell', sets: 4, reps: 8 },
        { name: 'Seated Dumbbell Shoulder Press', category: 'Shoulders', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Chest Cable Flyes', category: 'Chest', equipment: 'Cable', sets: 3, reps: 12 },
        { name: 'Skull Crushers (EZ Bar Extension)', category: 'Arms', equipment: 'Barbell', sets: 3, reps: 10 },
        { name: 'Dumbbell Lateral Raise', category: 'Shoulders', equipment: 'Dumbbell', sets: 4, reps: 15 }
      ],
      fri: [
        { name: 'Pull-Ups / Chin-Ups', category: 'Back', equipment: 'None/Bodyweight', sets: 4, reps: 8 },
        { name: 'T-Bar Row', category: 'Back', equipment: 'Barbell', sets: 3, reps: 10 },
        { name: 'Dumbbell Single-Arm Row', category: 'Back', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Preacher Curl', category: 'Arms', equipment: 'Barbell', sets: 3, reps: 10 },
        { name: 'Cable Face Pull', category: 'Back', equipment: 'Cable', sets: 3, reps: 15 }
      ],
      sat: [
        { name: 'Leg Press Machine', category: 'Legs', equipment: 'Machine', sets: 4, reps: 10 },
        { name: 'Bulgarian Split Squat', category: 'Legs', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Leg Extension', category: 'Legs', equipment: 'Machine', sets: 3, reps: 12 },
        { name: 'Lying Leg Curl', category: 'Legs', equipment: 'Machine', sets: 3, reps: 12 },
        { name: 'Hanging Leg Raise', category: 'Abs', equipment: 'None/Bodyweight', sets: 3, reps: 15 }
      ],
      sun: []
    }
  },

  bro_split: {
    title: '5-Day Classic Bro Split (Body Part Specialization)',
    plan: {
      mon: [ // Chest
        { name: 'Flat Barbell Bench Press', category: 'Chest', equipment: 'Barbell', sets: 4, reps: 8 },
        { name: 'Incline Dumbbell Bench Press', category: 'Chest', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Chest Cable Flyes', category: 'Chest', equipment: 'Cable', sets: 3, reps: 12 },
        { name: 'Standard Bodyweight Push-Ups', category: 'Chest', equipment: 'None/Bodyweight', sets: 3, reps: 15 }
      ],
      tue: [ // Back
        { name: 'Barbell Conventional Deadlift', category: 'Back', equipment: 'Barbell', sets: 4, reps: 5 },
        { name: 'Lat Pulldown', category: 'Back', equipment: 'Cable', sets: 3, reps: 10 },
        { name: 'Seated Cable Row', category: 'Back', equipment: 'Cable', sets: 3, reps: 12 },
        { name: 'Dumbbell Single-Arm Row', category: 'Back', equipment: 'Dumbbell', sets: 3, reps: 10 }
      ],
      wed: [ // Shoulders
        { name: 'Standing Barbell Overhead Press (OHP)', category: 'Shoulders', equipment: 'Barbell', sets: 4, reps: 8 },
        { name: 'Dumbbell Lateral Raise', category: 'Shoulders', equipment: 'Dumbbell', sets: 4, reps: 15 },
        { name: 'Arnold Press', category: 'Shoulders', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Cable Face Pull', category: 'Back', equipment: 'Cable', sets: 3, reps: 15 }
      ],
      thu: [ // Arms
        { name: 'Barbell Bicep Curl', category: 'Arms', equipment: 'Barbell', sets: 3, reps: 10 },
        { name: 'Tricep Rope Pushdown', category: 'Arms', equipment: 'Cable', sets: 3, reps: 12 },
        { name: 'Dumbbell Alternate Hammer Curl', category: 'Arms', equipment: 'Dumbbell', sets: 3, reps: 12 },
        { name: 'Skull Crushers (EZ Bar Extension)', category: 'Arms', equipment: 'Barbell', sets: 3, reps: 10 }
      ],
      fri: [ // Legs
        { name: 'Barbell Back Squat', category: 'Legs', equipment: 'Barbell', sets: 4, reps: 8 },
        { name: 'Leg Press Machine', category: 'Legs', equipment: 'Machine', sets: 3, reps: 10 },
        { name: 'Dumbbell Romanian Deadlift (RDL)', category: 'Legs', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Standing Calf Raise', category: 'Legs', equipment: 'Machine', sets: 4, reps: 15 }
      ],
      sat: [], sun: []
    }
  },

  upper_lower: {
    title: '4-Day Upper / Lower Power Building Split',
    plan: {
      mon: [ // Upper A
        { name: 'Flat Barbell Bench Press', category: 'Chest', equipment: 'Barbell', sets: 4, reps: 6 },
        { name: 'Barbell Bent-Over Row', category: 'Back', equipment: 'Barbell', sets: 4, reps: 6 },
        { name: 'Standing Barbell Overhead Press (OHP)', category: 'Shoulders', equipment: 'Barbell', sets: 3, reps: 8 },
        { name: 'Lat Pulldown', category: 'Back', equipment: 'Cable', sets: 3, reps: 10 },
        { name: 'Barbell Bicep Curl', category: 'Arms', equipment: 'Barbell', sets: 3, reps: 10 }
      ],
      tue: [ // Lower A
        { name: 'Barbell Back Squat', category: 'Legs', equipment: 'Barbell', sets: 4, reps: 6 },
        { name: 'Dumbbell Romanian Deadlift (RDL)', category: 'Legs', equipment: 'Dumbbell', sets: 3, reps: 8 },
        { name: 'Leg Press Machine', category: 'Legs', equipment: 'Machine', sets: 3, reps: 10 },
        { name: 'Standing Calf Raise', category: 'Legs', equipment: 'Machine', sets: 4, reps: 12 },
        { name: 'Hanging Leg Raise', category: 'Abs', equipment: 'None/Bodyweight', sets: 3, reps: 12 }
      ],
      wed: [],
      thu: [ // Upper B
        { name: 'Incline Dumbbell Bench Press', category: 'Chest', equipment: 'Dumbbell', sets: 4, reps: 8 },
        { name: 'Pull-Ups / Chin-Ups', category: 'Back', equipment: 'None/Bodyweight', sets: 4, reps: 8 },
        { name: 'Seated Dumbbell Shoulder Press', category: 'Shoulders', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Seated Cable Row', category: 'Back', equipment: 'Cable', sets: 3, reps: 12 },
        { name: 'Tricep Rope Pushdown', category: 'Arms', equipment: 'Cable', sets: 3, reps: 12 }
      ],
      fri: [ // Lower B
        { name: 'Barbell Conventional Deadlift', category: 'Back', equipment: 'Barbell', sets: 3, reps: 5 },
        { name: 'Bulgarian Split Squat', category: 'Legs', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Leg Extension', category: 'Legs', equipment: 'Machine', sets: 3, reps: 12 },
        { name: 'Lying Leg Curl', category: 'Legs', equipment: 'Machine', sets: 3, reps: 12 },
        { name: 'Ab Wheel Rollout', category: 'Abs', equipment: 'None/Bodyweight', sets: 3, reps: 10 }
      ],
      sat: [], sun: []
    }
  },

  full_body: {
    title: '3-Day Full Body Strength Foundation',
    plan: {
      mon: [
        { name: 'Barbell Back Squat', category: 'Legs', equipment: 'Barbell', sets: 3, reps: 8 },
        { name: 'Flat Barbell Bench Press', category: 'Chest', equipment: 'Barbell', sets: 3, reps: 8 },
        { name: 'Barbell Bent-Over Row', category: 'Back', equipment: 'Barbell', sets: 3, reps: 8 },
        { name: 'Standing Barbell Overhead Press (OHP)', category: 'Shoulders', equipment: 'Barbell', sets: 3, reps: 10 }
      ],
      wed: [
        { name: 'Barbell Conventional Deadlift', category: 'Back', equipment: 'Barbell', sets: 3, reps: 5 },
        { name: 'Incline Dumbbell Bench Press', category: 'Chest', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Lat Pulldown', category: 'Back', equipment: 'Cable', sets: 3, reps: 10 },
        { name: 'Dumbbell Lateral Raise', category: 'Shoulders', equipment: 'Dumbbell', sets: 3, reps: 12 }
      ],
      fri: [
        { name: 'Leg Press Machine', category: 'Legs', equipment: 'Machine', sets: 3, reps: 10 },
        { name: 'Tricep Bodyweight Dips', category: 'Arms', equipment: 'None/Bodyweight', sets: 3, reps: 10 },
        { name: 'Seated Cable Row', category: 'Back', equipment: 'Cable', sets: 3, reps: 12 },
        { name: 'Barbell Bicep Curl', category: 'Arms', equipment: 'Barbell', sets: 3, reps: 10 }
      ],
      tue: [], thu: [], sat: [], sun: []
    }
  },

  arnold: {
    title: '6-Day Arnold Schwarzenegger Golden Era Split',
    plan: {
      mon: [
        { name: 'Flat Barbell Bench Press', category: 'Chest', equipment: 'Barbell', sets: 4, reps: 8 },
        { name: 'Pull-Ups / Chin-Ups', category: 'Back', equipment: 'None/Bodyweight', sets: 4, reps: 8 },
        { name: 'Incline Dumbbell Bench Press', category: 'Chest', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Barbell Bent-Over Row', category: 'Back', equipment: 'Barbell', sets: 3, reps: 10 }
      ],
      tue: [
        { name: 'Standing Barbell Overhead Press (OHP)', category: 'Shoulders', equipment: 'Barbell', sets: 4, reps: 8 },
        { name: 'Dumbbell Lateral Raise', category: 'Shoulders', equipment: 'Dumbbell', sets: 4, reps: 12 },
        { name: 'Barbell Bicep Curl', category: 'Arms', equipment: 'Barbell', sets: 3, reps: 10 },
        { name: 'Skull Crushers (EZ Bar Extension)', category: 'Arms', equipment: 'Barbell', sets: 3, reps: 10 }
      ],
      wed: [
        { name: 'Barbell Back Squat', category: 'Legs', equipment: 'Barbell', sets: 4, reps: 8 },
        { name: 'Dumbbell Romanian Deadlift (RDL)', category: 'Legs', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Leg Press Machine', category: 'Legs', equipment: 'Machine', sets: 3, reps: 10 },
        { name: 'Standing Calf Raise', category: 'Legs', equipment: 'Machine', sets: 4, reps: 15 }
      ],
      thu: [
        { name: 'Incline Dumbbell Bench Press', category: 'Chest', equipment: 'Dumbbell', sets: 4, reps: 10 },
        { name: 'Seated Cable Row', category: 'Back', equipment: 'Cable', sets: 4, reps: 10 },
        { name: 'Chest Cable Flyes', category: 'Chest', equipment: 'Cable', sets: 3, reps: 12 },
        { name: 'Lat Pulldown', category: 'Back', equipment: 'Cable', sets: 3, reps: 12 }
      ],
      fri: [
        { name: 'Arnold Press', category: 'Shoulders', equipment: 'Dumbbell', sets: 4, reps: 10 },
        { name: 'Cable Face Pull', category: 'Back', equipment: 'Cable', sets: 4, reps: 15 },
        { name: 'Dumbbell Alternate Hammer Curl', category: 'Arms', equipment: 'Dumbbell', sets: 3, reps: 12 },
        { name: 'Tricep Rope Pushdown', category: 'Arms', equipment: 'Cable', sets: 3, reps: 12 }
      ],
      sat: [
        { name: 'Leg Press Machine', category: 'Legs', equipment: 'Machine', sets: 4, reps: 10 },
        { name: 'Bulgarian Split Squat', category: 'Legs', equipment: 'Dumbbell', sets: 3, reps: 10 },
        { name: 'Lying Leg Curl', category: 'Legs', equipment: 'Machine', sets: 3, reps: 12 },
        { name: 'Hanging Leg Raise', category: 'Abs', equipment: 'None/Bodyweight', sets: 3, reps: 15 }
      ],
      sun: []
    }
  },

  cardio_fatloss: {
    title: '5-Day Fat Loss & Cardio Conditioning',
    plan: {
      mon: [
        { name: 'Treadmill Running (HIIT)', category: 'Cardio', equipment: 'Machine', sets: 1, reps: 20 },
        { name: 'Full Body Burpees', category: 'Cardio', equipment: 'None/Bodyweight', sets: 3, reps: 15 },
        { name: 'Standard Bodyweight Push-Ups', category: 'Chest', equipment: 'None/Bodyweight', sets: 3, reps: 15 }
      ],
      tue: [
        { name: 'Stationary Bike Sprints', category: 'Cardio', equipment: 'Machine', sets: 1, reps: 20 },
        { name: 'Jump Rope Conditioning', category: 'Cardio', equipment: 'None/Bodyweight', sets: 3, reps: 100 },
        { name: 'Hanging Leg Raise', category: 'Abs', equipment: 'None/Bodyweight', sets: 3, reps: 15 }
      ],
      wed: [
        { name: 'Rowing Machine Ergometer', category: 'Cardio', equipment: 'Machine', sets: 1, reps: 15 },
        { name: 'Goblet Squat', category: 'Legs', equipment: 'Dumbbell', sets: 3, reps: 12 },
        { name: 'Plank Hold (Isometric)', category: 'Abs', equipment: 'None/Bodyweight', sets: 3, reps: 60 }
      ],
      thu: [
        { name: 'Full Body Burpees', category: 'Cardio', equipment: 'None/Bodyweight', sets: 4, reps: 15 },
        { name: 'Treadmill Running (HIIT)', category: 'Cardio', equipment: 'Machine', sets: 1, reps: 20 }
      ],
      fri: [
        { name: 'Jump Rope Conditioning', category: 'Cardio', equipment: 'None/Bodyweight', sets: 4, reps: 100 },
        { name: 'Ab Wheel Rollout', category: 'Abs', equipment: 'None/Bodyweight', sets: 3, reps: 12 }
      ],
      sat: [], sun: []
    }
  }
};

/* ── GLOBALS & INITIALIZATION ── */
const REQUEST_ID = <?= $requestId ?>;
let currentDay = '';
let currentSearch = '';
let currentCategory = '';
let currentEquipment = '';
let exerciseModal;
let weeklyPlan = { mon:[], tue:[], wed:[], thu:[], fri:[], sat:[], sun:[] };

document.addEventListener('DOMContentLoaded', function() {
  exerciseModal = new bootstrap.Modal(document.getElementById('exerciseModal'));

  // Day add buttons
  document.querySelectorAll('.add-exercise-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      currentDay    = this.getAttribute('data-day');
      const dayFull = this.getAttribute('data-day-full');
      document.getElementById('modalDayName').textContent = dayFull;
      currentSearch = ''; currentCategory = ''; currentEquipment = '';
      document.getElementById('exerciseSearch').value = '';
      document.getElementById('categoryFilter').value = '';
      document.getElementById('equipmentFilter').value = '';
      renderExerciseGrid();
      exerciseModal.show();
    });
  });

  // Custom exercise toggles
  document.getElementById('toggleCustomFormBtn').addEventListener('click', function() {
    const form = document.getElementById('customExerciseForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
  });
  document.getElementById('cancelCustomFormBtn').addEventListener('click', () => {
    document.getElementById('customExerciseForm').style.display = 'none';
  });

  document.getElementById('saveCustomExerciseBtn').addEventListener('click', saveCustomExercise);

  // Search & Filters (Instant client-side filter)
  document.getElementById('exerciseSearch').addEventListener('input', function() {
    currentSearch = this.value.trim().toLowerCase();
    renderExerciseGrid();
  });
  document.getElementById('categoryFilter').addEventListener('change', function() {
    currentCategory = this.value;
    renderExerciseGrid();
  });
  document.getElementById('equipmentFilter').addEventListener('change', function() {
    currentEquipment = this.value;
    renderExerciseGrid();
  });

  document.getElementById('savePlanBtn').addEventListener('click', savePlan);
});

/* ── PRE-BUILT TEMPLATE LOADER ── */
function applySelectedTemplate() {
  const key = document.getElementById('templateSelect').value;
  if (!key || !PREBUILT_TEMPLATES[key]) {
    showToast('Please select a pre-built split template first.', 'warning');
    return;
  }

  const tmpl = PREBUILT_TEMPLATES[key];
  if (confirm(`Load "${tmpl.title}" into the weekly builder? This will populate your schedule.`)) {
    document.getElementById('programNameInput').value = tmpl.title;
    
    // Clear and populate
    ['mon','tue','wed','thu','fri','sat','sun'].forEach(d => {
      weeklyPlan[d] = [];
      const list = tmpl.plan[d] || [];
      list.forEach((ex, idx) => {
        weeklyPlan[d].push({
          id: `tmpl_${d}_${idx}_${Date.now()}`,
          name: ex.name,
          category: ex.category || 'General',
          equipment: ex.equipment || 'Various',
          sets: ex.sets || 3,
          reps: ex.reps || 10,
          isCustom: false
        });
      });
      updateDayColumn(d);
    });

    showToast(`Successfully loaded ${tmpl.title}!`, 'success');
  }
}

/* ── RENDER EXERCISE GRID ── */
function renderExerciseGrid() {
  const grid = document.getElementById('exerciseGrid');
  grid.innerHTML = '';

  let filtered = LOCAL_EXERCISES.filter(ex => {
    // Search match
    if (currentSearch && !ex.name.toLowerCase().includes(currentSearch) && !ex.category.toLowerCase().includes(currentSearch) && !ex.muscles.toLowerCase().includes(currentSearch)) {
      return false;
    }
    // Category match
    if (currentCategory && ex.category.toLowerCase() !== currentCategory.toLowerCase()) {
      return false;
    }
    // Equipment match
    if (currentEquipment && ex.equipment.toLowerCase() !== currentEquipment.toLowerCase()) {
      return false;
    }
    return true;
  });

  if (filtered.length === 0) {
    grid.innerHTML = '<div class="col-12 text-center py-4"><p class="text-muted">No exercises match your search filters. Click "+ Add Custom Exercise" above to create one!</p></div>';
    return;
  }

  filtered.forEach(ex => {
    const col = document.createElement('div');
    col.className = 'col-md-6 col-lg-4';

    const card = document.createElement('div');
    card.className = 'card exercise-card h-100';
    card.innerHTML = `
      <img src="${ex.image}" class="card-img-top" alt="${ex.name}" style="height:120px;object-fit:cover;" onerror="this.src='https://via.placeholder.com/400x120?text=${encodeURIComponent(ex.name)}'">
      <div class="card-body p-3">
        <h6 class="card-title fw-bold mb-1" style="font-size:13px;color:var(--txt-pri);">${ex.name}</h6>
        <div class="d-flex flex-wrap gap-1 mb-2">
          <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:10px;">🏷️ ${ex.category}</span>
          <span class="badge bg-light text-secondary border" style="font-size:10px;">🔧 ${ex.equipment}</span>
        </div>
        <div class="text-muted small" style="font-size:11px;">
          <i class="bi bi-heart-pulse text-danger me-1"></i>${ex.muscles}
        </div>
      </div>
    `;

    card.addEventListener('click', () => addExerciseToDay(ex));
    col.appendChild(card);
    grid.appendChild(col);
  });
}

/* ── ADD EXERCISE TO DAY ── */
function addExerciseToDay(exercise) {
  const sets = prompt(`How many sets for "${exercise.name}"?`, exercise.sets || 3);
  if (sets === null) return;
  const reps = prompt(`How many reps for "${exercise.name}"?`, exercise.reps || 10);
  if (reps === null) return;

  const exData = {
    id: exercise.id || 'ex_' + Date.now(),
    name: exercise.name,
    category: exercise.category || 'General',
    equipment: exercise.equipment || 'Various',
    muscles: exercise.muscles || 'N/A',
    isCustom: !!exercise.isCustom,
    sets: parseInt(sets) || 3,
    reps: parseInt(reps) || 10
  };

  weeklyPlan[currentDay].push(exData);
  updateDayColumn(currentDay);
  exerciseModal.hide();
  showToast(`Added ${exData.name} to ${currentDay.toUpperCase()}`, 'success');
}

/* ── CUSTOM EXERCISE SAVER ── */
function saveCustomExercise() {
  const name  = document.getElementById('customExerciseName').value.trim();
  const part  = document.getElementById('customBodyPart').value;
  const equip = document.getElementById('customEquipment').value;
  const instr = document.getElementById('customInstructions').value.trim();
  const sets  = document.getElementById('customSets').value;
  const reps  = document.getElementById('customReps').value;

  if (!name || !part || !equip) {
    showToast('Please fill in Exercise Name, Body Part, and Equipment.', 'warning');
    return;
  }

  const customEx = {
    id: 'custom_' + Date.now(),
    name: name,
    category: part,
    equipment: equip,
    muscles: part + ' (Custom)',
    image: 'https://via.placeholder.com/400x120?text=' + encodeURIComponent(name),
    isCustom: true,
    sets: parseInt(sets) || 3,
    reps: parseInt(reps) || 10
  };

  LOCAL_EXERCISES.unshift(customEx);
  renderExerciseGrid();

  document.getElementById('customExerciseName').value = '';
  document.getElementById('customExerciseForm').style.display = 'none';
  showToast(`Created custom exercise "${name}"`, 'success');
}

/* ── UPDATE DAY COLUMN UI ── */
function updateDayColumn(day) {
  const col   = document.querySelector(`.day-column[data-day="${day}"]`);
  if (!col) return;

  const list  = col.querySelector('.exercise-list');
  const badge = col.querySelector('.exercise-count');
  const exs   = weeklyPlan[day] || [];

  badge.textContent = exs.length;
  badge.classList.toggle('zero', exs.length === 0);

  if (exs.length === 0) {
    list.innerHTML = '<div class="empty-state"><i class="bi bi-moon-stars"></i><p>Rest day</p></div>';
    return;
  }

  list.innerHTML = exs.map((ex, i) => `
    <div class="exercise-item">
      <div class="d-flex align-items-center justify-content-between mb-1">
        <div class="exercise-item-name">${ex.name}</div>
        <button class="btn btn-sm text-danger p-0 border-0" onclick="removeExercise('${day}',${i})" title="Remove">
          <i class="bi bi-trash-fill" style="font-size:0.8rem;"></i>
        </button>
      </div>
      <div class="exercise-item-meta"><i class="bi bi-tag me-1 text-success"></i>${ex.category}</div>
      <div class="exercise-item-meta"><i class="bi bi-trophy me-1 text-warning"></i>${ex.sets||3} sets × ${ex.reps||10} reps</div>
    </div>
  `).join('');
}

function removeExercise(day, index) {
  weeklyPlan[day].splice(index, 1);
  updateDayColumn(day);
  showToast('Exercise removed', 'info');
}

/* ── SAVE PLAN ── */
function savePlan() {
  const title = document.getElementById('programNameInput').value.trim() || 'Custom Workout Program';
  const total = Object.values(weeklyPlan).reduce((s, d) => s + d.length, 0);

  if (total === 0) {
    showToast('Please add at least one exercise to the plan or load a pre-built template.', 'warning');
    return;
  }

  const fullPlanPayload = {
    title: title,
    plan: weeklyPlan
  };

  localStorage.setItem('workout_plan_' + REQUEST_ID, JSON.stringify(fullPlanPayload));
  showToast('Workout plan saved successfully!', 'success');
  setTimeout(() => {
    window.location.href = 'index.php?r=trainer/createMealPlan&request_id=' + REQUEST_ID;
  }, 1200);
}

/* ── GENERATE AI PLAN (GEMINI) ── */
async function generateAIPlan(requestId) {
  const btn    = document.getElementById('generateAiBtn');
  const status = document.getElementById('aiStatus');
  const errBox = document.getElementById('aiError');
  const errMsg = document.getElementById('aiErrorMsg');

  btn.disabled  = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:.8rem;height:.8rem;border-width:2px;"></span> Generating…';
  status.style.display = 'flex';
  errBox.style.display = 'none';

  try {
    const response = await fetch('index.php?r=fitness/generateAiPlan', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ request_id: requestId })
    });

    if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
    const data = await response.json();

    if (!data.success) {
      errMsg.textContent = data.error || 'Failed to generate plan. Please try again.';
      errBox.style.display = 'block';
      btn.innerHTML = '<i class="bi bi-stars"></i> Retry Generate AI Plan';
      btn.disabled  = false;
      return;
    }

    if (data.workoutPlan) {
      ['MON','TUE','WED','THU','FRI','SAT','SUN'].forEach(day => {
        const ld  = day.toLowerCase();
        const exs = data.workoutPlan[day] || [];
        weeklyPlan[ld] = exs.map((ex, i) => ({
          id:          'ai_' + ld + '_' + i,
          name:        ex.name || 'Exercise',
          category:    'AI Suggested',
          equipment:   'Various',
          muscles:     'N/A',
          isCustom:    false,
          sets:        parseInt(ex.sets) || 3,
          reps:        parseInt(ex.reps) || 10
        }));
        updateDayColumn(ld);
      });
    }

    btn.innerHTML = '<i class="bi bi-check-circle"></i> AI Plan Generated!';
    showToast('AI Plan generated! Customize and save when ready.', 'success');

  } catch (error) {
    errMsg.textContent = 'Network error: ' + error.message;
    errBox.style.display = 'block';
    btn.innerHTML = '<i class="bi bi-stars"></i> Retry Generate AI Plan';
    btn.disabled  = false;
  } finally {
    status.style.display = 'none';
  }
}

/* ── TOAST HELPER ── */
function showToast(message, type = 'info') {
  const cls   = type==='success'?'alert-success':type==='warning'?'alert-warning':type==='danger'?'alert-danger':'alert-info';
  const icon  = type==='success'?'check-circle':type==='warning'?'exclamation-triangle':'info-circle';
  const toast = document.createElement('div');
  toast.className = `alert ${cls} position-fixed top-0 end-0 m-3 shadow-sm`;
  toast.style.zIndex = '9999';
  toast.style.borderRadius = '10px';
  toast.innerHTML = `<i class="bi bi-${icon} me-2"></i>${message}`;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
