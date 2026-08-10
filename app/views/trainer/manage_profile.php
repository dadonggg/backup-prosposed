<?php
declare(strict_types=1);
$pageTitle = 'Profile & Availability Management';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-person-workspace text-success me-2"></i>My Trainer Profile</h1>
    <p class="text-muted">Manage your public trainer profile information and configure your availability time slots.</p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column: Edit Bio, Expertise, Certifications -->
    <div class="col-lg-6">
        <div class="card p-4 border-success h-100">
            <h2 class="h5 fw-bold mb-3 pb-2 border-bottom"><i class="bi bi-pencil-square text-success me-2"></i>Edit Profile Details</h2>
            <form action="index.php?r=trainer/saveprofile" method="POST" class="vstack gap-3">
                <div>
                    <label class="form-label" for="bio">Biography / About Me</label>
                    <textarea class="form-control" id="bio" name="bio" rows="6" placeholder="Write a short summary about your coaching style and philosophy..." required><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                </div>
                
                <div>
                    <label class="form-label" for="expertise">Specializations / Expertises (comma separated)</label>
                    <input type="text" class="form-control" id="expertise" name="expertise" value="<?= htmlspecialchars($profile['expertise'] ?? '') ?>" placeholder="e.g. Strength Training, Zumba, Weight Loss, Diet planning">
                    <small class="text-muted form-text">Separate tags using commas.</small>
                </div>
                
                <div>
                    <label class="form-label" for="certifications">Certifications & Awards</label>
                    <textarea class="form-control" id="certifications" name="certifications" rows="4" placeholder="List your professional credentials (e.g. ISSA Certified, CPR/AED)..."><?= htmlspecialchars($profile['certifications'] ?? '') ?></textarea>
                </div>
                
                <button class="btn btn-success mt-2" type="submit">
                    <i class="bi bi-save me-1"></i>Save Profile
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Availability Schedule Slots Config -->
    <div class="col-lg-6">
        <div class="card p-4 border-success mb-4">
            <h2 class="h5 fw-bold mb-3 pb-2 border-bottom"><i class="bi bi-calendar-plus text-success me-2"></i>Add Availability Slot</h2>
            <form action="index.php?r=trainer/addavailability" method="POST" class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small" for="session_date">Date</label>
                    <input type="date" class="form-control form-control-sm" id="session_date" name="session_date" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small" for="session_time">Time Slot</label>
                    <select class="form-select form-select-sm" id="session_time" name="session_time" required>
                        <option value="">— Select Slot —</option>
                        <option value="06:00 AM - 07:00 AM">06:00 AM - 07:00 AM</option>
                        <option value="07:00 AM - 08:00 AM">07:00 AM - 08:00 AM</option>
                        <option value="08:00 AM - 09:00 AM">08:00 AM - 09:00 AM</option>
                        <option value="09:00 AM - 10:00 AM">09:00 AM - 10:00 AM</option>
                        <option value="10:00 AM - 11:00 AM">10:00 AM - 11:00 AM</option>
                        <option value="11:00 AM - 12:00 PM">11:00 AM - 12:00 PM</option>
                        <option value="01:00 PM - 02:00 PM">01:00 PM - 02:00 PM</option>
                        <option value="02:00 PM - 03:00 PM">02:00 PM - 03:00 PM</option>
                        <option value="03:00 PM - 04:00 PM">03:00 PM - 04:00 PM</option>
                        <option value="04:00 PM - 05:00 PM">04:00 PM - 05:00 PM</option>
                        <option value="05:00 PM - 06:00 PM">05:00 PM - 06:00 PM</option>
                        <option value="06:00 PM - 07:00 PM">06:00 PM - 07:00 PM</option>
                        <option value="07:00 PM - 08:00 PM">07:00 PM - 08:00 PM</option>
                    </select>
                </div>
                <div class="col-12">
                    <button class="btn btn-sm btn-success w-100" type="submit">
                        <i class="bi bi-plus-circle me-1"></i>Add Available Slot
                    </button>
                </div>
            </form>
        </div>

        <div class="card p-4 border-success">
            <h2 class="h5 fw-bold mb-3 pb-2 border-bottom"><i class="bi bi-calendar-range text-success me-2"></i>My Active Slots</h2>
            <?php if (empty($schedules)): ?>
                <p class="small text-muted mb-0">No active availability slots found. Use the form above to add slots.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time Slot</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $s): ?>
                                <tr>
                                    <td class="small fw-semibold"><?= date('M d, Y', strtotime($s['session_date'])) ?></td>
                                    <td class="small"><?= htmlspecialchars($s['session_time']) ?></td>
                                    <td>
                                        <span class="badge <?= $s['status'] === 'booked' ? 'bg-secondary' : 'bg-success' ?> py-1" style="font-size:0.65rem;">
                                            <?= ucfirst($s['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <form action="index.php?r=trainer/deleteslot" method="POST" onsubmit="return confirm('Remove this availability slot?')">
                                            <input type="hidden" name="slot_id" value="<?= $s['id'] ?>">
                                            <button class="btn btn-xs btn-outline-danger py-0 px-2" type="submit" style="font-size:0.75rem;">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
