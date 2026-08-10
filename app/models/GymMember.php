<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class GymMember extends Model
{
    public function create(
        int $userId, int $appId, string $code, ?int $trainerId,
        string $paymentType = 'regular_monthly', float $paymentAmount = 0.0,
        ?string $startDate = null, ?string $expirationDate = null
    ): int {
        if ($startDate === null) { $startDate = date('Y-m-d'); }
        if ($expirationDate === null) {
            $days = 30;
            $expirationDate = date('Y-m-d', strtotime("+{$days} days"));
        }
        $stmt = $this->db()->prepare(
            'INSERT INTO gym_members
             (user_id, application_id, membership_code, assigned_trainer_id,
              payment_type, payment_amount, start_date, expiration_date)
             VALUES (:uid, :aid, :code, :tid, :pt, :pa, :sd, :ed)'
        );
        $stmt->execute([
            ':uid'=>$userId, ':aid'=>$appId, ':code'=>$code, ':tid'=>$trainerId,
            ':pt'=>$paymentType, ':pa'=>$paymentAmount, ':sd'=>$startDate, ':ed'=>$expirationDate,
        ]);
        return (int)$this->db()->lastInsertId();
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM gym_members WHERE user_id = :uid ORDER BY id DESC LIMIT 1');
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM gym_members WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByMembershipCode(string $code): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT gm.*, u.fullname, u.email FROM gym_members gm
             JOIN users u ON u.id = gm.user_id WHERE gm.membership_code = :c LIMIT 1'
        );
        $stmt->execute([':c' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findAll(): array
    {
        return $this->db()->query(
            'SELECT gm.*, u.fullname, u.email, u.profile_picture_url FROM gym_members gm
             JOIN users u ON u.id = gm.user_id ORDER BY gm.created_at DESC'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find active members – gracefully handles missing expiration_date column.
     */
    public function findAllActive(): array
    {
        try {
            return $this->db()->query(
                'SELECT gm.*, u.fullname, u.email FROM gym_members gm
                 JOIN users u ON u.id = gm.user_id
                 WHERE gm.is_active = 1 AND (gm.expiration_date IS NULL OR gm.expiration_date >= CURDATE())
                 ORDER BY gm.created_at DESC'
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Fallback if expiration_date column doesn't exist yet
            return $this->db()->query(
                'SELECT gm.*, u.fullname, u.email FROM gym_members gm
                 JOIN users u ON u.id = gm.user_id
                 WHERE gm.is_active = 1 ORDER BY gm.created_at DESC'
            )->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function getMonthlyRevenue(?string $monthYear = null): float
    {
        if ($monthYear === null) { $monthYear = date('Y-m'); }
        try {
            $stmt = $this->db()->prepare(
                "SELECT COALESCE(SUM(payment_amount),0) as total FROM gym_members
                 WHERE DATE_FORMAT(created_at, '%Y-%m') = :my"
            );
            $stmt->execute([':my' => $monthYear]);
            return (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (\PDOException $e) { return 0.0; }
    }

    public function getRevenueByMonth(int $months = 6): array
    {
        try {
            $stmt = $this->db()->prepare(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                        COALESCE(SUM(payment_amount),0) as total,
                        COUNT(*) as member_count
                 FROM gym_members
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :m MONTH)
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY month DESC"
            );
            $stmt->execute([':m' => $months]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) { return []; }
    }

    public function assignTrainer(int $memberId, int $trainerId): void
    {
        $stmt = $this->db()->prepare('UPDATE gym_members SET assigned_trainer_id = :tid WHERE id = :id');
        $stmt->execute([':tid' => $trainerId, ':id' => $memberId]);
    }

    public static function generateCode(): string
    {
        return 'GYM-' . strtoupper(bin2hex(random_bytes(4)));
    }
}
