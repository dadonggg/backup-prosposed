<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class CampaignInterest extends Model
{
    /** Check if table exists */
    public function tableExists(): bool
    {
        try {
            $this->db()->query('SELECT 1 FROM campaign_interests LIMIT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /** Save or update a member's interest in a campaign */
    public function saveResponse(int $campaignId, int $memberId, string $status): bool
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO campaign_interests (campaign_id, member_id, interest_status, responded_at)
             VALUES (:campaign_id, :member_id, :status, NOW())
             ON DUPLICATE KEY UPDATE interest_status = :status2, responded_at = NOW()'
        );
        return $stmt->execute([
            ':campaign_id' => $campaignId,
            ':member_id'   => $memberId,
            ':status'      => $status,
            ':status2'     => $status,
        ]);
    }

    /** Get a member's interest for a campaign */
    public function getResponse(int $campaignId, int $memberId): ?string
    {
        $stmt = $this->db()->prepare(
            'SELECT interest_status FROM campaign_interests
             WHERE campaign_id = :campaign_id AND member_id = :member_id LIMIT 1'
        );
        $stmt->execute([':campaign_id' => $campaignId, ':member_id' => $memberId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['interest_status'] : null;
    }

    /** Get all interest responses for a campaign */
    public function getCampaignInterestCounts(int $campaignId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT
                SUM(CASE WHEN interest_status = "interested" THEN 1 ELSE 0 END) AS interested_count,
                SUM(CASE WHEN interest_status = "not_interested" THEN 1 ELSE 0 END) AS not_interested_count
             FROM campaign_interests
             WHERE campaign_id = :campaign_id'
        );
        $stmt->execute([':campaign_id' => $campaignId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'interested_count'     => (int)($row['interested_count'] ?? 0),
            'not_interested_count' => (int)($row['not_interested_count'] ?? 0),
        ];
    }

    /** Get all interest responses for multiple campaigns */
    public function getBulkCampaignInterestCounts(array $campaignIds): array
    {
        if (empty($campaignIds)) return [];
        $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
        $stmt = $this->db()->prepare(
            "SELECT campaign_id,
                SUM(CASE WHEN interest_status = 'interested' THEN 1 ELSE 0 END) AS interested_count,
                SUM(CASE WHEN interest_status = 'not_interested' THEN 1 ELSE 0 END) AS not_interested_count
             FROM campaign_interests
             WHERE campaign_id IN ($placeholders)
             GROUP BY campaign_id"
        );
        $stmt->execute($campaignIds);
        $results = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[(int)$row['campaign_id']] = [
                'interested_count'     => (int)$row['interested_count'],
                'not_interested_count' => (int)$row['not_interested_count'],
            ];
        }
        return $results;
    }

    /** Get member responses for multiple campaigns */
    public function getMemberResponses(int $memberId, array $campaignIds): array
    {
        if (empty($campaignIds)) return [];
        $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
        $stmt = $this->db()->prepare(
            "SELECT campaign_id, interest_status
             FROM campaign_interests
             WHERE member_id = ? AND campaign_id IN ($placeholders)"
        );
        $stmt->execute(array_merge([$memberId], $campaignIds));
        $results = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[(int)$row['campaign_id']] = $row['interest_status'];
        }
        return $results;
    }
}
