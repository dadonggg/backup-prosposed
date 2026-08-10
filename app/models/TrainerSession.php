<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class TrainerSession extends Model
{
    public function create(int $memberId, int $trainerId, string $sessionDate, string $sessionTime,
                          int $duration = 60, string $sessionType = 'personal_training', 
                          string $memberNotes = '', float $price = 0): int
    {
        try {
            \App\Core\Database::beginTransaction();

            $stmt = $this->db()->prepare(
                'INSERT INTO trainer_sessions (member_id, trainer_id, session_date, session_time, duration_minutes, session_type, member_notes, price)
                 VALUES (:mid, :tid, :date, :time, :duration, :type, :notes, :price)'
            );
            $stmt->execute([
                ':mid' => $memberId,
                ':tid' => $trainerId,
                ':date' => $sessionDate,
                ':time' => $sessionTime,
                ':duration' => $duration,
                ':type' => $sessionType,
                ':notes' => $memberNotes,
                ':price' => $price
            ]);

            $sessionId = (int)$this->db()->lastInsertId();
            \App\Core\Database::commit();
            return $sessionId;

        } catch (\Exception $e) {
            \App\Core\Database::rollback();
            $this->logError("Failed to create trainer session: " . $e->getMessage());
            return 0;
        }
    }

    public function findByMemberId(int $memberId, ?string $status = null): array
    {
        $sql = 'SELECT ts.*, u.fullname as trainer_name, u.email as trainer_email
                FROM trainer_sessions ts
                JOIN employees e ON e.id = ts.trainer_id
                JOIN users u ON u.id = e.user_id
                WHERE ts.member_id = :mid';
        
        $params = [':mid' => $memberId];

        if ($status !== null) {
            $sql .= ' AND ts.status = :status';
            $params[':status'] = $status;
        }

        $sql .= ' ORDER BY ts.session_date DESC, ts.session_time DESC';

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findUpcomingSessions(int $memberId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT ts.*, u.fullname as trainer_name, u.email as trainer_email
             FROM trainer_sessions ts
             JOIN employees e ON e.id = ts.trainer_id
             JOIN users u ON u.id = e.user_id
             WHERE ts.member_id = :mid 
               AND ts.session_date >= CURDATE()
               AND ts.status = "scheduled"
             ORDER BY ts.session_date ASC, ts.session_time ASC'
        );
        $stmt->execute([':mid' => $memberId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByTrainerId(int $trainerId, ?string $date = null): array
    {
        $sql = 'SELECT ts.*, gm.membership_code, u.fullname as member_name
                FROM trainer_sessions ts
                JOIN gym_members gm ON gm.id = ts.member_id
                JOIN users u ON u.id = gm.user_id
                WHERE ts.trainer_id = :tid';
        
        $params = [':tid' => $trainerId];

        if ($date !== null) {
            $sql .= ' AND ts.session_date = :date';
            $params[':date'] = $date;
        }

        $sql .= ' ORDER BY ts.session_date DESC, ts.session_time DESC';

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $sessionId, string $status, string $trainerNotes = ''): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'UPDATE trainer_sessions 
                 SET status = :status, trainer_notes = :notes, updated_at = CURRENT_TIMESTAMP 
                 WHERE id = :id'
            );
            $stmt->execute([
                ':status' => $status,
                ':notes' => $trainerNotes,
                ':id' => $sessionId
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logError("Failed to update session status: " . $e->getMessage());
            return false;
        }
    }

    public function cancelSession(int $sessionId, string $reason = ''): bool
    {
        return $this->updateStatus($sessionId, 'cancelled', $reason);
    }

    public function getAvailableSlots(int $trainerId, string $date): array
    {
        // Get trainer's availability for the day of week
        $dayOfWeek = date('w', strtotime($date)); // 0=Sunday, 1=Monday, etc.
        
        $stmt = $this->db()->prepare(
            'SELECT start_time, end_time FROM trainer_availability 
             WHERE trainer_id = :tid AND day_of_week = :dow AND is_available = 1'
        );
        $stmt->execute([':tid' => $trainerId, ':dow' => $dayOfWeek]);
        $availability = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($availability)) return [];

        // Get already booked slots for this date
        $stmt = $this->db()->prepare(
            'SELECT session_time, duration_minutes FROM trainer_sessions 
             WHERE trainer_id = :tid AND session_date = :date AND status IN ("scheduled", "completed")'
        );
        $stmt->execute([':tid' => $trainerId, ':date' => $date]);
        $bookedSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Generate available time slots (1-hour intervals)
        $availableSlots = [];
        foreach ($availability as $avail) {
            $start = new \DateTime($date . ' ' . $avail['start_time']);
            $end = new \DateTime($date . ' ' . $avail['end_time']);
            
            while ($start < $end) {
                $slotTime = $start->format('H:i:s');
                $slotDateTime = $start->format('Y-m-d H:i:s');
                
                // Check if this slot is already booked
                $isBooked = false;
                foreach ($bookedSlots as $booked) {
                    $bookedStart = new \DateTime($date . ' ' . $booked['session_time']);
                    $bookedEnd = clone $bookedStart;
                    $bookedEnd->add(new \DateInterval('PT' . $booked['duration_minutes'] . 'M'));
                    
                    if ($start >= $bookedStart && $start < $bookedEnd) {
                        $isBooked = true;
                        break;
                    }
                }
                
                // Don't show past time slots for today
                $now = new \DateTime();
                $isPast = ($date === date('Y-m-d') && $start <= $now);
                
                if (!$isBooked && !$isPast) {
                    $availableSlots[] = [
                        'time' => $slotTime,
                        'display_time' => $start->format('g:i A'),
                        'datetime' => $slotDateTime
                    ];
                }
                
                $start->add(new \DateInterval('PT1H')); // Add 1 hour
            }
        }

        return $availableSlots;
    }

    public function getMemberSessionStats(int $memberId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT 
                COUNT(*) as total_sessions,
                COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_sessions,
                COUNT(CASE WHEN status = "scheduled" AND session_date >= CURDATE() THEN 1 END) as upcoming_sessions,
                COUNT(CASE WHEN status = "cancelled" THEN 1 END) as cancelled_sessions,
                COUNT(CASE WHEN status = "no_show" THEN 1 END) as no_show_sessions,
                SUM(CASE WHEN status = "completed" THEN duration_minutes ELSE 0 END) as total_training_minutes,
                MIN(CASE WHEN status = "scheduled" AND session_date >= CURDATE() THEN session_date END) as next_session_date
             FROM trainer_sessions 
             WHERE member_id = :mid'
        );
        $stmt->execute([':mid' => $memberId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTrainerSessionStats(int $trainerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT 
                COUNT(*) as total_sessions,
                COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_sessions,
                COUNT(CASE WHEN status = "scheduled" AND session_date >= CURDATE() THEN 1 END) as upcoming_sessions,
                COUNT(DISTINCT member_id) as unique_members,
                SUM(CASE WHEN status = "completed" THEN price ELSE 0 END) as total_revenue
             FROM trainer_sessions 
             WHERE trainer_id = :tid'
        );
        $stmt->execute([':tid' => $trainerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function logError(string $message): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/trainer_sessions.log';
        $logMessage = sprintf(
            "[%s] TrainerSession: %s\n",
            date('Y-m-d H:i:s'),
            $message
        );

        @error_log($logMessage, 3, $logFile);
    }
}