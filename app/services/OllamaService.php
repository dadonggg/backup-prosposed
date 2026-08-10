<?php
declare(strict_types=1);
namespace App\Services;

/**
 * Ollama AI Service
 * Generates fitness plans using local Ollama LLM
 * 
 * Requirements:
 * - Ollama installed: curl -fsSL https://ollama.com/install.sh | sh
 * - Model pulled: ollama pull llama3.2
 * - Server running: ollama serve
 */
class OllamaService
{
    private const OLLAMA_URL = 'http://localhost:11434/api/generate';
    private const MODEL = 'llama3.2';
    private const TIMEOUT = 120; // 2 minutes timeout
    
    /**
     * Generate complete fitness plan from client profile
     */
    public function generateFitnessPlan(array $clientProfile): array
    {
        $startTime = microtime(true);
        
        try {
            // Build prompt
            $prompt = $this->buildPrompt($clientProfile);
            
            // Call Ollama API
            $response = $this->callOllama($prompt);
            
            // Calculate generation time
            $generationTime = (int)((microtime(true) - $startTime) * 1000);
            
            // Parse and validate response
            $plan = $this->parseResponse($response);
            
            return [
                'success' => true,
                'plan' => $plan,
                'generationTime' => $generationTime,
                'model' => self::MODEL
            ];
            
        } catch (\Exception $e) {
            $generationTime = (int)((microtime(true) - $startTime) * 1000);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'generationTime' => $generationTime,
                'model' => self::MODEL
            ];
        }
    }
    
    /**
     * Build AI prompt from client profile
     */
    private function buildPrompt(array $profile): string
    {
        $goalsStr = is_array($profile['fitness_goals'] ?? null) 
            ? implode(', ', $profile['fitness_goals'])
            : ($profile['fitness_goals'] ?? 'General Fitness');
            
        $prompt = <<<PROMPT
You are an expert fitness trainer AI assistant creating personalized plans.

CLIENT PROFILE:
- Name: {$profile['name']}
- Age: {$profile['age']}
- Fitness Goals: {$goalsStr}
- Current Activity Level: {$profile['activity_level']}
- Medical Conditions: {$profile['medical_conditions'] ?: 'None'}
- Dietary Preferences: {$profile['dietary_preferences'] ?: 'None'}
- Sessions Per Week: {$profile['sessions_per_week']}

Generate a complete fitness plan with:

1. WORKOUT ROUTINE:
   - Assign exercises to specific days (MON, TUE, WED, THU, FRI, SAT, SUN)
   - Each exercise must have: name, sets, reps, restSeconds
   - Rest days should have empty array []
   - Match intensity to activity level
   - Focus on the client's fitness goals
   - Include variety and progression

2. MEAL PLAN:
   - Daily macro targets: calories, protein (g), carbs (g), fats (g)
   - Meals array with: type (Breakfast/Lunch/Dinner/Snack),
     time (HH:MM AM/PM format), foodName, amount, unit
   - Respect dietary preferences
   - Avoid foods conflicting with medical conditions
   - Provide practical, easy-to-follow meal suggestions

3. QUICK MEAL SUGGESTIONS:
   - Provide 3-4 quick meal ideas for each meal type
   - Make them practical and aligned with dietary preferences

4. AI NOTES:
   - Brief explanation of why this plan suits the client
   - Any warnings based on medical conditions
   - Tips for success

Respond ONLY with valid JSON in this exact format:
{
  "workoutRoutine": {
    "MON": [{"name": "Push-ups", "sets": 3, "reps": 12, "restSeconds": 60}],
    "TUE": [],
    "WED": [{"name": "Squats", "sets": 4, "reps": 15, "restSeconds": 90}],
    "THU": [],
    "FRI": [{"name": "Pull-ups", "sets": 3, "reps": 8, "restSeconds": 120}],
    "SAT": [],
    "SUN": []
  },
  "mealPlan": {
    "macros": {
      "calories": 2200,
      "protein": 165,
      "carbs": 220,
      "fats": 70
    },
    "meals": [
      {
        "type": "Breakfast",
        "time": "07:00 AM",
        "foodName": "Oatmeal with banana and protein powder",
        "amount": 250,
        "unit": "grams"
      }
    ],
    "quickSuggestions": {
      "Breakfast": ["Scrambled eggs with toast", "Greek yogurt with berries", "Protein pancakes"],
      "Lunch": ["Grilled chicken salad", "Turkey sandwich", "Quinoa bowl"],
      "Dinner": ["Salmon with vegetables", "Chicken stir-fry", "Lean beef with rice"],
      "Snack": ["Protein shake", "Mixed nuts", "Apple with peanut butter"]
    }
  },
  "aiNotes": "This plan focuses on..."
}
PROMPT;
        
        return $prompt;
    }
    
    /**
     * Call Ollama API
     */
    private function callOllama(string $prompt): string
    {
        $data = [
            'model' => self::MODEL,
            'prompt' => $prompt,
            'stream' => false,
            'format' => 'json',
            'options' => [
                'temperature' => 0.7,
                'top_p' => 0.9
            ]
        ];
        
        $ch = curl_init(self::OLLAMA_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new \Exception("Ollama connection error: {$curlError}. Make sure Ollama is running (ollama serve)");
        }
        
        if ($httpCode !== 200) {
            throw new \Exception("Ollama returned HTTP {$httpCode}");
        }
        
        if (!$response) {
            throw new \Exception("Empty response from Ollama");
        }
        
        return $response;
    }
    
    /**
     * Parse Ollama response
     */
    private function parseResponse(string $response): array
    {
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse Ollama response: " . json_last_error_msg());
        }
        
        if (!isset($decoded['response'])) {
            throw new \Exception("Invalid Ollama response format");
        }
        
        // The actual AI response is in the 'response' field
        $aiResponse = json_decode($decoded['response'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse AI plan JSON: " . json_last_error_msg());
        }
        
        // Validate required fields
        if (!isset($aiResponse['workoutRoutine']) || !isset($aiResponse['mealPlan'])) {
            throw new \Exception("AI response missing required fields");
        }
        
        return $aiResponse;
    }
    
    /**
     * Check if Ollama is running and model is available
     */
    public function checkOllamaStatus(): array
    {
        try {
            $ch = curl_init('http://localhost:11434/api/tags');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                return [
                    'available' => false,
                    'error' => 'Ollama server not responding'
                ];
            }
            
            $data = json_decode($response, true);
            $models = array_column($data['models'] ?? [], 'name');
            
            $hasModel = false;
            foreach ($models as $model) {
                if (strpos($model, self::MODEL) !== false) {
                    $hasModel = true;
                    break;
                }
            }
            
            return [
                'available' => $hasModel,
                'models' => $models,
                'error' => $hasModel ? null : 'Model ' . self::MODEL . ' not found. Run: ollama pull ' . self::MODEL
            ];
            
        } catch (\Exception $e) {
            return [
                'available' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
