<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

final class FitnessTrainerFeedback extends Model
{
    public function create(int $progressTrackingId, int $trainerId, int $memberId, int $serviceRequestId, array $data): int
    {
        try {
            $stmt = $this->db()->prepare(
                'INSERT INTO fitness_trainer_feedback 
                (progress_tracking_id, trainer_id, member_id, service_request_id, feedback_text, 
                 areas_of_improvement, encouragement, next_steps)
                VALUES (:prog_id, :tid, :mid, :req_id, :feedback, :improve, :encourage, :next)'
            );
            
            $stmt->execute([
                ':prog_id' => $progressTrackingId,
                ':tid' => $trainerId,
                ':mid' => $memberId,
                ':req_id' => $serviceRequestId,
                ':feedback' => $data['feedback_text'],
                ':improve' => $data['areas_of_improvement'] ?? '',
                ':encourage' => $data['encouragement'] ?? '',
                ':next' => $data['next_steps'] ?? ''
            ]);
            
            return (int)$this->db()->lastInsertId();
        } catch (\Exception $e) {
            $this->logError("Failed to create feedback: " . $e->getMessage());
            return 0;
        }
    }

    public function findByServiceRequestId(int $serviceRequestId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT ftf.*, u.fullname as trainer_name, fpt.snapshot_date, fpt.consistency_score
             FROM fitness_trainer_feedback ftf
             JOIN employees e ON e.id = ftf.trainer_id
             JOIN users u ON u.id = e.user_id
             JOIN fitness_progress_tracking fpt ON fpt.id = ftf.progress_tracking_id
             WHERE ftf.service_request_id = :req_id
             ORDER BY ftf.created_at DESC'
        );
        $stmt->execute([':req_id' => $serviceRequestId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByMemberId(int $memberId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT ftf.*, u.fullname as trainer_name, fpt.snapshot_date, fpt.consistency_score
             FROM fitness_trainer_feedback ftf
             JOIN employees e ON e.id = ftf.trainer_id
             JOIN users u ON u.id = e.user_id
             JOIN fitness_progress_tracking fpt ON fpt.id = ftf.progress_tracking_id
             WHERE ftf.member_id = :mid
             ORDER BY ftf.created_at DESC'
        );
        $stmt->execute([':mid' => $memberId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function logError(string $message): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/fitness_service.log';
        $logMessage = sprintf("[%s] FitnessTrainerFeedback: %s\n", date('Y-m-d H:i:s'), $message);
        @error_log($logMessage, 3, $logFile);
    }
}
