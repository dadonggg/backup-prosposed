<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class GymService extends Model
{
    public function create(int $ownerId, string $name, string $description, float $memberPrice, float $nonMemberPrice): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO gym_services (gym_owner_id, name, description, member_price, non_member_price)
             VALUES (:oid, :name, :desc, :mp, :nmp)'
        );
        $stmt->execute([':oid'=>$ownerId, ':name'=>$name, ':desc'=>$description, ':mp'=>$memberPrice, ':nmp'=>$nonMemberPrice]);
        return (int)$this->db()->lastInsertId();
    }

    public function update(int $id, string $name, string $description, float $memberPrice, float $nonMemberPrice): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE gym_services SET name=:name, description=:desc, member_price=:mp, non_member_price=:nmp WHERE id=:id'
        );
        $stmt->execute([':name'=>$name, ':desc'=>$description, ':mp'=>$memberPrice, ':nmp'=>$nonMemberPrice, ':id'=>$id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db()->prepare('DELETE FROM gym_services WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM gym_services WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByOwnerId(int $ownerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM gym_services WHERE gym_owner_id = :oid AND is_active = 1 ORDER BY name'
        );
        $stmt->execute([':oid' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllActive(): array
    {
        return $this->db()->query(
            'SELECT gs.*, u.fullname as owner_name FROM gym_services gs
             JOIN users u ON u.id = gs.gym_owner_id WHERE gs.is_active = 1 ORDER BY gs.name'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tableExists(): bool
    {
        try { $this->db()->query('SELECT 1 FROM gym_services LIMIT 1'); return true; }
        catch (\PDOException $e) { return false; }
    }
}
