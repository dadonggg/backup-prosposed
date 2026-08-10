<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class MemberGoal extends Model
{
    public function create(int $memberId, string $goalType, string $title, string $description = '',
                          ?float $targetValue = null, string $targetUnit = '', ?string $targetDate = null): int
    {
        try {
            \App\Core\Database::beginTransaction();

            $stmt = $this->db()->prepare(
                'INSERT INTO member_goals (member_id, goal_type, title, description, target_value, target_unit, target_date)
                 VALUES (:mid, :type, :title, :desc, :value, :unit, :date)'
            );
            $stmt->execute([
                ':mid' => $memberId,
                ':type' => $goalType,
                ':title' => $title,
                ':desc' => $description,
                ':value' => $targetValue,
                ':unit' => $targetUnit,
                ':date' => $targetDate
            ]);

            $goalId = (int)$this->db()->lastInsertId();
            \App\Core\Database::commit();
            return $goalId;

        } catch (\Exception $e) {
            \App\Core\Database::rollback();
            $this->logError("Failed to create goal: " . $e->getMessage());
            return 0;
        }
    }

    public function updateProgress(int $goalId, float $currentValue): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'UPDATE member_goals SET current_value = :value, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $stmt->execute([':value' => $currentValue, ':id' => $goalId]);
            
            // Check if goal is completed
            $this->checkGoalCompletion($goalId);
            
            return true;
        } catch (\Exception $e) {
            $this->logError("Failed to update goal progress: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus(int $goalId, string $status): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'UPDATE member_goals SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $stmt->execute([':status' => $status, ':id' => $goalId]);
            return true;
        } catch (\Exception $e) {
            $this->logError("Failed to update goal status: " . $e->getMessage());
            return false;
        }
    }

    public function findByMemberId(int $memberId, ?string $status = null): array
    {
        $sql = 'SELECT * FROM member_goals WHERE member_id = :mid';
        $params = [':mid' => $memberId];

        if ($status !== null) {
            $sql .= ' AND status = :status';
            $params[':status'] = $status;
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $goalId): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM member_goals WHERE id = :id');
        $stmt->execute([':id' => $goalId]);
        $goal = $stmt->fetch(PDO::FETCH_ASSOC);
        return $goal ?: null;
    }

    public function getMemberGoalStats(int $memberId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT 
                status,
                COUNT(*) as count,
                goal_type,
                COUNT(CASE WHEN goal_type = "weight_loss" THEN 1 END) as weight_loss_goals,
                COUNT(CASE WHEN goal_type = "weight_gain" THEN 1 END) as weight_gain_goals,
                COUNT(CASE WHEN goal_type = "strength" THEN 1 END) as strength_goals,
                COUNT(CASE WHEN goal_type = "endurance" THEN 1 END) as endurance_goals
             FROM member_goals 
             WHERE member_id = :mid
             GROUP BY status'
        );
        $stmt->execute([':mid' => $memberId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the results
        $stats = [
            'active' => 0,
            'completed' => 0,
            'paused' => 0,
            'cancelled' => 0,
            'by_type' => [
                'weight_loss' => 0,
                'weight_gain' => 0,
                'strength' => 0,
                'endurance' => 0,
                'other' => 0
            ]
        ];

        foreach ($results as $result) {
            $stats[$result['status']] = (int)$result['count'];
        }

        // Get type breakdown
        $stmt = $this->db()->prepare(
            'SELECT goal_type, COUNT(*) as count FROM member_goals WHERE member_id = :mid GROUP BY goal_type'
        );
        $stmt->execute([':mid' => $memberId]);
        $typeResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($typeResults as $type) {
            $stats['by_type'][$type['goal_type']] = (int)$type['count'];
        }

        return $stats;
    }

    public function getGoalProgress(int $goalId): array
    {
        $goal = $this->findById($goalId);
        if (!$goal) return [];

        $progress = [
            'goal' => $goal,
            'progress_percentage' => 0,
            'is_completed' => false,
            'days_remaining' => null,
            'is_overdue' => false
        ];

        // Calculate progress percentage
        if ($goal['target_value'] && $goal['target_value'] > 0) {
            $progress['progress_percentage'] = min(100, ($goal['current_value'] / $goal['target_value']) * 100);
            $progress['is_completed'] = $goal['current_value'] >= $goal['target_value'];
        }

        // Calculate days remaining
        if ($goal['target_date']) {
            $targetDate = new \DateTime($goal['target_date']);
            $today = new \DateTime();
            $diff = $today->diff($targetDate);
            
            if ($targetDate < $today) {
                $progress['is_overdue'] = true;
                $progress['days_remaining'] = -$diff->days;
            } else {
                $progress['days_remaining'] = $diff->days;
            }
        }

        return $progress;
    }

    private function checkGoalCompletion(int $goalId): void
    {
        $goal = $this->findById($goalId);
        if (!$goal || $goal['status'] !== 'active') return;

        // Check if goal is completed based on target value
        if ($goal['target_value'] && $goal['current_value'] >= $goal['target_value']) {
            $this->updateStatus($goalId, 'completed');
        }
    }

    private function logError(string $message): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/goals.log';
        $logMessage = sprintf(
            "[%s] MemberGoal: %s\n",
            date('Y-m-d H:i:s'),
            $message
        );

        @error_log($logMessage, 3, $logFile);
    }
}