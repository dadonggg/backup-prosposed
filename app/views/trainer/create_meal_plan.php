<?php
declare(strict_types=1);
$pageTitle = 'Create Meal Plan';
require __DIR__ . '/../partials/header.php';

// Get client info
$clientName = htmlspecialchars($clientRequest['member_name'] ?? 'Client');
$requestId = (int)($clientRequest['id'] ?? 0);

// Get existing plan data if available
$existingCalories = $plan['target_calories'] ?? 2000;
$existingProtein = $plan['target_protein_g'] ?? 150;
$existingCarbs = $plan['target_carbs_g'] ?? 200;
$existingFats = $plan['target_fats_g'] ?? 65;
$existingMealSuggestions = $plan['meal_suggestions'] ?? '';
$existingNutritionNotes = $plan['nutrition_notes'] ?? '';
?>

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-egg-fried me-2"></i>Create Meal Plan
            </h1>
            <p class="text-muted mb-0">Design a personalized nutrition plan for <?= $clientName ?></p>
        </div>
        <a href="index.php?r=trainer/createPlan&request_id=<?= $requestId ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Workout Plan
        </a>
    </div>
</div>

<!-- Meal Plan Form -->
<form method="POST" action="index.php?r=trainer/saveCompletePlan" id="mealPlanForm">
    <input type="hidden" name="request_id" value="<?= $requestId ?>">
    <input type="hidden" name="workout_plan" id="workoutPlanInput">
    <input type="hidden" name="fitness_level" value="intermediate">
    <input type="hidden" name="primary_goals" value="General fitness">
    <input type="hidden" name="limitations_notes" value="">
    <input type="hidden" name="recommended_sessions_per_week" value="3">

    <!-- Macro Targets -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="bi bi-calculator me-2"></i>Daily Macro Targets
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label">Target Calories</label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="target_calories" 
                               value="<?= $existingCalories ?>" min="1000" max="5000" step="50">
                        <span class="input-group-text">kcal</span>
                    </div>
                    <small class="text-muted">Daily calorie goal</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Protein</label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="target_protein_g" 
                               value="<?= $existingProtein ?>" min="50" max="500" step="5">
                        <span class="input-group-text">g</span>
                    </div>
                    <small class="text-muted">Grams per day</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Carbohydrates</label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="target_carbs_g" 
                               value="<?= $existingCarbs ?>" min="50" max="500" step="5">
                        <span class="input-group-text">g</span>
                    </div>
                    <small class="text-muted">Grams per day</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fats</label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="target_fats_g" 
                               value="<?= $existingFats ?>" min="20" max="200" step="5">
                        <span class="input-group-text">g</span>
                    </div>
                    <small class="text-muted">Grams per day</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Meal Suggestions -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-journal-text me-2"></i>Meal Suggestions & Guidelines
            </h5>
        </div>
        <div class="card-body">
            <!-- Structured Meal Builder -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="form-label mb-0 fw-bold">Build Meal Plan</label>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-info me-2" id="loadAiMealBtn" style="display: none;" onclick="triggerLoadAIMealPlan()">
                            <i class="bi bi-robot me-1"></i>Load AI Suggestions
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addMealRow()">
                            <i class="bi bi-plus-circle me-1"></i>Add Meal
                        </button>
                    </div>
                </div>
                
                <div id="mealBuilderContainer">
                    <!-- Meal rows will be added here dynamically -->
                </div>
                
                <!-- Hidden textarea to store final meal suggestions -->
                <textarea class="form-control d-none" name="meal_suggestions" id="mealSuggestionsHidden"><?= htmlspecialchars($existingMealSuggestions) ?></textarea>
            </div>

            <!-- Suggested Meals Quick Add -->
            <div class="mb-4">
                <label class="form-label fw-bold">Quick Add Suggested Meals</label>
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-body p-2">
                                <h6 class="mb-2 text-warning"><i class="bi bi-sunrise me-1"></i>Breakfast</h6>
                                <div class="d-grid gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="quickAddMeal('breakfast', '07:00', 'Scrambled Eggs with Toast')">Scrambled Eggs with Toast</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="quickAddMeal('breakfast', '07:00', 'Oatmeal with Banana')">Oatmeal with Banana</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="quickAddMeal('breakfast', '07:00', 'Greek Yogurt with Berries')">Greek Yogurt with Berries</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="quickAddMeal('breakfast', '07:00', 'Protein Pancakes')">Protein Pancakes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-body p-2">
                                <h6 class="mb-2 text-primary"><i class="bi bi-sun me-1"></i>Lunch</h6>
                                <div class="d-grid gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="quickAddMeal('lunch', '12:00', 'Grilled Chicken Salad')">Grilled Chicken Salad</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="quickAddMeal('lunch', '12:00', 'Tuna Sandwich')">Tuna Sandwich</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="quickAddMeal('lunch', '12:00', 'Beef Stir-fry with Rice')">Beef Stir-fry with Rice</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="quickAddMeal('lunch', '12:00', 'Vegetable Pasta')">Vegetable Pasta</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-body p-2">
                                <h6 class="mb-2 text-info"><i class="bi bi-moon me-1"></i>Dinner</h6>
                                <div class="d-grid gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="quickAddMeal('dinner', '18:00', 'Salmon with Vegetables')">Salmon with Vegetables</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="quickAddMeal('dinner', '18:00', 'Chicken Breast with Sweet Potato')">Chicken Breast with Sweet Potato</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="quickAddMeal('dinner', '18:00', 'Lean Beef with Quinoa')">Lean Beef with Quinoa</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="quickAddMeal('dinner', '18:00', 'Tofu Stir-fry')">Tofu Stir-fry</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nutrition Notes & Guidelines</label>
                <textarea class="form-control" name="nutrition_notes" rows="4" 
                          placeholder="Additional nutrition tips, hydration goals, supplement recommendations, meal timing strategies, etc."><?= htmlspecialchars($existingNutritionNotes) ?></textarea>
                <small class="text-muted">Any additional nutrition advice or guidelines</small>
            </div>
        </div>
    </div>

    <!-- Workout Plan Summary -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-calendar-week me-2"></i>Workout Plan Summary
            </h5>
        </div>
        <div class="card-body">
            <div id="workoutSummary" class="row g-3">
                <div class="col-12 text-center text-muted py-4">
                    <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
                    <p class="mt-2">Loading workout plan...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Save and Send Button -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">Ready to send the complete plan to your client?</h6>
                    <p class="text-muted small mb-0">This will send both the workout plan and meal plan to the client.</p>
                </div>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-send me-2"></i>Save & Send Complete Plan
                </button>
            </div>
        </div>
    </div>
</form>

<style>
.meal-row {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
}

.food-search-wrapper {
    position: relative;
}

.food-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: none;
}

.food-suggestion-item {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
}

.food-suggestion-item:hover {
    background: #f8f9fa;
}

.nutrient-card {
    background: #e7f5ff;
    border: 1px solid #74c0fc;
    border-radius: 8px;
    padding: 12px;
    margin-top: 10px;
}

.nutrient-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-top: 10px;
}

.nutrient-item {
    text-align: center;
}

.nutrient-value {
    display: block;
    font-size: 18px;
    font-weight: bold;
    color: #1971c2;
}

.nutrient-label {
    display: block;
    font-size: 11px;
    color: #495057;
    text-transform: uppercase;
}
</style>

<script>
const REQUEST_ID = <?= $requestId ?>;
let mealCounter = 0;
let searchTimeout = null;

const NUTRIENT_IDS = {
    CALORIES: 1008,
    PROTEIN: 1003,
    CARBS: 1005,
    FATS: 1004,
    CALCIUM: 1087,
    IRON: 1089,
    VITAMIN_C: 1162,
    VITAMIN_A: 1106
};

document.addEventListener('DOMContentLoaded', function() {
    // Load workout plan from localStorage
    const workoutPlanJSON = localStorage.getItem('workout_plan_' + REQUEST_ID);
    
    if (workoutPlanJSON) {
        try {
            const workoutPlan = JSON.parse(workoutPlanJSON);
            displayWorkoutSummary(workoutPlan);
            
            // Set hidden input
            document.getElementById('workoutPlanInput').value = workoutPlanJSON;
        } catch (e) {
            console.error('Error parsing workout plan:', e);
            document.getElementById('workoutSummary').innerHTML = 
                '<div class="col-12 text-center text-danger py-4"><p>Error loading workout plan</p></div>';
        }
    } else {
        document.getElementById('workoutSummary').innerHTML = 
            '<div class="col-12 text-center text-warning py-4"><p>No workout plan found. Please create a workout plan first.</p></div>';
    }

    // Load existing meals if any
    const existingMeals = document.getElementById('mealSuggestionsHidden').value;
    if (existingMeals) {
        // Parse existing meal text and create meal rows
        parseMealText(existingMeals);
    }

    // Check if there is an AI meal plan available in localStorage
    const aiMealPlanJSON = localStorage.getItem('ai_meal_plan_' + REQUEST_ID);
    if (aiMealPlanJSON) {
        document.getElementById('loadAiMealBtn').style.display = 'inline-block';
        if (!existingMeals || existingMeals.trim() === '') {
            try {
                const aiMealPlan = JSON.parse(aiMealPlanJSON);
                loadAIMealPlan(aiMealPlan);
            } catch (e) {
                console.error('Error parsing AI meal plan:', e);
            }
        }
    }

    // Form submission handler
    document.getElementById('mealPlanForm').addEventListener('submit', function(e) {
        // Validate that workout plan is loaded
        const workoutPlanInput = document.getElementById('workoutPlanInput').value;
        if (!workoutPlanInput || workoutPlanInput === '') {
            e.preventDefault();
            alert('Error: Workout plan not found. Please go back and create a workout plan first.');
            return false;
        }
        
        // Update hidden textarea with meal plan
        updateMealSuggestions();
    });
});

function addMealRow(mealType = 'breakfast', time = '07:00', foodName = '', amount = '', unit = 'grams') {
    mealCounter++;
    const container = document.getElementById('mealBuilderContainer');
    
    const mealRow = document.createElement('div');
    mealRow.className = 'meal-row';
    mealRow.id = 'meal-row-' + mealCounter;
    
    mealRow.innerHTML = `
        <div class="row g-2 align-items-start">
            <div class="col-md-2">
                <label class="form-label small">Meal Type</label>
                <select class="form-select form-select-sm meal-type">
                    <option value="breakfast" ${mealType === 'breakfast' ? 'selected' : ''}>Breakfast</option>
                    <option value="lunch" ${mealType === 'lunch' ? 'selected' : ''}>Lunch</option>
                    <option value="dinner" ${mealType === 'dinner' ? 'selected' : ''}>Dinner</option>
                    <option value="snack" ${mealType === 'snack' ? 'selected' : ''}>Snack</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Time</label>
                <input type="time" class="form-control form-control-sm meal-time" value="${time}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Food Name</label>
                <div class="food-search-wrapper">
                    <input type="text" class="form-control form-control-sm food-search" 
                           placeholder="Search food..." value="${foodName}"
                           onkeyup="searchFood(this, ${mealCounter})" 
                           onfocus="this.select()">
                    <div class="food-suggestions" id="suggestions-${mealCounter}"></div>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Amount</label>
                <input type="number" class="form-control form-control-sm food-amount" 
                       placeholder="100" value="${amount}" step="0.1" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Unit</label>
                <select class="form-select form-select-sm food-unit">
                    <option value="grams" ${unit === 'grams' ? 'selected' : ''}>grams</option>
                    <option value="cups" ${unit === 'cups' ? 'selected' : ''}>cups</option>
                    <option value="pieces" ${unit === 'pieces' ? 'selected' : ''}>pieces</option>
                    <option value="oz" ${unit === 'oz' ? 'selected' : ''}>oz</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeMealRow(${mealCounter})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="col-12">
                <div class="nutrient-card d-none" id="nutrient-card-${mealCounter}">
                    <strong class="d-block mb-2">Nutritional Information:</strong>
                    <div class="nutrient-grid">
                        <div class="nutrient-item">
                            <span class="nutrient-value" id="cal-${mealCounter}">-</span>
                            <span class="nutrient-label">Calories</span>
                        </div>
                        <div class="nutrient-item">
                            <span class="nutrient-value" id="protein-${mealCounter}">-</span>
                            <span class="nutrient-label">Protein (g)</span>
                        </div>
                        <div class="nutrient-item">
                            <span class="nutrient-value" id="carbs-${mealCounter}">-</span>
                            <span class="nutrient-label">Carbs (g)</span>
                        </div>
                        <div class="nutrient-item">
                            <span class="nutrient-value" id="fats-${mealCounter}">-</span>
                            <span class="nutrient-label">Fats (g)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(mealRow);
}

function removeMealRow(id) {
    const row = document.getElementById('meal-row-' + id);
    if (row) {
        row.remove();
    }
}

function searchFood(input, rowId) {
    const query = input.value.trim();
    const suggestionsDiv = document.getElementById('suggestions-' + rowId);
    
    if (query.length < 2) {
        suggestionsDiv.style.display = 'none';
        return;
    }
    
    // Debounce search
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        try {
            suggestionsDiv.innerHTML = '<div class="food-suggestion-item text-muted">Searching...</div>';
            suggestionsDiv.style.display = 'block';
            
            const response = await fetch(`index.php?r=foodapi/search&query=${encodeURIComponent(query)}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('API Response:', data); // Debug log
            
            if (data.error) {
                suggestionsDiv.innerHTML = `<div class="food-suggestion-item text-danger">Error: ${data.error}</div>`;
                suggestionsDiv.style.display = 'block';
                return;
            }
            
            if (data.foods && data.foods.length > 0) {
                displayFoodSuggestions(data.foods, suggestionsDiv, rowId);
            } else {
                suggestionsDiv.innerHTML = '<div class="food-suggestion-item text-muted">No results found</div>';
                suggestionsDiv.style.display = 'block';
            }
        } catch (error) {
            console.error('Error searching food:', error);
            suggestionsDiv.innerHTML = `<div class="food-suggestion-item text-danger">Error: ${error.message}</div>`;
            suggestionsDiv.style.display = 'block';
        }
    }, 500);
}

function displayFoodSuggestions(foods, suggestionsDiv, rowId) {
    suggestionsDiv.innerHTML = '';
    
    foods.slice(0, 10).forEach(food => {
        const item = document.createElement('div');
        item.className = 'food-suggestion-item';
        
        // Extract nutrients safely
        let nutrientText = 'Nutritional info unavailable';
        if (food.foodNutrients && food.foodNutrients.length > 0) {
            const calories = food.foodNutrients.find(n => n.nutrientId === NUTRIENT_IDS.CALORIES)?.value || 0;
            const protein = food.foodNutrients.find(n => n.nutrientId === NUTRIENT_IDS.PROTEIN)?.value || 0;
            const carbs = food.foodNutrients.find(n => n.nutrientId === NUTRIENT_IDS.CARBS)?.value || 0;
            const fats = food.foodNutrients.find(n => n.nutrientId === NUTRIENT_IDS.FATS)?.value || 0;
            
            nutrientText = `${Math.round(calories)} cal · ${protein.toFixed(1)}g protein · ${carbs.toFixed(1)}g carbs · ${fats.toFixed(1)}g fat`;
        }
        
        item.innerHTML = `
            <strong>${food.description}</strong>
            <br><small class="text-muted">${food.brandName || 'Generic'}</small>
            <br><small class="text-info">${nutrientText}</small>
        `;
        item.onclick = () => selectFood(food, rowId);
        suggestionsDiv.appendChild(item);
    });
    
    suggestionsDiv.style.display = 'block';
}

function selectFood(food, rowId) {
    const row = document.getElementById('meal-row-' + rowId);
    const foodInput = row.querySelector('.food-search');
    const suggestionsDiv = document.getElementById('suggestions-' + rowId);
    
    foodInput.value = food.description;
    suggestionsDiv.style.display = 'none';
    
    // Extract and display nutrients
    displayNutrients(food.foodNutrients, rowId);
}

function displayNutrients(nutrients, rowId) {
    const nutrientCard = document.getElementById('nutrient-card-' + rowId);
    
    if (!nutrients || nutrients.length === 0) {
        nutrientCard.classList.add('d-none');
        return;
    }
    
    // Extract key nutrients
    const calories = nutrients.find(n => n.nutrientId === NUTRIENT_IDS.CALORIES)?.value || 0;
    const protein = nutrients.find(n => n.nutrientId === NUTRIENT_IDS.PROTEIN)?.value || 0;
    const carbs = nutrients.find(n => n.nutrientId === NUTRIENT_IDS.CARBS)?.value || 0;
    const fats = nutrients.find(n => n.nutrientId === NUTRIENT_IDS.FATS)?.value || 0;
    
    document.getElementById('cal-' + rowId).textContent = Math.round(calories);
    document.getElementById('protein-' + rowId).textContent = protein.toFixed(1);
    document.getElementById('carbs-' + rowId).textContent = carbs.toFixed(1);
    document.getElementById('fats-' + rowId).textContent = fats.toFixed(1);
    
    nutrientCard.classList.remove('d-none');
}

function quickAddMeal(mealType, time, foodName) {
    addMealRow(mealType, time, foodName, '100', 'grams');
}

function updateMealSuggestions() {
    const container = document.getElementById('mealBuilderContainer');
    const mealRows = container.querySelectorAll('.meal-row');
    
    let mealText = '';
    const mealsByType = {
        breakfast: [],
        lunch: [],
        dinner: [],
        snack: []
    };
    
    mealRows.forEach(row => {
        const mealType = row.querySelector('.meal-type').value;
        const time = row.querySelector('.meal-time').value;
        const foodName = row.querySelector('.food-search').value;
        const amount = row.querySelector('.food-amount').value;
        const unit = row.querySelector('.food-unit').value;
        
        if (foodName.trim()) {
            mealsByType[mealType].push({
                time: time,
                food: foodName,
                amount: amount,
                unit: unit
            });
        }
    });
    
    // Format meal text
    Object.keys(mealsByType).forEach(mealType => {
        if (mealsByType[mealType].length > 0) {
            mealText += `${mealType.toUpperCase()}:\n`;
            mealsByType[mealType].forEach(meal => {
                mealText += `- ${meal.time}: ${meal.food}`;
                if (meal.amount) {
                    mealText += ` (${meal.amount} ${meal.unit})`;
                }
                mealText += '\n';
            });
            mealText += '\n';
        }
    });
    
    document.getElementById('mealSuggestionsHidden').value = mealText;
}

function parseMealText(text) {
    // Simple parser - just add one empty row for now
    // In production, you'd parse the text format
    if (text.trim()) {
        addMealRow();
    }
}

// Close suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.food-search-wrapper')) {
        document.querySelectorAll('.food-suggestions').forEach(div => {
            div.style.display = 'none';
        });
    }
});

function displayWorkoutSummary(workoutPlan) {
    const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
    const dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const summaryDiv = document.getElementById('workoutSummary');

    // ── FIX: the payload saved by create_plan.php is {title, plan:{mon:[],tue:[],...}}
    // Detect whether exercises are nested under `.plan` or at the root level.
    const planData = (workoutPlan && workoutPlan.plan) ? workoutPlan.plan : workoutPlan;
    const planTitle = workoutPlan.title || '';

    let html = '';
    let totalExercises = 0;

    days.forEach((day, index) => {
        const exercises = planData[day] || [];
        totalExercises += exercises.length;

        html += `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar-day me-1"></i>${dayNames[index]}
                            <span class="badge ${exercises.length > 0 ? 'bg-success' : 'bg-secondary'} float-end">${exercises.length}</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        ${exercises.length === 0 ?
                            '<p class="text-muted small mb-0"><i class="bi bi-moon-stars me-1"></i>Rest day</p>' :
                            exercises.map(ex => `
                                <div class="small mb-2">
                                    <i class="bi bi-check-circle text-success me-1"></i>
                                    <strong>${ex.name}</strong>
                                    ${ex.isCustom ? '<span class="badge bg-success ms-1">Custom</span>' : ''}
                                    <br><span class="text-muted" style="font-size:11px;">${ex.sets||3} sets × ${ex.reps||10} reps · ${ex.category||''}</span>
                                </div>
                            `).join('')
                        }
                    </div>
                </div>
            </div>
        `;
    });

    summaryDiv.innerHTML = html;

    // Show title + total exercise count
    const activeDays = days.filter(d => (planData[d] || []).length > 0).length;
    const titleLabel = planTitle ? `<strong>${planTitle}</strong> — ` : '';
    summaryDiv.insertAdjacentHTML('beforebegin', `
        <div class="alert ${totalExercises > 0 ? 'alert-success' : 'alert-warning'} mb-3">
            <i class="bi bi-${totalExercises > 0 ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${totalExercises > 0
                ? `${titleLabel}<strong>${totalExercises}</strong> exercises loaded across <strong>${activeDays}</strong> training day${activeDays !== 1 ? 's' : ''}.`
                : 'No exercises found in the saved workout plan. Please go back and add exercises.'}
        </div>
    `);
}

// Load AI Meal Plan suggestions
function loadAIMealPlan(mealPlan) {
    // Populate macros
    const macros = mealPlan.macros || {};
    if (macros.calories) document.querySelector('input[name="target_calories"]').value = macros.calories;
    if (macros.protein) document.querySelector('input[name="target_protein_g"]').value = macros.protein;
    if (macros.carbs) document.querySelector('input[name="target_carbs_g"]').value = macros.carbs;
    if (macros.fats) document.querySelector('input[name="target_fats_g"]').value = macros.fats;

    // Populate meals
    const meals = mealPlan.meals || [];
    const container = document.getElementById('mealBuilderContainer');
    container.innerHTML = ''; // Clear rows
    
    meals.forEach(meal => {
        const type = (meal.type || 'breakfast').toLowerCase();
        const time = convertTo24h(meal.time || '08:00');
        const food = meal.foodName || meal.food || '';
        const amount = meal.amount || '';
        const unit = meal.unit || 'grams';
        
        addMealRow(type, time, food, amount, unit);
    });
    
    // Add AI Notes to nutrition notes if exists
    const aiNotes = localStorage.getItem('ai_notes_' + REQUEST_ID);
    if (aiNotes) {
        const notesTextarea = document.querySelector('textarea[name="nutrition_notes"]');
        if (notesTextarea && !notesTextarea.value.trim()) {
            notesTextarea.value = aiNotes;
        }
    }
}

// Trigger load manually from the button
function triggerLoadAIMealPlan() {
    const aiMealPlanJSON = localStorage.getItem('ai_meal_plan_' + REQUEST_ID);
    if (aiMealPlanJSON) {
        try {
            if (confirm('Load AI recommended meal plan? This will overwrite the current meal builder entries.')) {
                const aiMealPlan = JSON.parse(aiMealPlanJSON);
                loadAIMealPlan(aiMealPlan);
            }
        } catch (e) {
            console.error('Error parsing AI meal plan:', e);
            alert('Failed to parse AI meal suggestions.');
        }
    } else {
        alert('No AI meal suggestions found. Did you generate the AI plan in the previous step?');
    }
}

// Convert "07:00 AM" to "07:00" for time inputs
function convertTo24h(timeStr) {
    if (!timeStr) return '08:00';
    timeStr = timeStr.trim();
    const match = timeStr.match(/^(\d+):(\d+)\s*(AM|PM)$/i);
    if (!match) {
        if (/^\d{2}:\d{2}$/.test(timeStr)) return timeStr;
        return '08:00';
    }
    let h = parseInt(match[1]);
    const m = match[2];
    const period = match[3].toUpperCase();
    if (period === 'PM' && h !== 12) h += 12;
    if (period === 'AM' && h === 12) h = 0;
    return `${String(h).padStart(2, '0')}:${m}`;
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
