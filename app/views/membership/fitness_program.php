<?php
declare(strict_types=1);
$pageTitle = 'My Fitness Program';
require __DIR__ . '/../partials/header.php';

$program          = $program ?? null;
$gymName          = $gymName ?? 'Your Gym';
$gymEquipmentList = $gymEquipmentList ?? [];
$showForm         = !$program || isset($_GET['regenerate']);

$daysOfWeek = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
?>

<style>
/* ── Fitness Program Page ────────────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.fp-page { font-family: 'Inter', sans-serif; }

/* Hero */
.fp-hero {
    background: linear-gradient(135deg, #0f2117 0%, #1B6B2A 60%, #2d8a42 100%);
    border-radius: 18px;
    padding: 2rem 2.4rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.75rem;
}
.fp-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
    pointer-events: none;
}
.fp-hero::after {
    content: '';
    position: absolute;
    bottom: -60px; left: -30px;
    width: 160px; height: 160px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
    pointer-events: none;
}
.fp-hero-content { position: relative; z-index: 2; }
.fp-hero h1 { font-size: 1.8rem; font-weight: 800; margin-bottom: .3rem; color: #fff !important; text-shadow: 0 1px 4px rgba(0,0,0,.4); }
.fp-hero p  { opacity: .9; font-size: .95rem; margin-bottom: 0; color: #fff !important; }

/* Form card */
.fp-form-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(27,107,42,.1);
    overflow: hidden;
    margin-bottom: 2rem;
}
.fp-form-header {
    background: linear-gradient(90deg, #1B6B2A, #2E8B3E);
    color: #fff;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: .6rem;
    font-weight: 600;
    font-size: 1rem;
}
.fp-form-body { padding: 1.5rem; }

/* Day checkbox pills */
.day-pills { display: flex; flex-wrap: wrap; gap: .5rem; }
.day-pill { position: relative; }
.day-pill input[type="checkbox"] { display: none; }
.day-pill label {
    display: inline-block;
    padding: .38rem .9rem;
    border-radius: 50px;
    border: 2px solid #c5e0c8;
    background: #f0f9f1;
    cursor: pointer;
    font-size: .85rem;
    font-weight: 500;
    color: #2E8B3E;
    transition: all .2s;
    user-select: none;
}
.day-pill input:checked + label {
    background: #1B6B2A;
    border-color: #1B6B2A;
    color: #fff;
    box-shadow: 0 2px 8px rgba(27,107,42,.35);
}
.day-pill label:hover { border-color: #2E8B3E; background: #e0f4e3; }

/* Equipment chips (read-only) */
.equip-chips { display: flex; flex-wrap: wrap; gap: .45rem; }
.equip-chip {
    background: linear-gradient(135deg,#e8f5e9,#f0fdf4);
    border: 1.5px solid #a5d6a7;
    border-radius: 50px;
    padding: .28rem .85rem;
    font-size: .8rem;
    font-weight: 600;
    color: #1B6B2A;
    display: flex; align-items: center; gap: .3rem;
}
.equip-chip i { font-size: .75rem; }

/* Submit buttons row */
.btn-generate {
    background: linear-gradient(135deg, #1B6B2A, #4CAF50);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: .75rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: .02em;
    transition: all .25s;
    box-shadow: 0 4px 16px rgba(27,107,42,.3);
}
.btn-generate:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(27,107,42,.4); color: #fff; }
.btn-generate:active { transform: translateY(0); }
.btn-manual {
    background: #fff;
    border: 2px solid #1B6B2A;
    color: #1B6B2A;
    border-radius: 12px;
    padding: .73rem 1.6rem;
    font-size: .95rem;
    font-weight: 600;
    letter-spacing: .02em;
    transition: all .22s;
}
.btn-manual:hover { background: #f0f9f1; color: #1B6B2A; }

/* Loading overlay */
#fp-loading-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(15,33,23,.82);
    z-index: 9999;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    backdrop-filter: blur(6px);
}
.fp-spinner {
    width: 72px; height: 72px;
    border: 6px solid rgba(255,255,255,.15);
    border-top-color: #4CAF50;
    border-radius: 50%;
    animation: fp-spin 1s linear infinite;
    margin-bottom: 1.2rem;
}
@keyframes fp-spin { to { transform: rotate(360deg); } }

/* Program display */
.fp-program-wrapper { margin-top: .5rem; }
.fp-split-badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: linear-gradient(135deg,#1B6B2A,#4CAF50);
    color: #fff;
    border-radius: 50px;
    padding: .45rem 1.2rem;
    font-size: .9rem;
    font-weight: 700;
    letter-spacing: .03em;
    box-shadow: 0 4px 14px rgba(27,107,42,.3);
    margin-bottom: 1rem;
}
.fp-rationale-card {
    background: linear-gradient(135deg, #f0f9f1, #e8f5e9);
    border-left: 4px solid #2E8B3E;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    font-size: .92rem;
    color: #1a2e1a;
    margin-bottom: 1.25rem;
}

/* Split options accordion */
.fp-split-options { margin-bottom: 1.4rem; }
.fp-split-options summary {
    list-style: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    color: #1B6B2A;
    font-size: .87rem;
    font-weight: 600;
    padding: .3rem 0;
    user-select: none;
}
.fp-split-options summary::-webkit-details-marker { display: none; }
.fp-split-options summary .chevron { transition: transform .2s; }
.fp-split-options[open] summary .chevron { transform: rotate(90deg); }
.fp-split-options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: .75rem;
    margin-top: .75rem;
    padding-bottom: .25rem;
}
.fp-split-card {
    background: #fff;
    border: 1.5px solid #c5e0c8;
    border-radius: 12px;
    padding: .9rem 1rem;
    box-shadow: 0 2px 8px rgba(27,107,42,.06);
    transition: border-color .2s;
}
.fp-split-card.recommended { border-color: #1B6B2A; background: #f0f9f1; }
.fp-split-card-name { font-weight: 700; font-size: .9rem; color: #1B6B2A; margin-bottom: .3rem; }
.fp-split-card-why { font-size: .8rem; color: #444; line-height: 1.5; margin-bottom: .4rem; }
.fp-split-card-equip { display: flex; flex-wrap: wrap; gap: .3rem; }
.fp-split-equip-pill {
    background: #e8f5e9;
    border-radius: 50px;
    padding: .1rem .55rem;
    font-size: .72rem;
    color: #1B6B2A;
    font-weight: 500;
}

/* Day tabs */
.fp-day-tabs { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.25rem; }
.fp-day-tab {
    padding: .42rem 1.1rem;
    border-radius: 50px;
    background: #f0f9f1;
    border: 2px solid #c5e0c8;
    color: #2E8B3E;
    font-size: .82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.fp-day-tab.active { background: #1B6B2A; border-color: #1B6B2A; color: #fff; box-shadow: 0 3px 10px rgba(27,107,42,.3); }
.fp-day-tab:hover:not(.active) { border-color: #2E8B3E; background: #e0f4e3; }

/* Day panels */
.fp-day-panel { display: none; }
.fp-day-panel.active { display: block; }
.fp-day-header {
    background: linear-gradient(90deg, #1B6B2A, #2E8B3E);
    color: #fff;
    border-radius: 12px 12px 0 0;
    padding: .9rem 1.4rem;
    display: flex; align-items: center; justify-content: space-between;
}
.fp-day-header h3 { font-size: 1rem; font-weight: 700; margin: 0; }
.fp-day-header .focus-badge {
    background: rgba(255,255,255,.2);
    border-radius: 50px;
    padding: .2rem .7rem;
    font-size: .78rem;
    font-weight: 500;
}

/* Exercise table */
.fp-exercise-table {
    width: 100%;
    background: #fff;
    border-radius: 0 0 12px 12px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(27,107,42,.08);
    margin-bottom: 1.25rem;
}
.fp-exercise-table thead { background: #f0f9f1; }
.fp-exercise-table thead th {
    padding: .65rem 1rem;
    font-size: .78rem;
    font-weight: 700;
    color: #2E8B3E;
    text-transform: uppercase;
    letter-spacing: .06em;
    border-bottom: 2px solid #c5e0c8;
}
.fp-exercise-table tbody tr { border-bottom: 1px solid #f0f4f0; transition: background .15s; }
.fp-exercise-table tbody tr:last-child { border-bottom: none; }
.fp-exercise-table tbody tr:hover { background: #f8fdf8; }
.fp-exercise-table td { padding: .75rem 1rem; font-size: .88rem; color: #1a2e1a; vertical-align: middle; }
.fp-exercise-name-btn {
    color: #1B6B2A;
    font-weight: 700;
    transition: color .2s;
    cursor: pointer;
}
.fp-exercise-name-btn:hover { color: #2E8B3E; text-decoration: underline !important; }
.fp-chip {
    display: inline-flex; align-items: center; gap: .3rem;
    background: #e8f5e9; color: #1B6B2A;
    border-radius: 50px; padding: .18rem .65rem;
    font-size: .78rem; font-weight: 600;
}
.fp-chip.rest { background: #fff3e0; color: #e65100; }
.fp-chip.equip { background: #e3f2fd; color: #1565c0; }
.fp-note-text { font-size: .78rem; color: #4a6a4a; font-style: italic; max-width: 240px; }

/* Notes cards */
.fp-note-card {
    border-radius: 12px; padding: 1.1rem 1.3rem;
    margin-bottom: 1rem;
    display: flex; gap: .85rem; align-items: flex-start;
}
.fp-note-card.progression { background: #fff8e1; border-left: 4px solid #f9a825; }
.fp-note-card.nutrition   { background: #e3f2fd; border-left: 4px solid #1976d2; }
.fp-note-icon { font-size: 1.5rem; line-height: 1; margin-top: .1rem; }
.fp-note-card h5 { font-size: .83rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .3rem; color: #555; }
.fp-note-card p { font-size: .88rem; margin: 0; color: #333; line-height: 1.55; }

/* Regenerate btn */
.btn-regenerate {
    background: transparent;
    border: 2px solid #1B6B2A; color: #1B6B2A;
    border-radius: 10px; padding: .5rem 1.3rem;
    font-size: .88rem; font-weight: 600; transition: all .2s;
}
.btn-regenerate:hover { background: #1B6B2A; color: #fff; }

/* Profile meta row */
.fp-meta-row { display: flex; flex-wrap: wrap; gap: .6rem; margin-bottom: 1.4rem; }
.fp-meta-pill {
    background: #f0f9f1; border: 1px solid #c5e0c8;
    border-radius: 50px; padding: .28rem .85rem;
    font-size: .78rem; color: #2E8B3E; font-weight: 500;
    display: flex; align-items: center; gap: .35rem;
}

/* ── Live Workout Modal Styling ─────────────────────────────────────────── */
.live-session-modal {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(10, 24, 15, 0.95);
    display: none; flex-direction: column;
    color: #fff; backdrop-filter: blur(8px);
    overflow-y: auto;
}
.live-topbar {
    background: rgba(255,255,255,0.08);
    padding: .9rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.12);
    display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .8rem;
}
.live-timer-badge {
    background: #1B6B2A; border-radius: 50px; padding: .35rem 1rem;
    font-weight: 700; font-size: 1rem; letter-spacing: .05em; color: #fff;
    box-shadow: 0 2px 10px rgba(27,107,42,0.4);
}
.live-box {
    max-width: 700px; margin: 1.5rem auto; width: 92%;
    background: #142a1b; border: 1px solid rgba(255,255,255,0.12);
    border-radius: 16px; padding: 1.5rem; box-shadow: 0 8px 32px rgba(0,0,0,0.5);
}
.live-set-row {
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px; padding: .75rem 1rem; margin-bottom: .6rem;
    display: flex; justify-content: space-between; align-items: center; gap: .8rem;
}
.live-set-row.done { background: rgba(76,175,80,0.18); border-color: #4CAF50; }
.live-rest-overlay {
    position: fixed; inset: 0; z-index: 10000;
    background: rgba(10, 24, 15, 0.92);
    display: none; flex-direction: column; align-items: center; justify-content: center;
    color: #fff; backdrop-filter: blur(6px);
}
.live-rest-circle {
    width: 140px; height: 140px; border-radius: 50%;
    border: 6px solid #4CAF50; display: flex; align-items: center; justify-content: center;
    font-size: 2.8rem; font-weight: 800; color: #a5d6a7; margin-bottom: 1rem;
    box-shadow: 0 0 30px rgba(76,175,80,0.4);
}
</style>

<!-- Loading Overlay -->
<div id="fp-loading-overlay">
    <div class="fp-spinner"></div>
    <h4 class="mb-1" style="font-weight:700;">Generating Your Program…</h4>
    <p style="opacity:.82;font-size:.9rem;text-align:center;">
        Gemini AI is analysing your gym's equipment<br>and building your personalised weekly plan.<br>
        <span style="opacity:.7;font-size:.82rem;">This may take up to 30 seconds.</span>
    </p>
</div>

<div class="fp-page">

<!-- Hero -->
<div class="fp-hero">
    <div class="fp-hero-content">
        <h1><i class="bi bi-lightning-charge-fill me-2" style="color:#a5d6a7;"></i>My Fitness Program</h1>
        <p>Powered by Gemini AI · Personalised for <strong><?= htmlspecialchars($gymName) ?></strong></p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 mb-4">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>

<?php if (!$tableReady): ?>
    <div class="alert alert-warning rounded-3">
        <i class="bi bi-database-exclamation me-2"></i>
        <strong>Setup required:</strong> Please run <code>sql/create_fitness_programs.sql</code> in phpMyAdmin to enable this feature.
    </div>

<?php elseif ($showForm): ?>
<!-- ═══════════════════════════════════════════════════════════════════
     PROFILE FORM
══════════════════════════════════════════════════════════════════════ -->
<div class="fp-form-card">
    <div class="fp-form-header">
        <i class="bi bi-person-lines-fill fs-5"></i>
        <?= $program ? 'Regenerate Your Fitness Program' : 'Create Your Fitness Program' ?>
    </div>
    <div class="fp-form-body">
        <?php if ($program): ?>
            <div class="alert alert-info rounded-3 mb-4">
                <i class="bi bi-info-circle me-1"></i> You already have a program. Submitting this form will <strong>replace</strong> your current one.
            </div>
        <?php endif; ?>

        <form id="fp-form" method="post" action="index.php?r=membership/generateprogram" onsubmit="showLoading()">
            <div class="row g-4">

                <!-- Training Goal -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="fp_goal">
                        <i class="bi bi-bullseye me-1 text-success"></i>Training Goal <span class="text-danger">*</span>
                    </label>
                    <select class="form-select rounded-3" name="goal" id="fp_goal" required>
                        <option value="">— Select goal —</option>
                        <?php foreach (['Bulking','Cutting','Maintaining'] as $g): ?>
                            <option value="<?= $g ?>" <?= ($program['goal'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text mt-1">
                        <strong>Bulking</strong> = gain muscle · <strong>Cutting</strong> = lose fat · <strong>Maintaining</strong> = stay fit
                    </div>
                </div>

                <!-- Experience Level -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="fp_exp">
                        <i class="bi bi-bar-chart-fill me-1 text-success"></i>Experience Level <span class="text-danger">*</span>
                    </label>
                    <select class="form-select rounded-3" name="experience_level" id="fp_exp" required>
                        <option value="">— Select level —</option>
                        <?php foreach (['Beginner','Intermediate','Advanced'] as $e): ?>
                            <option value="<?= $e ?>" <?= ($program['experience_level'] ?? '') === $e ? 'selected' : '' ?>><?= $e ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Session Length -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="fp_length">
                        <i class="bi bi-clock-fill me-1 text-success"></i>Session Length <span class="text-danger">*</span>
                    </label>
                    <select class="form-select rounded-3" name="session_length" id="fp_length" required>
                        <?php foreach ([30=>30,45=>45,60=>60,90=>90] as $v): ?>
                            <option value="<?= $v ?>" <?= ((int)($program['session_length'] ?? 60)) === $v ? 'selected' : '' ?>><?= $v ?> minutes</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Training Days -->
                <div class="col-12">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar3 me-1 text-success"></i>Training Days <span class="text-danger">*</span>
                        <span class="text-muted fw-normal ms-1 small" id="day-count-label"></span>
                    </label>
                    <?php
                        $savedDays = [];
                        if ($program) {
                            $savedDays = array_map('trim', explode(',', $program['list_of_weekdays'] ?? ''));
                        }
                    ?>
                    <div class="day-pills">
                        <?php foreach ($daysOfWeek as $day): ?>
                        <div class="day-pill">
                            <input type="checkbox" name="weekdays[]" id="day_<?= strtolower($day) ?>"
                                   value="<?= $day ?>"
                                   onchange="updateDayCount()"
                                   <?= in_array($day, $savedDays, true) ? 'checked' : '' ?>>
                            <label for="day_<?= strtolower($day) ?>"><?= substr($day, 0, 3) ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-text mt-1">Select the days you can train each week. The AI will match the best split.</div>
                </div>

                <!-- Gym Equipment (DB-sourced chips or textarea fallback) -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-dumbbell me-1 text-success"></i>Equipment at <?= htmlspecialchars($gymName) ?>
                    </label>

                    <?php if (!empty($gymEquipmentList)): ?>
                        <!-- DB equipment chips — read-only -->
                        <div class="equip-chips mb-2">
                            <?php foreach ($gymEquipmentList as $eq): ?>
                                <span class="equip-chip"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($eq) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Equipment pulled from your gym's inventory. The AI will only use what's listed here.
                        </div>
                        <!-- Hidden so the controller can fall back to it if needed -->
                        <input type="hidden" name="equipment" value="<?= htmlspecialchars(implode(', ', $gymEquipmentList)) ?>">
                    <?php else: ?>
                        <!-- Fallback free-text -->
                        <textarea class="form-control rounded-3" name="equipment" id="fp_equipment" rows="3"
                                  placeholder="e.g. Full gym with barbells, dumbbells, cables, machines&#10;or: Home dumbbells only (up to 30 kg)"
                                  required><?= htmlspecialchars($program['equipment'] ?? '') ?></textarea>
                        <div class="form-text">
                            <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
                            No equipment found in database for this gym. Describe what's available manually. Ask your gym owner to add equipment in the Equipment Manager.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Injuries -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="fp_injuries">
                        <i class="bi bi-bandaid me-1 text-success"></i>Injuries / Limitations
                        <span class="text-muted fw-normal small">(optional)</span>
                    </label>
                    <textarea class="form-control rounded-3" name="injuries_limitations" id="fp_injuries" rows="3"
                              placeholder="e.g. Left knee pain — avoid deep squats&#10;or: None"><?= htmlspecialchars($program['injuries_limitations'] ?? '') ?></textarea>
                    <div class="form-text">The AI will substitute or modify exercises for listed limitations.</div>
                </div>

                <!-- Submit row -->
                <div class="col-12 d-flex flex-wrap gap-3 align-items-center">
                    <button type="submit" class="btn btn-generate" id="fp-submit-btn">
                        <i class="bi bi-magic me-2"></i><?= $program ? 'Regenerate with AI' : 'Generate with AI' ?>
                    </button>
                    <button type="button" class="btn btn-manual" id="fp-manual-btn"
                            data-bs-toggle="modal" data-bs-target="#manualBuilderModal">
                        <i class="bi bi-pencil-square me-2"></i>Build My Own Program
                    </button>
                    <?php if ($program): ?>
                        <a href="index.php?r=membership/fitnessprogram" class="text-muted small">
                            <i class="bi bi-arrow-left me-1"></i>Back to my program
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ═══════════════════════════════════════════════════════════════════
     PROGRAM DISPLAY
══════════════════════════════════════════════════════════════════════ -->
<?php
    $prog         = $program['program'] ?? [];
    $schedule     = $prog['weekly_schedule'] ?? [];
    $splitOptions = $prog['split_options'] ?? [];
    $recSplit     = $prog['recommended_split'] ?? '';
?>
<div class="fp-program-wrapper">

    <!-- Header row -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <div class="fp-split-badge">
                <i class="bi bi-trophy-fill"></i>
                <?= htmlspecialchars($prog['split_name'] ?? $program['split_name'] ?? 'Custom Split') ?>
            </div>
            <p class="text-muted small mb-0">
                Generated on <?= date('F j, Y g:i A', strtotime($program['generated_at'])) ?> ·
                <?= htmlspecialchars($gymName) ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?r=membership/fitnessprogram&regenerate=1" class="btn btn-regenerate">
                <i class="bi bi-arrow-repeat me-1"></i>Regenerate
            </a>
            <button type="button" class="btn btn-manual"
                    data-bs-toggle="modal" data-bs-target="#manualBuilderModal">
                <i class="bi bi-pencil-square me-1"></i>Edit Manually
            </button>
        </div>
    </div>

    <!-- Profile meta pills -->
    <div class="fp-meta-row">
        <span class="fp-meta-pill"><i class="bi bi-bullseye"></i><?= htmlspecialchars($program['goal']) ?></span>
        <span class="fp-meta-pill"><i class="bi bi-bar-chart-fill"></i><?= htmlspecialchars($program['experience_level']) ?></span>
        <span class="fp-meta-pill"><i class="bi bi-calendar3"></i><?= $program['available_days'] ?> days/week · <?= htmlspecialchars($program['list_of_weekdays']) ?></span>
        <span class="fp-meta-pill"><i class="bi bi-clock"></i><?= $program['session_length'] ?> min sessions</span>
        <span class="fp-meta-pill"><i class="bi bi-dumbbell"></i><?= htmlspecialchars($program['equipment']) ?></span>
        <?php if ($program['injuries_limitations']): ?>
            <span class="fp-meta-pill"><i class="bi bi-bandaid"></i><?= htmlspecialchars($program['injuries_limitations']) ?></span>
        <?php endif; ?>
    </div>

    <!-- Why this split? (rationale) -->
    <?php if (!empty($prog['rationale'])): ?>
    <div class="fp-rationale-card">
        <i class="bi bi-chat-quote-fill text-success me-2"></i>
        <strong>Why this split?</strong> <?= htmlspecialchars($prog['rationale']) ?>
    </div>
    <?php endif; ?>

    <!-- Split options candidates (collapsible) -->
    <?php if (!empty($splitOptions)): ?>
    <details class="fp-split-options mb-3">
        <summary>
            <i class="bi bi-diagram-3 text-success"></i>
            Splits the AI considered
            <i class="bi bi-chevron-right chevron"></i>
        </summary>
        <div class="fp-split-options-grid">
            <?php foreach ($splitOptions as $opt): ?>
            <?php $isRec = strcasecmp($opt['name'] ?? '', $recSplit) === 0; ?>
            <div class="fp-split-card <?= $isRec ? 'recommended' : '' ?>">
                <div class="fp-split-card-name">
                    <?= htmlspecialchars($opt['name'] ?? '') ?>
                    <?php if ($isRec): ?><span class="badge bg-success ms-1" style="font-size:.65rem;">✓ Chosen</span><?php endif; ?>
                </div>
                <div class="fp-split-card-why"><?= htmlspecialchars($opt['why_it_fits'] ?? '') ?></div>
                <?php if (!empty($opt['equipment_used'])): ?>
                <div class="fp-split-card-equip">
                    <?php foreach ((array)$opt['equipment_used'] as $eq): ?>
                        <span class="fp-split-equip-pill"><?= htmlspecialchars($eq) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </details>
    <?php endif; ?>

    <!-- Day Tabs -->
    <?php if (!empty($schedule)): ?>
    <div class="fp-day-tabs" id="fp-day-tabs">
        <?php foreach ($schedule as $i => $dayBlock): ?>
            <button class="fp-day-tab <?= $i === 0 ? 'active' : '' ?>"
                    onclick="switchDay(<?= $i ?>)"
                    id="fp-tab-<?= $i ?>">
                <i class="bi bi-calendar-week me-1"></i><?= htmlspecialchars($dayBlock['day'] ?? "Day " . ($i+1)) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Day Panels -->
    <?php foreach ($schedule as $i => $dayBlock): ?>
    <div class="fp-day-panel <?= $i === 0 ? 'active' : '' ?>" id="fp-panel-<?= $i ?>">
        <div class="fp-day-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-fire fs-5"></i>
                <h3 class="mb-0"><?= htmlspecialchars($dayBlock['day'] ?? "Day " . ($i+1)) ?></h3>
                <?php if (!empty($dayBlock['focus'])): ?>
                    <span class="focus-badge"><?= htmlspecialchars($dayBlock['focus']) ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($dayBlock['exercises'])): ?>
            <button type="button" class="btn btn-sm btn-light text-success fw-bold rounded-pill px-3 shadow-sm"
                    onclick="startLiveWorkoutSession(<?= htmlspecialchars(json_encode($dayBlock['exercises'] ?? []), ENT_QUOTES) ?>, '<?= htmlspecialchars($dayBlock['day'] ?? '') ?>')">
                <i class="bi bi-play-circle-fill me-1 text-success"></i> 🚀 Start Workout
            </button>
            <?php endif; ?>
        </div>
        <table class="fp-exercise-table">
            <thead>
                <tr>
                    <th><i class="bi bi-hash me-1"></i>#</th>
                    <th>Exercise Name (Click for Form Guide 🎥)</th>
                    <th>Equipment Needed</th>
                    <th>Sets × Reps</th>
                    <th>Rest (Sec)</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($dayBlock['exercises'] ?? []) as $ei => $ex): ?>
                <tr>
                    <td><span class="text-muted fw-bold"><?= $ei + 1 ?></span></td>
                    <td>
                        <button type="button" class="btn btn-link p-0 text-start text-decoration-none fp-exercise-name-btn"
                                onclick="openExerciseGuide(<?= htmlspecialchars(json_encode($ex['name'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($ex['equipment_needed'] ?? ''), ENT_QUOTES) ?>)">
                            <i class="bi bi-info-circle-fill text-success me-1"></i>
                            <span class="fp-exercise-name"><?= htmlspecialchars($ex['name'] ?? '—') ?></span>
                            <span class="badge bg-light text-success border ms-1" style="font-size:.68rem;">Form & Video 🎥</span>
                        </button>
                    </td>
                    <td>
                        <?php if (!empty($ex['equipment_needed'])): ?>
                            <span class="fp-chip equip">
                                <i class="bi bi-gear-fill"></i>
                                <?= htmlspecialchars($ex['equipment_needed']) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="fp-chip">
                            <i class="bi bi-arrow-repeat"></i>
                            <?= (int)($ex['sets'] ?? 0) ?> Sets × <?= htmlspecialchars($ex['reps'] ?? '—') ?> Reps
                        </span>
                    </td>
                    <td>
                        <?php $restSec = (int)($ex['rest_seconds'] ?? 0); ?>
                        <span class="fp-chip rest">
                            <i class="bi bi-hourglass-split"></i>
                            <?= $restSec >= 60 ? floor($restSec/60) . 'm ' . ($restSec%60 > 0 ? $restSec%60 . 's' : '') : $restSec . 's rest' ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!empty($ex['notes'])): ?>
                            <span class="fp-note-text"><?= htmlspecialchars($ex['notes']) ?></span>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Progression & Nutrition Notes -->
    <div class="row g-3 mt-1">
        <?php if (!empty($prog['progression_notes'])): ?>
        <div class="col-md-6">
            <div class="fp-note-card progression">
                <div class="fp-note-icon">📈</div>
                <div>
                    <h5>Progression Notes</h5>
                    <p><?= nl2br(htmlspecialchars($prog['progression_notes'])) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($prog['nutrition_note'])): ?>
        <div class="col-md-6">
            <div class="fp-note-card nutrition">
                <div class="fp-note-icon">🥗</div>
                <div>
                    <h5>Nutrition Note</h5>
                    <p><?= nl2br(htmlspecialchars($prog['nutrition_note'])) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>
<?php endif; ?>

</div><!-- /.fp-page -->

<!-- ═══════════════════════════════════════════════════════════════════
     MANUAL PROGRAM BUILDER MODAL
══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="manualBuilderModal" tabindex="-1" aria-labelledby="manualBuilderLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header" style="background:linear-gradient(90deg,#1B6B2A,#2E8B3E);color:#fff;">
        <h5 class="modal-title fw-bold" id="manualBuilderLabel">
            <i class="bi bi-pencil-square me-2"></i>Build My Own Program
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- Step 1: basics -->
        <div class="row g-3 mb-3 pb-3 border-bottom">
            <div class="col-md-4">
                <label class="form-label fw-semibold small" for="mb_goal">Training Goal</label>
                <select class="form-select form-select-sm" id="mb_goal">
                    <option value="Maintaining">Maintaining</option>
                    <option value="Bulking">Bulking</option>
                    <option value="Cutting">Cutting</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small" for="mb_exp">Experience Level</label>
                <select class="form-select form-select-sm" id="mb_exp">
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small" for="mb_length">Session Length</label>
                <select class="form-select form-select-sm" id="mb_length">
                    <option value="30">30 min</option>
                    <option value="45">45 min</option>
                    <option value="60" selected>60 min</option>
                    <option value="90">90 min</option>
                </select>
            </div>
        </div>

        <!-- Step 2: Suggested Preset Workouts -->
        <div class="preset-card mb-4">
            <div class="preset-title">
                <i class="bi bi-lightning-charge-fill text-success"></i> Quick-Load Suggested Workout Routines
            </div>
            <div class="preset-buttons">
                <button type="button" class="btn-preset" onclick="mbLoadPreset('ppl')">
                    ⚡ Push / Pull / Legs (PPL) Split
                </button>
                <button type="button" class="btn-preset" onclick="mbLoadPreset('upper_lower')">
                    ⚡ Upper / Lower Body Split
                </button>
                <button type="button" class="btn-preset" onclick="mbLoadPreset('full_body')">
                    ⚡ Full Body 3-Day Split
                </button>
                <button type="button" class="btn-preset" onclick="mbLoadPreset('bro_split')">
                    ⚡ Bro Split (Body Part Split)
                </button>
                <button type="button" class="btn-preset" onclick="mbLoadPreset('dumbbells_only')">
                    ⚡ Dumbbells & Bench Only
                </button>
            </div>
            <div class="preset-equip-hint" id="preset-equip-text">
                <i class="bi bi-info-circle"></i> Click a routine above to auto-fill days, exercises, sets, reps, and required equipment.
            </div>
        </div>

        <!-- Step 3: pick days -->
        <p class="fw-semibold mb-2"><i class="bi bi-calendar3 text-success me-1"></i>Select Training Days</p>
        <div class="mb-day-select-row mb-3" id="mb-day-select">
            <?php foreach ($daysOfWeek as $day): ?>
            <div class="mb-day-checkbox">
                <input type="checkbox" id="mb_day_<?= strtolower($day) ?>" value="<?= $day ?>"
                       onchange="mbToggleDay('<?= $day ?>', this.checked)">
                <label for="mb_day_<?= strtolower($day) ?>"><?= substr($day,0,3) ?></label>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Step 4: exercise builder (dynamically added) -->
        <div id="mb-days-container">
            <p class="text-muted small fst-italic">Check the days above or pick a suggested routine to start adding exercises.</p>
        </div>

      </div>

      <div class="modal-footer gap-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-generate" onclick="mbSaveProgram()">
            <i class="bi bi-floppy-fill me-1"></i>Save My Program
        </button>
      </div>

    </div>
  </div>
</div>

<!-- Hidden form for manual submit -->
<form id="mb-submit-form" method="post" action="index.php?r=membership/savemanualprogram" style="display:none;">
    <input type="hidden" name="manual_program_json" id="mb-json-field">
    <input type="hidden" name="goal" id="mb-goal-field">
    <input type="hidden" name="experience_level" id="mb-exp-field">
    <input type="hidden" name="session_length" id="mb-len-field">
</form>


<!-- ═══════════════════════════════════════════════════════════════════
     EXERCISE FORM & VIDEO INSTRUCTION GUIDE MODAL
══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="exerciseGuideModal" tabindex="-1" aria-labelledby="exerciseGuideTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#0f2117,#1B6B2A);color:#fff;">
        <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="exerciseGuideTitle">
            <i class="bi bi-journal-check text-warning"></i>
            <span id="guide-modal-ex-name">Exercise Guide</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">

        <!-- Muscle & Equipment Row -->
        <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
            <span class="guide-badge" id="guide-modal-muscles"><i class="bi bi-bullseye me-1"></i>Target Muscles</span>
            <span class="guide-badge" style="background:#e3f2fd;color:#1565c0;border-color:#90caf9;" id="guide-modal-equip"><i class="bi bi-gear-fill me-1"></i>Equipment</span>
        </div>

        <!-- Instructions & Setup -->
        <div class="guide-step-box">
            <div class="guide-step-title">
                <i class="bi bi-1-circle-fill text-success"></i> Setup & Starting Position
            </div>
            <p class="mb-0 small text-dark" id="guide-modal-setup">Setup details...</p>
        </div>

        <div class="guide-step-box">
            <div class="guide-step-title">
                <i class="bi bi-2-circle-fill text-success"></i> Execution & Motion
            </div>
            <p class="mb-0 small text-dark" id="guide-modal-execution">Execution details...</p>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="guide-step-box h-100 mb-0">
                    <div class="guide-step-title text-success">
                        <i class="bi bi-check-circle-fill me-1"></i> Key Form Cues & Breathing
                    </div>
                    <ul class="guide-cue-list" id="guide-modal-cues">
                        <li>Form cue item...</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="guide-step-box h-100 mb-0" style="background:#fff5f5;border-color:#ffcdd2;">
                    <div class="guide-step-title text-danger">
                        <i class="bi bi-x-circle-fill me-1"></i> Common Mistakes to Avoid
                    </div>
                    <ul class="guide-cue-list text-danger" id="guide-modal-mistakes">
                        <li>Mistake item...</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Video Demonstration -->
        <div class="mt-3">
            <h6 class="fw-bold text-success d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-youtube text-danger fs-5"></i> Video Demonstration & Tutorial
            </h6>
            <div id="guide-modal-video-container">
                <!-- Video embed or link -->
            </div>
        </div>

      </div>
      <div class="modal-footer bg-light">
        <a id="guide-youtube-search-btn" href="#" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill fw-semibold">
            <i class="bi bi-youtube me-1"></i>Watch More Tutorials on YouTube
        </a>
        <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Close Guide</button>
      </div>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════════
     LIVE INTERACTIVE WORKOUT SESSION TRACKER MODAL
══════════════════════════════════════════════════════════════════════ -->
<div class="live-session-modal" id="liveWorkoutModal">
    <div class="live-topbar">
        <div class="d-flex align-items-center gap-3">
            <span class="live-timer-badge"><i class="bi bi-stopwatch me-1"></i><span id="live-elapsed-time">00:00</span></span>
            <span class="fw-bold text-white fs-6" id="live-day-title">Workout Session</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="closeLiveWorkoutSession()">
            <i class="bi bi-x-lg me-1"></i> End Workout
        </button>
    </div>

    <div class="live-box" id="live-content-box">
        <!-- Exercise Content Rendered Dynamically -->
    </div>
</div>

<!-- LIVE REST COUNTDOWN TIMER OVERLAY -->
<div class="live-rest-overlay" id="liveRestOverlay">
    <h3 class="fw-bold text-success mb-3"><i class="bi bi-moon-stars-fill me-2"></i>Rest & Recover</h3>
    <div class="live-rest-circle" id="live-rest-countdown">90</div>
    <p class="text-white-50 small mb-4" id="live-rest-next-label">Next Set</p>
    <button type="button" class="btn btn-outline-light rounded-pill px-4" onclick="skipLiveRestTimer()">
        <i class="bi bi-skip-forward-fill me-1"></i>Skip Rest
    </button>
</div>


<script>
/* ── Day count label ─────────────────────────────────────────────── */
function updateDayCount() {
    var checked = document.querySelectorAll('input[name="weekdays[]"]:checked').length;
    var lbl = document.getElementById('day-count-label');
    if (lbl) lbl.textContent = checked > 0 ? '(' + checked + ' day' + (checked > 1 ? 's' : '') + ' selected)' : '';
}
document.addEventListener('DOMContentLoaded', updateDayCount);

/* ── Loading overlay ─────────────────────────────────────────────── */
function showLoading() {
    var overlay = document.getElementById('fp-loading-overlay');
    if (overlay) overlay.style.display = 'flex';
}

/* ── Day tab switching ───────────────────────────────────────────── */
function switchDay(idx) {
    document.querySelectorAll('.fp-day-tab').forEach(function(t, i) {
        t.classList.toggle('active', i === idx);
    });
    document.querySelectorAll('.fp-day-panel').forEach(function(p, i) {
        p.classList.toggle('active', i === idx);
    });
}

/* ══════════════════════════════════════════════════════════════════
   LIVE WORKOUT SESSION TRACKER JS LOGIC
══════════════════════════════════════════════════════════════════ */
var liveState = {
    exercises: [],
    dayName: '',
    currentExIdx: 0,
    startTime: null,
    timerInterval: null,
    restInterval: null,
    completedCount: 0
};

function startLiveWorkoutSession(exercises, dayName) {
    if (!exercises || exercises.length === 0) {
        alert('No exercises found in this session.');
        return;
    }
    liveState.exercises = exercises;
    liveState.dayName = dayName || 'Today';
    liveState.currentExIdx = 0;
    liveState.startTime = Date.now();
    liveState.completedCount = 0;

    document.getElementById('live-day-title').textContent = liveState.dayName + ' Workout';
    document.getElementById('liveWorkoutModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Start elapsed stopwatch
    if (liveState.timerInterval) clearInterval(liveState.timerInterval);
    liveState.timerInterval = setInterval(updateLiveTimer, 1000);

    renderLiveExercise(0);
}

function updateLiveTimer() {
    var seconds = Math.floor((Date.now() - liveState.startTime) / 1000);
    var mins = Math.floor(seconds / 60);
    var secs = seconds % 60;
    var timeStr = (mins < 10 ? '0' + mins : mins) + ':' + (secs < 10 ? '0' + secs : secs);
    document.getElementById('live-elapsed-time').textContent = timeStr;
}

function renderLiveExercise(idx) {
    liveState.currentExIdx = idx;
    var ex = liveState.exercises[idx];
    var totalEx = liveState.exercises.length;

    var container = document.getElementById('live-content-box');
    var setsCount = parseInt(ex.sets || 3, 10);
    var restSec = parseInt(ex.rest_seconds || 90, 10);

    var html = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge bg-success" style="font-size:.8rem;">Exercise ${idx + 1} of ${totalEx}</span>
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill" onclick="openExerciseGuide('${htmlEscape(ex.name)}', '${htmlEscape(ex.equipment_needed || '')}')">
                <i class="bi bi-journal-text me-1"></i>View Form Guide
            </button>
        </div>
        <h2 class="fw-extrabold text-white mb-1" style="font-size:1.4rem;">${htmlEscape(ex.name)}</h2>
        <p class="text-white-50 small mb-3">
            <i class="bi bi-gear-fill text-success me-1"></i>${htmlEscape(ex.equipment_needed || 'Gym Equipment')} · 
            Target: <strong>${setsCount} Sets × ${htmlEscape(ex.reps || '10-12')} Reps</strong>
        </p>
        
        <div class="mb-4">
            <label class="form-label text-white-50 small fw-semibold">SET TRACKER & LOGGING</label>`;

    for (var s = 1; s <= setsCount; s++) {
        // Extract default target rep number
        var targetRepsStr = ex.reps || '10';
        var matchRep = targetRepsStr.match(/\d+/);
        var defaultRepsVal = matchRep ? matchRep[0] : '10';

        html += `
            <div class="live-set-row p-2 px-3 mb-2" id="live-setrow-${s}" style="background:rgba(255,255,255,0.08);border:1.5px solid rgba(255,255,255,0.15);border-radius:12px;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success font-weight-bold" style="font-size:0.85rem;">SET ${s}</span>
                    <span class="text-white-50 small">Target: ${htmlEscape(ex.reps || '10-12')}</span>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto" id="live-setinputs-${s}">
                    <div class="d-flex flex-column align-items-start">
                        <span class="text-white-50" style="font-size:0.68rem;font-weight:700;letter-spacing:.05em;">WEIGHT (KG)</span>
                        <input type="number" step="0.5" placeholder="e.g. 20" id="live-weight-${s}" 
                               class="form-control form-control-sm text-center fw-bold" 
                               style="width:95px;background:#ffffff !important;color:#000000 !important;font-size:0.95rem;border:2px solid #2E8B3E;box-shadow:0 2px 6px rgba(0,0,0,0.3);">
                    </div>
                    <div class="d-flex flex-column align-items-start">
                        <span class="text-white-50" style="font-size:0.68rem;font-weight:700;letter-spacing:.05em;">REPS DONE</span>
                        <input type="number" min="1" max="100" value="${defaultRepsVal}" id="live-reps-${s}" 
                               class="form-control form-control-sm text-center fw-bold" 
                               style="width:80px;background:#ffffff !important;color:#000000 !important;font-size:0.95rem;border:2px solid #2E8B3E;box-shadow:0 2px 6px rgba(0,0,0,0.3);">
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 mt-3 fw-bold" id="live-btn-${s}" onclick="completeLiveSet(${s}, ${setsCount}, ${restSec})">
                        <i class="bi bi-check-circle-fill me-1"></i>Done
                    </button>
                </div>
            </div>`;
    }

    html += `</div>`;

    if (idx < totalEx - 1) {
        html += `<button type="button" class="btn btn-success w-100 py-2 rounded-3 fw-bold mt-2" onclick="renderLiveExercise(${idx + 1})">
            Next Exercise: ${htmlEscape(liveState.exercises[idx + 1].name)} <i class="bi bi-arrow-right ms-1"></i>
        </button>`;
    } else {
        html += `<button type="button" class="btn btn-warning w-100 py-3 rounded-3 fw-bold mt-2 text-dark" onclick="finishWorkoutSession()">
            🎉 Finish & Save Workout
        </button>`;
    }

    container.innerHTML = html;
}

function completeLiveSet(setNum, totalSets, restSec) {
    var weightInput = document.getElementById('live-weight-' + setNum);
    var repsInput   = document.getElementById('live-reps-' + setNum);
    var inputsArea  = document.getElementById('live-setinputs-' + setNum);
    var row         = document.getElementById('live-setrow-' + setNum);

    var weightVal = weightInput ? weightInput.value.trim() : '0';
    var repsVal   = repsInput   ? repsInput.value.trim()   : '10';

    if (row) row.style.background = 'rgba(76,175,80,0.22)';
    if (row) row.style.borderColor = '#4CAF50';

    if (inputsArea) {
        inputsArea.innerHTML = `
            <span class="badge bg-success px-3 py-2" style="font-size:0.88rem;box-shadow:0 2px 8px rgba(76,175,80,0.3);">
                <i class="bi bi-check-circle-fill me-1"></i> Logged: <strong>${weightVal || '0'} kg</strong> × <strong>${repsVal} reps</strong>
            </span>`;
    }

    liveState.completedCount++;

    // Trigger rest countdown if not last set
    if (setNum < totalSets && restSec > 0) {
        startLiveRestTimer(restSec, 'Set ' + (setNum + 1));
    }
}

function startLiveRestTimer(seconds, nextLabel) {
    var overlay = document.getElementById('liveRestOverlay');
    var countdown = document.getElementById('live-rest-countdown');
    var label = document.getElementById('live-rest-next-label');

    label.textContent = 'Next up: ' + nextLabel;
    countdown.textContent = seconds;
    overlay.style.display = 'flex';

    if (liveState.restInterval) clearInterval(liveState.restInterval);
    var remaining = seconds;
    liveState.restInterval = setInterval(function() {
        remaining--;
        countdown.textContent = remaining;
        if (remaining <= 0) {
            skipLiveRestTimer();
        }
    }, 1000);
}

function skipLiveRestTimer() {
    if (liveState.restInterval) clearInterval(liveState.restInterval);
    document.getElementById('liveRestOverlay').style.display = 'none';
}

function finishWorkoutSession() {
    closeLiveWorkoutSession();
    alert('🎉 Awesome job! Workout completed and progress tracked successfully!');
}

function closeLiveWorkoutSession() {
    if (liveState.timerInterval) clearInterval(liveState.timerInterval);
    if (liveState.restInterval) clearInterval(liveState.restInterval);
    document.getElementById('liveWorkoutModal').style.display = 'none';
    document.getElementById('liveRestOverlay').style.display = 'none';
    document.body.style.overflow = 'auto';
}

/* ══════════════════════════════════════════════════════════════════
   EXERCISE FORM & VIDEO GUIDE DATABASE
══════════════════════════════════════════════════════════════════ */
var EXERCISE_GUIDE_DB = {
    "bench press": {
        muscles: "Chest (Pectoralis Major), Triceps, Front Shoulders",
        setup: "Lie flat on the bench with feet pressed firmly into the floor. Grip the barbell slightly wider than shoulder-width with wrists straight. Retract your shoulder blades into the bench.",
        execution: "Unrack the barbell and lower it under control to mid-chest level while tucking elbows at roughly a 45-degree angle. Press the bar back up explosively to full extension without locking elbows hard.",
        cues: ["Keep feet flat on floor for leg drive", "Inhale on the way down, exhale as you press up", "Keep shoulder blades pinched together throughout the lift"],
        mistakes: ["Flaring elbows out to 90 degrees (strains shoulders)", "Bouncing the bar off your chest", "Arching lower back off the bench excessively"],
        embedUrl: "https://www.youtube-nocookie.com/embed/rT7DgCr-3pg"
    },
    "barbell back squat": {
        muscles: "Quadriceps, Glutes, Hamstrings, Lower Back & Core",
        setup: "Step under the barbell resting it across your upper traps. Grip the bar firmly, pull elbows back, and unrack. Stand with feet shoulder-width apart, toes pointing slightly outward (15-30°).",
        execution: "Brace your core, hinge at hips and knees simultaneously. Lower your hips down until thighs are at least parallel to the floor. Drive through your mid-foot/heels to return to standing position.",
        cues: ["Keep chest proud and eyes looking straight ahead", "Push knees outward in line with your toes", "Take a deep breath and brace core before descending"],
        mistakes: ["Knees caving inward (valgus collapse)", "Rounding your spine at the bottom (butt wink)", "Rising onto toes instead of staying mid-foot"],
        embedUrl: "https://www.youtube-nocookie.com/embed/nFAscG0XUNY"
    },
    "barbell deadlift": {
        muscles: "Hamstrings, Glutes, Erector Spinae (Lower Back), Latissimus Dorsi, Traps",
        setup: "Stand with feet hip-width apart, bar over mid-foot. Bend at hips to grip the bar just outside legs. Pull your chest up to flatten back and engage lats ('wedge yourself').",
        execution: "Drive through the floor with legs while keeping the bar close to shins and thighs. Stand upright, squeezing glutes at the top without leaning back.",
        cues: ["Drag the bar up your legs", "Keep arms straight like ropes", "Brace your core like preparing for a punch"],
        mistakes: ["Rounding the lower spine", "Hitching or resting bar on knees during lockout", "Jerking the bar off the floor instead of creating tension"],
        embedUrl: "https://www.youtube-nocookie.com/embed/op9kVnSso6Q"
    },
    "lat pulldown": {
        muscles: "Latissimus Dorsi (Lats), Rhomboids, Rear Shoulders, Biceps",
        setup: "Sit at lat pulldown machine with thigh pads secured over knees. Grip the bar slightly wider than shoulder-width with palms facing forward. Lean back slightly (10-15°).",
        execution: "Pull the bar down toward your upper chest by driving elbows down and back. Squeeze lats at the bottom, then slowly control the bar back up to full overhead stretch.",
        cues: ["Think about pulling with your elbows, not hands", "Keep chest open and shoulders down away from ears", "Exhale as you pull down"],
        mistakes: ["Swinging body back aggressively to pull momentum", "Pulling bar down behind the neck (causes shoulder impingement)", "Not allowing full stretch at the top"],
        embedUrl: "https://www.youtube-nocookie.com/embed/CAwf7n6Luuc"
    },
    "overhead dumbbell press": {
        muscles: "Shoulders (Anterior & Lateral Deltoids), Triceps, Upper Chest",
        setup: "Sit on an upright bench or stand tall. Hold dumbbells at shoulder height with palms facing forward or neutral (semi-facing). Keep core tight.",
        execution: "Press dumbbells vertically overhead until arms are extended above shoulders without arching lower back. Lower back down to shoulder level with control.",
        cues: ["Keep ribs down and core braced", "Press straight up overhead, not forward", "Exhale as you press upward"],
        mistakes: ["Arching lower back excessively", "Bouncing at bottom position", "Using leg momentum on strict shoulder press"],
        embedUrl: "https://www.youtube-nocookie.com/embed/qEwKCR5JCog"
    },
    "leg press": {
        muscles: "Quadriceps, Glutes, Hamstrings",
        setup: "Sit in leg press machine with back and head flat against pads. Place feet shoulder-width apart in middle of footplate.",
        execution: "Release safety handles. Lower footplate by bending knees toward chest until knees form a 90-degree angle. Press through footplate to return to starting position without locking knees.",
        cues: ["Keep lower back pressed flat into seat pad", "Push through heels/midfoot", "Stop just short of locking knees hard"],
        mistakes: ["Allowing lower back to curl off back pad at bottom", "Locking knees out violently at top", "Placing feet too low (puts excess pressure on knees)"],
        embedUrl: "https://www.youtube-nocookie.com/embed/IZxyjWCy360"
    },
    "bent-over barbell row": {
        muscles: "Latissimus Dorsi, Rhomboids, Rear Delts, Mid/Lower Back",
        setup: "Stand with feet shoulder-width apart, holding barbell with overhand grip. Hinge forward at hips keeping back flat until torso is at a 45-degree angle.",
        execution: "Pull bar towards belly button, bringing elbows back past torso. Pause and squeeze shoulder blades, then lower bar under control.",
        cues: ["Keep neck neutral aligned with spine", "Pull towards belly button, not chest", "Brace abs tightly"],
        mistakes: ["Using momentum and standing upright during rep", "Rounding spine", "Flaring elbows excessively"],
        embedUrl: "https://www.youtube-nocookie.com/embed/VKFeB7jy8v0"
    },
    "dumbbell bicep curls": {
        muscles: "Biceps (Biceps Brachii), Brachialis",
        setup: "Stand upright holding dumbbells at sides with palms facing forward or neutral. Keep elbows tucked close to torso.",
        execution: "Curl weights upward toward shoulders while contracting biceps. Pause at top squeeze, then slowly lower dumbbells back down to full arm extension.",
        cues: ["Keep elbows pinned to your sides", "Control the negative (eccentric) phase for 2 seconds", "Squeeze bicep at peak contraction"],
        mistakes: ["Swinging torso for momentum", "Moving elbows forward to cheat", "Cutting ROM short at the bottom"],
        embedUrl: "https://www.youtube-nocookie.com/embed/ykJmrZ5v0Oo"
    }
};

function openExerciseGuide(exName, equipNeeded) {
    if (!exName) return;

    var cleanName = exName.trim();
    var key = cleanName.toLowerCase();
    
    // Look up exact or partial match in DB
    var match = null;
    Object.keys(EXERCISE_GUIDE_DB).forEach(function(k) {
        if (key.includes(k) || k.includes(key)) {
            match = EXERCISE_GUIDE_DB[k];
        }
    });

    // Populate modal titles and equipment
    document.getElementById('guide-modal-ex-name').textContent = cleanName;
    document.getElementById('guide-modal-equip').innerHTML = '<i class="bi bi-gear-fill me-1"></i>Equipment: ' + (equipNeeded || 'Standard Gym Equipment');

    if (match) {
        document.getElementById('guide-modal-muscles').innerHTML = '<i class="bi bi-bullseye me-1"></i>Muscles: ' + match.muscles;
        document.getElementById('guide-modal-setup').textContent = match.setup;
        document.getElementById('guide-modal-execution').textContent = match.execution;

        // Render Cues
        var cuesHtml = '';
        match.cues.forEach(function(c) { cuesHtml += '<li>' + c + '</li>'; });
        document.getElementById('guide-modal-cues').innerHTML = cuesHtml;

        // Render Mistakes
        var mistakesHtml = '';
        match.mistakes.forEach(function(m) { mistakesHtml += '<li>' + m + '</li>'; });
        document.getElementById('guide-modal-mistakes').innerHTML = mistakesHtml;

        // Render Video Embed
        if (match.embedUrl) {
            document.getElementById('guide-modal-video-container').innerHTML = 
                '<div class="guide-video-wrapper"><iframe src="' + match.embedUrl + '" title="' + cleanName + ' Form Video" allowfullscreen></iframe></div>';
        } else {
            renderSearchVideoFallback(cleanName);
        }

    } else {
        // Fallback generic guide for exercises not explicitly in dictionary
        document.getElementById('guide-modal-muscles').innerHTML = '<i class="bi bi-bullseye me-1"></i>Target Muscle Group: Specific to exercise';
        document.getElementById('guide-modal-setup').textContent = 'Position your body securely using the designated equipment. Ensure your spine is neutral, feet stable, and core engaged before initiating movement.';
        document.getElementById('guide-modal-execution').textContent = 'Perform movement through full comfortable range of motion. Control the eccentric (lowering) phase for 2 seconds and push/pull concentric phase with intent.';
        document.getElementById('guide-modal-cues').innerHTML = '<li>Maintain control throughout the entire rep</li><li>Inhale on lowering, exhale on exertion</li><li>Keep joint alignment neutral</li>';
        document.getElementById('guide-modal-mistakes').innerHTML = '<li>Using excessive momentum or ego lifting</li><li>Cutting range of motion short</li><li>Losing core brace or spinal position</li>';
        
        renderSearchVideoFallback(cleanName);
    }

    // Set YouTube search link button
    var searchUrl = 'https://www.youtube.com/results?search_query=how+to+do+' + encodeURIComponent(cleanName) + '+proper+form';
    document.getElementById('guide-youtube-search-btn').href = searchUrl;

    // Show bootstrap modal
    var modalEl = document.getElementById('exerciseGuideModal');
    var modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function renderSearchVideoFallback(cleanName) {
    var searchUrl = 'https://www.youtube.com/results?search_query=how+to+do+' + encodeURIComponent(cleanName) + '+proper+form';
    document.getElementById('guide-modal-video-container').innerHTML = 
        '<div class="alert alert-light border rounded-3 text-center my-2 p-3">' +
            '<i class="bi bi-youtube text-danger fs-3 mb-2 d-block"></i>' +
            '<p class="mb-2 fw-semibold small">Watch proper execution tutorial for <strong>' + cleanName + '</strong> on YouTube:</p>' +
            '<a href="' + searchUrl + '" target="_blank" class="btn btn-danger btn-sm rounded-pill font-weight-bold">' +
                '<i class="bi bi-play-circle-fill me-1"></i>Open Video Tutorial on YouTube' +
            '</a>' +
        '</div>';
}

/* ══════════════════════════════════════════════════════════════════
   PRESET ROUTINE TEMPLATES WITH SUGGESTED EQUIPMENT
══════════════════════════════════════════════════════════════════ */
var PRESET_ROUTINES = {
    ppl: {
        name: "Push / Pull / Legs (PPL)",
        equip: "Suggested Equipment: Barbell, Flat Bench, Squat Rack, Dumbbells, Lat Pulldown, Cable Machine, Leg Press",
        schedule: {
            "Monday": { focus: "Push (Chest, Shoulders, Triceps)", exercises: [
                { name: "Barbell Bench Press", sets: 4, reps: "8-10", rest: 120, equip: "Barbell + Flat Bench" },
                { name: "Overhead Dumbbell Press", sets: 3, reps: "10-12", rest: 90, equip: "Dumbbells" },
                { name: "Incline Dumbbell Press", sets: 3, reps: "10-12", rest: 90, equip: "Dumbbells + Incline Bench" },
                { name: "Tricep Cable Pushdown", sets: 3, reps: "12-15", rest: 60, equip: "Cable Machine" }
            ]},
            "Tuesday": { focus: "Pull (Back & Biceps)", exercises: [
                { name: "Bent-Over Barbell Row", sets: 4, reps: "8-10", rest: 120, equip: "Barbell + Plates" },
                { name: "Lat Pulldown", sets: 3, reps: "10-12", rest: 90, equip: "Lat Pulldown Machine" },
                { name: "Seated Cable Row", sets: 3, reps: "10-12", rest: 90, equip: "Cable Machine" },
                { name: "Dumbbell Bicep Curls", sets: 3, reps: "12-15", rest: 60, equip: "Dumbbells" }
            ]},
            "Wednesday": { focus: "Legs & Core", exercises: [
                { name: "Barbell Back Squat", sets: 4, reps: "8-10", rest: 120, equip: "Barbell + Squat Rack" },
                { name: "Leg Press", sets: 3, reps: "10-12", rest: 90, equip: "Leg Press Machine" },
                { name: "Romanian Deadlift", sets: 3, reps: "10-12", rest: 90, equip: "Barbell or Dumbbells" },
                { name: "Standing Calf Raises", sets: 4, reps: "15-20", rest: 60, equip: "Dumbbells or Bodyweight" }
            ]},
            "Thursday": { focus: "Push (Chest, Shoulders, Triceps)", exercises: [
                { name: "Dumbbell Bench Press", sets: 4, reps: "10-12", rest: 90, equip: "Dumbbells + Flat Bench" },
                { name: "Lateral Deltoid Raises", sets: 4, reps: "12-15", rest: 60, equip: "Dumbbells" },
                { name: "Dips / Push-ups", sets: 3, reps: "12-15", rest: 60, equip: "Parallel Bars or Bodyweight" },
                { name: "Skullcrushers", sets: 3, reps: "10-12", rest: 60, equip: "Barbell or EZ Bar" }
            ]},
            "Friday": { focus: "Pull (Back & Biceps)", exercises: [
                { name: "Lat Pulldown (Wide Grip)", sets: 4, reps: "10-12", rest: 90, equip: "Lat Pulldown Machine" },
                { name: "Single-Arm Dumbbell Row", sets: 3, reps: "10-12", rest: 90, equip: "Dumbbell + Flat Bench" },
                { name: "Face Pulls", sets: 4, reps: "15", rest: 60, equip: "Cable Machine" },
                { name: "Hammer Curls", sets: 3, reps: "12-15", rest: 60, equip: "Dumbbells" }
            ]},
            "Saturday": { focus: "Legs & Abs", exercises: [
                { name: "Leg Press", sets: 4, reps: "10-12", rest: 90, equip: "Leg Press Machine" },
                { name: "Lying Leg Curls", sets: 3, reps: "12-15", rest: 60, equip: "Leg Curl Machine" },
                { name: "Walking Dumbbell Lunges", sets: 3, reps: "12 reps/leg", rest: 90, equip: "Dumbbells" },
                { name: "Hanging Leg Raises", sets: 3, reps: "15", rest: 60, equip: "Pull-up Bar" }
            ]}
        }
    },
    upper_lower: {
        name: "Upper / Lower Body Split",
        equip: "Suggested Equipment: Barbell, Dumbbells, Bench, Squat Rack, Cable Machine",
        schedule: {
            "Monday": { focus: "Upper Body A", exercises: [
                { name: "Bench Press", sets: 4, reps: "8-10", rest: 120, equip: "Barbell + Flat Bench" },
                { name: "Bent-Over Barbell Row", sets: 4, reps: "8-10", rest: 120, equip: "Barbell + Plates" },
                { name: "Overhead Dumbbell Press", sets: 3, reps: "10-12", rest: 90, equip: "Dumbbells" },
                { name: "Lat Pulldown", sets: 3, reps: "10-12", rest: 90, equip: "Lat Pulldown Machine" },
                { name: "Dumbbell Bicep Curls", sets: 3, reps: "12", rest: 60, equip: "Dumbbells" }
            ]},
            "Tuesday": { focus: "Lower Body A", exercises: [
                { name: "Barbell Back Squat", sets: 4, reps: "8-10", rest: 120, equip: "Barbell + Squat Rack" },
                { name: "Romanian Deadlift", sets: 3, reps: "10-12", rest: 90, equip: "Barbell" },
                { name: "Leg Press", sets: 3, reps: "12-15", rest: 90, equip: "Leg Press Machine" },
                { name: "Standing Calf Raises", sets: 4, reps: "15", rest: 60, equip: "Dumbbells" }
            ]},
            "Thursday": { focus: "Upper Body B", exercises: [
                { name: "Incline Dumbbell Press", sets: 4, reps: "10-12", rest: 90, equip: "Dumbbells + Incline Bench" },
                { name: "Seated Cable Row", sets: 4, reps: "10-12", rest: 90, equip: "Cable Machine" },
                { name: "Dumbbell Lateral Raise", sets: 3, reps: "12-15", rest: 60, equip: "Dumbbells" },
                { name: "Tricep Cable Pushdown", sets: 3, reps: "12-15", rest: 60, equip: "Cable Machine" }
            ]},
            "Friday": { focus: "Lower Body B", exercises: [
                { name: "Barbell Deadlift", sets: 3, reps: "6-8", rest: 150, equip: "Barbell + Plates" },
                { name: "Dumbbell Goblet Squat", sets: 3, reps: "10-12", rest: 90, equip: "Dumbbells" },
                { name: "Walking Lunges", sets: 3, reps: "12 reps/leg", rest: 90, equip: "Dumbbells" },
                { name: "Ab Crunches", sets: 3, reps: "20", rest: 60, equip: "Bodyweight / Mat" }
            ]}
        }
    },
    full_body: {
        name: "Full Body 3-Day Split",
        equip: "Suggested Equipment: Barbell, Dumbbells, Bench, Squat Rack",
        schedule: {
            "Monday": { focus: "Full Body A", exercises: [
                { name: "Barbell Back Squat", sets: 4, reps: "8-10", rest: 120, equip: "Barbell + Squat Rack" },
                { name: "Bench Press", sets: 4, reps: "8-10", rest: 120, equip: "Barbell + Flat Bench" },
                { name: "Lat Pulldown", sets: 3, reps: "10-12", rest: 90, equip: "Lat Pulldown Machine" },
                { name: "Overhead Dumbbell Press", sets: 3, reps: "10-12", rest: 90, equip: "Dumbbells" }
            ]},
            "Wednesday": { focus: "Full Body B", exercises: [
                { name: "Barbell Deadlift", sets: 3, reps: "6-8", rest: 150, equip: "Barbell + Plates" },
                { name: "Overhead Dumbbell Press", sets: 4, reps: "8-10", rest: 120, equip: "Dumbbells" },
                { name: "Incline Dumbbell Press", sets: 3, reps: "10-12", rest: 90, equip: "Dumbbells + Bench" },
                { name: "Dumbbell Bicep Curls", sets: 3, reps: "12", rest: 60, equip: "Dumbbells" }
            ]},
            "Friday": { focus: "Full Body C", exercises: [
                { name: "Leg Press", sets: 4, reps: "10-12", rest: 90, equip: "Leg Press Machine" },
                { name: "Bent-Over Barbell Row", sets: 4, reps: "10-12", rest: 90, equip: "Barbell + Plates" },
                { name: "Dumbbell Chest Flyes", sets: 3, reps: "12-15", rest: 60, equip: "Dumbbells + Flat Bench" },
                { name: "Tricep Cable Pushdown", sets: 3, reps: "12", rest: 60, equip: "Cable Machine" }
            ]}
        }
    },
    bro_split: {
        name: "Bro Split (Body Part)",
        equip: "Suggested Equipment: Barbells, Dumbbells, Bench, Cables, Lat Pulldown, Leg Press",
        schedule: {
            "Monday": { focus: "Chest Day", exercises: [
                { name: "Bench Press", sets: 4, reps: "8-10", rest: 120, equip: "Barbell + Flat Bench" },
                { name: "Incline Dumbbell Press", sets: 3, reps: "10-12", rest: 90, equip: "Dumbbells + Incline Bench" },
                { name: "Cable Chest Crossover", sets: 3, reps: "12-15", rest: 60, equip: "Cable Machine" },
                { name: "Push-ups", sets: 3, reps: "15", rest: 60, equip: "Bodyweight" }
            ]},
            "Tuesday": { focus: "Back Day", exercises: [
                { name: "Lat Pulldown", sets: 4, reps: "8-10", rest: 90, equip: "Lat Pulldown Machine" },
                { name: "Bent-Over Barbell Row", sets: 4, reps: "8-10", rest: 120, equip: "Barbell + Plates" },
                { name: "Single-Arm Dumbbell Row", sets: 3, reps: "10-12", rest: 90, equip: "Dumbbells + Flat Bench" },
                { name: "Barbell Deadlift", sets: 3, reps: "8-10", rest: 120, equip: "Barbell + Plates" }
            ]},
            "Wednesday": { focus: "Shoulders & Traps", exercises: [
                { name: "Overhead Dumbbell Press", sets: 4, reps: "8-10", rest: 120, equip: "Dumbbells" },
                { name: "Lateral Deltoid Raises", sets: 4, reps: "12-15", rest: 60, equip: "Dumbbells" },
                { name: "Front Dumbbell Raises", sets: 3, reps: "12", rest: 60, equip: "Dumbbells" },
                { name: "Barbell Shrugs", sets: 4, reps: "12-15", rest: 60, equip: "Barbell" }
            ]},
            "Thursday": { focus: "Leg Day", exercises: [
                { name: "Barbell Back Squat", sets: 4, reps: "8-10", rest: 120, equip: "Barbell + Squat Rack" },
                { name: "Leg Press", sets: 3, reps: "10-12", rest: 90, equip: "Leg Press Machine" },
                { name: "Lying Leg Curls", sets: 3, reps: "12", rest: 60, equip: "Leg Curl Machine" },
                { name: "Standing Calf Raises", sets: 4, reps: "15-20", rest: 60, equip: "Dumbbells" }
            ]},
            "Friday": { focus: "Arm Day (Biceps & Triceps)", exercises: [
                { name: "Dumbbell Bicep Curls", sets: 4, reps: "10-12", rest: 60, equip: "Dumbbells" },
                { name: "Tricep Cable Pushdown", sets: 4, reps: "10-12", rest: 60, equip: "Cable Machine" },
                { name: "Hammer Curls", sets: 3, reps: "12", rest: 60, equip: "Dumbbells" },
                { name: "Overhead Dumbbell Extension", sets: 3, reps: "12", rest: 60, equip: "Dumbbells" }
            ]}
        }
    },
    dumbbells_only: {
        name: "Dumbbells & Bench Only",
        equip: "Suggested Equipment: Pair of Adjustable Dumbbells, Flat Bench, Mat",
        schedule: {
            "Monday": { focus: "Upper Body (Dumbbells)", exercises: [
                { name: "Dumbbell Bench Press", sets: 4, reps: "10-12", rest: 90, equip: "Dumbbells + Flat Bench" },
                { name: "Single-Arm Dumbbell Row", sets: 4, reps: "10-12", rest: 90, equip: "Dumbbell + Flat Bench" },
                { name: "Overhead Dumbbell Press", sets: 3, reps: "10-12", rest: 90, equip: "Dumbbells + Bench" },
                { name: "Dumbbell Bicep Curls", sets: 3, reps: "12-15", rest: 60, equip: "Dumbbells" }
            ]},
            "Wednesday": { focus: "Lower Body & Core (Dumbbells)", exercises: [
                { name: "Dumbbell Goblet Squat", sets: 4, reps: "10-12", rest: 90, equip: "Dumbbell" },
                { name: "Romanian Deadlift", sets: 4, reps: "10-12", rest: 90, equip: "Dumbbells" },
                { name: "Walking Dumbbell Lunges", sets: 3, reps: "12 reps/leg", rest: 90, equip: "Dumbbells" },
                { name: "Standing Calf Raises", sets: 4, reps: "15", rest: 60, equip: "Dumbbell" }
            ]},
            "Friday": { focus: "Full Body Conditioning", exercises: [
                { name: "Dumbbell Press", sets: 4, reps: "10-12", rest: 90, equip: "Dumbbells" },
                { name: "Single-Arm Dumbbell Row", sets: 3, reps: "10 reps/side", rest: 90, equip: "Dumbbells" },
                { name: "Overhead Dumbbell Press", sets: 3, reps: "12-15", rest: 60, equip: "Dumbbells" },
                { name: "Hammer Curls", sets: 3, reps: "12", rest: 60, equip: "Dumbbells" }
            ]}
        }
    }
};

function mbLoadPreset(key) {
    var preset = PRESET_ROUTINES[key];
    if (!preset) return;

    // Update equipment text hint
    var hint = document.getElementById('preset-equip-text');
    if (hint) {
        hint.innerHTML = '<i class="bi bi-gear-fill text-success"></i> <strong>' + preset.equip + '</strong>';
    }

    // Reset current selected days
    mbSelectedDays.forEach(function(d) {
        var cb = document.getElementById('mb_day_' + d.toLowerCase());
        if (cb) cb.checked = false;
        var block = document.getElementById('mb-block-' + d);
        if (block) block.remove();
    });
    mbSelectedDays = [];
    mbExCounter = {};

    // Check new days and populate exercises
    Object.keys(preset.schedule).forEach(function(day) {
        var cb = document.getElementById('mb_day_' + day.toLowerCase());
        if (cb) cb.checked = true;
        mbSelectedDays.push(day);
        mbAddDayBlock(day, false); // create block without auto blank row

        var container = document.getElementById('mb-exercises-' + day);
        if (!container) return;
        container.innerHTML = ''; // clear

        preset.schedule[day].exercises.forEach(function(ex) {
            mbAddExercise(day, ex.name, ex.sets, ex.reps, ex.rest, ex.equip);
        });
    });

    mbRenderEmpty();
}

/* ══════════════════════════════════════════════════════════════════
   MANUAL BUILDER LOGIC
══════════════════════════════════════════════════════════════════ */
var MB_DAYS_ORDER = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
var mbSelectedDays = []; // ordered array of selected day names

function mbToggleDay(day, checked) {
    if (checked) {
        if (!mbSelectedDays.includes(day)) mbSelectedDays.push(day);
        // Keep order
        mbSelectedDays.sort((a, b) => MB_DAYS_ORDER.indexOf(a) - MB_DAYS_ORDER.indexOf(b));
        mbAddDayBlock(day, true);
    } else {
        mbSelectedDays = mbSelectedDays.filter(d => d !== day);
        var el = document.getElementById('mb-block-' + day);
        if (el) el.remove();
    }
    mbRenderEmpty();
}

function mbRenderEmpty() {
    var cont = document.getElementById('mb-days-container');
    if (mbSelectedDays.length === 0) {
        if (!cont.querySelector('.mb-empty-hint')) {
            var hint = document.createElement('p');
            hint.className = 'text-muted small fst-italic mb-empty-hint';
            hint.textContent = 'Check the days above or pick a suggested routine to start adding exercises.';
            cont.innerHTML = '';
            cont.appendChild(hint);
        }
    } else {
        var hint = cont.querySelector('.mb-empty-hint');
        if (hint) hint.remove();
    }
}

function mbAddDayBlock(day, autoAddRow) {
    var cont = document.getElementById('mb-days-container');
    var hint = cont.querySelector('.mb-empty-hint');
    if (hint) hint.remove();

    // Insert in correct day order
    var block = document.createElement('div');
    block.className = 'mb-day-block';
    block.id = 'mb-block-' + day;
    block.innerHTML = `
        <div class="mb-day-title">
            <i class="bi bi-calendar-week text-success"></i> ${day}
            <span class="badge bg-secondary ms-auto" style="font-size:.7rem;">
                <span id="mb-ex-count-${day}">0</span> exercise(s)
            </span>
        </div>
        <div class="mb-exercise-header-row">
            <span>Exercise Name</span>
            <span>Sets</span>
            <span>Reps</span>
            <span>Rest (Sec)</span>
            <span>Equipment Needed</span>
            <span></span>
        </div>
        <div id="mb-exercises-${day}"></div>
        <button type="button" class="mb-add-exercise-btn mt-2" onclick="mbAddExercise('${day}')">
            <i class="bi bi-plus-circle me-1"></i>Add Exercise
        </button>`;

    // Insert before the next day block (maintain order)
    var inserted = false;
    var dayIdx = MB_DAYS_ORDER.indexOf(day);
    var allBlocks = cont.querySelectorAll('.mb-day-block');
    for (var i = 0; i < allBlocks.length; i++) {
        var blockDay = allBlocks[i].id.replace('mb-block-', '');
        if (MB_DAYS_ORDER.indexOf(blockDay) > dayIdx) {
            cont.insertBefore(block, allBlocks[i]);
            inserted = true;
            break;
        }
    }
    if (!inserted) cont.appendChild(block);

    // Auto-add one empty exercise row if requested
    if (autoAddRow !== false) {
        mbAddExercise(day);
    }
}

var mbExCounter = {};
function mbAddExercise(day, name, sets, reps, rest, equip) {
    if (!mbExCounter[day]) mbExCounter[day] = 0;
    mbExCounter[day]++;
    var idx = mbExCounter[day];

    name  = name  !== undefined ? name  : '';
    sets  = sets  !== undefined ? sets  : 3;
    reps  = reps  !== undefined ? reps  : '10-12';
    rest  = rest  !== undefined ? rest  : 90;
    equip = equip !== undefined ? equip : '';

    var container = document.getElementById('mb-exercises-' + day);
    if (!container) return;

    var row = document.createElement('div');
    row.className = 'mb-exercise-row';
    row.id = 'mb-exrow-' + day + '-' + idx;
    row.innerHTML = `
        <input type="text" value="${htmlEscape(name)}" placeholder="e.g. Bench Press" id="mb-name-${day}-${idx}" class="form-control form-control-sm">
        <input type="number" min="1" max="15" value="${sets}" title="Sets (e.g. 3)" id="mb-sets-${day}-${idx}" class="form-control form-control-sm text-center">
        <input type="text" value="${htmlEscape(reps)}" placeholder="10-12" title="Reps (e.g. 10-12)" id="mb-reps-${day}-${idx}" class="form-control form-control-sm text-center">
        <input type="number" min="0" max="600" value="${rest}" title="Rest in seconds (e.g. 90)" id="mb-rest-${day}-${idx}" class="form-control form-control-sm text-center">
        <input type="text" value="${htmlEscape(equip)}" placeholder="e.g. Barbell + Bench" id="mb-equip-${day}-${idx}" class="form-control form-control-sm">
        <button type="button" class="mb-remove-btn" onclick="mbRemoveExercise('${day}', ${idx})" title="Remove Exercise">
            <i class="bi bi-x-circle-fill"></i>
        </button>`;
    container.appendChild(row);
    mbUpdateCount(day);
}

function htmlEscape(str) {
    return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function mbRemoveExercise(day, idx) {
    var row = document.getElementById('mb-exrow-' + day + '-' + idx);
    if (row) row.remove();
    mbUpdateCount(day);
}

function mbUpdateCount(day) {
    var container = document.getElementById('mb-exercises-' + day);
    if (!container) return;
    var count = container.querySelectorAll('.mb-exercise-row').length;
    var badge = document.getElementById('mb-ex-count-' + day);
    if (badge) badge.textContent = count;
}

function mbSaveProgram() {
    var weeklySchedule = [];

    if (mbSelectedDays.length === 0) {
        alert('Please select at least one training day or pick a suggested routine.');
        return;
    }

    mbSelectedDays.forEach(function(day) {
        var container = document.getElementById('mb-exercises-' + day);
        if (!container) return;
        var rows = container.querySelectorAll('.mb-exercise-row');
        var exercises = [];

        rows.forEach(function(row) {
            var id = row.id.replace('mb-exrow-' + day + '-', '');
            var name = (document.getElementById('mb-name-' + day + '-' + id) || {}).value || '';
            name = name.trim();
            if (name === '') return; // skip empty rows

            var sets  = parseInt((document.getElementById('mb-sets-' + day + '-' + id) || {}).value || '3', 10);
            var reps  = (document.getElementById('mb-reps-' + day + '-' + id) || {}).value || '10-12';
            var rest  = parseInt((document.getElementById('mb-rest-' + day + '-' + id) || {}).value || '90', 10);
            var equip = (document.getElementById('mb-equip-' + day + '-' + id) || {}).value || '';

            exercises.push({
                name: name,
                equipment_needed: equip.trim(),
                sets: sets,
                reps: reps.trim(),
                rest_seconds: rest,
                notes: ''
            });
        });

        var focus = exercises.length > 0 ? 'Custom Workout' : 'Rest Day';
        weeklySchedule.push({ day: day, focus: focus, exercises: exercises });
    });

    if (weeklySchedule.length === 0) {
        alert('No valid exercises found. Please add at least one exercise.');
        return;
    }

    // Fill hidden form
    document.getElementById('mb-json-field').value = JSON.stringify(weeklySchedule);
    document.getElementById('mb-goal-field').value = document.getElementById('mb_goal').value;
    document.getElementById('mb-exp-field').value  = document.getElementById('mb_exp').value;
    document.getElementById('mb-len-field').value  = document.getElementById('mb_length').value;

    document.getElementById('mb-submit-form').submit();
}
</script>

<!-- ══ EXERCISE FORM GUIDE & VIDEO MODAL ══ -->
<div class="modal fade" id="exerciseFormGuideModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
      <div class="modal-header bg-dark text-white border-0 py-3">
        <h5 class="modal-title fw-bold" id="exGuideTitle"><i class="bi bi-journal-text me-2 text-success"></i>Exercise Guide</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4 bg-light">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6 rounded-pill" id="exGuideCategory">Category</span>
          <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 fs-6 rounded-pill" id="exGuideEquipment">Equipment</span>
        </div>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-body">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check me-2 text-success"></i>Step-by-Step Form &amp; Execution Guide</h6>
            <ol class="ps-3 mb-3 text-secondary" id="exGuideSteps" style="line-height: 1.7; font-size: 0.92rem;"></ol>
            <div class="alert alert-success border-0 bg-success-subtle text-success mb-0 rounded-3 p-3" style="font-size: 0.88rem;">
              <i class="bi bi-lightbulb-fill me-2"></i><strong id="exGuideTips">Pro Form Tip: Keep core engaged and movement controlled.</strong>
            </div>
          </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body text-center p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-youtube me-2 text-danger"></i>Video Demonstration &amp; Form Tutorial</h6>
            <p class="text-muted small mb-3">Watch proper posture, grip, and motion video guide below:</p>
            <div class="d-grid gap-2 col-md-10 mx-auto">
              <a href="#" id="exGuideYoutubeBtn" target="_blank" class="btn btn-danger btn-lg rounded-pill fw-bold py-2 shadow-sm">
                <i class="bi bi-play-btn-fill me-2"></i>Watch HD Video Tutorial on YouTube 🎥
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const FORM_GUIDES = {
  'bench press': {
    steps: [
      "Lie flat on bench with feet planted on floor. Retract shoulder blades back and down.",
      "Grip barbell slightly wider than shoulder-width with wrists firm and straight.",
      "Unrack bar under control and lower it steadily to your mid-chest while inhaling.",
      "Press bar powerfully upward until arms extend, driving through palms while exhaling."
    ],
    tips: "Keep your elbows tucked at ~45° to protect shoulder joints; avoid flaring elbows wide."
  },
  'squat': {
    steps: [
      "Place barbell on upper traps with feet shoulder-width apart, toes pointed slightly outward.",
      "Brace core tightly, push hips back, and bend knees to lower into a deep squat.",
      "Descend until thighs are parallel to the floor, keeping chest elevated.",
      "Drive through heels and mid-foot to stand back up powerfully while exhaling."
    ],
    tips: "Ensure knees track over toes and avoid letting knees buckle inward."
  },
  'deadlift': {
    steps: [
      "Stand with feet hip-width apart, bar over mid-foot. Hinge at hips to grab the bar.",
      "Engage lats, straighten lower back, and take a deep breath to brace core.",
      "Drive floor away with legs and pull hips forward to stand tall at lockout.",
      "Hinge at hips to lower bar back to the floor under complete control."
    ],
    tips: "Keep the bar close to your body throughout; do not round your lumbar spine."
  },
  'lat pulldown': {
    steps: [
      "Sit securely at lat pulldown machine with knees locked under pads. Grip bar wide.",
      "Lean back slightly (~10-15°) and initiate pull by pulling shoulder blades downward.",
      "Pull bar down until it lightly touches your upper chest while exhaling.",
      "Slowly extend arms upward to full overhead stretch while inhaling."
    ],
    tips: "Focus on pulling with your elbows rather than yanking with hands."
  },
  'overhead press': {
    steps: [
      "Hold barbell at collarbone level with elbows slightly forward.",
      "Brace core and squeeze glutes. Press bar vertically overhead, clearing your head.",
      "Lock arms overhead with head slightly forward at top of motion.",
      "Lower bar back down under control to upper chest."
    ],
    tips: "Avoid arching lower back excessively; keep core locked tight."
  },
  'bicep curl': {
    steps: [
      "Stand tall holding weights with palms facing forward.",
      "Keep upper arms stationary at sides, curl weights toward shoulders.",
      "Squeeze biceps tightly at top peak contraction for 1 second.",
      "Lower weights steadily back down to full extension."
    ],
    tips: "Do not swing hips or use body momentum to raise weights."
  },
  'tricep pushdown': {
    steps: [
      "Attach bar or rope to high cable pulley. Hinge slightly forward at hips.",
      "Pin elbows tightly to your sides throughout whole set.",
      "Extend arms down until fully locked out, flexing triceps.",
      "Slowly return hands to chest height under control."
    ],
    tips: "Keep elbows fixed; do not let shoulders swing."
  }
};

function openExerciseGuide(name, equipment, category) {
  document.getElementById('exGuideTitle').innerHTML = '<i class="bi bi-journal-text me-2 text-success"></i>' + name;
  document.getElementById('exGuideCategory').textContent = '🏷️ ' + (category || 'General');
  document.getElementById('exGuideEquipment').textContent = '🔧 ' + (equipment || 'Equipment Needed');

  const lower = (name || '').toLowerCase();
  let guide = null;
  for (let k in FORM_GUIDES) {
    if (lower.includes(k)) { guide = FORM_GUIDES[k]; break; }
  }

  const stepsEl = document.getElementById('exGuideSteps');
  const tipsEl = document.getElementById('exGuideTips');
  
  if (guide) {
    stepsEl.innerHTML = guide.steps.map(s => `<li>${s}</li>`).join('');
    tipsEl.textContent = 'Pro Form Tip: ' + guide.tips;
  } else {
    stepsEl.innerHTML = `
      <li>Position yourself with proper posture and firm grip on ${equipment || 'the equipment'}.</li>
      <li>Perform full range of motion under steady, controlled speed.</li>
      <li>Exhale during effort phase and inhale while returning to start position.</li>
      <li>Maintain core tightness and neutral spine alignment throughout.</li>
    `;
    tipsEl.textContent = `Pro Form Tip: Perform ${name} with smooth cadence and controlled breathing.`;
  }

  const ytQuery = encodeURIComponent(name + ' exercise proper form execution tutorial');
  document.getElementById('exGuideYoutubeBtn').href = 'https://www.youtube.com/results?search_query=' + ytQuery;

  const modal = new bootstrap.Modal(document.getElementById('exerciseFormGuideModal'));
  modal.show();
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
