<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class LegalDocument extends Model
{
    public function create(int $userId, string $certReg, string $mayors, string $bizName, string $fireSafety, string $gymName = '', string $gymLogo = '', string $gymAddress = '', int $maintenanceCount = 0, int $trainerCount = 0): int
    {
        try {
            \App\Core\Database::beginTransaction();

            $stmt = $this->db()->prepare(
                'INSERT INTO legal_documents (user_id, gym_name, gym_logo, gym_address, maintenance_count, trainer_count, cert_registration, mayors_permit, business_name_cert, fire_safety_cert, status)
                 VALUES (:uid, :gn, :gl, :ga, :mc, :tc, :cr, :mp, :bn, :fs, "pending")'
            );
            $stmt->execute([
                ':uid' => $userId, 
                ':gn' => $gymName,
                ':gl' => $gymLogo,
                ':ga' => $gymAddress,
                ':mc' => $maintenanceCount,
                ':tc' => $trainerCount,
                ':cr' => $certReg, 
                ':mp' => $mayors, 
                ':bn' => $bizName, 
                ':fs' => $fireSafety
            ]);
            
            $id = (int)$this->db()->lastInsertId();

            \App\Core\Database::commit();
            return $id;

        } catch (\Exception $e) {
            \App\Core\Database::rollback();
            $this->logError("create failed for user ID $userId: " . $e->getMessage());
            return 0;
        }
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM legal_documents WHERE user_id = :uid ORDER BY id DESC LIMIT 1');
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM legal_documents WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findAllPending(): array
    {
        $stmt = $this->db()->query(
            'SELECT ld.*, u.fullname, u.email
             FROM legal_documents ld
             JOIN users u ON u.id = ld.user_id
             WHERE ld.id IN (
                 SELECT MAX(id) FROM legal_documents GROUP BY user_id
             )
             ORDER BY ld.created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find all verified gyms for membership application listing
     */
    public function findAllVerified(): array
    {
        $stmt = $this->db()->query(
            'SELECT ld.*, u.fullname, u.email FROM legal_documents ld
             JOIN users u ON u.id = ld.user_id 
             WHERE ld.status = "verified"
             ORDER BY ld.updated_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $id, string $status, string $feedback = ''): void
    {
        $stmt = $this->db()->prepare('UPDATE legal_documents SET status = :s, admin_feedback = :f WHERE id = :id');
        $stmt->execute([':s' => $status, ':f' => $feedback, ':id' => $id]);
    }

    public function updateDocuments(int $id, string $certReg, string $mayors, string $bizName, string $fireSafety): bool
    {
        try {
            \App\Core\Database::beginTransaction();

            $stmt = $this->db()->prepare(
                'UPDATE legal_documents SET cert_registration=:cr, mayors_permit=:mp, business_name_cert=:bn,
                 fire_safety_cert=:fs, status="pending", admin_feedback=NULL,
                 cert_registration_status="pending", mayors_permit_status="pending",
                 business_name_cert_status="pending", fire_safety_cert_status="pending",
                 cert_registration_comment=NULL, mayors_permit_comment=NULL,
                 business_name_cert_comment=NULL, fire_safety_cert_comment=NULL,
                 cert_registration_checked=0, mayors_permit_checked=0,
                 business_name_cert_checked=0, fire_safety_cert_checked=0
                 WHERE id=:id'
            );
            $stmt->execute([':cr' => $certReg, ':mp' => $mayors, ':bn' => $bizName, ':fs' => $fireSafety, ':id' => $id]);

            $rowCount = $stmt->rowCount();
            if ($rowCount === 0) {
                $this->logError("updateDocuments: No rows affected for document ID $id");
                \App\Core\Database::rollback();
                return false;
            }

            \App\Core\Database::commit();
            return true;

        } catch (\Exception $e) {
            \App\Core\Database::rollback();
            $this->logError("updateDocuments failed for document ID $id: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update per-document status, comment, and checklist for a specific permit.
     * $docField is one of: cert_registration, mayors_permit, business_name_cert, fire_safety_cert
     * Returns true on success, false on failure
     */
    public function updateDocStatus(int $id, string $docField, string $status, string $comment, bool $checked): bool
    {
        $allowed = ['cert_registration', 'mayors_permit', 'business_name_cert', 'fire_safety_cert'];
        if (!in_array($docField, $allowed, true)) {
            $this->logError("Invalid document field: $docField");
            return false;
        }

        try {
            \App\Core\Database::beginTransaction();

            $sql = "UPDATE legal_documents SET
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

            $rowCount = $stmt->rowCount();
            if ($rowCount === 0) {
                $this->logError("updateDocStatus: No rows affected for document ID $id, field $docField");
                \App\Core\Database::rollback();
                return false;
            }

            \App\Core\Database::commit();
            return true;

        } catch (\Exception $e) {
            \App\Core\Database::rollback();
            $this->logError("updateDocStatus failed for document ID $id, field $docField: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Recompute overall status from per-document statuses.
     * If all are 'approved' => 'verified'. If any is 'flagged' => 'resubmit'. Else 'pending'.
     * Returns true on success, false on failure
     */
    public function recomputeOverallStatus(int $id): bool
    {
        $doc = $this->findById($id);
        if (!$doc) {
            $this->logError("recomputeOverallStatus: Document ID $id not found");
            return false;
        }

        $fields = ['cert_registration_status', 'mayors_permit_status', 'business_name_cert_status', 'fire_safety_cert_status'];
        $allApproved = true;
        $anyFlagged = false;
        
        foreach ($fields as $f) {
            if (($doc[$f] ?? 'pending') === 'flagged') { 
                $anyFlagged = true; 
            }
            if (($doc[$f] ?? 'pending') !== 'approved') { 
                $allApproved = false; 
            }
        }

        try {
            \App\Core\Database::beginTransaction();

            if ($allApproved) {
                $this->updateStatusInternal($id, 'verified', 'All documents verified.');
            } elseif ($anyFlagged) {
                // Collect flagged document comments
                $feedback = [];
                $labels = [
                    'cert_registration' => 'Certificate of Registration',
                    'mayors_permit' => "Mayor's Permit",
                    'business_name_cert' => 'Business Name Certificate',
                    'fire_safety_cert' => 'Fire Safety Certificate',
                ];
                foreach ($labels as $key => $label) {
                    if (($doc[$key . '_status'] ?? 'pending') === 'flagged') {
                        $comment = $doc[$key . '_comment'] ?? '';
                        $feedback[] = $label . ': ' . ($comment !== '' ? $comment : 'Flagged for resubmission');
                    }
                }
                $this->updateStatusInternal($id, 'resubmit', implode(' | ', $feedback));
            } else {
                $this->updateStatusInternal($id, 'pending', '');
            }

            \App\Core\Database::commit();
            return true;

        } catch (\Exception $e) {
            \App\Core\Database::rollback();
            $this->logError("recomputeOverallStatus failed for document ID $id: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Internal method to update status without transaction management
     * Used by recomputeOverallStatus which manages its own transaction
     */
    private function updateStatusInternal(int $id, string $status, string $feedback = ''): void
    {
        $stmt = $this->db()->prepare('UPDATE legal_documents SET status = :s, admin_feedback = :f WHERE id = :id');
        $stmt->execute([':s' => $status, ':f' => $feedback, ':id' => $id]);
    }

    /**
     * Resubmit a single document — update just that file and reset its per-doc status.
     * Returns true on success, false on failure
     */
    public function resubmitSingleDoc(int $id, string $docField, string $newPath): bool
    {
        $allowed = ['cert_registration', 'mayors_permit', 'business_name_cert', 'fire_safety_cert'];
        if (!in_array($docField, $allowed, true)) {
            $this->logError("Invalid document field in resubmitSingleDoc: $docField");
            return false;
        }

        try {
            \App\Core\Database::beginTransaction();

            $sql = "UPDATE legal_documents SET
                {$docField} = :path,
                {$docField}_status = 'pending',
                {$docField}_comment = NULL,
                {$docField}_checked = 0
                WHERE id = :id";
            
            $stmt = $this->db()->prepare($sql);
            $stmt->execute([':path' => $newPath, ':id' => $id]);

            $rowCount = $stmt->rowCount();
            if ($rowCount === 0) {
                $this->logError("resubmitSingleDoc: No rows affected for document ID $id, field $docField");
                \App\Core\Database::rollback();
                return false;
            }

            \App\Core\Database::commit();

            // Recompute overall status after successful resubmission
            $this->recomputeOverallStatus($id);

            return true;

        } catch (\Exception $e) {
            \App\Core\Database::rollback();
            $this->logError("resubmitSingleDoc failed for document ID $id, field $docField: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment staff count for a gym owner
     * @param int $gymOwnerId The gym owner's user ID
     * @param string $staffType Either 'maintenance' or 'trainer'
     * @return bool Success status
     */
    public function incrementStaffCount(int $gymOwnerId, string $staffType): bool
    {
        if (!in_array($staffType, ['maintenance', 'trainer'], true)) {
            $this->logError("Invalid staff type: $staffType");
            return false;
        }

        $column = $staffType === 'maintenance' ? 'maintenance_count' : 'trainer_count';

        try {
            $stmt = $this->db()->prepare(
                "UPDATE legal_documents SET {$column} = {$column} + 1 WHERE user_id = :uid"
            );
            $stmt->execute([':uid' => $gymOwnerId]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logError("incrementStaffCount failed for gym owner ID $gymOwnerId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrement staff count for a gym owner
     * @param int $gymOwnerId The gym owner's user ID
     * @param string $staffType Either 'maintenance' or 'trainer'
     * @return bool Success status
     */
    public function decrementStaffCount(int $gymOwnerId, string $staffType): bool
    {
        if (!in_array($staffType, ['maintenance', 'trainer'], true)) {
            $this->logError("Invalid staff type: $staffType");
            return false;
        }

        $column = $staffType === 'maintenance' ? 'maintenance_count' : 'trainer_count';

        try {
            $stmt = $this->db()->prepare(
                "UPDATE legal_documents SET {$column} = GREATEST(0, {$column} - 1) WHERE user_id = :uid"
            );
            $stmt->execute([':uid' => $gymOwnerId]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logError("decrementStaffCount failed for gym owner ID $gymOwnerId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log errors to database log file
     */
    private function logError(string $message): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/database.log';
        $logMessage = sprintf(
            "[%s] LegalDocument: %s\n",
            date('Y-m-d H:i:s'),
            $message
        );

        @error_log($logMessage, 3, $logFile);
    }
}
