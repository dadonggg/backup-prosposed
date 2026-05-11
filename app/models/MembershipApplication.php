<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class MembershipApplication extends Model
{
    /** Payment pricing constants (fallback — gym owner sets dynamic prices) */
    public const PRICE_STUDENT_MONTHLY  = 600.00;
    public const PRICE_REGULAR_MONTHLY  = 700.00;
    public const PRICE_WITH_TRAINER     = 1500.00;

    public static function getPriceForType(string $type): float
    {
        return match ($type) {
            'student_monthly'  => self::PRICE_STUDENT_MONTHLY,
            'regular_monthly'  => self::PRICE_REGULAR_MONTHLY,
            'with_trainer'     => self::PRICE_WITH_TRAINER,
            default            => 0.0,
        };
    }

    /**
     * Check if user already has an active application (pending/verified/approved).
     * Prevents duplicate membership applications.
     */
    public function hasActiveApplication(int $userId): bool
    {
        $stmt = $this->db()->prepare(
            "SELECT COUNT(*) FROM membership_applications
             WHERE user_id = :uid AND status IN ('pending','verified','approved')"
        );
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(
        int $userId,
        string $fn,
        string $ln,
        string $mi,
        string $phone,
        ?int $trainerId,
        string $paymentType = 'regular_monthly',
        ?string $studentProof = null
    ): int {
        $amount = self::getPriceForType($paymentType);
        $stmt = $this->db()->prepare(
            'INSERT INTO membership_applications
             (user_id, first_name, last_name, middle_initial, phone_number,
              preferred_trainer_id, payment_type, payment_amount, student_proof, status)
             VALUES (:uid, :fn, :ln, :mi, :ph, :tid, :pt, :pa, :sp, "pending")'
        );
        $stmt->execute([
            ':uid' => $userId,
            ':fn'  => $fn,
            ':ln'  => $ln,
            ':mi'  => $mi ?: null,
            ':ph'  => $phone,
            ':tid' => $trainerId,
            ':pt'  => $paymentType,
            ':pa'  => $amount,
            ':sp'  => $studentProof,
        ]);
        return (int)$this->db()->lastInsertId();
    }

    /**
     * Create membership application with service selection and payment mode
     */
    public function createWithService(
        int $userId,
        string $fn,
        string $ln,
        string $mi,
        string $phone,
        ?int $trainerId,
        string $paymentType,
        ?int $serviceId,
        float $paymentAmount,
        string $paymentMode,
        int $gymOwnerId
    ): int {
        $stmt = $this->db()->prepare(
            'INSERT INTO membership_applications
             (user_id, gym_owner_id, first_name, last_name, middle_initial, phone_number,
              preferred_trainer_id, payment_type, service_id, payment_amount, payment_mode, payment_status, status)
             VALUES (:uid, :goid, :fn, :ln, :mi, :ph, :tid, :pt, :sid, :pa, :pm, "pending", "pending")'
        );
        $stmt->execute([
            ':uid' => $userId,
            ':goid' => $gymOwnerId,
            ':fn'  => $fn,
            ':ln'  => $ln,
            ':mi'  => $mi ?: null,
            ':ph'  => $phone,
            ':tid' => $trainerId,
            ':pt'  => $paymentType,
            ':sid' => $serviceId,
            ':pa'  => $paymentAmount,
            ':pm'  => $paymentMode,
        ]);
        return (int)$this->db()->lastInsertId();
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM membership_applications WHERE user_id = :uid ORDER BY id DESC LIMIT 1');
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT ma.*, u.fullname, u.email, u.firstname, u.lastname, u.middle_initial as user_mi
             FROM membership_applications ma
             JOIN users u ON u.id = ma.user_id WHERE ma.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findAll(): array
    {
        return $this->db()->query(
            'SELECT ma.*, u.fullname, u.email FROM membership_applications ma
             JOIN users u ON u.id = ma.user_id ORDER BY ma.created_at DESC'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update status with feedback. Properly handles the full workflow.
     */
    public function updateStatus(int $id, string $status, string $feedback = '', ?int $reviewerId = null): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE membership_applications SET status=:s, admin_feedback=:f, reviewer_id=:rid WHERE id=:id'
        );
        $stmt->execute([':s' => $status, ':f' => $feedback, ':rid' => $reviewerId, ':id' => $id]);
    }

    public function assignTrainer(int $id, int $trainerId): void
    {
        $stmt = $this->db()->prepare('UPDATE membership_applications SET preferred_trainer_id=:tid WHERE id=:id');
        $stmt->execute([':tid' => $trainerId, ':id' => $id]);
    }

    /**
     * Reset application for resubmission — keeps the same record, sets status back to pending.
     */
    public function resubmit(int $id, string $fn, string $ln, string $mi, string $phone,
                              ?int $trainerId, string $paymentType, ?string $studentProof = null): void
    {
        $amount = self::getPriceForType($paymentType);
        $stmt = $this->db()->prepare(
            'UPDATE membership_applications SET
             first_name=:fn, last_name=:ln, middle_initial=:mi, phone_number=:ph,
             preferred_trainer_id=:tid, payment_type=:pt, payment_amount=:pa, student_proof=:sp,
             status="pending", admin_feedback=NULL
             WHERE id=:id'
        );
        $stmt->execute([
            ':fn'  => $fn, ':ln'  => $ln, ':mi'  => $mi ?: null, ':ph'  => $phone,
            ':tid' => $trainerId, ':pt'  => $paymentType, ':pa'  => $amount,
            ':sp'  => $studentProof, ':id'  => $id,
        ]);
    }

    /**
     * Resubmit membership application with service selection and payment mode
     */
    public function resubmitWithService(
        int $id,
        string $fn,
        string $ln,
        string $mi,
        string $phone,
        ?int $trainerId,
        string $paymentType,
        ?int $serviceId,
        float $paymentAmount,
        string $paymentMode,
        int $gymOwnerId
    ): void {
        $stmt = $this->db()->prepare(
            'UPDATE membership_applications SET
             gym_owner_id=:goid, first_name=:fn, last_name=:ln, middle_initial=:mi, phone_number=:ph,
             preferred_trainer_id=:tid, payment_type=:pt, service_id=:sid, payment_amount=:pa, 
             payment_mode=:pm, payment_status="pending", status="pending", admin_feedback=NULL
             WHERE id=:id'
        );
        $stmt->execute([
            ':goid' => $gymOwnerId,
            ':fn'  => $fn,
            ':ln'  => $ln,
            ':mi'  => $mi ?: null,
            ':ph'  => $phone,
            ':tid' => $trainerId,
            ':pt'  => $paymentType,
            ':sid' => $serviceId,
            ':pa'  => $paymentAmount,
            ':pm'  => $paymentMode,
            ':id'  => $id,
        ]);
    }

    /**
     * Get count of active (pending/verified) applications per user.
     */
    public function countActiveByUserId(int $userId): int
    {
        $stmt = $this->db()->prepare(
            "SELECT COUNT(*) FROM membership_applications
             WHERE user_id = :uid AND status IN ('pending','verified')"
        );
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function markPaymentSubmitted(int $id): void
    {
        $stmt = $this->db()->prepare('UPDATE membership_applications SET payment_submitted_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
