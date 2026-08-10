<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

final class CustomExercise extends Model
{
    /** Create a custom exercise */
    public function create(int $trainerId, array $data): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO custom_exercises 
            (trainer_id, exercise_name, body_part, equipment, instructions, default_sets, default_reps)
            VALUES 
            (:trainer_id, :exercise_name, :body_part, :equipment, :instructions, :default_sets, :default_reps)'
        );
        
        $stmt->execute([
            ':trainer_id' => $trainerId,
            ':exercise_name' => $data['exercise_name'],
            ':body_part' => $data['body_part'],
            ':equipment' => $data['equipment'],
            ':instructions' => $data['instructions'] ?? '',
            ':default_sets' => (int)($data['default_sets'] ?? 3),
            ':default_reps' => (int)($data['default_reps'] ?? 10)
        ]);
        
        return (int)$this->db()->lastInsertId();
    }

    /** Find all custom exercises by trainer */
    public function findByTrainerId(int $trainerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM custom_exercises 
             WHERE trainer_id = :trainer_id
             ORDER BY created_at DESC'
        );
        $stmt->execute([':trainer_id' => $trainerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Find custom exercise by ID */
    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM custom_exercises WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Delete custom exercise */
    public function delete(int $id, int $trainerId): bool
    {
        $stmt = $this->db()->prepare(
            'DELETE FROM custom_exercises WHERE id = :id AND trainer_id = :trainer_id'
        );
        return $stmt->execute([':id' => $id, ':trainer_id' => $trainerId]);
    }
}
