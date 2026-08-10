<?php
declare(strict_types=1);
$pageTitle = 'Review Staff Application';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <a href="index.php?r=staff/applications" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left"></i> Back to Applications</a>
    <h1 class="h3 mb-1">Review Application #<?= $app['id'] ?></h1>
    <p class="text-muted">Applicant: <strong><?= htmlspecialchars($applicantUser['fullname'] ?? '') ?></strong> (<?= htmlspecialchars($applicantUser['email'] ?? '') ?>)</p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left: Applicant Profile & Uploaded Documents -->
    <div class="col-lg-8">
        <!-- Applicant profile details -->
        <div class="card mb-4">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-person-circle me-1"></i>Applicant Profile</h2></div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-sm-4 text-muted">Full Name</dt>
                    <dd class="col-sm-8 fw-semibold"><?= htmlspecialchars($applicantUser['fullname'] ?? 'N/A') ?></dd>
                    
                    <dt class="col-sm-4 text-muted">Email</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($applicantUser['email'] ?? 'N/A') ?></dd>

                    <dt class="col-sm-4 text-muted">Age / Birth Date</dt>
                    <dd class="col-sm-8"><?= isset($applicantUser['birth_date']) ? $applicantUser['birth_date'] . ' (' . \App\Models\User::computeAge($applicantUser['birth_date']) . ' y/o)' : 'N/A' ?></dd>

                    <dt class="col-sm-4 text-muted">Height / Weight</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars((string)($applicantUser['height_cm'] ?? 'N/A')) ?> cm / <?= htmlspecialchars((string)($applicantUser['weight_kg'] ?? 'N/A')) ?> kg</dd>
                </dl>
            </div>
        </div>

        <!-- Documents Pulled from User Profile -->
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-file-earmark-lock me-1"></i>Qualifications & Documents</h2></div>
            <div class="card-body">
                <?php if (empty($userDocs)): ?>
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i> The applicant has not uploaded any documents or certifications to their Profile & Settings yet.
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php if ($app['application_type'] === 'trainer'): ?>
                            <!-- Certifications & Specialization -->
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="fw-bold small mb-2"><i class="bi bi-award text-success me-1"></i>Trainer Certification</div>
                                    <?php if (isset($userDocs['certification'])): ?>
                                        <div class="small mb-2">
                                            <strong>Specialization:</strong> <?= htmlspecialchars($userDocs['certification']['specialization'] ?? 'General') ?>
                                        </div>
                                        <?php if (!empty($userDocs['certification']['doc_path'])): ?>
                                            <a href="public/<?= htmlspecialchars($userDocs['certification']['doc_path']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                                <i class="bi bi-file-earmark-pdf"></i> View Certificate
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">No document file attached.</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-danger small">No certification uploaded.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Maintenance Certificate -->
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="fw-bold small mb-2"><i class="bi bi-file-earmark-medical text-success me-1"></i>Maintenance/Medical Certificate</div>
                                    <?php if (isset($userDocs['medical_certificate'])): ?>
                                        <a href="public/<?= htmlspecialchars($userDocs['medical_certificate']['doc_path']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                            <i class="bi bi-file-earmark-pdf"></i> View Certificate
                                        </a>
                                    <?php else: ?>
                                        <span class="text-danger small">No certificate uploaded.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Resume -->
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="fw-bold small mb-2"><i class="bi bi-file-earmark-person text-success me-1"></i>Resume / CV</div>
                                <?php if (isset($userDocs['resume'])): ?>
                                    <a href="public/<?= htmlspecialchars($userDocs['resume']['doc_path']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-file-earmark-text"></i> View Resume
                                    </a>
                                <?php else: ?>
                                    <span class="text-danger small">No resume uploaded.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Review Action Panel -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-info-circle me-1"></i>Application Information</h2></div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-6 text-muted">Applied Role</dt>
                    <dd class="col-6"><span class="badge bg-success"><?= $app['application_type'] === 'trainer' ? 'Fitness Trainer' : 'Maintenance Officer' ?></span></dd>
                    
                    <dt class="col-6 text-muted">Submit Date</dt>
                    <dd class="col-6"><?= htmlspecialchars($app['created_at']) ?></dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-lightning-charge me-1"></i>Final Decision</h2></div>
            <div class="card-body">
                <?php if ($app['status'] === 'pending'): ?>
                    <form method="post" class="vstack gap-3">
                        <div>
                            <label class="form-label small" for="feedback">Reviewer Feedback</label>
                            <textarea class="form-control form-control-sm" id="feedback" name="feedback" rows="3" placeholder="Optional comments or feedback..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm w-100"><i class="bi bi-check-circle"></i> Approve & Hire</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm w-100"><i class="bi bi-x-circle"></i> Reject</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="text-center py-3">
                        <span class="badge <?= $app['status'] === 'approved' ? 'bg-success' : 'bg-danger' ?> fs-6 px-3 py-2 mb-2">
                            <?= ucfirst($app['status']) ?>
                        </span>
                        <p class="small text-muted mb-0">Decision made. Feedback: "<?= htmlspecialchars($app['feedback'] ?? 'None') ?>"</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
