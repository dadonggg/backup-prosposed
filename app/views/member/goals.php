<?php
declare(strict_types=1);
$pageTitle = 'Fitness Goals';
require __DIR__ . '/../partials/header.php';
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

/* ── Cards ── */
.fit-card {
  background: var(--bg-card);
  border: 1px solid var(--border-card);
  border-radius: 12px;
  box-shadow: var(--shadow-card);
  margin-bottom: 1.5rem;
  overflow: hidden;
}

.fit-card-header {
  background: var(--bg-section-header);
  border-left: 4px solid var(--accent-teal);
  border-bottom: 1px solid var(--border-card);
  padding: 14px 20px;
}
.fit-card-header.types-hd {
  background: #f8fafc;
  border-left: 4px solid var(--accent-blue);
}
.fit-card-header.trophy-hd {
  background: #fffbeb;
  border-left: 4px solid #f59e0b;
}

.fit-heading {
  color: var(--accent-teal) !important;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin: 0;
}
.fit-card-header.types-hd .fit-heading {
  color: var(--text-primary) !important;
}
.fit-card-header.trophy-hd .fit-heading {
  color: #d97706 !important;
}

/* ── Buttons ── */
.btn-fit-primary {
  background: var(--accent-green-btn);
  color: #fff !important;
  border: none;
  border-radius: 8px;
  padding: 10px 18px;
  font-weight: 600;
  font-size: 14px;
  transition: all .2s;
  box-shadow: var(--shadow-sm);
  display: inline-block;
}
.btn-fit-primary:hover {
  background: var(--accent-green-hover);
  transform: translateY(-1px);
}

.btn-fit-outline {
  background: #ffffff;
  border: 1px solid var(--border-card);
  color: var(--text-secondary) !important;
  border-radius: 8px;
  padding: 10px 18px;
  font-weight: 600;
  font-size: 14px;
  transition: all .2s;
  display: inline-block;
}
.btn-fit-outline:hover {
  border-color: var(--accent-teal);
  color: var(--accent-teal) !important;
  background: #f0fdf9;
}

.btn-fit-danger {
  background: #ef4444;
  color: #fff !important;
  border: none;
  border-radius: 8px;
  padding: 10px 18px;
  font-weight: 600;
  font-size: 14px;
  transition: all .2s;
}
.btn-fit-danger:hover {
  background: #dc2626;
}

/* ── Inputs ── */
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

/* ── Subcard for Active Goal ── */
.goal-subcard {
  border: 1px solid var(--border-card);
  border-radius: 10px;
  padding: 1.2rem;
  background: #ffffff;
  margin-bottom: 1rem;
}
.goal-subcard:last-child {
  margin-bottom: 0;
}
.goal-subcard.overdue {
  border-color: #f87171;
}

.goal-type-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-extrabold mb-1" style="color: var(--text-primary); font-size: 26px; font-weight: 800;">
            <i class="bi bi-target me-2" style="color: var(--accent-teal)"></i>Fitness Goals
        </h1>
        <p class="mb-0" style="color: var(--text-secondary); font-size: 14px;">Set and track your personal fitness goals</p>
    </div>
    <button class="btn-fit-primary" data-bs-toggle="modal" data-bs-target="#addGoalModal">
        <i class="bi bi-plus-circle me-1"></i>Set New Goal
    </button>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="border-radius: 12px;"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success" style="border-radius: 12px;"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Goal Statistics -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="fit-card text-center p-4">
            <div class="display-6 fw-bold" style="color: #d97706;"><?= (int)($stats['active'] ?? 0) ?></div>
            <div style="color: var(--text-secondary); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px;">Active Goals</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fit-card text-center p-4">
            <div class="display-6 fw-bold" style="color: #16a34a;"><?= (int)($stats['completed'] ?? 0) ?></div>
            <div style="color: var(--text-secondary); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px;">Completed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fit-card text-center p-4">
            <div class="display-6 fw-bold" style="color: #475569;"><?= (int)($stats['paused'] ?? 0) ?></div>
            <div style="color: var(--text-secondary); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px;">Paused</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fit-card text-center p-4">
            <div class="display-6 fw-bold" style="color: #ef4444;"><?= (int)($stats['cancelled'] ?? 0) ?></div>
            <div style="color: var(--text-secondary); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px;">Cancelled</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Active Goals -->
    <div class="col-lg-8">
        <div class="fit-card">
            <div class="fit-card-header">
                <h5 class="fit-heading">
                    <i class="bi bi-play-circle me-2"></i>Active Goals
                </h5>
            </div>
            <div class="p-4">
                <?php if (empty($activeGoals)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-target display-1 text-muted"></i>
                    <h5 class="mt-3 fw-bold" style="color: var(--text-primary);">No active goals yet</h5>
                    <p class="text-muted mb-4">Set your first fitness goal to start tracking your progress!</p>
                    <button class="btn-fit-primary" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                        <i class="bi bi-plus-circle me-1"></i>Set Your First Goal
                    </button>
                </div>
                <?php else: ?>
                <?php foreach ($activeGoals as $goalData): 
                    $goal = $goalData['goal'];
                    $progress = $goalData['progress_percentage'];
                    $isOverdue = $goalData['is_overdue'];
                    $daysRemaining = $goalData['days_remaining'];
                    
                    $__gt = $goal['goal_type'];
                    if ($__gt === 'weight_loss') { $typeIcon = 'bi-arrow-down-circle'; }
                    elseif ($__gt === 'weight_gain') { $typeIcon = 'bi-arrow-up-circle'; }
                    elseif ($__gt === 'strength') { $typeIcon = 'bi-lightning'; }
                    elseif ($__gt === 'endurance') { $typeIcon = 'bi-heart-pulse'; }
                    else { $typeIcon = 'bi-bullseye'; }

                    if ($__gt === 'weight_loss') { $typeColor = 'danger'; }
                    elseif ($__gt === 'weight_gain') { $typeColor = 'success'; }
                    elseif ($__gt === 'strength') { $typeColor = 'primary'; }
                    elseif ($__gt === 'endurance') { $typeColor = 'info'; }
                    else { $typeColor = 'secondary'; }

                    $badgeColor = $isOverdue ? '#ef4444' : 'var(--accent-teal)';
                ?>
                <div class="goal-subcard <?= $isOverdue ? 'overdue' : '' ?>">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="goal-type-icon bg-<?= $typeColor ?> bg-opacity-10 me-3">
                                    <i class="bi <?= $typeIcon ?> text-<?= $typeColor ?>" style="font-size: 1.2rem;"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold" style="font-size: 16px; color: var(--text-primary);"><?= htmlspecialchars($goal['title']) ?></h5>
                                    <div class="text-muted small">
                                        <?= ucfirst(str_replace('_', ' ', $goal['goal_type'])) ?>
                                        <?php if ($goal['target_date']): ?>
                                        • Target: <?= date('M j, Y', strtotime($goal['target_date'])) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge fs-6" style="background-color: <?= $badgeColor ?>; color: #fff; border-radius: 6px; padding: 4px 8px;">
                                    <?= number_format((float)($progress ?? 0), 1) ?>%
                                </span>
                                <?php if ($daysRemaining !== null): ?>
                                <div class="small text-muted mt-1">
                                    <?= $isOverdue ? "$daysRemaining days overdue" : "$daysRemaining days left" ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($goal['description']): ?>
                        <p style="font-size: 14px; color: var(--text-secondary);" class="mb-3"><?= htmlspecialchars($goal['description']) ?></p>
                        <?php endif; ?>

                        <div class="progress mb-3" style="height: 8px; border-radius: 4px; background: #e2e8f0;">
                            <div class="progress-bar" 
                                 style="width: <?= min(100, (float)$progress) ?>%; background-color: <?= $badgeColor ?>; border-radius: 4px;"></div>
                        </div>

                        <?php if ($goal['target_value']): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div style="font-size: 14px; color: var(--text-primary);">
                                <strong><?= number_format((float)($goal['current_value'] ?? 0), 1) ?></strong> / 
                                <?= number_format((float)($goal['target_value'] ?? 0), 1) ?> <?= htmlspecialchars($goal['target_unit']) ?>
                            </div>
                            <div>
                                <form method="post" class="d-inline-flex align-items-center">
                                    <input type="hidden" name="action" value="update_progress">
                                    <input type="hidden" name="goal_id" value="<?= $goal['id'] ?>">
                                    <input type="number" name="current_value" class="form-control fit-input form-control-sm me-2" 
                                           style="width: 100px;" step="0.1" value="<?= $goal['current_value'] ?>" 
                                           placeholder="Current">
                                    <button type="submit" class="btn-fit-outline" style="padding: 6px 12px; font-size: 13px;">Update</button>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2">
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="goal_id" value="<?= $goal['id'] ?>">
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn-fit-primary" style="padding: 8px 14px; font-size: 13px;">
                                    <i class="bi bi-check-circle me-1"></i>Mark Complete
                                </button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="goal_id" value="<?= $goal['id'] ?>">
                                <input type="hidden" name="status" value="paused">
                                <button type="submit" class="btn-fit-outline" style="padding: 8px 14px; font-size: 13px;">
                                    <i class="bi bi-pause-circle me-1"></i>Pause
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Completed Goals & Goal Types -->
    <div class="col-lg-4">
        <!-- Goal Types Breakdown -->
        <div class="fit-card">
            <div class="fit-card-header types-hd">
                <h5 class="fit-heading">
                    <i class="bi bi-pie-chart me-2"></i>Goal Types
                </h5>
            </div>
            <div class="p-4">
                <?php 
                $goalTypes = [
                    'weight_loss' => ['Weight Loss', 'danger', 'bi-arrow-down-circle'],
                    'weight_gain' => ['Weight Gain', 'success', 'bi-arrow-up-circle'],
                    'strength' => ['Strength', 'primary', 'bi-lightning'],
                    'endurance' => ['Endurance', 'info', 'bi-heart-pulse'],
                    'other' => ['Other', 'secondary', 'bi-bullseye']
                ];
                
                foreach ($goalTypes as $type => $info):
                    $count = (int)($stats['by_type'][$type] ?? 0);
                    if ($count > 0):
                ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center" style="font-size: 14px;">
                        <i class="bi <?= $info[2] ?> text-<?= $info[1] ?> me-2"></i>
                        <?= $info[0] ?>
                    </div>
                    <span class="badge bg-<?= $info[1] ?>" style="border-radius: 6px; padding: 4px 8px;"><?= $count ?></span>
                </div>
                <?php endif; endforeach; ?>
            </div>
        </div>

        <!-- Recent Completed Goals -->
        <?php if (!empty($completedGoals)): ?>
        <div class="fit-card mt-4">
            <div class="fit-card-header trophy-hd">
                <h5 class="fit-heading">
                    <i class="bi bi-trophy me-2"></i>Recent Achievements
                </h5>
            </div>
            <div class="p-4">
                <?php foreach (array_slice($completedGoals, 0, 5) as $goal): ?>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-check-circle text-success"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="font-size: 14px; color: var(--text-primary);"><?= htmlspecialchars($goal['title']) ?></div>
                        <small class="text-muted" style="font-size: 12px;">
                            Completed <?= date('M j, Y', strtotime($goal['updated_at'])) ?>
                        </small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: none; overflow: hidden; background: #ffffff;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-card); background: #f8fafc; padding: 16px 24px;">
                <h5 class="modal-title fw-bold" style="color: var(--text-primary); font-size: 16px;">
                    <i class="bi bi-plus-circle me-2" style="color: var(--accent-teal);"></i>Set New Goal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body" style="padding: 24px;">
                    <input type="hidden" name="action" value="add_goal">
                    
                    <div class="mb-3">
                        <label class="fit-label">Goal Type</label>
                        <select name="goal_type" class="form-select fit-input" style="width: 100%;" required>
                            <option value="">Select goal type...</option>
                            <option value="weight_loss">Weight Loss</option>
                            <option value="weight_gain">Weight Gain</option>
                            <option value="strength">Strength Training</option>
                            <option value="endurance">Endurance</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fit-label">Goal Title</label>
                        <input type="text" name="title" class="form-control fit-input" required 
                               placeholder="e.g., Lose 10kg, Bench press 100kg">
                    </div>
                    
                    <div class="mb-3">
                        <label class="fit-label">Description (optional)</label>
                        <textarea name="description" class="form-control fit-input" rows="3" 
                                  placeholder="Describe your goal and why it's important to you..."></textarea>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="fit-label">Target Value (optional)</label>
                            <input type="number" name="target_value" class="form-control fit-input" step="0.1" 
                                   placeholder="e.g., 70">
                        </div>
                        <div class="col-md-6">
                            <label class="fit-label">Unit (optional)</label>
                            <input type="text" name="target_unit" class="form-control fit-input" 
                                   placeholder="e.g., kg, lbs, minutes">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fit-label">Target Date (optional)</label>
                        <input type="date" name="target_date" class="form-control fit-input" 
                               min="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border-card); background: #f8fafc; padding: 16px 24px;">
                    <button type="button" class="btn-fit-outline" data-bs-dismiss="modal" style="padding: 8px 16px;">Cancel</button>
                    <button type="submit" class="btn-fit-primary" style="padding: 8px 16px;">
                        <i class="bi bi-save me-1"></i>Create Goal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>