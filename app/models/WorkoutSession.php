<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class WorkoutSession extends Model
{
    public function create(int $memberId, string $sessionDate, string $sessionType = 'mixed', 
                          int $duration = 0, int $calories = 0, string $notes = ''): int
    {
        try {
            \App\Core\Database::beginTransaction();

            // Convert duration minutes to seconds for storage (total_duration is in seconds)
            $durationSecs = $duration * 60;

            $stmt = $this->db()->prepare(
                'INSERT INTO workout_sessions (member_id, session_date, day_of_week, status,
                  started_at, total_duration, total_calories, notes)
                 VALUES (:mid, :date, :dow, "completed", NOW(), :duration, :calories, :notes)'
            );
            $stmt->execute([
                ':mid'      => $memberId,
                ':date'     => $sessionDate,
                ':dow'      => date('l', strtotime($sessionDate)),
                ':duration' => $durationSecs,
                ':calories' => $calories,
                ':notes'    => $notes
            ]);

            $sessionId = (int)$this->db()->lastInsertId();
            \App\Core\Database::commit();
            return $sessionId;

        } catch (\Exception $e) {
            \App\Core\Database::rollback();
            $this->logError("Failed to create workout session: " . $e->getMessage());
            return 0;
        }
    }

    public function addExercise(int $sessionId, string $exerciseName, int $sets = 0, 
                               string $reps = '', float $weight = 0, float $distance = 0, 
                               int $duration = 0, string $notes = ''): bool
    {
        try {
            // duration is in minutes, convert to seconds for session_exercises
            $durationSecs = $duration * 60;
            $stmt = $this->db()->prepare(
                'INSERT INTO session_exercises
                 (session_id, exercise_name, sets_completed, reps_per_set, weight_used,
                  duration_secs, is_completed, notes)
                 VALUES (:sid, :name, :sets, :reps, :weight, :duration, 1, :notes)'
            );
            $stmt->execute([
                ':sid'      => $sessionId,
                ':name'     => $exerciseName,
                ':sets'     => $sets,
                ':reps'     => $reps,
                ':weight'   => json_encode([$weight]),
                ':duration' => $durationSecs,
                ':notes'    => $notes
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logError("Failed to add exercise: " . $e->getMessage());
            return false;
        }
    }

    public function findByMemberId(int $memberId, int $limit = 10): array
    {
        $stmt = $this->db()->prepare(
            'SELECT ws.*, 
                    COUNT(se.id) as exercise_count,
                    GROUP_CONCAT(se.exercise_name SEPARATOR ", ") as exercises,
                    ROUND(ws.total_duration / 60) as duration_minutes,
                    ws.total_calories as calories_burned
             FROM workout_sessions ws
             LEFT JOIN session_exercises se ON ws.id = se.session_id
             WHERE ws.member_id = :mid
             GROUP BY ws.id
             ORDER BY ws.session_date DESC, ws.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':mid', $memberId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSessionWithExercises(int $sessionId): ?array
    {
        // Get session details
        $stmt = $this->db()->prepare(
            'SELECT *,
             ROUND(total_duration / 60) as duration_minutes,
             total_calories as calories_burned
             FROM workout_sessions WHERE id = :id'
        );
        $stmt->execute([':id' => $sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$session) return null;

        // Get exercises for this session (now stored in session_exercises)
        $stmt = $this->db()->prepare('SELECT * FROM session_exercises WHERE session_id = :sid ORDER BY id');
        $stmt->execute([':sid' => $sessionId]);
        $session['exercises'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $session;
    }

    public function getMemberStats(int $memberId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT 
                COUNT(*) as total_sessions,
                ROUND(SUM(total_duration) / 60) as total_minutes,
                ROUND(AVG(total_duration) / 60) as avg_duration,
                SUM(total_calories) as total_calories,
                MAX(session_date) as last_workout,
                MIN(session_date) as first_workout
             FROM workout_sessions 
             WHERE member_id = :mid'
        );
        $stmt->execute([':mid' => $memberId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get monthly progress (last 6 months)
        $stmt = $this->db()->prepare(
            "SELECT 
                DATE_FORMAT(session_date, '%Y-%m') as month,
                COUNT(*) as session_count,
                ROUND(SUM(total_duration) / 60) as total_minutes,
                SUM(total_calories) as total_calories
             FROM workout_sessions 
             WHERE member_id = :mid 
               AND session_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY DATE_FORMAT(session_date, '%Y-%m')
             ORDER BY month DESC"
        );
        $stmt->execute([':mid' => $memberId]);
        $stats['monthly_progress'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    public function getPopularExercises(int $memberId, int $limit = 10): array
    {
        $stmt = $this->db()->prepare(
            'SELECT 
                se.exercise_name,
                COUNT(*) as frequency,
                SUM(se.sets_completed) as total_sets
             FROM session_exercises se
             JOIN workout_sessions ws ON ws.id = se.session_id
             WHERE ws.member_id = :mid
             GROUP BY se.exercise_name
             ORDER BY frequency DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':mid', $memberId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function logError(string $message): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/workout.log';
        $logMessage = sprintf(
            "[%s] WorkoutSession: %s\n",
            date('Y-m-d H:i:s'),
            $message
        );

        @error_log($logMessage, 3, $logFile);
    }
}