<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class MembershipPlan extends Model
{
    public function create(int $ownerId, string $name, string $description, float $price, int $durationDays): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO membership_plans (gym_owner_id, name, description, price, duration_days)
             VALUES (:oid, :name, :desc, :price, :dur)'
        );
        $stmt->execute([':oid'=>$ownerId, ':name'=>$name, ':desc'=>$description, ':price'=>$price, ':dur'=>$durationDays]);
        return (int)$this->db()->lastInsertId();
    }

    public function update(int $id, string $name, string $description, float $price, int $durationDays): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE membership_plans SET name=:name, description=:desc, price=:price, duration_days=:dur WHERE id=:id'
        );
        $stmt->execute([':name'=>$name, ':desc'=>$description, ':price'=>$price, ':dur'=>$durationDays, ':id'=>$id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db()->prepare('DELETE FROM membership_plans WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM membership_plans WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByOwnerId(int $ownerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM membership_plans WHERE gym_owner_id = :oid AND is_active = 1 ORDER BY price ASC'
        );
        $stmt->execute([':oid' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllActive(): array
    {
        return $this->db()->query(
            'SELECT mp.*, u.fullname as owner_name FROM membership_plans mp
             JOIN users u ON u.id = mp.gym_owner_id WHERE mp.is_active = 1 ORDER BY mp.price ASC'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tableExists(): bool
    {
        try { $this->db()->query('SELECT 1 FROM membership_plans LIMIT 1'); return true; }
        catch (\PDOException $e) { return false; }
    }
}
