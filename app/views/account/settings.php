<?php
declare(strict_types=1);
$pageTitle = 'Account & Profile Settings';
require __DIR__ . '/../partials/header.php';

// Fallback helper for initial initials avatar
$fullname = $user['fullname'] ?? 'User';
$parts = explode(' ', trim($fullname));
$initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[count($parts)-1] ?? '', 0, 1));
if (strlen($initials) === 1) {
    $initials = strtoupper(substr($fullname, 0, 2));
}
$avatarUrl = !empty($user['profile_picture_url']) ? 'public/' . ltrim($user['profile_picture_url'], '/') : null;
?>

<style>
.account-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid var(--nf-border);
    box-shadow: 0 4px 20px rgba(27,107,42,.06);
    overflow: hidden;
}
.account-header {
    background: linear-gradient(135deg, #0e1c12 0%, #164a20 60%, #1B6B2A 100%);
    padding: 2.2rem 2rem;
    color: #fff;
    position: relative;
}
.avatar-preview-lg {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #ffffff;
    box-shadow: 0 8px 24px rgba(0,0,0,.25);
    background: linear-gradient(135deg, #1B6B2A 0%, #2E8B3E 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 800;
    color: #ffffff;
}
.upload-btn-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.btn-photo-action {
    border-radius: 10px;
    padding: 0.6rem 1.2rem;
    font-size: 0.9rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}
.crop-preview-box {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto;
    border: 3px solid #1B6B2A;
    box-shadow: 0 4px 14px rgba(27,107,42,.2);
}
.webcam-video-box {
    width: 100%;
    max-width: 440px;
    height: 330px;
    background: #000000;
    border-radius: 12px;
    object-fit: cover;
    margin: 0 auto;
    display: block;
    box-shadow: 0 4px 18px rgba(0,0,0,0.3);
}
</style>

<div class="container-fluid max-w-1000 py-3">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-person-gear text-success me-2"></i>Account & Profile Settings</h1>
            <p class="text-muted mb-0">Manage your profile picture, personal information, and platform credentials.</p>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 mb-4 shadow-sm">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mb-4 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Left Column: Profile Picture Card -->
        <div class="col-lg-5">
            <div class="account-card">
                <div class="account-header text-center">
                    <div class="d-flex justify-content-center mb-3">
                        <?php if ($avatarUrl): ?>
                            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="avatar-preview-lg" id="currentAvatarImg">
                        <?php else: ?>
                            <div class="avatar-preview-lg" id="currentAvatarFallback"><?= htmlspecialchars($initials) ?></div>
                        <?php endif; ?>
                    </div>
                    <h2 class="h5 fw-bold mb-1 text-white"><?= htmlspecialchars($fullname) ?></h2>
                    <p class="small text-white-50 mb-0"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                    <span class="badge bg-success border border-white mt-2 px-3 py-1"><?= ucfirst(str_replace('_',' ',$user['role'] ?? 'user')) ?></span>
                </div>

                <div class="p-4">
                    <h3 class="h6 fw-bold mb-3"><i class="bi bi-camera-fill text-success me-2"></i>Update Profile Picture</h3>
                    <p class="text-muted small mb-3">
                        Upload a new photo using your camera or pick a file from your device. Supported formats: JPG, PNG, WEBP (Max 5MB).
                    </p>

                    <form id="avatarUploadForm" action="index.php?r=account/uploadpicture" method="POST" enctype="multipart/form-data">

                        <!-- Hidden File Inputs -->
                        <!-- 1. Main File Input for form submission -->
                        <input type="file" id="mainFileInput" name="profile_picture" accept="image/*" style="display:none;" onchange="handleFileSelect(this)">
                        
                        <!-- 2. Mobile Native Camera Capture Input -->
                        <input type="file" id="cameraNativeInput" accept="image/*" capture="user" style="display:none;" onchange="handleFileSelect(this)">

                        <div class="upload-btn-group mb-3">
                            <button type="button" class="btn btn-outline-success btn-photo-action" onclick="openLiveCamera()">
                                <i class="bi bi-camera-video-fill"></i> Take Photo (Camera)
                            </button>
                            <button type="button" class="btn btn-success btn-photo-action text-white" onclick="document.getElementById('mainFileInput').click()">
                                <i class="bi bi-upload"></i> Choose File
                            </button>
                        </div>

                        <!-- Preview & Submit Container -->
                        <div id="previewContainer" style="display:none;" class="p-3 border rounded-3 bg-light text-center mb-3">
                            <div class="text-muted small fw-semibold mb-2">Crop Preview</div>
                            <div class="crop-preview-box mb-2">
                                <img id="croppedPreviewImg" src="" style="width:100%;height:100%;object-fit:cover;">
                            </div>
                            <div class="d-flex justify-content-center gap-2">
                                <button type="submit" class="btn btn-success btn-sm px-4 rounded-pill fw-bold">
                                    <i class="bi bi-check-lg me-1"></i> Save Picture
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-pill" onclick="cancelPreview()">
                                    Cancel
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Personal Information -->
        <div class="col-lg-7">
            <div class="account-card p-4">
                <h3 class="h6 fw-bold mb-4 pb-2 border-bottom"><i class="bi bi-person-text text-success me-2"></i>Personal Details</h3>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label text-muted small fw-semibold mb-1">Full Name</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" readonly>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label text-muted small fw-semibold mb-1">Email Address</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label text-muted small fw-semibold mb-1">Role</label>
                        <input type="text" class="form-control bg-light" value="<?= ucfirst(str_replace('_',' ',$user['role'] ?? '')) ?>" readonly>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label text-muted small fw-semibold mb-1">Height (cm)</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars((string)($user['height_cm'] ?? 'N/A')) ?>" readonly>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label text-muted small fw-semibold mb-1">Weight (kg)</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars((string)($user['weight_kg'] ?? 'N/A')) ?>" readonly>
                    </div>
                    <div class="col-12 mt-4">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex align-items-center gap-2 text-success fw-bold small mb-1">
                                <i class="bi bi-shield-check"></i> Account Verification
                            </div>
                            <div class="small text-muted">
                                Status: <?= !empty($user['is_verified']) ? '<span class="text-success fw-semibold">Verified Member ✓</span>' : '<span class="text-warning fw-semibold">Pending Verification</span>' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Documents & Certifications Section -->
            <?php
            $showDocs = false;
            $appPosition = '';
            
            if (in_array($user['role'], ['trainer', 'maintenance', 'fitness_trainer', 'maintenance_officer'], true)) {
                $showDocs = true;
                $appPosition = in_array($user['role'], ['trainer', 'fitness_trainer'], true) ? 'trainer' : 'maintenance';
            } elseif ($staffApp && in_array($staffApp['status'], ['pending', 'approved'], true)) {
                $showDocs = true;
                $appPosition = $staffApp['application_type'];
            }
            ?>
            
            <?php if ($showDocs): ?>
                <div class="account-card p-4 mt-4">
                    <h3 class="h6 fw-bold mb-4 pb-2 border-bottom"><i class="bi bi-file-earmark-check text-success me-2"></i>Documents & Certifications</h3>
                    <p class="text-muted small">Manage your professional credentials here. These will be visible to Gym Owners for applications and verification.</p>
                    
                    <div class="row g-4">
                        <?php if ($appPosition === 'trainer'): ?>
                            <!-- Trainer: certifications, specialization, resume/CV -->
                            <div class="col-md-6">
                                <div class="card p-3 h-100 border-success">
                                    <h4 class="h6 fw-bold text-success mb-2"><i class="bi bi-award me-1"></i>Specialization & Certification</h4>
                                    <form action="index.php?r=account/uploaddocument" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="doc_type" value="certification">
                                        
                                        <div class="mb-3">
                                            <label class="form-label small mb-1">Specialization (e.g. Strength & Conditioning, Yoga, Weight Loss)</label>
                                            <input type="text" class="form-control form-control-sm" name="specialization" 
                                                   value="<?= htmlspecialchars($userDocs['certification']['specialization'] ?? $userDocs['resume']['specialization'] ?? '') ?>" placeholder="Enter specialization...">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label small mb-1">Upload Certification Document</label>
                                            <input class="form-control form-control-sm" type="file" name="document_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                        </div>
                                        
                                        <?php if (!empty($userDocs['certification']['doc_path'])): ?>
                                            <div class="mb-3">
                                                <a href="public/<?= htmlspecialchars($userDocs['certification']['doc_path']) ?>" target="_blank" class="btn btn-outline-info btn-sm w-100">
                                                    <i class="bi bi-file-earmark-pdf me-1"></i>View Current Certification
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-sm btn-success w-100" type="submit">Update Certification</button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Maintenance: relevant certificates -->
                            <div class="col-md-6">
                                <div class="card p-3 h-100 border-success">
                                    <h4 class="h6 fw-bold text-success mb-2"><i class="bi bi-file-earmark-medical me-1"></i>Relevant Certificate / Medical Certificate</h4>
                                    <form action="index.php?r=account/uploaddocument" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="doc_type" value="medical_certificate">
                                        
                                        <div class="mb-3">
                                            <label class="form-label small mb-1">Select Certificate File</label>
                                            <input class="form-control form-control-sm" type="file" name="document_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                        </div>
                                        
                                        <?php if (!empty($userDocs['medical_certificate']['doc_path'])): ?>
                                            <div class="mb-3">
                                                <a href="public/<?= htmlspecialchars($userDocs['medical_certificate']['doc_path']) ?>" target="_blank" class="btn btn-outline-info btn-sm w-100">
                                                    <i class="bi bi-file-earmark-pdf me-1"></i>View Current Certificate
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-sm btn-success w-100" type="submit">Upload Certificate</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Common: resume/CV -->
                        <div class="col-md-6">
                            <div class="card p-3 h-100 border-success">
                                <h4 class="h6 fw-bold text-success mb-2"><i class="bi bi-file-earmark-person me-1"></i>Resume / CV</h4>
                                <form action="index.php?r=account/uploaddocument" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="doc_type" value="resume">
                                    
                                    <div class="mb-3">
                                        <label class="form-label small mb-1">Select Resume File</label>
                                        <input class="form-control form-control-sm" type="file" name="document_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                    </div>
                                    
                                    <?php if (!empty($userDocs['resume']['doc_path'])): ?>
                                        <div class="mb-3">
                                            <a href="public/<?= htmlspecialchars($userDocs['resume']['doc_path']) ?>" target="_blank" class="btn btn-outline-info btn-sm w-100">
                                                <i class="bi bi-file-earmark-text me-1"></i>View Current Resume
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-sm btn-success w-100" type="submit">Upload Resume</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<!-- ─── Live Camera Modal ─── -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold" id="cameraModalLabel"><i class="bi bi-camera-video-fill me-2 text-success"></i>Take Live Photo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="closeCameraStream()"></button>
            </div>
            <div class="modal-body bg-dark text-center p-4">
                <video id="webcamStream" class="webcam-video-box" autoplay playsinline></video>
                <canvas id="snapshotCanvas" style="display:none;"></canvas>
            </div>
            <div class="modal-footer bg-dark border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-success px-4 rounded-pill fw-bold" onclick="capturePhotoFromStream()">
                    <i class="bi bi-camera-fill me-1"></i> Snap Photo
                </button>
                <button type="button" class="btn btn-outline-light px-3 rounded-pill" data-bs-dismiss="modal" onclick="closeCameraStream()">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let mediaStream = null;

function openLiveCamera() {
    // If WebRTC is supported, open live camera modal
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480, facingMode: "user" } })
            .then(function(stream) {
                mediaStream = stream;
                const videoEl = document.getElementById('webcamStream');
                videoEl.srcObject = stream;
                const modal = new bootstrap.Modal(document.getElementById('cameraModal'));
                modal.show();
            })
            .catch(function(err) {
                console.warn('Webcam stream error or denied, falling back to native file capture:', err);
                document.getElementById('cameraNativeInput').click();
            });
    } else {
        // Fallback for mobile native camera picker
        document.getElementById('cameraNativeInput').click();
    }
}

function closeCameraStream() {
    if (mediaStream) {
        mediaStream.getTracks().forEach(track => track.stop());
        mediaStream = null;
    }
}

function capturePhotoFromStream() {
    const video = document.getElementById('webcamStream');
    const canvas = document.getElementById('snapshotCanvas');
    const context = canvas.getContext('2d');

    const width = video.videoWidth || 640;
    const height = video.videoHeight || 480;
    canvas.width = width;
    canvas.height = height;

    context.drawImage(video, 0, 0, width, height);

    canvas.toBlob(function(blob) {
        const file = new File([blob], "camera_photo.jpg", { type: "image/jpeg" });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        
        const mainInput = document.getElementById('mainFileInput');
        mainInput.files = dataTransfer.files;
        handleFileSelect(mainInput);

        closeCameraStream();
        const modalEl = document.getElementById('cameraModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }, 'image/jpeg', 0.9);
}

function handleFileSelect(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    // Ensure main form input gets the file
    if (input.id !== 'mainFileInput') {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        document.getElementById('mainFileInput').files = dataTransfer.files;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('croppedPreviewImg').src = e.target.result;
        document.getElementById('previewContainer').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function cancelPreview() {
    document.getElementById('mainFileInput').value = '';
    document.getElementById('cameraNativeInput').value = '';
    document.getElementById('previewContainer').style.display = 'none';
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
