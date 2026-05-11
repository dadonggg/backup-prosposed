<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use PDO;

final class FinancialRecord extends Model
{
    /* ─── Investment (formerly "Budget") ─── */

    public function setInvestment(int $ownerId, float $amount): int
    {
        // Delete old investment records for this owner, then insert new
        $stmt = $this->db()->prepare('DELETE FROM financial_records WHERE gym_owner_id = :oid AND record_type IN ("budget","investment")');
        $stmt->execute([':oid' => $ownerId]);

        $stmt = $this->db()->prepare(
            'INSERT INTO financial_records (gym_owner_id, record_type, description, amount)
             VALUES (:oid, "investment", "Total Investment", :amt)'
        );
        $stmt->execute([':oid' => $ownerId, ':amt' => $amount]);
        return (int)$this->db()->lastInsertId();
    }

    public function getInvestment(int $ownerId): float
    {
        $stmt = $this->db()->prepare(
            'SELECT amount FROM financial_records WHERE gym_owner_id = :oid AND record_type IN ("budget","investment") LIMIT 1'
        );
        $stmt->execute([':oid' => $ownerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['amount'] : 0.0;
    }

    /* ─── Investment Usage (tracked under investment, NOT deducted from ops) ─── */

    public function addInvestmentUsage(int $ownerId, string $name, string $category, float $amount, string $notes = ''): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO financial_records (gym_owner_id, record_type, description, category, amount, notes)
             VALUES (:oid, "investment_usage", :desc, :cat, :amt, :notes)'
        );
        $stmt->execute([':oid' => $ownerId, ':desc' => $name, ':cat' => $category, ':amt' => $amount, ':notes' => $notes]);
        return (int)$this->db()->lastInsertId();
    }

    public function getInvestmentUsages(int $ownerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM financial_records WHERE gym_owner_id = :oid AND record_type = "investment_usage" ORDER BY created_at DESC'
        );
        $stmt->execute([':oid' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalInvestmentUsage(int $ownerId): float
    {
        $stmt = $this->db()->prepare(
            'SELECT COALESCE(SUM(amount),0) as total FROM financial_records WHERE gym_owner_id = :oid AND record_type = "investment_usage"'
        );
        $stmt->execute([':oid' => $ownerId]);
        return (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* ─── Operational Expenses (deducted from monthly profit only) ─── */

    public function addOperationalExpense(int $ownerId, string $name, string $category, float $amount, string $notes = ''): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO financial_records (gym_owner_id, record_type, description, category, amount, notes)
             VALUES (:oid, "operational_expense", :desc, :cat, :amt, :notes)'
        );
        $stmt->execute([':oid' => $ownerId, ':desc' => $name, ':cat' => $category, ':amt' => $amount, ':notes' => $notes]);
        return (int)$this->db()->lastInsertId();
    }

    public function getOperationalExpenses(int $ownerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM financial_records WHERE gym_owner_id = :oid AND record_type = "operational_expense" ORDER BY created_at DESC'
        );
        $stmt->execute([':oid' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalOperationalExpenses(int $ownerId): float
    {
        $stmt = $this->db()->prepare(
            'SELECT COALESCE(SUM(amount),0) as total FROM financial_records WHERE gym_owner_id = :oid AND record_type = "operational_expense"'
        );
        $stmt->execute([':oid' => $ownerId]);
        return (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* ─── Revenue ─── */

    public function addRevenue(int $ownerId, string $name, float $amount, string $notes = '', string $category = 'Others'): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO financial_records (gym_owner_id, record_type, description, category, amount, notes)
             VALUES (:oid, "revenue", :desc, :cat, :amt, :notes)'
        );
        $stmt->execute([':oid' => $ownerId, ':desc' => $name, ':cat' => $category, ':amt' => $amount, ':notes' => $notes]);
        return (int)$this->db()->lastInsertId();
    }

    public function getRevenues(int $ownerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM financial_records WHERE gym_owner_id = :oid AND record_type = "revenue" ORDER BY created_at DESC'
        );
        $stmt->execute([':oid' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalRevenue(int $ownerId): float
    {
        $stmt = $this->db()->prepare(
            'SELECT COALESCE(SUM(amount),0) as total FROM financial_records WHERE gym_owner_id = :oid AND record_type = "revenue"'
        );
        $stmt->execute([':oid' => $ownerId]);
        return (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Get revenue breakdown by category
     * Returns array with keys: Membership Revenue, Trainer Sessions, Others
     */
    public function getRevenueBreakdown(int $ownerId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT category, COALESCE(SUM(amount),0) as total 
             FROM financial_records 
             WHERE gym_owner_id = :oid AND record_type = "revenue"
             GROUP BY category'
        );
        $stmt->execute([':oid' => $ownerId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $breakdown = [
            'Membership Revenue' => 0.0,
            'Trainer Sessions' => 0.0,
            'Others' => 0.0,
        ];
        
        foreach ($results as $row) {
            $category = $row['category'] ?? 'Others';
            if (isset($breakdown[$category])) {
                $breakdown[$category] = (float)$row['total'];
            } else {
                $breakdown['Others'] += (float)$row['total'];
            }
        }
        
        return $breakdown;
    }

    /* ─── Monthly Profit = Revenue - Operational Expenses ─── */

    public function getMonthlyProfit(int $ownerId): float
    {
        $revenue = $this->getTotalRevenue($ownerId);
        $opex = $this->getTotalOperationalExpenses($ownerId);
        return $revenue - $opex;
    }

    /* ─── Legacy support: old getExpenses / getBudget map to new methods ─── */

    /** @deprecated Use getInvestment() */
    public function setBudget(int $ownerId, float $amount): int
    {
        return $this->setInvestment($ownerId, $amount);
    }

    /** @deprecated Use getInvestment() */
    public function getBudget(int $ownerId): float
    {
        return $this->getInvestment($ownerId);
    }

    /** @deprecated Use addOperationalExpense() */
    public function addExpense(int $ownerId, string $name, string $category, float $amount, string $notes = ''): int
    {
        return $this->addOperationalExpense($ownerId, $name, $category, $amount, $notes);
    }

    /** @deprecated Use getOperationalExpenses() */
    public function getExpenses(int $ownerId): array
    {
        return $this->getOperationalExpenses($ownerId);
    }

    /** @deprecated Use getTotalOperationalExpenses() */
    public function getTotalExpenses(int $ownerId): float
    {
        return $this->getTotalOperationalExpenses($ownerId);
    }
}
