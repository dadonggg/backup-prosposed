<?php
declare(strict_types=1);
$pageTitle = 'Edit Meal Plan';
require __DIR__ . '/../partials/header.php';

$clientName = htmlspecialchars($request['member_name'] ?? 'Client');
$macros = $aiSuggestion['meal_macros'] ?? ['calories' => 2000, 'protein' => 150, 'carbs' => 200, 'fats' => 65];
$meals = $aiSuggestion['meal_suggestions'] ?? [];
$quickSuggestions = $aiSuggestion['meal_quick_suggestions'] ?? [];
?>

<style>
.meal-plan-editor {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px;
}

.macro-targets {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}

.macro-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.macro-field {
    display: flex;
    flex-direction: column;
}

.macro-field label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 6px;
    text-transform: uppercase;
}

.macro-input {
    display: flex;
    align-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.macro-input input {
    flex: 1;
    border: none;
    padding: 10px 12px;
    font-size: 16px;
    font-weight: 600;
}

.macro-input span {
    padding: 0 12px;
    background: #f8fafc;
    color: #64748b;
    font-size: 14px;
}

.meal-rows {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}

.meal-row {
    display: grid;
    grid-template-columns: 120px 100px 2fr 100px 100px 60px;
    gap: 12px;
    align-items: end;
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 12px;
}

.meal-field label {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 4px;
    display: block;
    text-transform: uppercase;
}

.meal-field input,
.meal-field select {
    width: 100%;
    padding: 8px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
}

.quick-add {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.quick-add-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.quick-add-item {
    background: #fff;
    border: 1px solid #e2e8f0;
    padding: 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
}

.quick-add-item:hover {
    background: #f0fdf4;
    border-color: #10b981;
}

.btn-add-meal {
    background: #10b981;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.btn-remove-meal {
    background: #ef4444;
    color: white;
    border: none;
    padding: 8px;
    border-radius: 6px;
    cursor: pointer;
}

.save-bar {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-send {
    background: #10b981;
    color: white;
    border: none;
    padding: 12px 32px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
}

.btn-back {
    background: #fff;
    color: #64748b;
    border: 1px solid #e2e8f0;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}
</style>

<div class="meal-plan-editor">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-egg-fried me-2" style="color: #f59e0b;"></i>
                Edit Meal Plan for <?= $clientName ?>
            </h1>
            <p class="text-muted mb-0">Customize the AI-generated meal plan</p>
        </div>
    </div>

    <!-- Macro Targets -->
    <div class="macro-targets">
        <h5 class="mb-3">Daily Macro Targets</h5>
        <div class="macro-grid">
            <div class="macro-field">
                <label>Target Calories</label>
                <div class="macro-input">
                    <input type="number" id="calories" value="<?= $macros['calories'] ?? 2000 ?>" min="1000" max="5000">
                    <span>kcal</span>
                </div>
            </div>
            <div class="macro-field">
                <label>Protein</label>
                <div class="macro-input">
                    <input type="number" id="protein" value="<?= $macros['protein'] ?? 150 ?>" min="50" max="500">
                    <span>g</span>
                </div>
            </div>
            <div class="macro-field">
                <label>Carbs</label>
                <div class="macro-input">
                    <input type="number" id="carbs" value="<?= $macros['carbs'] ?? 200 ?>" min="50" max="500">
                    <span>g</span>
                </div>
            </div>
            <div class="macro-field">
                <label>Fats</label>
                <div class="macro-input">
                    <input type="number" id="fats" value="<?= $macros['fats'] ?? 65 ?>" min="20" max="200">
                    <span>g</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Meal Rows -->
    <div class="meal-rows">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Meal Plan</h5>
            <button class="btn-add-meal" onclick="addMealRow()">
                <i class="bi bi-plus-circle me-1"></i>Add Meal
            </button>
        </div>
        
        <div id="mealRowsContainer">
            <?php foreach ($meals as $meal): ?>
            <div class="meal-row">
                <div class="meal-field">
                    <label>Type</label>
                    <select class="meal-type">
                        <option value="Breakfast" <?= ($meal['type'] ?? '') === 'Breakfast' ? 'selected' : '' ?>>Breakfast</option>
                        <option value="Lunch" <?= ($meal['type'] ?? '') === 'Lunch' ? 'selected' : '' ?>>Lunch</option>
                        <option value="Dinner" <?= ($meal['type'] ?? '') === 'Dinner' ? 'selected' : '' ?>>Dinner</option>
                        <option value="Snack" <?= ($meal['type'] ?? '') === 'Snack' ? 'selected' : '' ?>>Snack</option>
                    </select>
                </div>
                <div class="meal-field">
                    <label>Time</label>
                    <input type="time" class="meal-time" value="<?= date('H:i', strtotime($meal['time'] ?? '07:00')) ?>">
                </div>
                <div class="meal-field">
                    <label>Food Name</label>
                    <input type="text" class="meal-food" value="<?= htmlspecialchars($meal['foodName'] ?? '') ?>" placeholder="e.g. Oatmeal with banana">
                </div>
                <div class="meal-field">
                    <label>Amount</label>
                    <input type="number" class="meal-amount" value="<?= $meal['amount'] ?? 100 ?>" min="0">
                </div>
                <div class="meal-field">
                    <label>Unit</label>
                    <select class="meal-unit">
                        <option value="grams" <?= ($meal['unit'] ?? '') === 'grams' ? 'selected' : '' ?>>grams</option>
                        <option value="cups" <?= ($meal['unit'] ?? '') === 'cups' ? 'selected' : '' ?>>cups</option>
                        <option value="pieces" <?= ($meal['unit'] ?? '') === 'pieces' ? 'selected' : '' ?>>pieces</option>
                        <option value="oz" <?= ($meal['unit'] ?? '') === 'oz' ? 'selected' : '' ?>>oz</option>
                    </select>
                </div>
                <div class="meal-field">
                    <label>&nbsp;</label>
                    <button class="btn-remove-meal" onclick="removeMealRow(this)">🗑️</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Quick Add Suggestions -->
    <?php if (!empty($quickSuggestions)): ?>
    <div class="quick-add">
        <h5 class="mb-3">Quick Add Suggested Meals</h5>
        <div class="quick-add-grid">
            <?php foreach ($quickSuggestions as $type => $foods): ?>
            <div>
                <h6 class="mb-2"><?= htmlspecialchars($type) ?></h6>
                <?php foreach ((array)$foods as $food): ?>
                <div class="quick-add-item" onclick='quickAddMeal("<?= $type ?>", "<?= htmlspecialchars($food, ENT_QUOTES) ?>")'>
                    <?= htmlspecialchars($food) ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Save Bar -->
    <div class="save-bar">
        <button class="btn-back" onclick="history.back()">
            <i class="bi bi-arrow-left me-1"></i>Back to Workout
        </button>
        <div class="text-end">
            <h6 class="mb-1">Ready to send the complete plan?</h6>
            <p class="text-muted small mb-0">This will send both workout and meal plans to the client</p>
        </div>
        <button class="btn-send" onclick="saveAndSendPlan()">
            <i class="bi bi-send me-2"></i>Save & Send to Client
        </button>
    </div>
</div>

<script>
const REQUEST_ID = <?= $request['id'] ?>;

function addMealRow(type = 'Snack', time = '15:00', foodName = '', amount = 100, unit = 'grams') {
    const container = document.getElementById('mealRowsContainer');
    const row = document.createElement('div');
    row.className = 'meal-row';
    row.innerHTML = `
        <div class="meal-field">
            <label>Type</label>
            <select class="meal-type">
                <option value="Breakfast" ${type === 'Breakfast' ? 'selected' : ''}>Breakfast</option>
                <option value="Lunch" ${type === 'Lunch' ? 'selected' : ''}>Lunch</option>
                <option value="Dinner" ${type === 'Dinner' ? 'selected' : ''}>Dinner</option>
                <option value="Snack" ${type === 'Snack' ? 'selected' : ''}>Snack</option>
            </select>
        </div>
        <div class="meal-field">
            <label>Time</label>
            <input type="time" class="meal-time" value="${time}">
        </div>
        <div class="meal-field">
            <label>Food Name</label>
            <input type="text" class="meal-food" value="${foodName}" placeholder="e.g. Grilled Chicken">
        </div>
        <div class="meal-field">
            <label>Amount</label>
            <input type="number" class="meal-amount" value="${amount}" min="0">
        </div>
        <div class="meal-field">
            <label>Unit</label>
            <select class="meal-unit">
                <option value="grams" ${unit === 'grams' ? 'selected' : ''}>grams</option>
                <option value="cups" ${unit === 'cups' ? 'selected' : ''}>cups</option>
                <option value="pieces" ${unit === 'pieces' ? 'selected' : ''}>pieces</option>
                <option value="oz" ${unit === 'oz' ? 'selected' : ''}>oz</option>
            </select>
        </div>
        <div class="meal-field">
            <label>&nbsp;</label>
            <button class="btn-remove-meal" onclick="removeMealRow(this)">🗑️</button>
        </div>
    `;
    container.appendChild(row);
}

function removeMealRow(btn) {
    btn.closest('.meal-row').remove();
}

function quickAddMeal(type, foodName) {
    const timeMap = {
        'Breakfast': '07:00',
        'Lunch': '12:00',
        'Dinner': '18:00',
        'Snack': '15:00'
    };
    addMealRow(type, timeMap[type] || '15:00', foodName);
}

async function saveAndSendPlan() {
    // Collect macros
    const macros = {
        calories: parseInt(document.getElementById('calories').value),
        protein: parseInt(document.getElementById('protein').value),
        carbs: parseInt(document.getElementById('carbs').value),
        fats: parseInt(document.getElementById('fats').value)
    };
    
    // Collect meals
    const meals = [];
    document.querySelectorAll('.meal-row').forEach(row => {
        const type = row.querySelector('.meal-type').value;
        const time = row.querySelector('.meal-time').value;
        const foodName = row.querySelector('.meal-food').value;
        const amount = parseInt(row.querySelector('.meal-amount').value);
        const unit = row.querySelector('.meal-unit').value;
        
        if (foodName.trim()) {
            meals.push({
                type,
                time: formatTime(time),
                foodName,
                amount,
                unit
            });
        }
    });
    
    if (meals.length === 0) {
        alert('Please add at least one meal');
        return;
    }
    
    // Send to server
    try {
        const response = await fetch('index.php?r=trainerAi/saveMealPlan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                request_id: REQUEST_ID,
                macros: macros,
                meals: meals
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ Complete plan sent to client successfully!');
            window.location.href = 'index.php?r=trainer/clients';
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to save meal plan');
    }
}

function formatTime(time) {
    const [hours, minutes] = time.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return `${h12}:${minutes} ${ampm}`;
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
