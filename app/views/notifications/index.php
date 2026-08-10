<?php
declare(strict_types=1);
$pageTitle = 'Notifications';
require __DIR__ . '/../partials/header.php';

$displayName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
if ($displayName === '') $displayName = $user['fullname'] ?? 'User';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
  --bg-page: #f0f2f0;
  --bg-card: #ffffff;
  --border-card: #e2e8f0;
  --accent-teal: #0d9488;
  --text-primary: #1e293b;
  --text-secondary: #64748b;
  --shadow-card: 0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.05);
}

body {
  background: var(--bg-page) !important;
  font-family: 'Inter', system-ui, sans-serif !important;
  color: var(--text-primary) !important;
}

.notif-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
}

.notif-card {
  background: var(--bg-card);
  border: 1px solid var(--border-card);
  border-radius: 12px;
  box-shadow: var(--shadow-card);
  margin-bottom: 1rem;
  overflow: hidden;
  transition: all 0.2s;
  cursor: pointer;
}

.notif-card:hover {
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  transform: translateY(-2px);
}

.notif-card.unread {
  background: linear-gradient(to right, #f0fdf9 0%, #ffffff 100%);
  border-left: 4px solid var(--accent-teal);
}

.notif-header {
  display: flex;
  align-items: start;
  gap: 1rem;
  padding: 1.2rem;
}

.notif-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
}

.notif-icon.fitness {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.notif-icon.success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.notif-icon.info {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
}

.notif-icon.warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.notif-content {
  flex: 1;
  min-width: 0;
}

.notif-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 4px;
}

.notif-message {
  font-size: 14px;
  color: var(--text-secondary);
  line-height: 1.5;
  margin-bottom: 8px;
}

.notif-time {
  font-size: 12px;
  color: var(--text-secondary);
  font-weight: 500;
}

.unread-badge {
  width: 10px;
  height: 10px;
  background: #ef4444;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 6px;
}

.btn-mark-all {
  background: var(--accent-teal);
  color: #fff !important;
  border: none;
  border-radius: 8px;
  padding: 10px 18px;
  font-weight: 600;
  font-size: 14px;
  transition: all .2s;
}

.btn-mark-all:hover {
  background: #0f766e;
  transform: translateY(-1px);
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.empty-state i {
  font-size: 4rem;
  color: #cbd5e1;
  margin-bottom: 1rem;
}
</style>

<div class="notif-page">
  <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fw-extrabold mb-1" style="color: var(--text-primary); font-size: 26px; font-weight: 800;">
          <i class="bi bi-bell-fill me-2" style="color: var(--accent-teal)"></i>Notifications
        </h1>
        <p class="mb-0" style="color: var(--text-secondary); font-size: 14px;">
          Stay updated with your fitness journey
        </p>
      </div>
      <div class="d-flex gap-2">
        <?php if (!empty(array_filter($notifications, fn($n) => $n['is_read'] == 0))): ?>
        <button onclick="markAllAsRead()" class="btn-mark-all">
          <i class="bi bi-check-all me-1"></i>Mark All as Read
        </button>
        <?php endif; ?>
        <a href="index.php?r=fitness/status" class="btn btn-outline-secondary" style="border-radius: 8px; padding: 10px 18px; font-weight: 600;">
          <i class="bi bi-arrow-left me-1"></i>Back
        </a>
      </div>
    </div>
  </div>

  <?php if (empty($notifications)): ?>
  <!-- Empty State -->
  <div class="empty-state">
    <i class="bi bi-bell-slash"></i>
    <h5 style="color: var(--text-primary); font-weight: 700;">No Notifications Yet</h5>
    <p style="color: var(--text-secondary);">You're all caught up! Notifications will appear here.</p>
  </div>
  <?php else: ?>
  
  <!-- Notifications List -->
  <?php foreach ($notifications as $notification): 
    $isUnread = $notification['is_read'] == 0;
    $__type = $notification['type'];
    if ($__type === 'fitness_plan_ready') { $iconClass = 'fitness'; }
    elseif ($__type === 'trainer_assigned') { $iconClass = 'success'; }
    elseif ($__type === 'progress_feedback') { $iconClass = 'info'; }
    else { $iconClass = 'info'; }

    if ($__type === 'fitness_plan_ready') { $iconEmoji = '🎉'; }
    elseif ($__type === 'trainer_assigned') { $iconEmoji = '👤'; }
    elseif ($__type === 'progress_feedback') { $iconEmoji = '📊'; }
    else { $iconEmoji = '📢'; }
    
    $timeAgo = '';
    $diff = time() - strtotime($notification['created_at']);
    if ($diff < 60) {
      $timeAgo = 'Just now';
    } elseif ($diff < 3600) {
      $timeAgo = floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
      $timeAgo = floor($diff / 3600) . ' hours ago';
    } else {
      $timeAgo = floor($diff / 86400) . ' days ago';
    }
  ?>
  <div class="notif-card <?= $isUnread ? 'unread' : '' ?>" 
       onclick="handleNotificationClick(<?= $notification['id'] ?>, '<?= htmlspecialchars($notification['action_url'] ?? '', ENT_QUOTES) ?>')">
    <div class="notif-header">
      <div class="notif-icon <?= $iconClass ?>">
        <?= $iconEmoji ?>
      </div>
      <div class="notif-content">
        <div class="notif-title"><?= htmlspecialchars($notification['title']) ?></div>
        <div class="notif-message"><?= htmlspecialchars($notification['message']) ?></div>
        <div class="notif-time">
          <i class="bi bi-clock me-1"></i><?= $timeAgo ?>
        </div>
      </div>
      <?php if ($isUnread): ?>
      <div class="unread-badge"></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
  
  <?php endif; ?>
</div>

<script>
function handleNotificationClick(notificationId, actionUrl) {
  // Mark as read
  fetch('index.php?r=notification/markAsRead', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      notification_id: notificationId
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success && actionUrl) {
      // Navigate to action URL
      window.location.href = actionUrl;
    } else if (data.success) {
      // Just reload to update unread status
      location.reload();
    }
  })
  .catch(error => {
    console.error('Error marking notification as read:', error);
    // Still navigate even if marking fails
    if (actionUrl) {
      window.location.href = actionUrl;
    }
  });
}

function markAllAsRead() {
  fetch('index.php?r=notification/markAllAsRead', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert('Error: ' + (data.error || 'Failed to mark notifications as read'));
    }
  })
  .catch(error => {
    console.error('Error marking all as read:', error);
    alert('Error marking notifications as read');
  });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
