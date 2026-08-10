<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;
use App\Models\GymMember;

/**
 * Workout Session Controller
 * Handles interactive workout sessions with rep tracking and calorie burn
 */
final class WorkoutSessionController extends Controller
{
    /**
     * Require active member authentication
     */
    private function requireActiveMember(): array
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }
        
        $user = (new User())->findById((int)$_SESSION['user_id']);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'User not found']);
            exit;
        }

        $member = (new GymMember())->findByUserId((int)$user['id']);
        if (!$member || ($member['membership_status'] ?? '') !== 'active') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Active membership required']);
            exit;
        }

        return ['user' => $user, 'member' => $member];
    }

    /**
     * Start new workout session
     * POST: index.php?r=workoutSession/start
     */
    public function startAction(): void
    {
        header('Content-Type: application/json');
        ini_set('display_errors', '0');
        
        $data = $this->requireActiveMember();
        $member = $data['member'];
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $requestId = (int)($input['request_id'] ?? 0);
            $dayOfWeek = $input['day_of_week'] ?? '';
            
            if ($requestId === 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid request ID']);
                return;
            }
            
            $pdo = Database::pdo();
            
            // Check if session already exists today
            $stmt = $pdo->prepare(
                'SELECT id FROM workout_sessions 
                 WHERE member_id = ? 
                 AND session_date = CURDATE() 
                 AND status = "in_progress"
                 LIMIT 1'
            );
            $stmt->execute([$member['id']]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($existing) {
                echo json_encode([
                    'success' => true,
                    'session_id' => $existing['id'],
                    'message' => 'Resumed existing session'
                ]);
                return;
            }
            
            // Create new session
            $stmt = $pdo->prepare(
                'INSERT INTO workout_sessions
                 (member_id, service_request_id, session_date, day_of_week, 
                  status, started_at)
                 VALUES (?, ?, CURDATE(), ?, "in_progress", NOW())'
            );
            $stmt->execute([$member['id'], $requestId, $dayOfWeek]);
            
            echo json_encode([
                'success' => true,
                'session_id' => $pdo->lastInsertId(),
                'message' => 'Session started'
            ]);
            
        } catch (\Exception $e) {
            error_log('Error starting workout session: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Failed to start session: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Complete workout session
     * POST: index.php?r=workoutSession/complete
     */
    public function completeAction(): void
    {
        header('Content-Type: application/json');
        ini_set('display_errors', '0');
        
        $data = $this->requireActiveMember();
        $member = $data['member'];
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $sessionId = (int)($input['session_id'] ?? 0);
            $duration = (int)($input['total_duration'] ?? 0);
            $calories = (float)($input['total_calories'] ?? 0);
            $notes = trim($input['notes'] ?? '');
            $exerciseLog = $input['exercise_log'] ?? [];
            
            if ($sessionId === 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid session ID']);
                return;
            }
            
            $pdo = Database::pdo();
            $pdo->beginTransaction();
            
            // Update session
            $stmt = $pdo->prepare(
                'UPDATE workout_sessions SET
                 status = "completed",
                 completed_at = NOW(),
                 total_duration = ?,
                 total_calories = ?,
                 notes = ?
                 WHERE id = ? AND member_id = ?'
            );
            $stmt->execute([$duration, $calories, $notes, $sessionId, $member['id']]);
            
            // Save each exercise into session_exercises
            foreach ($exerciseLog as $ex) {
                $repsData = json_encode(array_column($ex['set_data'] ?? [], 'reps'));
                $weightData = json_encode(array_column($ex['set_data'] ?? [], 'weight'));
                
                $stmt = $pdo->prepare(
                    'INSERT INTO session_exercises
                     (session_id, exercise_name, exercise_id, sets_completed,
                      reps_per_set, weight_used, duration_secs, calories_burned, is_completed)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)'
                );
                $stmt->execute([
                    $sessionId,
                    $ex['exercise_name'] ?? '',
                    $ex['exercise_id'] ?? '',
                    (int)($ex['sets_completed'] ?? 0),
                    $repsData,
                    $weightData,
                    (int)($ex['duration_secs'] ?? 0),
                    (float)($ex['calories_burned'] ?? 0)
                ]);
            }

            // ── Auto-insert into fitness_workout_logs (Daily Logs) ────────────
            // Use request_id from payload, or fall back to workout_sessions table
            $requestId = (int)($input['request_id'] ?? 0);
            if ($requestId === 0) {
                $s = $pdo->prepare('SELECT service_request_id FROM workout_sessions WHERE id = ?');
                $s->execute([$sessionId]);
                $row = $s->fetch(\PDO::FETCH_ASSOC);
                $requestId = (int)($row['service_request_id'] ?? 0);
            }

            if ($requestId > 0) {
                $today = date('Y-m-d');

                foreach ($exerciseLog as $ex) {
                    $setData    = $ex['set_data'] ?? [];
                    $setsCount  = (int)($ex['sets_completed'] ?? count($setData));
                    $allReps    = array_column($setData, 'reps');
                    $allWeights = array_column($setData, 'weight');

                    // Average reps across sets (rounded up)
                    $avgReps = ($setsCount > 0 && count($allReps) > 0)
                        ? (int)ceil(array_sum($allReps) / count($allReps))
                        : 10;

                    // Maximum weight used across sets
                    $maxWeight = count($allWeights) > 0
                        ? (float)max($allWeights)
                        : 0.0;

                    // Duration in minutes (minimum 1)
                    $durationMins = max(1, (int)ceil((int)($ex['duration_secs'] ?? 0) / 60));

                    $autoNote = 'Auto-logged from workout session tracker';
                    if (!empty($notes)) {
                        $autoNote .= ' — ' . $notes;
                    }

                    $stmt = $pdo->prepare(
                        'INSERT INTO fitness_workout_logs
                         (member_id, service_request_id, log_date, exercise_name,
                          sets, reps, weight_kg, duration_minutes, notes)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([
                        $member['id'],
                        $requestId,
                        $today,
                        $ex['exercise_name'] ?? 'Exercise',
                        $setsCount,
                        $avgReps,
                        $maxWeight,
                        $durationMins,
                        $autoNote,
                    ]);
                }
            }
            // ─────────────────────────────────────────────────────────────────
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Workout session saved successfully!'
            ]);
            
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Error completing workout session: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Failed to save session: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get weekly progress stats
     * GET: index.php?r=workoutSession/weeklyStats
     */
    public function weeklyStatsAction(): void
    {
        header('Content-Type: application/json');
        ini_set('display_errors', '0');
        
        $data = $this->requireActiveMember();
        $member = $data['member'];
        
        try {
            $pdo = Database::pdo();
            
            // Get last 7 days stats
            $stmt = $pdo->prepare(
                'SELECT 
                    session_date,
                    SUM(total_calories) as day_calories,
                    SUM(total_duration) as day_duration,
                    COUNT(*) as sessions
                 FROM workout_sessions
                 WHERE member_id = ?
                 AND session_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 AND status = "completed"
                 GROUP BY session_date
                 ORDER BY session_date ASC'
            );
            $stmt->execute([$member['id']]);
            $weeklyData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Calculate streak
            $stmtStreak = $pdo->prepare(
                'SELECT COUNT(DISTINCT session_date) as streak
                 FROM workout_sessions
                 WHERE member_id = ?
                 AND status = "completed"
                 AND session_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)'
            );
            $stmtStreak->execute([$member['id']]);
            $streakData = $stmtStreak->fetch(\PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'weekly_data' => $weeklyData,
                'streak' => (int)($streakData['streak'] ?? 0)
            ]);
            
        } catch (\Exception $e) {
            error_log('Error fetching weekly stats: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch stats'
            ]);
        }
    }
}
