<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;
use App\Models\Employee;
use App\Models\FitnessServiceRequest;
use App\Models\FitnessClientProfile;
use App\Models\FitnessAISuggestion;

/**
 * Trainer AI Controller
 * Handles trainer viewing and editing of AI-generated fitness plans
 */
final class TrainerAIController extends Controller
{
    /**
     * Require trainer authentication
     */
    private function requireTrainer(): array
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
        
        $user = (new User())->findById((int)$_SESSION['user_id']);
        if (!$user || $user['role'] !== 'trainer') {
            $_SESSION['error'] = 'Access denied. Trainers only.';
            $this->redirect('home/landing');
        }
        
        $employee = (new Employee())->findByUserId((int)$user['id']);
        if (!$employee) {
            $_SESSION['error'] = 'Trainer profile not found.';
            $this->redirect('home/landing');
        }
        
        return ['user' => $user, 'employee' => $employee];
    }
    
    /**
     * View AI suggestions for a client
     * GET: index.php?r=trainerAi/viewSuggestions&request_id=X
     */
    public function viewSuggestionsAction(): void
    {
        $data = $this->requireTrainer();
        $user = $data['user'];
        $employee = $data['employee'];
        
        $requestId = (int)($_GET['request_id'] ?? 0);
        if ($requestId === 0) {
            $_SESSION['error'] = 'Invalid request ID';
            $this->redirect('trainer/clients');
        }
        
        // Get service request
        $requestModel = new FitnessServiceRequest();
        $request = $requestModel->findById($requestId);
        
        if (!$request) {
            $_SESSION['error'] = 'Service request not found';
            $this->redirect('trainer/clients');
        }
        
        // Verify this trainer is assigned
        if ((int)$request['assigned_trainer_id'] !== (int)$employee['id']) {
            $_SESSION['error'] = 'You are not assigned to this client';
            $this->redirect('trainer/clients');
        }
        
        // Get client profile
        $profileModel = new FitnessClientProfile();
        $profile = $profileModel->findByServiceRequestId($requestId);
        
        // Get AI suggestions
        $aiModel = new FitnessAISuggestion();
        $aiSuggestion = $aiModel->findByServiceRequestId($requestId);
        
        // Check if AI plan exists
        if (!$aiSuggestion) {
            $_SESSION['info'] = 'No AI suggestions found for this client. You can create a plan manually.';
            $this->redirect('trainer/createPlan&request_id=' . $requestId);
        }
        
        // Render view
        require __DIR__ . '/../views/trainer/ai_suggestions.php';
    }
    
    /**
     * Save edited workout plan
     * POST: index.php?r=trainerAi/saveWorkout
     */
    public function saveWorkoutAction(): void
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            return;
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $requestId = (int)($data['request_id'] ?? 0);
            $workoutSchedule = $data['workout_schedule'] ?? [];
            
            if ($requestId === 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid request ID']);
                return;
            }
            
            // Get trainer info
            $user = (new User())->findById((int)$_SESSION['user_id']);
            $employee = (new Employee())->findByUserId((int)$user['id']);
            
            // Get request
            $request = (new FitnessServiceRequest())->findById($requestId);
            
            // Get AI suggestion ID
            $aiSuggestion = (new FitnessAISuggestion())->findByServiceRequestId($requestId);
            $aiSuggestionId = $aiSuggestion['id'] ?? null;
            
            // Save to database
            $pdo = Database::pdo();
            
            // Check if draft exists
            $stmt = $pdo->prepare(
                'SELECT id FROM fitness_trainer_workout_plans
                 WHERE service_request_id = :request_id AND status = "draft"
                 LIMIT 1'
            );
            $stmt->execute([':request_id' => $requestId]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Update existing draft
                $stmt = $pdo->prepare(
                    'UPDATE fitness_trainer_workout_plans
                     SET workout_schedule = :schedule, updated_at = NOW()
                     WHERE id = :id'
                );
                $stmt->execute([
                    ':schedule' => json_encode($workoutSchedule),
                    ':id' => $existing['id']
                ]);
            } else {
                // Create new draft
                $stmt = $pdo->prepare(
                    'INSERT INTO fitness_trainer_workout_plans
                     (service_request_id, trainer_id, member_id, ai_suggestion_id,
                      workout_schedule, status)
                     VALUES (:request_id, :trainer_id, :member_id, :ai_suggestion_id,
                             :schedule, "draft")'
                );
                $stmt->execute([
                    ':request_id' => $requestId,
                    ':trainer_id' => $employee['id'],
                    ':member_id' => $request['member_id'],
                    ':ai_suggestion_id' => $aiSuggestionId,
                    ':schedule' => json_encode($workoutSchedule)
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Workout plan saved as draft'
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Save edited meal plan and send complete plan to client
     * POST: index.php?r=trainerAi/saveMealPlan
     */
    public function saveMealPlanAction(): void
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            return;
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $requestId = (int)($data['request_id'] ?? 0);
            $macros = $data['macros'] ?? [];
            $meals = $data['meals'] ?? [];
            
            if ($requestId === 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid request ID']);
                return;
            }
            
            // Get trainer info
            $user = (new User())->findById((int)$_SESSION['user_id']);
            $employee = (new Employee())->findByUserId((int)$user['id']);
            
            // Get request
            $request = (new FitnessServiceRequest())->findById($requestId);
            
            // Get AI suggestion ID
            $aiSuggestion = (new FitnessAISuggestion())->findByServiceRequestId($requestId);
            $aiSuggestionId = $aiSuggestion['id'] ?? null;
            
            $pdo = Database::pdo();
            
            // Save meal plan
            $stmt = $pdo->prepare(
                'INSERT INTO fitness_trainer_meal_plans
                 (service_request_id, trainer_id, member_id, ai_suggestion_id,
                  daily_macros, meals, status, sent_at)
                 VALUES (:request_id, :trainer_id, :member_id, :ai_suggestion_id,
                         :macros, :meals, "sent", NOW())
                 ON DUPLICATE KEY UPDATE
                 daily_macros = :macros, meals = :meals, status = "sent", sent_at = NOW()'
            );
            $stmt->execute([
                ':request_id' => $requestId,
                ':trainer_id' => $employee['id'],
                ':member_id' => $request['member_id'],
                ':ai_suggestion_id' => $aiSuggestionId,
                ':macros' => json_encode($macros),
                ':meals' => json_encode($meals)
            ]);
            
            // Mark workout plan as sent
            $stmt = $pdo->prepare(
                'UPDATE fitness_trainer_workout_plans
                 SET status = "sent", sent_at = NOW(), client_notified = 1
                 WHERE service_request_id = :request_id'
            );
            $stmt->execute([':request_id' => $requestId]);
            
            // Create notification for client
            $this->createNotification(
                $request['member_id'],
                'fitness_plan_ready',
                '🎉 Your personalized fitness plan is ready!',
                'Your trainer has created a customized workout and meal plan for you. Click to view it now.',
                'index.php?r=fitness/plan&request_id=' . $requestId
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Complete plan sent to client!'
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Edit meal plan page
     * GET: index.php?r=trainerAi/editMealPlan&request_id=X
     */
    public function editMealPlanAction(): void
    {
        $data = $this->requireTrainer();
        $user = $data['user'];
        $employee = $data['employee'];
        
        $requestId = (int)($_GET['request_id'] ?? 0);
        if ($requestId === 0) {
            $_SESSION['error'] = 'Invalid request ID';
            $this->redirect('trainer/clients');
        }
        
        // Get service request
        $requestModel = new FitnessServiceRequest();
        $request = $requestModel->findById($requestId);
        
        if (!$request) {
            $_SESSION['error'] = 'Service request not found';
            $this->redirect('trainer/clients');
        }
        
        // Verify this trainer is assigned
        if ((int)$request['assigned_trainer_id'] !== (int)$employee['id']) {
            $_SESSION['error'] = 'You are not assigned to this client';
            $this->redirect('trainer/clients');
        }
        
        // Get AI suggestions
        $aiModel = new FitnessAISuggestion();
        $aiSuggestion = $aiModel->findByServiceRequestId($requestId);
        
        // Get workout plan draft
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT workout_schedule FROM fitness_trainer_workout_plans
             WHERE service_request_id = :request_id AND status = "draft"
             LIMIT 1'
        );
        $stmt->execute([':request_id' => $requestId]);
        $workoutDraft = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Render view
        require __DIR__ . '/../views/trainer/edit_meal_plan.php';
    }

    /**
     * Create notification for user
     */
    private function createNotification(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null
    ): void {
        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare(
                'INSERT INTO notifications 
                 (user_id, type, title, message, action_url, is_read, created_at)
                 VALUES (:user_id, :type, :title, :message, :action_url, 0, NOW())'
            );
            $stmt->execute([
                ':user_id' => $userId,
                ':type' => $type,
                ':title' => $title,
                ':message' => $message,
                ':action_url' => $actionUrl
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            error_log('Failed to create notification: ' . $e->getMessage());
        }
    }
}
