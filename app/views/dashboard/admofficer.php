<?php
declare(strict_types=1);
$pageTitle = 'Administrative Officer Dashboard';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Administrative Officer Dashboard</h1>
    <p class="text-muted mb-0">Verify membership registrations, assign trainers, and manage gym members.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-25 text-warning"><i class="bi bi-person-plus"></i></div>
                <div><div class="text-muted small">Pending Applications</div><div class="fw-bold"><?= $pendingCount ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-25 text-success"><i class="bi bi-people-fill"></i></div>
                <div><div class="text-muted small">Total Gym Members</div><div class="fw-bold"><?= count($gymMembers) ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-25 text-info"><i class="bi bi-person-badge"></i></div>
                <div><div class="text-muted small">Total Employees</div><div class="fw-bold"><?= count($employees) ?></div></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-person-plus me-2"></i>Membership Applications</h2></div>
            <div class="card-body">
                <p class="small text-muted">Review membership forms from fitness enthusiasts. Verify, assign trainers, confirm payment, and generate membership codes.</p>
                <a href="index.php?r=admofficer/memberships" class="btn btn-primary btn-sm"><i class="bi bi-arrow-right"></i> Review Applications</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-person-badge me-2"></i>Employee List</h2></div>
            <div class="card-body">
                <p class="small text-muted">View all hired employees (trainers and maintenance) and their availability for assignment.</p>
                <a href="index.php?r=admofficer/employees" class="btn btn-info btn-sm"><i class="bi bi-arrow-right"></i> View Employees</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-people-fill me-2"></i>Gym Members</h2></div>
            <div class="card-body">
                <p class="small text-muted">View all verified gym members and their membership codes.</p>
                <a href="index.php?r=admofficer/members" class="btn btn-success btn-sm"><i class="bi bi-arrow-right"></i> View Members</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-calendar-check me-2"></i>Attendance Log</h2></div>
            <div class="card-body">
                <p class="small text-muted">Monitor member check-ins and attendance records.</p>
                <a href="index.php?r=admofficer/attendance" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-right"></i> View Log</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
