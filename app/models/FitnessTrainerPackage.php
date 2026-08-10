<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use PDO;

final class FitnessTrainerPackage extends Model
{
    public function create(int $gymOwnerId, array $data): int
    {
        try {
            $stmt = $this->db()->prepare(
                'INSERT INTO fitness_trainer_packages 
                (gym_owner_id, package_name, training_type, session_count, price, duration_minutes, description, is_active)
                VALUES (:owner_id, :name, :type, :sessions, :price, :duration, :desc, :active)'
            );
            
            $stmt->execute([
                ':owner_id' => $gymOwnerId,
                ':name' => $data['package_name'],
                ':type' => $data['training_type'] ?? 'all',
                ':sessions' => $data['session_count'],
                ':price' => round((float)$data['price'], 2),
                ':duration' => $data['duration_minutes'] ?? 60,
                ':desc' => $data['description'] ?? '',
                ':active' => $data['is_active'] ?? true
            ]);
            
            return (int)$this->db()->lastInsertId();
        } catch (\Exception $e) {
            $this->logError("Failed to create package: " . $e->getMessage());
            return 0;
        }
    }

    public function findByGymOwner(int $gymOwnerId, bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM fitness_trainer_packages WHERE gym_owner_id = :owner_id';
        
        if ($activeOnly) {
            $sql .= ' AND is_active = 1';
        }
        
        $sql .= ' ORDER BY session_count ASC';
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([':owner_id' => $gymOwnerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllActive(): array
    {
        $stmt = $this->db()->query(
            'SELECT ftp.*, u.fullname as gym_owner_name
             FROM fitness_trainer_packages ftp
             JOIN users u ON u.id = ftp.gym_owner_id
             WHERE ftp.is_active = 1
             ORDER BY ftp.session_count ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT ftp.*, u.fullname as gym_owner_name
             FROM fitness_trainer_packages ftp
             JOIN users u ON u.id = ftp.gym_owner_id
             WHERE ftp.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function update(int $id, int $gymOwnerId, array $data): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'UPDATE fitness_trainer_packages 
                 SET package_name = :name, training_type = :type, session_count = :sessions,
                     price = :price, duration_minutes = :duration, description = :desc,
                     is_active = :active, updated_at = NOW()
                 WHERE id = :id AND gym_owner_id = :owner_id'
            );
            
            return $stmt->execute([
                ':name' => $data['package_name'],
                ':type' => $data['training_type'] ?? 'all',
                ':sessions' => $data['session_count'],
                ':price' => round((float)$data['price'], 2),
                ':duration' => $data['duration_minutes'] ?? 60,
                ':desc' => $data['description'] ?? '',
                ':active' => $data['is_active'] ?? true,
                ':id' => $id,
                ':owner_id' => $gymOwnerId
            ]);
        } catch (\Exception $e) {
            $this->logError("Failed to update package: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id, int $gymOwnerId): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'DELETE FROM fitness_trainer_packages WHERE id = :id AND gym_owner_id = :owner_id'
            );
            return $stmt->execute([':id' => $id, ':owner_id' => $gymOwnerId]);
        } catch (\Exception $e) {
            $this->logError("Failed to delete package: " . $e->getMessage());
            return false;
        }
    }

    public function toggleActive(int $id, int $gymOwnerId): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'UPDATE fitness_trainer_packages 
                 SET is_active = NOT is_active, updated_at = NOW()
                 WHERE id = :id AND gym_owner_id = :owner_id'
            );
            return $stmt->execute([':id' => $id, ':owner_id' => $gymOwnerId]);
        } catch (\Exception $e) {
            $this->logError("Failed to toggle package status: " . $e->getMessage());
            return false;
        }
    }

    private function logError(string $message): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/fitness_service.log';
        $logMessage = sprintf("[%s] FitnessTrainerPackage: %s\n", date('Y-m-d H:i:s'), $message);
        @error_log($logMessage, 3, $logFile);
    }
}
