<?php
declare(strict_types=1);
$pageTitle = 'Trainer Profile';
require __DIR__ . '/../partials/header.php';

$avatar = !empty($trainer['profile_picture_url']) ? 'public/' . ltrim($trainer['profile_picture_url'], '/') : null;
$nameParts = explode(' ', trim($trainer['fullname'] ?? 'Trainer'));
$initials = strtoupper(substr($nameParts[0] ?? 'T', 0, 1) . substr($nameParts[count($nameParts)-1] ?? '', 0, 1));
$ratingVal = floatval($trainer['avg_rating'] ?? 0);
$reviewCount = intval($trainer['review_count'] ?? 0);
$expertiseTags = !empty($profile['expertise']) ? explode(',', $profile['expertise']) : [];
?>

<div class="mb-4">
    <a href="index.php?r=fitness/directory" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left"></i> Back to Directory</a>
    <h1 class="h3 mb-0">Coach Profile</h1>
</div>

<div class="row g-4">
    <!-- Left Column: Bio & Info -->
    <div class="col-lg-5">
        <div class="card text-center p-4 border-success mb-4">
            <div class="mb-3">
                <?php if ($avatar): ?>
                    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="rounded-circle shadow-sm" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid var(--nf-green);">
                <?php else: ?>
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold fs-2 mx-auto" style="width: 120px; height: 120px; border: 4px solid var(--nf-green);">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <h2 class="h4 fw-bold mb-1"><?= htmlspecialchars($trainer['fullname']) ?></h2>
            <p class="text-success small mb-2"><i class="bi bi-patch-check-fill me-1"></i>Hired Fitness Trainer</p>
            
            <div class="d-flex align-items-center justify-content-center gap-1 mb-3">
                <div class="text-warning">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi <?= $i <= round($ratingVal) ? 'bi-star-fill' : 'bi-star' ?>"></i>
                    <?php endfor; ?>
                </div>
                <span class="small fw-bold ms-1"><?= number_format($ratingVal, 1) ?></span>
                <span class="text-muted small">(<?= $reviewCount ?> reviews)</span>
            </div>
            
            <div class="text-start border-top pt-3 mt-2">
                <h3 class="h6 fw-bold mb-2">Specializations</h3>
                <div>
                    <?php if (empty($expertiseTags)): ?>
                        <span class="badge bg-light text-muted border">General Fitness</span>
                    <?php else: ?>
                        <?php foreach ($expertiseTags as $tag): ?>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 mb-1 me-1">
                                <?= htmlspecialchars(trim($tag)) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card p-4 border-success mb-4">
            <h3 class="h6 fw-bold mb-3"><i class="bi bi-journal-text me-2 text-success"></i>About Coach</h3>
            <p class="small text-muted mb-0"><?= nl2br(htmlspecialchars($profile['bio'] ?? 'This trainer has not added a biography yet.')) ?></p>
        </div>

        <div class="card p-4 border-success">
            <h3 class="h6 fw-bold mb-3"><i class="bi bi-award me-2 text-success"></i>Certifications</h3>
            <p class="small text-muted mb-0"><?= nl2br(htmlspecialchars($profile['certifications'] ?? 'No certifications listed.')) ?></p>
        </div>
    </div>

    <!-- Right Column: Availability & Booking Calendar -->
    <div class="col-lg-7">
        <div class="card p-4 border-success mb-4">
            <h3 class="h6 fw-bold mb-3"><i class="bi bi-calendar3 me-2 text-success"></i>Availability & Booking</h3>
            <p class="text-muted small mb-3">Select one of the open time slots below to submit a direct coaching request.</p>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success small mb-3"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger small mb-3"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (empty($schedules)): ?>
                <div class="alert alert-light text-center py-4 border">
                    <i class="bi bi-calendar-x text-muted display-6 mb-2"></i>
                    <p class="small text-muted mb-0">No available booking slots listed by this trainer at the moment.</p>
                </div>
            <?php else: ?>
                <form action="index.php?r=fitness/booktrainer" method="POST" class="vstack gap-3">
                    <input type="hidden" name="trainer_id" value="<?= (int)$trainer['employee_id'] ?>">
                    
                    <div>
                        <label class="form-label small fw-bold">Select Date & Time Slot <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <?php foreach ($schedules as $s): ?>
                                <?php
                                $isBooked = ($s['status'] === 'booked');
                                $formattedDate = date('M d, Y', strtotime($s['session_date']));
                                $timeSlot = $s['session_time'];
                                ?>
                                <div class="col-sm-6">
                                    <input type="radio" class="btn-check" name="schedule_id" id="sched_<?= $s['id'] ?>" value="<?= $s['id'] ?>" <?= $isBooked ? 'disabled' : '' ?> required>
                                    <label class="btn btn-outline-success btn-sm w-100 text-start d-flex justify-content-between align-items-center py-2" for="sched_<?= $s['id'] ?>">
                                        <div>
                                            <i class="bi bi-calendar-event me-1"></i><?= $formattedDate ?><br>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($timeSlot) ?></small>
                                        </div>
                                        <?php if ($isBooked): ?>
                                            <span class="badge bg-secondary">Booked</span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-20 text-success">Open</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button class="btn btn-success mt-2" type="submit">
                        <i class="bi bi-send me-1"></i>Request Coaching
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Ratings & Reviews Section -->
        <div class="card p-4 border-success">
            <h3 class="h6 fw-bold mb-3"><i class="bi bi-chat-left-quote me-2 text-success"></i>Ratings & Reviews</h3>
            <?php if (empty($reviews)): ?>
                <p class="small text-muted mb-0">No reviews yet for this coach.</p>
            <?php else: ?>
                <div class="vstack gap-3">
                    <?php foreach ($reviews as $rev): ?>
                        <div class="border-bottom pb-3 mb-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="fw-bold small"><?= htmlspecialchars($rev['fullname'] ?? 'Enthusiast') ?></div>
                                <div class="text-warning small">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi <?= $i <= $rev['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="small text-muted mb-0">"<?= htmlspecialchars($rev['review_text'] ?? '') ?>"</p>
                            <small class="text-muted d-block mt-1" style="font-size:0.7rem;"><?= date('M d, Y g:i A', strtotime($rev['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
