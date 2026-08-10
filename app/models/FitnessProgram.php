<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * FitnessProgram — stores Gemini-generated weekly fitness programs per member.
 */
final class FitnessProgram extends Model
{
    // ─── Guard ──────────────────────────────────────────────────────────────

    public function tableExists(): bool
    {
        try {
            $this->db()->query("SELECT 1 FROM fitness_programs LIMIT 1");
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    // ─── Read ────────────────────────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM fitness_programs WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->decode($row) : null;
    }

    public function findByMemberId(int $memberId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM fitness_programs WHERE member_id = :mid ORDER BY generated_at DESC LIMIT 1'
        );
        $stmt->execute([':mid' => $memberId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->decode($row) : null;
    }

    // ─── Write ───────────────────────────────────────────────────────────────

    /**
     * Insert or update a fitness program for a member.
     *
     * @param int    $memberId    gym_members.id
     * @param int    $userId      users.id
     * @param array  $profile     { goal, experience_level, available_days, list_of_weekdays,
     *                              session_length, equipment, injuries_limitations, gym_name }
     * @param array  $program     Decoded Gemini JSON array
     * @return int   Row ID
     */
    public function upsert(int $memberId, int $userId, array $profile, array $program): int
    {
        $programJson = json_encode($program, JSON_UNESCAPED_UNICODE);
        $splitName   = $program['split_name'] ?? null;

        $existing = $this->findByMemberId($memberId);

        if ($existing) {
            $stmt = $this->db()->prepare(
                'UPDATE fitness_programs SET
                    goal                 = :goal,
                    experience_level     = :exp,
                    available_days       = :days,
                    list_of_weekdays     = :weekdays,
                    session_length       = :length,
                    equipment            = :equip,
                    injuries_limitations = :injuries,
                    gym_name             = :gym,
                    split_name           = :split,
                    program_json         = :json,
                    generated_at         = NOW()
                 WHERE member_id = :mid'
            );
            $stmt->execute([
                ':goal'     => $profile['goal'],
                ':exp'      => $profile['experience_level'],
                ':days'     => $profile['available_days'],
                ':weekdays' => $profile['list_of_weekdays'],
                ':length'   => $profile['session_length'],
                ':equip'    => $profile['equipment'],
                ':injuries' => $profile['injuries_limitations'] ?? null,
                ':gym'      => $profile['gym_name'],
                ':split'    => $splitName,
                ':json'     => $programJson,
                ':mid'      => $memberId,
            ]);
            return $existing['id'];
        }

        $stmt = $this->db()->prepare(
            'INSERT INTO fitness_programs
             (member_id, user_id, goal, experience_level, available_days, list_of_weekdays,
              session_length, equipment, injuries_limitations, gym_name, split_name, program_json)
             VALUES
             (:mid, :uid, :goal, :exp, :days, :weekdays,
              :length, :equip, :injuries, :gym, :split, :json)'
        );
        $stmt->execute([
            ':mid'      => $memberId,
            ':uid'      => $userId,
            ':goal'     => $profile['goal'],
            ':exp'      => $profile['experience_level'],
            ':days'     => $profile['available_days'],
            ':weekdays' => $profile['list_of_weekdays'],
            ':length'   => $profile['session_length'],
            ':equip'    => $profile['equipment'],
            ':injuries' => $profile['injuries_limitations'] ?? null,
            ':gym'      => $profile['gym_name'],
            ':split'    => $splitName,
            ':json'     => $programJson,
        ]);
        return (int)$this->db()->lastInsertId();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Decode the program_json column back into a PHP array. */
    private function decode(array $row): array
    {
        $row['program'] = json_decode($row['program_json'] ?? '{}', true) ?? [];
        return $row;
    }
}
