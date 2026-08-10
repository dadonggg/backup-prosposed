<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class InspectionChecklist extends Model
{
    /** Check if table exists */
    public function tableExists(): bool
    {
        try {
            $this->db()->query('SELECT 1 FROM inspection_checklist_items LIMIT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /** Insert multiple checklist items for an inspection */
    public function saveItems(int $inspectionId, array $items): void
    {
        // Delete existing items first (replace strategy for draft updates)
        $this->deleteByInspection($inspectionId);

        $stmt = $this->db()->prepare(
            'INSERT INTO inspection_checklist_items (inspection_id, item_description, is_done, notes)
             VALUES (:iid, :desc, :done, :notes)'
        );
        foreach ($items as $item) {
            $stmt->execute([
                ':iid'   => $inspectionId,
                ':desc'  => $item['description'] ?? '',
                ':done'  => isset($item['done']) && $item['done'] ? 1 : 0,
                ':notes' => $item['notes'] ?? null,
            ]);
        }
    }

    /** Get all checklist items for an inspection */
    public function findByInspection(int $inspectionId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM inspection_checklist_items
             WHERE inspection_id = :iid
             ORDER BY id ASC'
        );
        $stmt->execute([':iid' => $inspectionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Delete all items for an inspection */
    public function deleteByInspection(int $inspectionId): void
    {
        $stmt = $this->db()->prepare(
            'DELETE FROM inspection_checklist_items WHERE inspection_id = :iid'
        );
        $stmt->execute([':iid' => $inspectionId]);
    }
}
