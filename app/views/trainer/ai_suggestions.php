<?php
declare(strict_types=1);
$pageTitle = 'AI Generated Plan - Review & Edit';
require __DIR__ . '/../partials/header.php';

$clientName = htmlspecialchars($request['member_name'] ?? 'Client');
$workoutSuggestions = $aiSuggestion['workout_suggestions'] ?? [];
$mealMacros = $aiSuggestion['meal_macros'] ?? [];
$mealSuggestions = $aiSuggestion['meal_suggestions'] ?? [];
$quickSuggestions = $aiSuggestion['meal_quick_suggestions'] ?? [];
$aiNotes = $aiSuggestion['ai_notes'] ?? '';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

body {
    background: #f8f9fa;
    font-family: 'Inter', sans-serif;
}

.ai-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
    margin-left: 8px;
}

.client-profile-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.ai-notes-box {
    background: #f0f9ff;
    border-left: 4px solid #0ea5e9;
    padding: 16px;
    border-radius: 8px;
    margin-top: 16px;
}

.workout-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
    margin-top: 20px;
}

.day-column {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.day-header {
    background: #f8fafc;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 700;
    font-size: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.exercise-card {
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
}

.exercise-card:last-child {
    border-bottom: none;
}

.exercise-card.ai-generated {
    background: linear-gradient(90deg, rgba(102,126,234,0.05) 0%, rgba(118,75,162,0.05) 100%);
}

.ex-input {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 13px;
    width: 100%;
    margin-bottom: 6px;
}

.ex-numbers {
    display: flex;
    gap: 8px;
    align-items: center;
    font-size: 12px;
    margin-bottom: 6px;
}

.ex-numbers input {
    width: 60px;
    padding: 4px 8px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 12px;
}

.btn-add-exercise, .btn-remove {
    font-size: 12px;
    padding: 6px 12px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
}

.btn-add-exercise {
    background: #10b981;
    color: white;
    width: 100%;
    margin-top: 8px;
}

.btn-remove {
    background: #ef4444;
    color: white;
}

.rest-day {
    padding: 20px;
    text-align: center;
    color: #94a3b8;
    font-style: italic;
}

.save-bar {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-top: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.btn-primary {
    background: #3b82f6;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
}

.btn-success {
    background: #10b981;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
}

.meal-plan-section {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    margin-top: 24px;
}

.macro-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-top: 16px;
}

.macro-field input {
    width: 100%;
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}
</style>

<div class="container-fluid p-4" style="max-width: 1400px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-robot me-2" style="color: #667eea;"></i>
                AI Generated Plan
                <span class="ai-badge">✨ AI POWERED</span>
            </h1>
            <p class="text-muted mb-0">Review and customize for <?= $clientName ?></p>
        </div>
        <a href="index.php?r=trainer/clients" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Clients
        </a>
    </div>

    <!-- Client Profile Panel -->
    <div class="client-profile-panel">
        <h5 class="mb-3"><i class="bi bi-person-circle me-2"></i><?= $clientName ?>'s Profile</h5>
        <div class="row">
            <div class="col-md-3">
                <strong>Goals:</strong><br>
                <?php 
                $goals = $profile['fitness_goals'] ?? [];
                if (is_string($goals)) {
                    $goals = json_decode($goals, true) ?? [];
                }
                echo implode(', ', array_map('ucfirst', (array)$goals));
                ?>
            </div>
            <div class="col-md-3">
                <strong>Activity Level:</strong><br>
                <?= htmlspecialchars($profile['activity_level'] ?? 'N/A') ?>
            </div>
            <div class="col-md-3">
                <strong>Medical:</strong><br>
                <?= htmlspecialchars($profile['medical_conditions'] ?: 'None') ?>
            </div>
            <div class="col-md-3">
                <strong>Diet:</strong><br>
                <?= htmlspecialchars($profile['dietary_preferences'] ?: 'None') ?>
            </div>
        </div>
        
        <?php if ($aiNotes): ?>
        <div class="ai-notes-box">
            <strong><i class="bi bi-lightbulb me-2"></i>AI Analysis:</strong><br>
            <?= nl2br(htmlspecialchars($aiNotes)) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Workout Plan Editor -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Weekly Workout Schedule</h5>
        </div>
        <div class="card-body">
            <div class="workout-grid" id="workoutGrid">
                <?php
                $days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
                foreach ($days as $day):
                    $exercises = $workoutSuggestions[$day] ?? [];
                ?>
                <div class="day-column">
                    <div class="day-header">
                        <span><?= $day ?></span>
                        <span class="badge bg-secondary"><?= count($exercises) ?></span>
                    </div>
                    <div class="exercises-container" data-day="<?= $day ?>">
                        <?php if (empty($exercises)): ?>
                        <div class="rest-day">Rest day 😴</div>
                        <?php else: ?>
                        <?php foreach ($exercises as $index => $exercise): ?>
                        <div class="exercise-card ai-generated">
                            <input type="text" 
                                   class="ex-input" 
                                   value="<?= htmlspecialchars($exercise['name'] ?? '') ?>"
                                   placeholder="Exercise name"
                                   data-field="name">
                            <div class="ex-numbers">
                                <input type="number" 
                                       value="<?= $exercise['sets'] ?? 3 ?>" 
                                       data-field="sets">
                                sets
                            </div>
                            <div class="ex-numbers">
                                <input type="number" 
                                       value="<?= $exercise['reps'] ?? 10 ?>" 
                                       data-field="reps">
                                reps
                            </div>
                            <div class="ex-numbers">
                                <input type="number" 
                                       value="<?= $exercise['restSeconds'] ?? 60 ?>" 
                                       data-field="restSeconds">
                                sec rest
                            </div>
                            <button class="btn-remove" onclick="removeExercise(this)">✕ Remove</button>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button class="btn-add-exercise" onclick="addExercise('<?= $day ?>')">
                        ⊕ Add Exercise
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Save Bar -->
    <div class="save-bar">
        <div>
            <h6 class="mb-1">Ready to continue?</h6>
            <p class="text-muted small mb-0">Save workout plan and continue to meal planning</p>
        </div>
        <button class="btn-primary" onclick="saveWorkoutPlan()">
            Save Workout & Continue to Meal Plan →
        </button>
    </div>
</div>

<script>
const REQUEST_ID = <?= $request['id'] ?>;

function addExercise(day) {
    const container = document.querySelector(`.exercises-container[data-day="${day}"]`);
    
    // Remove rest day message if exists
    const restDay = container.querySelector('.rest-day');
    if (restDay) restDay.remove();
    
    const exerciseCard = document.createElement('div');
    exerciseCard.className = 'exercise-card';
    exerciseCard.innerHTML = `
        <input type="text" class="ex-input" placeholder="Exercise name" data-field="name">
        <div class="ex-numbers">
            <input type="number" value="3" data-field="sets"> sets
        </div>
        <div class="ex-numbers">
            <input type="number" value="10" data-field="reps"> reps
        </div>
        <div class="ex-numbers">
            <input type="number" value="60" data-field="restSeconds"> sec rest
        </div>
        <button class="btn-remove" onclick="removeExercise(this)">✕ Remove</button>
    `;
    
    container.appendChild(exerciseCard);
    updateDayBadge(day);
}

function removeExercise(btn) {
    const card = btn.closest('.exercise-card');
    const container = card.closest('.exercises-container');
    const day = container.dataset.day;
    
    card.remove();
    
    // If no exercises left, show rest day
    const remaining = container.querySelectorAll('.exercise-card');
    if (remaining.length === 0) {
        container.innerHTML = '<div class="rest-day">Rest day 😴</div>';
    }
    
    updateDayBadge(day);
}

function updateDayBadge(day) {
    const container = document.querySelector(`.exercises-container[data-day="${day}"]`);
    const count = container.querySelectorAll('.exercise-card').length;
    const badge = container.closest('.day-column').querySelector('.badge');
    badge.textContent = count;
}

async function saveWorkoutPlan() {
    const workoutSchedule = {};
    const days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
    
    days.forEach(day => {
        const container = document.querySelector(`.exercises-container[data-day="${day}"]`);
        const exercises = [];
        
        container.querySelectorAll('.exercise-card').forEach(card => {
            const name = card.querySelector('[data-field="name"]').value;
            const sets = parseInt(card.querySelector('[data-field="sets"]').value) || 3;
            const reps = parseInt(card.querySelector('[data-field="reps"]').value) || 10;
            const restSeconds = parseInt(card.querySelector('[data-field="restSeconds"]').value) || 60;
            
            if (name.trim()) {
                exercises.push({ name, sets, reps, restSeconds });
            }
        });
        
        workoutSchedule[day] = exercises;
    });
    
    try {
        const response = await fetch('index.php?r=trainerAi/saveWorkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                request_id: REQUEST_ID,
                workout_schedule: workoutSchedule
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Redirect to meal plan editor (we'll create this next)
            window.location.href = 'index.php?r=trainerAi/editMealPlan&request_id=' + REQUEST_ID;
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to save workout plan');
    }
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
