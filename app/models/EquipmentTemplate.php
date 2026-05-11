<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

final class EquipmentTemplate extends Model
{
    /**
     * Get all equipment templates available to a gym owner
     * Includes global templates and templates created by the gym owner
     */
    public function getAvailableTemplates(int $gymOwnerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT DISTINCT equipment_name, brand, dimensions, weight_kg, category, is_global, created_by
             FROM equipment_templates 
             WHERE is_global = 1 OR created_by = :gym_owner_id
             ORDER BY equipment_name ASC, brand ASC'
        );
        $stmt->execute([':gym_owner_id' => $gymOwnerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get templates for a specific equipment name
     */
    public function getTemplatesByName(string $equipmentName, int $gymOwnerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT equipment_name, brand, dimensions, weight_kg, category, is_global, created_by
             FROM equipment_templates 
             WHERE equipment_name = :name AND (is_global = 1 OR created_by = :gym_owner_id)
             ORDER BY is_global DESC, brand ASC'
        );
        $stmt->execute([
            ':name' => $equipmentName,
            ':gym_owner_id' => $gymOwnerId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all unique equipment names available to a gym owner
     */
    public function getUniqueEquipmentNames(int $gymOwnerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT DISTINCT equipment_name
             FROM equipment_templates 
             WHERE is_global = 1 OR created_by = :gym_owner_id
             ORDER BY equipment_name ASC'
        );
        $stmt->execute([':gym_owner_id' => $gymOwnerId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'equipment_name');
    }

    /**
     * Create a new equipment template
     */
    public function createTemplate(
        string $equipmentName,
        ?string $brand,
        ?string $dimensions,
        ?float $weightKg,
        ?string $category,
        int $createdBy,
        bool $isGlobal = false
    ): int {
        $stmt = $this->db()->prepare(
            'INSERT INTO equipment_templates 
             (equipment_name, brand, dimensions, weight_kg, category, created_by, is_global)
             VALUES (:name, :brand, :dimensions, :weight, :category, :created_by, :is_global)'
        );
        
        $stmt->execute([
            ':name' => $equipmentName,
            ':brand' => $brand,
            ':dimensions' => $dimensions,
            ':weight' => $weightKg,
            ':category' => $category,
            ':created_by' => $createdBy,
            ':is_global' => $isGlobal ? 1 : 0
        ]);
        
        return (int)$this->db()->lastInsertId();
    }

    /**
     * Check if a template already exists (flexible matching)
     */
    public function templateExists(
        string $equipmentName,
        ?string $brand,
        ?string $dimensions,
        ?float $weightKg,
        int $createdBy
    ): bool {
        // If we have full details, check for exact match
        if (!empty($brand) && !empty($dimensions) && $weightKg !== null) {
            $stmt = $this->db()->prepare(
                'SELECT COUNT(*) as count
                 FROM equipment_templates 
                 WHERE equipment_name = :name 
                 AND brand = :brand 
                 AND dimensions = :dimensions 
                 AND weight_kg = :weight 
                 AND created_by = :created_by'
            );
            
            $stmt->execute([
                ':name' => $equipmentName,
                ':brand' => $brand,
                ':dimensions' => $dimensions,
                ':weight' => $weightKg,
                ':created_by' => $createdBy
            ]);
        } else {
            // Just check if equipment name exists for this user
            $stmt = $this->db()->prepare(
                'SELECT COUNT(*) as count
                 FROM equipment_templates 
                 WHERE equipment_name = :name 
                 AND created_by = :created_by'
            );
            
            $stmt->execute([
                ':name' => $equipmentName,
                ':created_by' => $createdBy
            ]);
        }
        
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }

    /**
     * Get templates created by a specific gym owner
     */
    public function getTemplatesByOwner(int $gymOwnerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM equipment_templates 
             WHERE created_by = :gym_owner_id
             ORDER BY equipment_name ASC, created_at DESC'
        );
        $stmt->execute([':gym_owner_id' => $gymOwnerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a template (only if created by the gym owner)
     */
    public function deleteTemplate(int $templateId, int $gymOwnerId): bool
    {
        $stmt = $this->db()->prepare(
            'DELETE FROM equipment_templates 
             WHERE id = :id AND created_by = :gym_owner_id AND is_global = 0'
        );
        $stmt->execute([
            ':id' => $templateId,
            ':gym_owner_id' => $gymOwnerId
        ]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Get templates as JSON for JavaScript
     */
    public function getTemplatesAsJson(int $gymOwnerId): string
    {
        $templates = $this->getAvailableTemplates($gymOwnerId);
        
        // Group templates by equipment name
        $grouped = [];
        foreach ($templates as $template) {
            $name = $template['equipment_name'];
            if (!isset($grouped[$name])) {
                $grouped[$name] = [];
            }
            $grouped[$name][] = [
                'brand' => $template['brand'],
                'dimensions' => $template['dimensions'],
                'weight_kg' => $template['weight_kg'],
                'category' => $template['category'],
                'is_global' => (bool)$template['is_global']
            ];
        }
        
        return json_encode($grouped, JSON_UNESCAPED_UNICODE);
    }
}