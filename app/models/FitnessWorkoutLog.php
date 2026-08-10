<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

final class FitnessWorkoutLog extends Model
{
    public function create(int $memberId, int $serviceRequestId, array $data): int
    {
        try {
            $stmt = $this->db()->prepare(
                'INSERT INTO fitness_workout_logs 
                (member_id, service_request_id, log_date, exercise_name, sets, reps, weight_kg, duration_minutes, notes)
                VALUES (:mid, :req_id, :date, :exercise, :sets, :reps, :weight, :duration, :notes)'
            );
            
            $stmt->execute([
                ':mid' => $memberId,
                ':req_id' => $serviceRequestId,
                ':date' => $data['log_date'],
                ':exercise' => $data['exercise_name'],
                ':sets' => $data['sets'] ?? 0,
                ':reps' => $data['reps'] ?? 0,
                ':weight' => $data['weight_kg'] ?? 0,
                ':duration' => $data['duration_minutes'] ?? 0,
                ':notes' => $data['notes'] ?? ''
            ]);
            
            return (int)$this->db()->lastInsertId();
        } catch (\Exception $e) {
            $this->logError("Failed to create workout log: " . $e->getMessage());
            return 0;
        }
    }

    public function findByServiceRequestId(int $serviceRequestId, int $limit = 50): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM fitness_workout_logs 
             WHERE service_request_id = :req_id 
             ORDER BY log_date DESC, created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue(':req_id', $serviceRequestId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByMemberId(int $memberId, int $limit = 50): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM fitness_workout_logs 
             WHERE member_id = :mid 
             ORDER BY log_date DESC, created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue(':mid', $memberId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLoggedDates(int $serviceRequestId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT DISTINCT log_date FROM fitness_workout_logs 
             WHERE service_request_id = :req_id 
             ORDER BY log_date ASC'
        );
        $stmt->execute([':req_id' => $serviceRequestId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'log_date');
    }

    public function getWeeklyFrequency(int $serviceRequestId, int $weeks = 4): array
    {
        $stmt = $this->db()->prepare(
            'SELECT 
                YEARWEEK(log_date) as week,
                COUNT(DISTINCT log_date) as workout_days
             FROM fitness_workout_logs 
             WHERE service_request_id = :req_id 
               AND log_date >= DATE_SUB(CURDATE(), INTERVAL :weeks WEEK)
             GROUP BY YEARWEEK(log_date)
             ORDER BY week ASC'
        );
        $stmt->execute([':req_id' => $serviceRequestId, ':weeks' => $weeks]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id, int $memberId): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'DELETE FROM fitness_workout_logs WHERE id = :id AND member_id = :mid'
            );
            return $stmt->execute([':id' => $id, ':mid' => $memberId]);
        } catch (\Exception $e) {
            $this->logError("Failed to delete workout log: " . $e->getMessage());
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
        $logMessage = sprintf("[%s] FitnessWorkoutLog: %s\n", date('Y-m-d H:i:s'), $message);
        @error_log($logMessage, 3, $logFile);
    }
}
