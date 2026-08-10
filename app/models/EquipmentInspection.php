<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class EquipmentInspection extends Model
{
    /** Check if table exists */
    public function tableExists(): bool
    {
        try {
            $this->db()->query('SELECT 1 FROM equipment_inspections LIMIT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /** Create a new inspection (draft) */
    public function create(
        int $equipmentId,
        int $maintenanceId,
        int $gymId,
        string $inspectionDate,
        string $condition,
        string $remarks,
        ?string $signatureData
    ): int {
        $stmt = $this->db()->prepare(
            'INSERT INTO equipment_inspections
                (equipment_id, maintenance_id, gym_id, inspection_date, overall_condition, remarks, signature_data, status)
             VALUES
                (:eid, :mid, :gid, :date, :cond, :rem, :sig, "draft")'
        );
        $stmt->execute([
            ':eid'  => $equipmentId,
            ':mid'  => $maintenanceId,
            ':gid'  => $gymId,
            ':date' => $inspectionDate,
            ':cond' => $condition,
            ':rem'  => $remarks,
            ':sig'  => $signatureData,
        ]);
        return (int)$this->db()->lastInsertId();
    }

    /** Update an existing inspection */
    public function update(
        int $id,
        string $condition,
        string $remarks,
        ?string $signatureData
    ): void {
        $stmt = $this->db()->prepare(
            'UPDATE equipment_inspections
             SET overall_condition = :cond, remarks = :rem, signature_data = :sig
             WHERE id = :id AND status = "draft"'
        );
        $stmt->execute([':cond' => $condition, ':rem' => $remarks, ':sig' => $signatureData, ':id' => $id]);
    }

    /** Submit an inspection to gym owner */
    public function submit(int $id): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE equipment_inspections
             SET status = "submitted", submitted_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
    }

    /** Verify an inspection (gym owner) */
    public function verify(int $id, int $verifiedBy): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE equipment_inspections
             SET status = "verified", verified_at = NOW(), verified_by = :vby
             WHERE id = :id'
        );
        $stmt->execute([':vby' => $verifiedBy, ':id' => $id]);
    }

    /** Find by id with equipment and user info */
    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT ei.*, ge.name AS equipment_name, ge.category, ge.brand,
                    ge.weight_kg, ge.image_path,
                    u.fullname AS staff_name,
                    vr.fullname AS verified_by_name
             FROM equipment_inspections ei
             JOIN gym_equipment ge ON ge.id = ei.equipment_id
             JOIN users u ON u.id = ei.maintenance_id
             LEFT JOIN users vr ON vr.id = ei.verified_by
             WHERE ei.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Find all inspections by maintenance staff user id */
    public function findByMaintenanceUser(int $userId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT ei.*, ge.name AS equipment_name, ge.category
             FROM equipment_inspections ei
             JOIN gym_equipment ge ON ge.id = ei.equipment_id
             WHERE ei.maintenance_id = :uid
             ORDER BY ei.created_at DESC'
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Find inspections for gym owner view */
    public function findByGymOwner(int $gymId, string $filter = 'all'): array
    {
        $sql = 'SELECT ei.*, ge.name AS equipment_name, ge.category,
                       u.fullname AS staff_name
                FROM equipment_inspections ei
                JOIN gym_equipment ge ON ge.id = ei.equipment_id
                JOIN users u ON u.id = ei.maintenance_id
                WHERE ei.gym_id = :gid';
        if ($filter === 'pending') {
            $sql .= ' AND ei.status = "submitted"';
        } elseif ($filter === 'verified') {
            $sql .= ' AND ei.status = "verified"';
        }
        $sql .= ' ORDER BY ei.created_at DESC';

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([':gid' => $gymId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Get last inspection for a specific equipment */
    public function findLatestForEquipment(int $equipmentId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM equipment_inspections
             WHERE equipment_id = :eid AND status != "draft"
             ORDER BY inspection_date DESC LIMIT 1'
        );
        $stmt->execute([':eid' => $equipmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Count inspections today by maintenance user */
    public function countInspectedToday(int $userId): int
    {
        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) FROM equipment_inspections
             WHERE maintenance_id = :uid AND inspection_date = CURDATE()'
        );
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /** Count pending (draft) reports */
    public function countDraft(int $userId): int
    {
        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) FROM equipment_inspections
             WHERE maintenance_id = :uid AND status = "draft"'
        );
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /** Count submitted reports */
    public function countSubmitted(int $userId): int
    {
        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) FROM equipment_inspections
             WHERE maintenance_id = :uid AND status = "submitted"'
        );
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /** Get recent inspections for dashboard */
    public function getRecent(int $userId, int $limit = 5): array
    {
        $stmt = $this->db()->prepare(
            'SELECT ei.*, ge.name AS equipment_name
             FROM equipment_inspections ei
             JOIN gym_equipment ge ON ge.id = ei.equipment_id
             WHERE ei.maintenance_id = :uid
             ORDER BY ei.created_at DESC
             LIMIT ' . $limit
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
