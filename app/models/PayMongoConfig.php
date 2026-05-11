<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class PayMongoConfig extends Model
{
    /**
     * Create or update PayMongo configuration for a gym owner
     */
    public function upsert(int $gymOwnerId, string $publicKey, string $secretKey, bool $isActive = true): int
    {
        // Check if config already exists
        $existing = $this->findByOwnerId($gymOwnerId);
        
        if ($existing) {
            // Update existing config
            $stmt = $this->db()->prepare(
                'UPDATE paymongo_config 
                 SET public_key = :pk, secret_key = :sk, is_active = :active, updated_at = NOW()
                 WHERE gym_owner_id = :oid'
            );
            $stmt->execute([
                ':pk' => $publicKey,
                ':sk' => $secretKey,
                ':active' => $isActive ? 1 : 0,
                ':oid' => $gymOwnerId,
            ]);
            return (int)$existing['id'];
        } else {
            // Create new config
            $stmt = $this->db()->prepare(
                'INSERT INTO paymongo_config (gym_owner_id, public_key, secret_key, is_active)
                 VALUES (:oid, :pk, :sk, :active)'
            );
            $stmt->execute([
                ':oid' => $gymOwnerId,
                ':pk' => $publicKey,
                ':sk' => $secretKey,
                ':active' => $isActive ? 1 : 0,
            ]);
            return (int)$this->db()->lastInsertId();
        }
    }

    /**
     * Find PayMongo config by gym owner ID
     */
    public function findByOwnerId(int $gymOwnerId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM paymongo_config WHERE gym_owner_id = :oid LIMIT 1'
        );
        $stmt->execute([':oid' => $gymOwnerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $id, bool $isActive): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE paymongo_config SET is_active = :active, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':active' => $isActive ? 1 : 0, ':id' => $id]);
    }

    /**
     * Delete configuration
     */
    public function delete(int $id): void
    {
        $stmt = $this->db()->prepare('DELETE FROM paymongo_config WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /**
     * Check if table exists
     */
    public function tableExists(): bool
    {
        try {
            $this->db()->query('SELECT 1 FROM paymongo_config LIMIT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Get masked secret key for display (shows only last 4 characters)
     */
    public static function maskSecretKey(string $secretKey): string
    {
        if (strlen($secretKey) <= 4) {
            return str_repeat('*', strlen($secretKey));
        }
        return str_repeat('*', strlen($secretKey) - 4) . substr($secretKey, -4);
    }
}
