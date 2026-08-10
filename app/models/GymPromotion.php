<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class GymPromotion extends Model
{
    /** Check if table exists */
    public function tableExists(): bool
    {
        try {
            $this->db()->query('SELECT 1 FROM gym_promotions LIMIT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /** Create a new promotion */
    public function create(
        int $moId,
        int $gymId,
        string $title,
        string $description,
        string $discountType,
        float $discountValue,
        string $promoCode,
        ?string $imagePath,
        string $validFrom,
        string $validUntil,
        string $status
    ): int {
        $stmt = $this->db()->prepare(
            'INSERT INTO gym_promotions (marketing_officer_id, gym_id, title, description, discount_type, discount_value, promo_code, image_path, valid_from, valid_until, status)
             VALUES (:mo_id, :gym_id, :title, :description, :disc_type, :disc_val, :code, :image_path, :from, :until, :status)'
        );
        $stmt->execute([
            ':mo_id'       => $moId,
            ':gym_id'      => $gymId,
            ':title'       => $title,
            ':description' => $description,
            ':disc_type'   => $discountType,
            ':disc_val'    => $discountValue,
            ':code'        => $promoCode,
            ':image_path'  => $imagePath,
            ':from'        => $validFrom,
            ':until'       => $validUntil,
            ':status'      => $status,
        ]);
        return (int)$this->db()->lastInsertId();
    }

    /** Update an existing promotion */
    public function update(
        int $id,
        string $title,
        string $description,
        string $discountType,
        float $discountValue,
        string $promoCode,
        ?string $imagePath,
        string $validFrom,
        string $validUntil,
        string $status
    ): void {
        $sql = 'UPDATE gym_promotions SET title = :title, description = :description, discount_type = :disc_type,
                       discount_value = :disc_val, promo_code = :code, valid_from = :from, valid_until = :until,
                       status = :status';
        $params = [
            ':title'     => $title,
            ':description'=> $description,
            ':disc_type' => $discountType,
            ':disc_val'  => $discountValue,
            ':code'      => $promoCode,
            ':from'      => $validFrom,
            ':until'     => $validUntil,
            ':status'    => $status,
            ':id'        => $id,
        ];
        
        if ($imagePath !== null) {
            $sql .= ', image_path = :image_path';
            $params[':image_path'] = $imagePath;
        }
        
        $sql .= ' WHERE id = :id';
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
    }

    /** Delete a promotion */
    public function delete(int $id): void
    {
        $stmt = $this->db()->prepare('DELETE FROM gym_promotions WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /** Update status */
    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db()->prepare('UPDATE gym_promotions SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $id]);
    }

    /** Find by ID */
    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM gym_promotions WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Find by Marketing Officer */
    public function findByMarketingOfficer(int $moId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM gym_promotions WHERE marketing_officer_id = :mo_id ORDER BY created_at DESC'
        );
        $stmt->execute([':mo_id' => $moId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Find active promotions for a gym */
    public function findActiveByGym(int $gymId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM gym_promotions 
             WHERE gym_id = :gym_id AND status = "active" AND (valid_until >= CURDATE() OR valid_until IS NULL)
             ORDER BY created_at DESC'
        );
        $stmt->execute([':gym_id' => $gymId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Count active promotions for a gym */
    public function countActiveByGym(int $gymId): int
    {
        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) FROM gym_promotions 
             WHERE gym_id = :gym_id AND status = "active" AND (valid_until >= CURDATE() OR valid_until IS NULL)'
        );
        $stmt->execute([':gym_id' => $gymId]);
        return (int)$stmt->fetchColumn();
    }
}
