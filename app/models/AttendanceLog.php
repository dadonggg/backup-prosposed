<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class AttendanceLog extends Model
{
    public function create(int $memberId, string $code): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO attendance_log (member_id, membership_code) VALUES (:mid, :code)'
        );
        $stmt->execute([':mid' => $memberId, ':code' => $code]);
        return (int)$this->db()->lastInsertId();
    }

    public function findByMemberId(int $memberId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM attendance_log WHERE member_id = :mid ORDER BY check_in DESC'
        );
        $stmt->execute([':mid' => $memberId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAll(): array
    {
        return $this->db()->query(
            'SELECT al.*, gm.membership_code, u.fullname FROM attendance_log al
             JOIN gym_members gm ON gm.id = al.member_id
             JOIN users u ON u.id = gm.user_id ORDER BY al.check_in DESC'
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
