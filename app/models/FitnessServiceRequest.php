<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

final class FitnessServiceRequest extends Model
{
    /** Create a new fitness service request (supports new normalised columns) */
    public function create(int $memberId, array $data): int
    {
        // Base insert — always present columns
        $sql = 'INSERT INTO fitness_service_requests
                (member_id, full_name, address, city, phone, email,
                 training_type, session_preference, training_preference,
                 specific_trainer_request, status';

        $params = [
            ':member_id'               => $memberId,
            ':full_name'               => $data['full_name']               ?? '',
            ':address'                 => $data['address']                 ?? '',
            ':city'                    => $data['city']                    ?? '',
            ':phone'                   => $data['phone']                   ?? '',
            ':email'                   => $data['email']                   ?? '',
            ':training_type'           => $data['training_type']           ?? '',
            ':session_preference'      => $data['session_preference']      ?? '1',
            ':training_preference'     => $data['training_preference']     ?? '',
            ':specific_trainer_request'=> $data['specific_trainer_request'] ?? '',
            ':status'                  => 'pending',
        ];

        // Optional new columns — added by migration; skip gracefully if absent
        $extras = [
            'street'                  => ':street',
            'barangay'                => ':barangay',
            'district'                => ':district',
            'province'                => ':province',
            'training_type_custom'    => ':training_type_custom',
            'schedule_preference_json'=> ':schedule_preference_json',
        ];

        $extraCols = [];
        foreach ($extras as $col => $placeholder) {
            if (array_key_exists($col, $data)) {
                $extraCols[]       = $col;
                $params[$placeholder] = $data[$col];
            }
        }

        if ($extraCols) {
            $sql .= ', ' . implode(', ', $extraCols);
        }

        $valuePlaceholders = array_keys($params);
        // Remove ':status' from params keys to build VALUES list properly
        // We build VALUES by matching the columns order
        $colList = ['member_id','full_name','address','city','phone','email',
                    'training_type','session_preference','training_preference',
                    'specific_trainer_request','status'];
        $valList = [':member_id',':full_name',':address',':city',':phone',':email',
                    ':training_type',':session_preference',':training_preference',
                    ':specific_trainer_request',':status'];

        foreach ($extraCols as $col) {
            $colList[] = $col;
            $valList[] = ':' . $col;
            // fix: map col name → placeholder
            $params[':' . $col] = $data[$col];
            unset($params[$extras[$col]]); // remove duplicates added above
        }

        $stmt = $this->db()->prepare(
            'INSERT INTO fitness_service_requests (' . implode(', ', $colList) . ')
             VALUES (' . implode(', ', $valList) . ')'
        );

        // Re-build clean params (only placeholders that appear in $valList)
        $cleanParams = [];
        foreach ($valList as $ph) {
            $cleanParams[$ph] = $params[$ph] ?? null;
        }

        $stmt->execute($cleanParams);
        return (int)$this->db()->lastInsertId();
    }

    /** Find request by ID */
    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT fsr.*, 
                    gm.membership_code,
                    u.fullname as member_name,
                    tu.fullname as trainer_name,
                    assigned_user.fullname as assigned_by_name
             FROM fitness_service_requests fsr
             LEFT JOIN gym_members gm ON gm.id = fsr.member_id
             LEFT JOIN users u ON u.id = gm.user_id
             LEFT JOIN employees e ON e.id = fsr.assigned_trainer_id
             LEFT JOIN users tu ON tu.id = e.user_id
             LEFT JOIN users assigned_user ON assigned_user.id = fsr.assigned_by
             WHERE fsr.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Find all requests by member ID */
    public function findByMemberId(int $memberId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT fsr.*,
                    tu.fullname as trainer_name
             FROM fitness_service_requests fsr
             LEFT JOIN employees e ON e.id = fsr.assigned_trainer_id
             LEFT JOIN users tu ON tu.id = e.user_id
             WHERE fsr.member_id = :member_id
             ORDER BY fsr.created_at DESC'
        );
        $stmt->execute([':member_id' => $memberId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Find all pending requests (for admin officer) */
    public function findPending(): array
    {
        $stmt = $this->db()->query(
            'SELECT fsr.*,
                    gm.membership_code,
                    u.fullname as member_name,
                    u.email as member_email
             FROM fitness_service_requests fsr
             JOIN gym_members gm ON gm.id = fsr.member_id
             JOIN users u ON u.id = gm.user_id
             WHERE fsr.status = "pending"
             ORDER BY fsr.created_at ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Find all requests (for admin officer) */
    public function findAll(): array
    {
        try {
            $stmt = $this->db()->query(
                'SELECT fsr.*,
                        gm.membership_code,
                        u.fullname as member_name,
                        u.email as member_email,
                        tu.fullname as trainer_name
                 FROM fitness_service_requests fsr
                 JOIN gym_members gm ON gm.id = fsr.member_id
                 JOIN users u ON u.id = gm.user_id
                 LEFT JOIN employees e ON e.id = fsr.assigned_trainer_id
                 LEFT JOIN users tu ON tu.id = e.user_id
                 ORDER BY fsr.created_at DESC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /** Check if member has an active request */
    public function hasActiveRequest(int $memberId): bool
    {
        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) as count 
             FROM fitness_service_requests 
             WHERE member_id = :member_id 
             AND status IN ("pending", "assigned")
             LIMIT 1'
        );
        $stmt->execute([':member_id' => $memberId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0) > 0;
    }

    /** Assign trainer to request */
    public function assignTrainer(int $requestId, int $trainerId, int $assignedBy): bool
    {
        $stmt = $this->db()->prepare(
            'UPDATE fitness_service_requests 
             SET assigned_trainer_id = :trainer_id,
                 assigned_by = :assigned_by,
                 assigned_at = NOW(),
                 status = "assigned"
             WHERE id = :id'
        );
        return $stmt->execute([
            ':trainer_id' => $trainerId,
            ':assigned_by' => $assignedBy,
            ':id' => $requestId
        ]);
    }

    /** Update request status */
    public function updateStatus(int $requestId, string $status): bool
    {
        $stmt = $this->db()->prepare(
            'UPDATE fitness_service_requests 
             SET status = :status
             WHERE id = :id'
        );
        return $stmt->execute([
            ':status' => $status,
            ':id' => $requestId
        ]);
    }

    /** Get request statistics */
    public function getStats(): array
    {
        try {
            $stmt = $this->db()->query(
                'SELECT 
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = "assigned" THEN 1 ELSE 0 END) as assigned,
                    SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled
                 FROM fitness_service_requests'
            );
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'total_requests' => 0, 'pending' => 0,
                'assigned' => 0, 'completed' => 0, 'cancelled' => 0
            ];
        } catch (\Exception $e) {
            return ['total_requests' => 0, 'pending' => 0, 'assigned' => 0, 'completed' => 0, 'cancelled' => 0];
        }
    }

    /**
     * Find requests by trainer ID.
     * Covers BOTH assignment paths:
     *   1. Admin-assigned (trainer_assignments table)
     *   2. Direct booking (assigned_trainer_id on the request + status accepted)
     */
    public function findByTrainerId(int $trainerId): array
    {
        try {
            $stmt = $this->db()->prepare(
                'SELECT DISTINCT fsr.*,
                        gm.membership_code,
                        u.fullname as member_name,
                        u.email as member_email
                 FROM fitness_service_requests fsr
                 JOIN gym_members gm ON gm.id = fsr.member_id
                 JOIN users u ON u.id = gm.user_id
                 WHERE (
                     fsr.assigned_trainer_id = :trainer_id1
                     AND fsr.status IN ("assigned", "completed")
                 )
                 OR EXISTS (
                     SELECT 1 FROM trainer_assignments ta
                     WHERE ta.trainer_id = :trainer_id2
                       AND ta.client_id = gm.id
                       AND ta.status = "active"
                 )
                 ORDER BY fsr.created_at DESC'
            );
            $stmt->execute([':trainer_id1' => $trainerId, ':trainer_id2' => $trainerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Fallback: use only assigned_trainer_id if trainer_assignments doesn't exist
            $stmt = $this->db()->prepare(
                'SELECT fsr.*,
                        gm.membership_code,
                        u.fullname as member_name,
                        u.email as member_email
                 FROM fitness_service_requests fsr
                 JOIN gym_members gm ON gm.id = fsr.member_id
                 JOIN users u ON u.id = gm.user_id
                 WHERE fsr.assigned_trainer_id = :trainer_id
                   AND fsr.status IN ("assigned", "completed")
                 ORDER BY fsr.created_at DESC'
            );
            $stmt->execute([':trainer_id' => $trainerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Find requests by trainer ID with joined client profile data.
     * Covers BOTH assignment paths:
     *   1. Admin-assigned (trainer_assignments table)
     *   2. Direct booking (assigned_trainer_id on the request + status accepted)
     */
    public function findByTrainerIdWithProfiles(int $trainerId): array
    {
        try {
            $stmt = $this->db()->prepare(
                'SELECT DISTINCT fsr.*,
                        gm.membership_code,
                        u.id as client_user_id,
                        u.fullname as member_name,
                        u.email as member_email,
                        u.profile_picture_url,
                        fcp.id as profile_id,
                        fcp.age,
                        fcp.gender,
                        fcp.height_cm,
                        fcp.weight_kg,
                        fcp.fitness_goals,
                        fcp.medical_conditions,
                        fcp.activity_level,
                        fcp.dietary_preferences,
                        ftp.status as plan_status
                 FROM fitness_service_requests fsr
                 JOIN gym_members gm ON gm.id = fsr.member_id
                 JOIN users u ON u.id = gm.user_id
                 LEFT JOIN fitness_client_profiles fcp ON fcp.service_request_id = fsr.id
                 LEFT JOIN fitness_trainer_plans ftp ON ftp.service_request_id = fsr.id
                 WHERE (
                     fsr.assigned_trainer_id = :trainer_id1
                     AND fsr.status IN ("assigned", "completed")
                 )
                 OR EXISTS (
                     SELECT 1 FROM trainer_assignments ta
                     WHERE ta.trainer_id = :trainer_id2
                       AND ta.client_id = gm.id
                       AND ta.status = "active"
                 )
                 ORDER BY fsr.created_at DESC'
            );
            $stmt->execute([':trainer_id1' => $trainerId, ':trainer_id2' => $trainerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Fallback: use only assigned_trainer_id if trainer_assignments doesn't exist
            $stmt = $this->db()->prepare(
                'SELECT fsr.*,
                        gm.membership_code,
                        u.id as client_user_id,
                        u.fullname as member_name,
                        u.email as member_email,
                        u.profile_picture_url,
                        fcp.id as profile_id,
                        fcp.age,
                        fcp.gender,
                        fcp.height_cm,
                        fcp.weight_kg,
                        fcp.fitness_goals,
                        fcp.medical_conditions,
                        fcp.activity_level,
                        fcp.dietary_preferences,
                        ftp.status as plan_status
                 FROM fitness_service_requests fsr
                 JOIN gym_members gm ON gm.id = fsr.member_id
                 JOIN users u ON u.id = gm.user_id
                 LEFT JOIN fitness_client_profiles fcp ON fcp.service_request_id = fsr.id
                 LEFT JOIN fitness_trainer_plans ftp ON ftp.service_request_id = fsr.id
                 WHERE fsr.assigned_trainer_id = :trainer_id
                   AND fsr.status IN ("assigned", "completed")
                 ORDER BY fsr.created_at DESC'
            );
            $stmt->execute([':trainer_id' => $trainerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
