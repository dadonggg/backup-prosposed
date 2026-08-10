<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

final class FitnessClientProfile extends Model
{
    /** Create a new client profile */
    public function create(int $serviceRequestId, int $memberId, array $data): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO fitness_client_profiles 
            (service_request_id, member_id, age, gender, height_cm, weight_kg, 
             fitness_goals, medical_conditions, activity_level, dietary_preferences)
            VALUES 
            (:service_request_id, :member_id, :age, :gender, :height_cm, :weight_kg,
             :fitness_goals, :medical_conditions, :activity_level, :dietary_preferences)'
        );
        
        $stmt->execute([
            ':service_request_id' => $serviceRequestId,
            ':member_id' => $memberId,
            ':age' => $data['age'],
            ':gender' => $data['gender'],
            ':height_cm' => $data['height_cm'],
            ':weight_kg' => $data['weight_kg'],
            ':fitness_goals' => $data['fitness_goals'],
            ':medical_conditions' => $data['medical_conditions'],
            ':activity_level' => $data['activity_level'],
            ':dietary_preferences' => $data['dietary_preferences']
        ]);
        
        return (int)$this->db()->lastInsertId();
    }

    /** Find profile by service request ID */
    public function findByServiceRequestId(int $serviceRequestId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT fcp.*,
                    gm.membership_code,
                    u.fullname as member_name
             FROM fitness_client_profiles fcp
             JOIN gym_members gm ON gm.id = fcp.member_id
             JOIN users u ON u.id = gm.user_id
             WHERE fcp.service_request_id = :service_request_id
             LIMIT 1'
        );
        $stmt->execute([':service_request_id' => $serviceRequestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Find profile by ID */
    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT fcp.*,
                    gm.membership_code,
                    u.fullname as member_name
             FROM fitness_client_profiles fcp
             JOIN gym_members gm ON gm.id = fcp.member_id
             JOIN users u ON u.id = gm.user_id
             WHERE fcp.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Find profile by member ID */
    public function findByMemberId(int $memberId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM fitness_client_profiles 
             WHERE member_id = :member_id
             ORDER BY created_at DESC
             LIMIT 1'
        );
        $stmt->execute([':member_id' => $memberId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Update profile */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db()->prepare(
            'UPDATE fitness_client_profiles 
             SET age = :age,
                 gender = :gender,
                 height_cm = :height_cm,
                 weight_kg = :weight_kg,
                 fitness_goals = :fitness_goals,
                 medical_conditions = :medical_conditions,
                 activity_level = :activity_level,
                 dietary_preferences = :dietary_preferences,
                 updated_at = NOW()
             WHERE id = :id'
        );
        
        return $stmt->execute([
            ':age' => $data['age'],
            ':gender' => $data['gender'],
            ':height_cm' => $data['height_cm'],
            ':weight_kg' => $data['weight_kg'],
            ':fitness_goals' => $data['fitness_goals'],
            ':medical_conditions' => $data['medical_conditions'],
            ':activity_level' => $data['activity_level'],
            ':dietary_preferences' => $data['dietary_preferences'],
            ':id' => $id
        ]);
    }

    /** Calculate BMI */
    public function calculateBMI(float $heightCm, float $weightKg): float
    {
        if ($heightCm <= 0) return 0;
        $heightM = $heightCm / 100;
        return round($weightKg / ($heightM * $heightM), 2);
    }

    /** Get BMI category */
    public function getBMICategory(float $bmi): string
    {
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal weight';
        if ($bmi < 30) return 'Overweight';
        return 'Obese';
    }
}
