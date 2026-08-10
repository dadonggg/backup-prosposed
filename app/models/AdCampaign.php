<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class AdCampaign extends Model
{
    /** Check if table exists */
    public function tableExists(): bool
    {
        try {
            $this->db()->query('SELECT 1 FROM ad_campaigns LIMIT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /** Create a new campaign */
    public function create(
        int $moId,
        int $gymId,
        string $title,
        string $description,
        ?string $imagePath,
        string $targetAudience,
        string $status,
        string $startDate,
        string $endDate
    ): int {
        $stmt = $this->db()->prepare(
            'INSERT INTO ad_campaigns (marketing_officer_id, gym_id, title, description, image_path, target_audience, status, start_date, end_date)
             VALUES (:mo_id, :gym_id, :title, :description, :image_path, :target, :status, :start, :end)'
        );
        $stmt->execute([
            ':mo_id'       => $moId,
            ':gym_id'      => $gymId,
            ':title'       => $title,
            ':description' => $description,
            ':image_path'  => $imagePath,
            ':target'      => $targetAudience,
            ':status'      => $status,
            ':start'       => $startDate,
            ':end'         => $endDate,
        ]);
        return (int)$this->db()->lastInsertId();
    }

    /** Update an existing campaign */
    public function update(
        int $id,
        string $title,
        string $description,
        ?string $imagePath,
        string $targetAudience,
        string $status,
        string $startDate,
        string $endDate
    ): void {
        $sql = 'UPDATE ad_campaigns SET title = :title, description = :description, target_audience = :target,
                       status = :status, start_date = :start, end_date = :end';
        $params = [
            ':title'       => $title,
            ':description' => $description,
            ':target'      => $targetAudience,
            ':status'      => $status,
            ':start'       => $startDate,
            ':end'         => $endDate,
            ':id'          => $id,
        ];
        
        if ($imagePath !== null) {
            $sql .= ', image_path = :image_path';
            $params[':image_path'] = $imagePath;
        }
        
        $sql .= ' WHERE id = :id';
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
    }

    /** Delete a campaign */
    public function delete(int $id): void
    {
        $stmt = $this->db()->prepare('DELETE FROM ad_campaigns WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /** Update campaign status */
    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db()->prepare('UPDATE ad_campaigns SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $id]);
    }

    /** Find campaign by ID with views count */
    public function findById(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT ac.*, 
                    (SELECT COUNT(*) FROM campaign_views cv WHERE cv.campaign_id = ac.id) AS views_count
             FROM ad_campaigns ac 
             WHERE ac.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Find campaigns by Marketing Officer with views count */
    public function findByMarketingOfficer(int $moId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT ac.*, 
                    (SELECT COUNT(*) FROM campaign_views cv WHERE cv.campaign_id = ac.id) AS views_count
             FROM ad_campaigns ac
             WHERE ac.marketing_officer_id = :mo_id
             ORDER BY ac.created_at DESC'
        );
        $stmt->execute([':mo_id' => $moId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Find active campaigns for a gym with view count */
    public function findActiveByGym(int $gymId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT ac.*, 
                    (SELECT COUNT(*) FROM campaign_views cv WHERE cv.campaign_id = ac.id) AS views_count
             FROM ad_campaigns ac
             WHERE ac.gym_id = :gym_id 
             AND ac.status IN ("active", "published")
             AND CURDATE() BETWEEN ac.start_date AND ac.end_date
             ORDER BY ac.created_at DESC'
        );
        $stmt->execute([':gym_id' => $gymId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Find any active campaign builder campaign (for admin officer use) */
    public function findActiveCampaignBuilder(): ?array
    {
        // Try with source column first (set by Campaign Builder)
        try {
            $stmt = $this->db()->prepare(
                'SELECT * FROM ad_campaigns
                 WHERE source = "campaign_builder"
                 AND status IN ("active", "published")
                 ORDER BY created_at DESC
                 LIMIT 1'
            );
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) return $row;
        } catch (\PDOException $e) {
            // source column may not exist, fall through
        }

        // Fallback: any active campaign with extra_json (pricing/registrations)
        try {
            $stmt = $this->db()->prepare(
                'SELECT * FROM ad_campaigns
                 WHERE status IN ("active", "published")
                 AND extra_json IS NOT NULL
                 ORDER BY created_at DESC
                 LIMIT 1'
            );
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    /** Count active campaigns for a gym */
    public function countActiveByGym(int $gymId): int
    {
        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) FROM ad_campaigns 
             WHERE gym_id = :gym_id 
             AND status IN ("active", "published")
             AND CURDATE() BETWEEN start_date AND end_date'
        );
        $stmt->execute([':gym_id' => $gymId]);
        return (int)$stmt->fetchColumn();
    }
}
