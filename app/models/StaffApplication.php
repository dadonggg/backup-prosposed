<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class StaffApplication extends Model
{
    /**
     * Create a new staff application (position-only; documents are uploaded separately via Profile & Settings).
     */
    public function create(int $userId, string $type, ?int $gymOwnerId = null): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO staff_applications (user_id, gym_owner_id, application_type, medical_certificate, resume, status)
             VALUES (:uid, :gid, :type, NULL, NULL, "pending")'
        );
        $stmt->execute([':uid' => $userId, ':gid' => $gymOwnerId, ':type' => $type]);
        return (int)$this->db()->lastInsertId();
    }

    /**
     * Find the most recent application for a specific user + gym combination.
     */
    public function findByUserAndGym(int $userId, int $gymOwnerId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM staff_applications WHERE user_id = :uid AND gym_owner_id = :gid ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([':uid' => $userId, ':gid' => $gymOwnerId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    
    /**
     * Find all gyms with verified legal documents (available for staff applications)
     */
    public function findAvailableGyms(): array
    {
        $stmt = $this->db()->query(
            'SELECT u.id as gym_owner_id, u.fullname as owner_name, 
                    ld.gym_name, ld.gym_logo, ld.gym_address,
                    ld.maintenance_count, ld.trainer_count
             FROM users u
             JOIN legal_documents ld ON ld.user_id = u.id
             WHERE u.role = "gym_owner" AND ld.status = "verified"
             ORDER BY ld.gym_name'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM staff_applications WHERE user_id = :uid ORDER BY id DESC LIMIT 1');
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT sa.*, u.fullname, u.email FROM staff_applications sa
             JOIN users u ON u.id = sa.user_id WHERE sa.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findAllPending(): array
    {
        $stmt = $this->db()->query(
            'SELECT sa.*, u.fullname, u.email FROM staff_applications sa
             JOIN users u ON u.id = sa.user_id ORDER BY sa.created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Find all staff applications for a specific gym owner
     */
    public function findByGymOwner(int $gymOwnerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT sa.*, u.fullname, u.email FROM staff_applications sa
             JOIN users u ON u.id = sa.user_id 
             WHERE sa.gym_owner_id = :gid
             ORDER BY sa.created_at DESC'
        );
        $stmt->execute([':gid' => $gymOwnerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update application status with proper reviewer_id validation.
     * reviewer_id must be a valid users.id or null — NEVER 0.
     */
    public function updateStatus(int $id, string $status, string $feedback, ?int $reviewerId = null): void
    {
        // Prevent FK violation: reviewer_id must be valid or NULL, never 0
        if ($reviewerId !== null && $reviewerId <= 0) {
            $reviewerId = null;
        }

        $stmt = $this->db()->prepare(
            'UPDATE staff_applications SET status = :s, feedback = :f, reviewer_id = :rid WHERE id = :id'
        );
        $stmt->execute([':s' => $status, ':f' => $feedback, ':rid' => $reviewerId, ':id' => $id]);
    }

    public function updateDocuments(int $id, string $medCert, string $resume): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE staff_applications SET medical_certificate=:med, resume=:res, status="pending", feedback=NULL,
             medical_certificate_status="pending", medical_certificate_comment=NULL, medical_certificate_checked=0,
             resume_status="pending", resume_comment=NULL, resume_checked=0
             WHERE id=:id'
        );
        $stmt->execute([':med' => $medCert, ':res' => $resume, ':id' => $id]);
    }

    /** Resubmit only a single flagged document */
    public function updateSingleDocument(int $id, string $field, string $path): void
    {
        $allowed = ['medical_certificate', 'resume'];
        if (!in_array($field, $allowed, true)) return;

        $stmt = $this->db()->prepare(
            "UPDATE staff_applications SET
                {$field} = :path,
                {$field}_status = 'pending',
                {$field}_comment = NULL,
                {$field}_checked = 0
             WHERE id = :id"
        );
        $stmt->execute([':path' => $path, ':id' => $id]);
    }

    /**
     * Update per-document status, comment, and checklist.
     * $docField is one of: medical_certificate, resume
     */
    public function updateDocStatus(int $id, string $docField, string $status, string $comment, bool $checked): void
    {
        $allowed = ['medical_certificate', 'resume'];
        if (!in_array($docField, $allowed, true)) return;

        $sql = "UPDATE staff_applications SET
            {$docField}_status = :status,
            {$docField}_comment = :comment,
            {$docField}_checked = :checked
            WHERE id = :id";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            ':status' => $status,
            ':comment' => $comment,
            ':checked' => $checked ? 1 : 0,
            ':id' => $id,
        ]);
    }

    /**
     * Recompute overall status from per-document statuses.
     * All approved => approved. Any flagged => resubmit. Else pending.
     */
    public function recomputeOverallStatus(int $id, int $reviewerId): void
    {
        $app = $this->findById($id);
        if (!$app) return;

        $fields = ['medical_certificate_status', 'resume_status'];
        $allApproved = true;
        $anyFlagged = false;
        foreach ($fields as $f) {
            if (($app[$f] ?? 'pending') === 'flagged') { $anyFlagged = true; }
            if (($app[$f] ?? 'pending') !== 'approved') { $allApproved = false; }
        }

        if ($anyFlagged) {
            // Build feedback from flagged documents
            $feedback = [];
            $labels = [
                'medical_certificate' => 'Medical Certificate',
                'resume' => 'Resume / CV',
            ];
            foreach ($labels as $key => $label) {
                if (($app[$key . '_status'] ?? 'pending') === 'flagged') {
                    $comment = $app[$key . '_comment'] ?? '';
                    $feedback[] = $label . ': ' . ($comment !== '' ? $comment : 'Flagged for resubmission');
                }
            }
            $this->updateStatus($id, 'resubmit', implode(' | ', $feedback), $reviewerId);
        } elseif ($allApproved) {
            // Don't auto-approve (hire) — just set to pending so gym owner can do final approve
            $this->updateStatus($id, 'pending', 'All documents verified. Ready for final approval.', $reviewerId);
        }
    }
}
