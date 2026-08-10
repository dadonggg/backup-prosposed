<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

final class FitnessNutritionLog extends Model
{
    public function create(int $memberId, int $serviceRequestId, array $data): int
    {
        try {
            $stmt = $this->db()->prepare(
                'INSERT INTO fitness_nutrition_logs 
                (member_id, service_request_id, log_date, meal_type, food_item, calories, protein_g, carbs_g, fats_g, notes)
                VALUES (:mid, :req_id, :date, :meal, :food, :cal, :protein, :carbs, :fats, :notes)'
            );
            
            $stmt->execute([
                ':mid' => $memberId,
                ':req_id' => $serviceRequestId,
                ':date' => $data['log_date'],
                ':meal' => $data['meal_type'],
                ':food' => $data['food_item'],
                ':cal' => $data['calories'] ?? 0,
                ':protein' => $data['protein_g'] ?? 0,
                ':carbs' => $data['carbs_g'] ?? 0,
                ':fats' => $data['fats_g'] ?? 0,
                ':notes' => $data['notes'] ?? ''
            ]);
            
            return (int)$this->db()->lastInsertId();
        } catch (\Exception $e) {
            $this->logError("Failed to create nutrition log: " . $e->getMessage());
            return 0;
        }
    }

    public function findByServiceRequestId(int $serviceRequestId, int $limit = 50): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM fitness_nutrition_logs 
             WHERE service_request_id = :req_id 
             ORDER BY log_date DESC, created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue(':req_id', $serviceRequestId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDailySummary(int $serviceRequestId, string $date): array
    {
        $stmt = $this->db()->prepare(
            'SELECT 
                meal_type,
                SUM(calories) as total_calories,
                SUM(protein_g) as total_protein,
                SUM(carbs_g) as total_carbs,
                SUM(fats_g) as total_fats,
                COUNT(*) as meal_count
             FROM fitness_nutrition_logs 
             WHERE service_request_id = :req_id AND log_date = :date
             GROUP BY meal_type'
        );
        $stmt->execute([':req_id' => $serviceRequestId, ':date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id, int $memberId): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'DELETE FROM fitness_nutrition_logs WHERE id = :id AND member_id = :mid'
            );
            return $stmt->execute([':id' => $id, ':mid' => $memberId]);
        } catch (\Exception $e) {
            $this->logError("Failed to delete nutrition log: " . $e->getMessage());
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
        $logMessage = sprintf("[%s] FitnessNutritionLog: %s\n", date('Y-m-d H:i:s'), $message);
        @error_log($logMessage, 3, $logFile);
    }
}
