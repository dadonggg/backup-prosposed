<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

final class FitnessAISuggestion extends Model
{
    /**
     * Create or update AI suggestion
     */
    public function upsert(int $serviceRequestId, int $memberId, array $data): int
    {
        // Check if exists
        $existing = $this->findByServiceRequestId($serviceRequestId);
        
        if ($existing) {
            // Update existing
            $this->update($existing['id'], $data);
            return $existing['id'];
        }
        
        // Create new
        $stmt = $this->db()->prepare(
            'INSERT INTO fitness_ai_suggestions
             (service_request_id, member_id, fitness_goals, activity_level,
              medical_conditions, dietary_preferences, sessions_per_week,
              workout_suggestions, meal_macros, meal_suggestions,
              meal_quick_suggestions, ai_notes, ai_model)
             VALUES
             (:service_request_id, :member_id, :fitness_goals, :activity_level,
              :medical_conditions, :dietary_preferences, :sessions_per_week,
              :workout_suggestions, :meal_macros, :meal_suggestions,
              :meal_quick_suggestions, :ai_notes, :ai_model)'
        );
        
        $stmt->execute([
            ':service_request_id' => $serviceRequestId,
            ':member_id' => $memberId,
            ':fitness_goals' => json_encode($data['fitness_goals'] ?? []),
            ':activity_level' => $data['activity_level'] ?? null,
            ':medical_conditions' => $data['medical_conditions'] ?? null,
            ':dietary_preferences' => $data['dietary_preferences'] ?? null,
            ':sessions_per_week' => $data['sessions_per_week'] ?? 3,
            ':workout_suggestions' => json_encode($data['workout_suggestions'] ?? []),
            ':meal_macros' => json_encode($data['meal_macros'] ?? []),
            ':meal_suggestions' => json_encode($data['meal_suggestions'] ?? []),
            ':meal_quick_suggestions' => json_encode($data['meal_quick_suggestions'] ?? []),
            ':ai_notes' => $data['ai_notes'] ?? null,
            ':ai_model' => $data['ai_model'] ?? 'llama3.2'
        ]);
        
        return (int)$this->db()->lastInsertId();
    }
    
    /**
     * Update existing AI suggestion
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db()->prepare(
            'UPDATE fitness_ai_suggestions
             SET fitness_goals = :fitness_goals,
                 activity_level = :activity_level,
                 medical_conditions = :medical_conditions,
                 dietary_preferences = :dietary_preferences,
                 sessions_per_week = :sessions_per_week,
                 workout_suggestions = :workout_suggestions,
                 meal_macros = :meal_macros,
                 meal_suggestions = :meal_suggestions,
                 meal_quick_suggestions = :meal_quick_suggestions,
                 ai_notes = :ai_notes
             WHERE id = :id'
        );
        
        return $stmt->execute([
            ':id' => $id,
            ':fitness_goals' => json_encode($data['fitness_goals'] ?? []),
            ':activity_level' => $data['activity_level'] ?? null,
            ':medical_conditions' => $data['medical_conditions'] ?? null,
            ':dietary_preferences' => $data['dietary_preferences'] ?? null,
            ':sessions_per_week' => $data['sessions_per_week'] ?? 3,
            ':workout_suggestions' => json_encode($data['workout_suggestions'] ?? []),
            ':meal_macros' => json_encode($data['meal_macros'] ?? []),
            ':meal_suggestions' => json_encode($data['meal_suggestions'] ?? []),
            ':meal_quick_suggestions' => json_encode($data['meal_quick_suggestions'] ?? []),
            ':ai_notes' => $data['ai_notes'] ?? null
        ]);
    }
    
    /**
     * Find AI suggestion by service request ID
     */
    public function findByServiceRequestId(int $serviceRequestId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM fitness_ai_suggestions
             WHERE service_request_id = :id
             ORDER BY generated_at DESC
             LIMIT 1'
        );
        $stmt->execute([':id' => $serviceRequestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        // Decode JSON fields
        $row['fitness_goals'] = json_decode($row['fitness_goals'] ?? '[]', true);
        $row['workout_suggestions'] = json_decode($row['workout_suggestions'] ?? '{}', true);
        $row['meal_macros'] = json_decode($row['meal_macros'] ?? '{}', true);
        $row['meal_suggestions'] = json_decode($row['meal_suggestions'] ?? '[]', true);
        $row['meal_quick_suggestions'] = json_decode($row['meal_quick_suggestions'] ?? '{}', true);
        
        return $row;
    }
    
    /**
     * Find AI suggestion by member ID
     */
    public function findByMemberId(int $memberId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM fitness_ai_suggestions
             WHERE member_id = :id
             ORDER BY generated_at DESC
             LIMIT 1'
        );
        $stmt->execute([':id' => $memberId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        // Decode JSON fields
        $row['fitness_goals'] = json_decode($row['fitness_goals'] ?? '[]', true);
        $row['workout_suggestions'] = json_decode($row['workout_suggestions'] ?? '{}', true);
        $row['meal_macros'] = json_decode($row['meal_macros'] ?? '{}', true);
        $row['meal_suggestions'] = json_decode($row['meal_suggestions'] ?? '[]', true);
        $row['meal_quick_suggestions'] = json_decode($row['meal_quick_suggestions'] ?? '{}', true);
        
        return $row;
    }
    
    /**
     * Log AI generation attempt
     */
    public function logGeneration(int $serviceRequestId, int $memberId, array $logData): void
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO fitness_ai_generation_logs
             (service_request_id, member_id, prompt_sent, model_used,
              response_received, response_status, error_message, generation_time_ms)
             VALUES
             (:service_request_id, :member_id, :prompt_sent, :model_used,
              :response_received, :response_status, :error_message, :generation_time_ms)'
        );
        
        $stmt->execute([
            ':service_request_id' => $serviceRequestId,
            ':member_id' => $memberId,
            ':prompt_sent' => $logData['prompt'] ?? '',
            ':model_used' => $logData['model'] ?? 'llama3.2',
            ':response_received' => $logData['response'] ?? null,
            ':response_status' => $logData['status'] ?? 'success',
            ':error_message' => $logData['error'] ?? null,
            ':generation_time_ms' => $logData['generation_time'] ?? null
        ]);
    }
}
