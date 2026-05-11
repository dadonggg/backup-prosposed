<?php
declare(strict_types=1);
$pageTitle = 'Financial Dashboard';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-wallet2 me-2"></i>Financial Dashboard</h1>
    <p class="text-muted">Manage investment, track operational expenses, revenue &amp; monthly profit.</p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- ─── Stat Cards ─── -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-25 text-success"><i class="bi bi-cash-coin"></i></div>
                <div><div class="text-muted small">Investment</div><div class="fw-bold">₱<?= number_format($investment, 2) ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-25 text-warning"><i class="bi bi-tools"></i></div>
                <div><div class="text-muted small">Investment Used</div><div class="fw-bold">₱<?= number_format($totalInvUsage, 2) ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon <?= $investmentRemaining > 0 ? 'bg-primary bg-opacity-25 text-primary' : 'bg-danger bg-opacity-25 text-danger' ?>"><i class="bi bi-wallet2"></i></div>
                <div><div class="text-muted small">Remaining Investment</div><div class="fw-bold <?= $investmentRemaining <= 0 ? 'text-danger' : '' ?>">₱<?= number_format($investmentRemaining, 2) ?></div><div class="text-muted" style="font-size:.7rem">Investment − Used</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-25 text-info"><i class="bi bi-graph-up-arrow"></i></div>
                <div><div class="text-muted small">Total Revenue</div><div class="fw-bold">₱<?= number_format($totalRevenue, 2) ?></div></div>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon <?= $monthlyProfit >= 0 ? 'bg-success bg-opacity-25 text-success' : 'bg-danger bg-opacity-25 text-danger' ?>">
                    <i class="bi bi-piggy-bank"></i>
                </div>
                <div>
                    <div class="text-muted small">Monthly Profit</div>
                    <div class="fw-bold <?= $monthlyProfit < 0 ? 'text-danger' : '' ?>">₱<?= number_format($monthlyProfit, 2) ?></div>
                    <div class="text-muted" style="font-size:.7rem">Revenue − Op. Expenses</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($investmentRemaining <= 0 && $investment > 0): ?>
<div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle me-1"></i> Investment fully used! No remaining investment balance. Add more investment or reduce usage.</div>
<?php elseif ($investmentRemaining > 0 && $investmentRemaining < $investment * 0.2): ?>
<div class="alert alert-warning mb-4"><i class="bi bi-exclamation-triangle me-1"></i> Less than 20% of investment remaining (₱<?= number_format($investmentRemaining, 2) ?>).</div>
<?php endif; ?>

<!-- ─── Investment & Investment Usage ─── -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-cash-coin me-1"></i>Set Investment</h2></div>
            <div class="card-body">
                <form method="post" class="vstack gap-3">
                    <input type="hidden" name="action" value="set_investment">
                    <div>
                        <label class="form-label" for="investment_amount">Investment Amount (₱)</label>
                        <input class="form-control" id="investment_amount" type="number" name="investment_amount" step="0.01" min="0" value="<?= $investment ?>" required>
                    </div>
                    <button class="btn btn-primary btn-sm" type="submit">Set Investment</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-tools me-1"></i>Record Investment Usage</h2></div>
            <div class="card-body">
                <form method="post" class="vstack gap-3">
                    <input type="hidden" name="action" value="add_investment_usage">
                    <div><label class="form-label">Description</label><input class="form-control" name="inv_name" required></div>
                    <div>
                        <label class="form-label">Category</label>
                        <select class="form-select" name="inv_category">
                            <option value="Gym Beautification">Gym Beautification</option>
                            <option value="Equipment Upgrades">Equipment Upgrades</option>
                            <option value="Documents and Permits">Documents and Permits</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div><label class="form-label">Amount (₱)</label><input class="form-control" type="number" name="inv_amount" step="0.01" min="0" required></div>
                    <div><label class="form-label">Notes</label><textarea class="form-control" name="inv_notes" rows="2"></textarea></div>
                    <button class="btn btn-primary btn-sm" type="submit">Record Usage</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ─── Operational Expenses & Revenue ─── -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-receipt me-1"></i>Add Operational Expense</h2></div>
            <div class="card-body">
                <form method="post" class="vstack gap-3">
                    <input type="hidden" name="action" value="add_operational_expense">
                    <div><label class="form-label">Name</label><input class="form-control" name="expense_name" required></div>
                    <div><label class="form-label">Category</label><input class="form-control" name="expense_category" placeholder="e.g. Utilities, Rent, Salaries"></div>
                    <div><label class="form-label">Amount (₱)</label><input class="form-control" type="number" name="expense_amount" step="0.01" min="0" required></div>
                    <div><label class="form-label">Notes</label><textarea class="form-control" name="expense_notes" rows="2"></textarea></div>
                    <button class="btn btn-primary btn-sm" type="submit">Add Expense</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-graph-up me-1"></i>Other Revenue</h2></div>
            <div class="card-body">
                <form method="post" class="vstack gap-3">
                    <input type="hidden" name="action" value="add_revenue">
                    <div><label class="form-label">Description</label><input class="form-control" name="revenue_name" placeholder="e.g. Membership fees, Class fees" required></div>
                    <div><label class="form-label">Amount (₱)</label><input class="form-control" type="number" name="revenue_amount" step="0.01" min="0" required></div>
                    <div><label class="form-label">Notes</label><textarea class="form-control" name="revenue_notes" rows="2"></textarea></div>
                    <button class="btn btn-primary btn-sm" type="submit">Record Other Revenue</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ─── Investment Usage History ─── -->
<?php if (!empty($investmentUsages)): ?>
<div class="card mb-4">
    <div class="card-header px-3 py-2">
        <h2 class="h6 mb-0"><i class="bi bi-clock-history me-1"></i>Investment Usage History</h2>
        <div class="small text-muted mt-1">Remaining Investment: <strong class="<?= $investmentRemaining < 0 ? 'text-danger' : '' ?>">₱<?= number_format($investmentRemaining, 2) ?></strong></div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Description</th><th>Category</th><th>Amount</th><th>Date</th></tr></thead>
            <tbody>
                <?php foreach ($investmentUsages as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['description']) ?></td>
                    <td>
                        <?php if ((float)$e['amount'] < 0): ?>
                            <span class="badge bg-success"><?= htmlspecialchars($e['category'] ?? '') ?> (Refund)</span>
                        <?php else: ?>
                            <span class="badge bg-info"><?= htmlspecialchars($e['category'] ?? '') ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="<?= (float)$e['amount'] < 0 ? 'text-success' : '' ?>">
                        <?= (float)$e['amount'] < 0 ? '+' : '' ?>₱<?= number_format(abs((float)$e['amount']), 2) ?>
                    </td>
                    <td class="small"><?= htmlspecialchars($e['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ─── Operational Expenses History ─── -->
<?php if (!empty($operationalExpenses)): ?>
<div class="card mb-4">
    <div class="card-header px-3 py-2">
        <h2 class="h6 mb-0"><i class="bi bi-receipt-cutoff me-1"></i>Operational Expenses</h2>
        <div class="small text-muted mt-1">Total: <strong class="text-danger">₱<?= number_format($totalOpex, 2) ?></strong></div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Description</th><th>Category</th><th>Amount</th><th>Date</th></tr></thead>
            <tbody>
                <?php foreach ($operationalExpenses as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['description']) ?></td>
                    <td><?= htmlspecialchars($e['category'] ?? '') ?></td>
                    <td>₱<?= number_format((float)$e['amount'], 2) ?></td>
                    <td class="small"><?= htmlspecialchars($e['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ─── Revenue History ─── -->
<?php if (!empty($revenues)): ?>
<div class="card mb-4">
    <div class="card-header px-3 py-2">
        <h2 class="h6 mb-0"><i class="bi bi-graph-up-arrow me-1"></i>Revenue History</h2>
        <div class="small text-muted mt-1">Total: <strong class="text-success">₱<?= number_format($totalRevenue, 2) ?></strong></div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Description</th><th>Amount</th><th>Date</th></tr></thead>
            <tbody>
                <?php foreach ($revenues as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['description']) ?></td>
                    <td>₱<?= number_format((float)$e['amount'], 2) ?></td>
                    <td class="small"><?= htmlspecialchars($e['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
