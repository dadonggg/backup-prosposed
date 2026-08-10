<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class CampaignView extends Model
{
    /** Check if table exists */
    public function tableExists(): bool
    {
        try {
            $this->db()->query('SELECT 1 FROM campaign_views LIMIT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /** Log a campaign view for a member */
    public function log(int $campaignId, int $memberId): void
    {
        // Avoid duplicate logging on simple page refreshes
        $stmt = $this->db()->prepare(
            'SELECT id FROM campaign_views WHERE campaign_id = :cid AND member_id = :mid LIMIT 1'
        );
        $stmt->execute([':cid' => $campaignId, ':mid' => $memberId]);
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return; // Already logged
        }
        
        $stmt = $this->db()->prepare(
            'INSERT INTO campaign_views (campaign_id, member_id) VALUES (:cid, :mid)'
        );
        $stmt->execute([':cid' => $campaignId, ':mid' => $memberId]);
    }
}
