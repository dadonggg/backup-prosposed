<?php
declare(strict_types=1);
$pageTitle = 'Ad Campaigns';
require __DIR__ . '/../partials/header.php';
?>

<style>
.text-purple { color: #7c3aed !important; }
.btn-purple { background-color: #7c3aed !important; border-color: #7c3aed !important; color: #fff !important; }
.btn-purple:hover { background-color: #6d28d9 !important; border-color: #6d28d9 !important; color: #fff !important; }
.btn-outline-purple { border-color: #7c3aed !important; color: #7c3aed !important; }
.btn-outline-purple:hover { background-color: #7c3aed !important; color: #fff !important; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-megaphone me-2 text-purple"></i>Ad Campaigns</h1>
        <p class="text-muted mb-0">Manage your gym's advertising campaigns and track their performance.</p>
    </div>
    <a href="index.php?r=marketing/createcampaign" class="btn btn-purple">
        <i class="bi bi-plus-circle me-1"></i> Create Campaign
    </a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Target Audience</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th class="text-center">Views</th>
                        <th class="text-center text-success"><i class="bi bi-hand-thumbs-up me-1"></i>Interested</th>
                        <th class="text-center text-secondary"><i class="bi bi-hand-thumbs-down me-1"></i>Not Interested</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($campaigns)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-5">No campaigns yet. Click <strong>Create Campaign</strong> to get started.</td></tr>
                    <?php else: ?>
                        <?php foreach ($campaigns as $c): ?>
                        <?php
                            $__st = $c['status'];
                            if ($__st === 'active') { $statusBadge = 'bg-success'; }
                            elseif ($__st === 'ended') { $statusBadge = 'bg-danger'; }
                            else { $statusBadge = 'bg-secondary'; }
                        ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($c['title']) ?></div>
                                <?php if (!empty($c['image_path'])): ?>
                                    <span class="badge bg-light text-muted"><i class="bi bi-image me-1"></i>Banner</span>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= ucfirst(str_replace('_', ' ', $c['target_audience'])) ?></td>
                            <td class="small"><?= htmlspecialchars($c['start_date']) ?></td>
                            <td class="small"><?= htmlspecialchars($c['end_date']) ?></td>
                            <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($c['status']) ?></span></td>
                            <td class="text-center fw-bold text-purple"><?= (int)$c['views_count'] ?></td>
                            <?php
                            $interestData = $campaignInterestCounts[(int)$c['id']] ?? ['interested_count' => 0, 'not_interested_count' => 0];
                            ?>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success fw-semibold px-2">
                                    <i class="bi bi-check-circle me-1"></i><?= (int)$interestData['interested_count'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary fw-semibold px-2">
                                    <i class="bi bi-x-circle me-1"></i><?= (int)$interestData['not_interested_count'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1 flex-wrap">
                                    <a href="index.php?r=marketing/editcampaign&id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                    
                                    <?php if ($c['status'] === 'draft'): ?>
                                        <form method="POST" action="index.php?r=marketing/campaigns" class="d-inline" onsubmit="return confirm('Activate this campaign and notify all gym members?')">
                                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                            <input type="hidden" name="action" value="activate">
                                            <button type="submit" class="btn btn-sm btn-success" title="Activate"><i class="bi bi-play-fill"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($c['status'] === 'active'): ?>
                                        <form method="POST" action="index.php?r=marketing/campaigns" class="d-inline" onsubmit="return confirm('End this campaign?')">
                                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                            <input type="hidden" name="action" value="end">
                                            <button type="submit" class="btn btn-sm btn-warning" title="End Campaign"><i class="bi bi-stop-fill"></i></button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" action="index.php?r=marketing/campaigns" class="d-inline" onsubmit="return confirm('Delete this campaign permanently?')">
                                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
