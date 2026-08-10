<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

final class FitnessTrainerPlan extends Model
{
    public function create(int $serviceRequestId, int $trainerId, ?int $clientProfileId, array $data): int
    {
        try {
            $stmt = $this->db()->prepare(
                'INSERT INTO fitness_trainer_plans 
                (service_request_id, trainer_id, client_profile_id, fitness_level, primary_goals, 
                 limitations_notes, recommended_sessions_per_week, fitness_plan_monday, fitness_plan_tuesday,
                 fitness_plan_wednesday, fitness_plan_thursday, fitness_plan_friday, fitness_plan_saturday,
                 fitness_plan_sunday, fitness_plan_notes, target_calories, target_protein_g, target_carbs_g,
                 target_fats_g, meal_suggestions, nutrition_notes, status)
                VALUES (:req_id, :tid, :prof_id, :level, :goals, :limits, :sessions, :mon, :tue, :wed, :thu,
                        :fri, :sat, :sun, :fit_notes, :cal, :protein, :carbs, :fats, :meals, :nut_notes, :status)'
            );
            
            $stmt->execute([
                ':req_id'   => $serviceRequestId,
                ':tid'      => $trainerId,
                ':prof_id'  => $clientProfileId ?: null,
                ':level'    => $data['fitness_level'],
                ':goals'    => $data['primary_goals'] ?? '',
                ':limits'   => $data['limitations_notes'] ?? '',
                ':sessions' => $data['recommended_sessions_per_week'] ?? 3,
                ':mon'      => $data['fitness_plan_monday'] ?? '',
                ':tue'      => $data['fitness_plan_tuesday'] ?? '',
                ':wed'      => $data['fitness_plan_wednesday'] ?? '',
                ':thu'      => $data['fitness_plan_thursday'] ?? '',
                ':fri'      => $data['fitness_plan_friday'] ?? '',
                ':sat'      => $data['fitness_plan_saturday'] ?? '',
                ':sun'      => $data['fitness_plan_sunday'] ?? '',
                ':fit_notes'=> $data['fitness_plan_notes'] ?? '',
                ':cal'      => $data['target_calories'] ?? null,
                ':protein'  => $data['target_protein_g'] ?? null,
                ':carbs'    => $data['target_carbs_g'] ?? null,
                ':fats'     => $data['target_fats_g'] ?? null,
                ':meals'    => $data['meal_suggestions'] ?? '',
                ':nut_notes'=> $data['nutrition_notes'] ?? '',
                ':status'   => $data['status'] ?? 'draft'
            ]);
            
            return (int)$this->db()->lastInsertId();
        } catch (\Exception $e) {
            $this->logError("Failed to create trainer plan: " . $e->getMessage());
            return 0;
        }
    }

    public function findByServiceRequestId(int $serviceRequestId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT ftp.*, u.fullname as trainer_name
             FROM fitness_trainer_plans ftp
             JOIN employees e ON e.id = ftp.trainer_id
             JOIN users u ON u.id = e.user_id
             WHERE ftp.service_request_id = :req_id
             LIMIT 1'
        );
        $stmt->execute([':req_id' => $serviceRequestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function sendToClient(int $planId): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'UPDATE fitness_trainer_plans SET status = "sent", sent_at = NOW() WHERE id = :id'
            );
            return $stmt->execute([':id' => $planId]);
        } catch (\Exception $e) {
            $this->logError("Failed to send plan: " . $e->getMessage());
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'UPDATE fitness_trainer_plans 
                 SET fitness_level = :level, primary_goals = :goals, limitations_notes = :limits,
                     recommended_sessions_per_week = :sessions, fitness_plan_monday = :mon,
                     fitness_plan_tuesday = :tue, fitness_plan_wednesday = :wed, fitness_plan_thursday = :thu,
                     fitness_plan_friday = :fri, fitness_plan_saturday = :sat, fitness_plan_sunday = :sun,
                     fitness_plan_notes = :fit_notes, target_calories = :cal, target_protein_g = :protein,
                     target_carbs_g = :carbs, target_fats_g = :fats, meal_suggestions = :meals,
                     nutrition_notes = :nut_notes, status = :status, updated_at = NOW()
                 WHERE id = :id'
            );
            
            return $stmt->execute([
                ':level'    => $data['fitness_level'],
                ':goals'    => $data['primary_goals'] ?? '',
                ':limits'   => $data['limitations_notes'] ?? '',
                ':sessions' => $data['recommended_sessions_per_week'] ?? 3,
                ':mon'      => $data['fitness_plan_monday'] ?? '',
                ':tue'      => $data['fitness_plan_tuesday'] ?? '',
                ':wed'      => $data['fitness_plan_wednesday'] ?? '',
                ':thu'      => $data['fitness_plan_thursday'] ?? '',
                ':fri'      => $data['fitness_plan_friday'] ?? '',
                ':sat'      => $data['fitness_plan_saturday'] ?? '',
                ':sun'      => $data['fitness_plan_sunday'] ?? '',
                ':fit_notes'=> $data['fitness_plan_notes'] ?? '',
                ':cal'      => $data['target_calories'] ?? null,
                ':protein'  => $data['target_protein_g'] ?? null,
                ':carbs'    => $data['target_carbs_g'] ?? null,
                ':fats'     => $data['target_fats_g'] ?? null,
                ':meals'    => $data['meal_suggestions'] ?? '',
                ':nut_notes'=> $data['nutrition_notes'] ?? '',
                ':status'   => $data['status'] ?? 'draft',
                ':id'       => $id
            ]);
        } catch (\Exception $e) {
            $this->logError("Failed to update plan: " . $e->getMessage());
            return false;
        }
    }


    private function logError(string $message): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/fitness_service.log';
        $logMessage = sprintf("[%s] FitnessTrainerPlan: %s\n", date('Y-m-d H:i:s'), $message);
        @error_log($logMessage, 3, $logFile);
    }
}
