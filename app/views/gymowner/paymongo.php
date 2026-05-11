<?php
declare(strict_types=1);
$pageTitle = 'PayMongo Configuration';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-credit-card-2-front me-2"></i>PayMongo Configuration</h1>
    <p class="text-muted">Configure your PayMongo API keys to accept online payments from members.</p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Instructions Card -->
<div class="card mb-4">
    <div class="card-header px-3 py-2 bg-info text-white">
        <h2 class="h6 mb-0"><i class="bi bi-info-circle me-1"></i>How to Get Your PayMongo API Keys</h2>
    </div>
    <div class="card-body">
        <ol class="mb-0">
            <li class="mb-2">
                <strong>Sign up for PayMongo:</strong> Go to 
                <a href="https://dashboard.paymongo.com/signup" target="_blank" rel="noopener">
                    https://dashboard.paymongo.com/signup <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </li>
            <li class="mb-2">
                <strong>Verify your account:</strong> Complete the verification process (business documents required)
            </li>
            <li class="mb-2">
                <strong>Get API Keys:</strong> Go to 
                <a href="https://dashboard.paymongo.com/developers/api-keys" target="_blank" rel="noopener">
                    Developers → API Keys <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </li>
            <li class="mb-2">
                <strong>Copy Keys:</strong>
                <ul class="mt-2">
                    <li><strong>Public Key:</strong> Starts with <code>pk_test_</code> (test) or <code>pk_live_</code> (live)</li>
                    <li><strong>Secret Key:</strong> Starts with <code>sk_test_</code> (test) or <code>sk_live_</code> (live)</li>
                </ul>
            </li>
            <li>
                <strong>Paste below:</strong> Enter your keys in the form below and click "Save Configuration"
            </li>
        </ol>
        <div class="alert alert-warning mt-3 mb-0">
            <i class="bi bi-shield-exclamation me-1"></i>
            <strong>Security Note:</strong> Use <strong>test keys</strong> (<code>pk_test_</code> / <code>sk_test_</code>) 
            for testing. Switch to <strong>live keys</strong> (<code>pk_live_</code> / <code>sk_live_</code>) only when ready for production.
        </div>
    </div>
</div>

<?php if ($config): ?>
<!-- Current Configuration Card -->
<div class="card mb-4">
    <div class="card-header px-3 py-2 <?= $config['is_active'] ? 'bg-success text-white' : 'bg-secondary text-white' ?>">
        <h2 class="h6 mb-0">
            <i class="bi bi-<?= $config['is_active'] ? 'check-circle' : 'x-circle' ?> me-1"></i>
            Current Configuration - <?= $config['is_active'] ? 'Active' : 'Inactive' ?>
        </h2>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Public Key</dt>
            <dd class="col-sm-9">
                <code><?= htmlspecialchars($config['public_key']) ?></code>
                <?php if (str_starts_with($config['public_key'], 'pk_test_')): ?>
                    <span class="badge bg-warning text-dark ms-2">Test Mode</span>
                <?php elseif (str_starts_with($config['public_key'], 'pk_live_')): ?>
                    <span class="badge bg-success ms-2">Live Mode</span>
                <?php endif; ?>
            </dd>
            
            <dt class="col-sm-3">Secret Key</dt>
            <dd class="col-sm-9">
                <code><?= htmlspecialchars(\App\Core\DataMasking::apiKey($config['secret_key'])) ?></code>
                <small class="text-muted">(masked for security)</small>
                <span class="badge bg-danger ms-2">RESTRICTED</span>
            </dd>
            
            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">
                <?php if ($config['is_active']): ?>
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle"></i> Active - Online payments enabled
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary">
                        <i class="bi bi-x-circle"></i> Inactive - Online payments disabled
                    </span>
                <?php endif; ?>
            </dd>
            
            <dt class="col-sm-3">Last Updated</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($config['updated_at']) ?></dd>
        </dl>
        
        <div class="d-flex gap-2 mt-3">
            <form method="post" class="d-inline">
                <input type="hidden" name="action" value="toggle">
                <button type="submit" class="btn btn-<?= $config['is_active'] ? 'warning' : 'success' ?> btn-sm">
                    <i class="bi bi-<?= $config['is_active'] ? 'pause-circle' : 'play-circle' ?>"></i>
                    <?= $config['is_active'] ? 'Disable' : 'Enable' ?> PayMongo
                </button>
            </form>
            
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#updateForm">
                <i class="bi bi-pencil"></i> Update Keys
            </button>
            
            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete your PayMongo configuration? This cannot be undone.');">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash"></i> Delete Configuration
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Update Form (Collapsed by default) -->
<div class="collapse" id="updateForm">
<?php endif; ?>

<!-- Configuration Form -->
<div class="card">
    <div class="card-header px-3 py-2">
        <h2 class="h6 mb-0">
            <i class="bi bi-gear me-1"></i>
            <?= $config ? 'Update' : 'Setup' ?> PayMongo Configuration
        </h2>
    </div>
    <div class="card-body">
        <form method="post" class="vstack gap-3">
            <input type="hidden" name="action" value="save">
            
            <div>
                <label class="form-label" for="public_key">
                    Public Key <span class="text-danger">*</span>
                    <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" 
                       title="Starts with pk_test_ (test mode) or pk_live_ (live mode)"></i>
                </label>
                <input 
                    class="form-control font-monospace" 
                    id="public_key" 
                    type="text" 
                    name="public_key"
                    placeholder="pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxx"
                    value="<?= $config ? htmlspecialchars($config['public_key']) : '' ?>"
                    required
                    pattern="pk_(test|live)_.*"
                >
                <div class="form-text">
                    <i class="bi bi-shield-check me-1"></i>
                    This key is safe to use in client-side code. Get it from your PayMongo dashboard.
                </div>
            </div>
            
            <div>
                <label class="form-label" for="secret_key">
                    Secret Key <span class="text-danger">*</span>
                    <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" 
                       title="Starts with sk_test_ (test mode) or sk_live_ (live mode)"></i>
                </label>
                <input 
                    class="form-control font-monospace" 
                    id="secret_key" 
                    type="password" 
                    name="secret_key"
                    placeholder="sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxx"
                    value="<?= $config ? htmlspecialchars($config['secret_key']) : '' ?>"
                    required
                    pattern="sk_(test|live)_.*"
                >
                <div class="form-text">
                    <i class="bi bi-shield-exclamation me-1"></i>
                    <strong>Keep this secret!</strong> Never share this key or commit it to version control.
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="show_secret" onclick="toggleSecretKey()">
                    <label class="form-check-label" for="show_secret">
                        Show secret key
                    </label>
                </div>
            </div>
            
            <div class="form-check">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="is_active" 
                    name="is_active" 
                    value="1"
                    <?= ($config && $config['is_active']) || !$config ? 'checked' : '' ?>
                >
                <label class="form-check-label" for="is_active">
                    <strong>Enable online payments</strong>
                    <div class="small text-muted">
                        When enabled, customers can pay online via PayMongo. When disabled, only cash payments are accepted.
                    </div>
                </label>
            </div>
            
            <div class="alert alert-info mb-0">
                <i class="bi bi-lightbulb me-1"></i>
                <strong>Testing Tips:</strong>
                <ul class="mb-0 mt-2">
                    <li>Use test keys (<code>pk_test_</code> / <code>sk_test_</code>) for development</li>
                    <li>Test with PayMongo test card: <code>4343 4343 4343 4345</code></li>
                    <li>Any future expiry date and any 3-digit CVC will work in test mode</li>
                    <li>Switch to live keys only when ready to accept real payments</li>
                </ul>
            </div>
            
            <button class="btn btn-primary" type="submit">
                <i class="bi bi-save me-1"></i>
                <?= $config ? 'Update' : 'Save' ?> Configuration
            </button>
        </form>
    </div>
</div>

<?php if ($config): ?>
</div><!-- End collapse -->
<?php endif; ?>

<!-- PayMongo Features Card -->
<div class="card mt-4">
    <div class="card-header px-3 py-2 bg-light">
        <h2 class="h6 mb-0"><i class="bi bi-star me-1"></i>What You Get with PayMongo</h2>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex gap-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <div>
                        <strong>Multiple Payment Methods</strong>
                        <div class="small text-muted">Credit/Debit Cards, GCash, GrabPay, PayMaya</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <div>
                        <strong>Secure Payments</strong>
                        <div class="small text-muted">PCI-DSS compliant, encrypted transactions</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <div>
                        <strong>Instant Confirmation</strong>
                        <div class="small text-muted">Real-time payment status updates</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <div>
                        <strong>Automatic Revenue Tracking</strong>
                        <div class="small text-muted">Payments automatically recorded in your dashboard</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle secret key visibility
function toggleSecretKey() {
    const input = document.getElementById('secret_key');
    const checkbox = document.getElementById('show_secret');
    input.type = checkbox.checked ? 'text' : 'password';
}

// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
