<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

final class FitnessProgressTracking extends Model
{
    public function calculateAndSave(int $memberId, int $serviceRequestId): int
    {
        try {
            // Get all logged dates (both workout and nutrition)
            $workoutDates = $this->getWorkoutDates($serviceRequestId);
            $nutritionDates = $this->getNutritionDates($serviceRequestId);
            $allDates = array_unique(array_merge($workoutDates, $nutritionDates));
            sort($allDates);
            
            if (empty($allDates)) {
                return 0;
            }
            
            // Calculate consistency score using formula: Sc = Σ(B + (si · w))
            $B = 10; // Base points per log
            $w = 2;  // Streak bonus weight
            $totalScore = 0;
            $currentStreak = 0;
            $maxStreak = 0;
            $lastDate = null;
            
            foreach ($allDates as $date) {
                $dateObj = new \DateTime($date);
                
                if ($lastDate !== null) {
                    $lastDateObj = new \DateTime($lastDate);
                    $diff = $lastDateObj->diff($dateObj)->days;
                    
                    if ($diff === 1) {
                        $currentStreak++;
                    } else if ($diff > 1) {
                        $currentStreak = 0;
                    }
                }
                
                $totalScore += $B + ($currentStreak * $w);
                $maxStreak = max($maxStreak, $currentStreak);
                $lastDate = $date;
            }
            
            // Calculate workout frequency per week
            $totalDays = count($allDates);
            $firstDate = new \DateTime($allDates[0]);
            $lastDateObj = new \DateTime($allDates[count($allDates) - 1]);
            $daysDiff = $firstDate->diff($lastDateObj)->days + 1;
            $weeks = max(1, $daysDiff / 7);
            $workoutFrequency = $totalDays / $weeks;
            
            // Save progress snapshot
            $stmt = $this->db()->prepare(
                'INSERT INTO fitness_progress_tracking 
                (member_id, service_request_id, snapshot_date, consistency_score, current_streak, 
                 total_logged_days, total_workouts, total_nutrition_logs, workout_frequency_per_week)
                VALUES (:mid, :req_id, CURDATE(), :score, :streak, :days, :workouts, :nutrition, :frequency)'
            );
            
            $stmt->execute([
                ':mid' => $memberId,
                ':req_id' => $serviceRequestId,
                ':score' => round($totalScore, 2),
                ':streak' => $currentStreak,
                ':days' => $totalDays,
                ':workouts' => count($workoutDates),
                ':nutrition' => count($nutritionDates),
                ':frequency' => round($workoutFrequency, 2)
            ]);
            
            return (int)$this->db()->lastInsertId();
        } catch (\Exception $e) {
            $this->logError("Failed to calculate progress: " . $e->getMessage());
            return 0;
        }
    }

    public function getCurrentProgress(int $serviceRequestId): array
    {
        // Get all logged dates
        $workoutDates = $this->getWorkoutDates($serviceRequestId);
        $nutritionDates = $this->getNutritionDates($serviceRequestId);
        $allDates = array_unique(array_merge($workoutDates, $nutritionDates));
        sort($allDates);
        
        if (empty($allDates)) {
            return [
                'consistency_score' => 0,
                'current_streak' => 0,
                'total_logged_days' => 0,
                'total_workouts' => 0,
                'total_nutrition_logs' => 0,
                'workout_frequency_per_week' => 0
            ];
        }
        
        // Calculate consistency score
        $B = 10;
        $w = 2;
        $totalScore = 0;
        $currentStreak = 0;
        $lastDate = null;
        
        foreach ($allDates as $date) {
            $dateObj = new \DateTime($date);
            
            if ($lastDate !== null) {
                $lastDateObj = new \DateTime($lastDate);
                $diff = $lastDateObj->diff($dateObj)->days;
                
                if ($diff === 1) {
                    $currentStreak++;
                } else if ($diff > 1) {
                    $currentStreak = 0;
                }
            }
            
            $totalScore += $B + ($currentStreak * $w);
            $lastDate = $date;
        }
        
        // Calculate workout frequency
        $totalDays = count($allDates);
        $firstDate = new \DateTime($allDates[0]);
        $lastDateObj = new \DateTime($allDates[count($allDates) - 1]);
        $daysDiff = $firstDate->diff($lastDateObj)->days + 1;
        $weeks = max(1, $daysDiff / 7);
        $workoutFrequency = $totalDays / $weeks;
        
        return [
            'consistency_score' => round($totalScore, 2),
            'current_streak' => $currentStreak,
            'total_logged_days' => $totalDays,
            'total_workouts' => count($workoutDates),
            'total_nutrition_logs' => count($nutritionDates),
            'workout_frequency_per_week' => round($workoutFrequency, 2)
        ];
    }

    public function sendToTrainer(int $progressId): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'UPDATE fitness_progress_tracking SET sent_to_trainer = 1, sent_at = NOW() WHERE id = :id'
            );
            return $stmt->execute([':id' => $progressId]);
        } catch (\Exception $e) {
            $this->logError("Failed to send progress: " . $e->getMessage());
            return false;
        }
    }

    public function findByServiceRequestId(int $serviceRequestId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM fitness_progress_tracking 
             WHERE service_request_id = :req_id 
             ORDER BY snapshot_date DESC'
        );
        $stmt->execute([':req_id' => $serviceRequestId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findSentToTrainer(int $trainerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT fpt.*, fsr.full_name as client_name, u.fullname as member_fullname
             FROM fitness_progress_tracking fpt
             JOIN fitness_service_requests fsr ON fsr.id = fpt.service_request_id
             JOIN gym_members gm ON gm.id = fpt.member_id
             JOIN users u ON u.id = gm.user_id
             WHERE fsr.assigned_trainer_id = :tid AND fpt.sent_to_trainer = 1
             ORDER BY fpt.sent_at DESC'
        );
        $stmt->execute([':tid' => $trainerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findPendingByServiceRequestId(int $serviceRequestId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM fitness_progress_tracking 
             WHERE service_request_id = :req_id 
             AND sent_to_trainer = 1
             ORDER BY sent_at DESC
             LIMIT 1'
        );
        $stmt->execute([':req_id' => $serviceRequestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function getWorkoutDates(int $serviceRequestId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT DISTINCT log_date FROM fitness_workout_logs WHERE service_request_id = :req_id'
        );
        $stmt->execute([':req_id' => $serviceRequestId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'log_date');
    }

    private function getNutritionDates(int $serviceRequestId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT DISTINCT log_date FROM fitness_nutrition_logs WHERE service_request_id = :req_id'
        );
        $stmt->execute([':req_id' => $serviceRequestId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'log_date');
    }

    private function logError(string $message): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/fitness_service.log';
        $logMessage = sprintf("[%s] FitnessProgressTracking: %s\n", date('Y-m-d H:i:s'), $message);
        @error_log($logMessage, 3, $logFile);
    }
}
