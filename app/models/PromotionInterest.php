<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class PromotionInterest extends Model
{
    /** Check if table exists */
    public function tableExists(): bool
    {
        try {
            $this->db()->query('SELECT 1 FROM promotion_interests LIMIT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /** Save or update a member's interest in a promotion */
    public function saveResponse(int $promotionId, int $memberId, string $status): bool
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO promotion_interests (promotion_id, member_id, interest_status, responded_at)
             VALUES (:promotion_id, :member_id, :status, NOW())
             ON DUPLICATE KEY UPDATE interest_status = :status2, responded_at = NOW()'
        );
        return $stmt->execute([
            ':promotion_id' => $promotionId,
            ':member_id'    => $memberId,
            ':status'       => $status,
            ':status2'      => $status,
        ]);
    }

    /** Get a member's interest for a promotion */
    public function getResponse(int $promotionId, int $memberId): ?string
    {
        $stmt = $this->db()->prepare(
            'SELECT interest_status FROM promotion_interests
             WHERE promotion_id = :promotion_id AND member_id = :member_id LIMIT 1'
        );
        $stmt->execute([':promotion_id' => $promotionId, ':member_id' => $memberId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['interest_status'] : null;
    }

    /** Get interest counts for a promotion */
    public function getPromotionInterestCounts(int $promotionId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT
                SUM(CASE WHEN interest_status = "interested" THEN 1 ELSE 0 END) AS interested_count,
                SUM(CASE WHEN interest_status = "not_interested" THEN 1 ELSE 0 END) AS not_interested_count
             FROM promotion_interests
             WHERE promotion_id = :promotion_id'
        );
        $stmt->execute([':promotion_id' => $promotionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'interested_count'     => (int)($row['interested_count'] ?? 0),
            'not_interested_count' => (int)($row['not_interested_count'] ?? 0),
        ];
    }

    /** Get bulk interest counts for multiple promotions */
    public function getBulkPromotionInterestCounts(array $promotionIds): array
    {
        if (empty($promotionIds)) return [];
        $placeholders = implode(',', array_fill(0, count($promotionIds), '?'));
        $stmt = $this->db()->prepare(
            "SELECT promotion_id,
                SUM(CASE WHEN interest_status = 'interested' THEN 1 ELSE 0 END) AS interested_count,
                SUM(CASE WHEN interest_status = 'not_interested' THEN 1 ELSE 0 END) AS not_interested_count
             FROM promotion_interests
             WHERE promotion_id IN ($placeholders)
             GROUP BY promotion_id"
        );
        $stmt->execute($promotionIds);
        $results = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[(int)$row['promotion_id']] = [
                'interested_count'     => (int)$row['interested_count'],
                'not_interested_count' => (int)$row['not_interested_count'],
            ];
        }
        return $results;
    }

    /** Get member responses for multiple promotions */
    public function getMemberResponses(int $memberId, array $promotionIds): array
    {
        if (empty($promotionIds)) return [];
        $placeholders = implode(',', array_fill(0, count($promotionIds), '?'));
        $stmt = $this->db()->prepare(
            "SELECT promotion_id, interest_status
             FROM promotion_interests
             WHERE member_id = ? AND promotion_id IN ($placeholders)"
        );
        $stmt->execute(array_merge([$memberId], $promotionIds));
        $results = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[(int)$row['promotion_id']] = $row['interest_status'];
        }
        return $results;
    }
}
