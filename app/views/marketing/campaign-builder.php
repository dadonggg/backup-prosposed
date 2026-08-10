<?php
declare(strict_types=1);
$pageTitle = 'Campaign Builder';
require __DIR__ . '/../partials/header.php';

// $trainers is passed from MarketingController::campaignbuilderAction()
$trainers = $trainers ?? [];
$existingCampaign = $existingCampaign ?? null;
?>

<style>
/* ══════════════════════════════════════════════
   CAMPAIGN BUILDER — MARKETING OFFICER ONLY
══════════════════════════════════════════════ */
:root {
  --cb-green: #4ade80;
  --cb-card: #ffffff;
  --cb-border: #e5e7eb;
  --cb-radius: 14px;
}

/* PUBLISH BAR */
.publish-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fff;
  border: 1px solid var(--cb-border);
  border-radius: var(--cb-radius);
  padding: 12px 20px;
  margin-bottom: 22px;
  box-shadow: 0 1px 4px rgba(0,0,0,.05);
  flex-wrap: wrap;
}
.status-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: .82rem;
  font-weight: 700;
  padding: 5px 12px;
  border-radius: 20px;
}
.status-pill.draft { background: #fef3c7; color: #92400e; }
.status-pill.live  { background: #d1fae5; color: #065f46; }
.status-pill .dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink:0; }
.status-pill.draft .dot { background: #f59e0b; }
.status-pill.live  .dot { background: #10b981; }
.publish-bar-title { font-weight: 600; font-size: .9rem; color: #374151; flex: 1; min-width:160px; }
.btn-publish {
  padding: 9px 20px;
  border-radius: 8px;
  border: none;
  background: linear-gradient(135deg, #059669, #4ade80);
  color: #fff;
  font-weight: 700;
  font-size: .83rem;
  cursor: pointer;
  transition: all .2s;
  display: flex; align-items: center; gap: 6px;
  box-shadow: 0 2px 8px rgba(74,222,128,.35);
  white-space: nowrap;
}
.btn-publish:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(74,222,128,.45); }
.btn-unpublish {
  padding: 9px 20px;
  border-radius: 8px;
  border: none;
  background: #fee2e2;
  color: #b91c1c;
  font-weight: 700;
  font-size: .83rem;
  cursor: pointer;
  transition: all .2s;
  white-space: nowrap;
}
.btn-unpublish:hover { background: #fecaca; }

/* BUILDER TABS */
.builder-tabs {
  display: flex;
  border-bottom: 2px solid var(--cb-border);
  margin-bottom: 24px;
}
.builder-tab {
  padding: 10px 22px;
  border: none;
  background: none;
  font-size: .9rem;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  margin-bottom: -2px;
  transition: all .2s;
}
.builder-tab.active { color: #059669; border-bottom-color: #4ade80; }
.builder-tab:hover:not(.active) { color: #374151; }

/* CARDS */
.builder-card {
  background: #fff;
  border: 1px solid var(--cb-border);
  border-radius: var(--cb-radius);
  margin-bottom: 20px;
  box-shadow: 0 1px 4px rgba(0,0,0,.05);
  overflow: hidden;
}
.builder-card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 20px;
  border-bottom: 1px solid var(--cb-border);
  background: #fafafa;
}
.builder-card-header h3 { font-size: .95rem; font-weight: 700; margin: 0; color: #111827; }
.card-icon { width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.95rem; }
.builder-card-body { padding: 20px; }

/* FORM */
.cb-form-group { margin-bottom: 16px; }
.cb-label { display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:6px; }
.cb-input, .cb-select, .cb-textarea {
  width: 100%;
  padding: 9px 12px;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  font-size: .88rem;
  color: #111827;
  background: #fff;
  transition: border-color .2s, box-shadow .2s;
  font-family: inherit;
  box-sizing: border-box;
}
.cb-input:focus, .cb-select:focus, .cb-textarea:focus {
  outline: none;
  border-color: #4ade80;
  box-shadow: 0 0 0 3px rgba(74,222,128,.15);
}
.cb-textarea { min-height: 90px; resize: vertical; }
.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }

/* COLOR PICKER */
.color-picker-row { display:flex;gap:10px;flex-wrap:wrap;align-items:center; }
.color-circle {
  width:32px;height:32px;border-radius:50%;cursor:pointer;
  border:3px solid transparent;transition:all .2s;position:relative;
}
.color-circle.selected { border-color:#111827;transform:scale(1.18);box-shadow:0 0 0 2px rgba(0,0,0,.15); }
.color-circle::after {
  content:'✓';position:absolute;inset:0;
  display:flex;align-items:center;justify-content:center;
  color:rgba(255,255,255,.0);font-size:.7rem;font-weight:900;
}
.color-circle.selected::after { color:rgba(255,255,255,.9); }

/* TAG INPUT */
.tag-input-area {
  display:flex;flex-wrap:wrap;gap:6px;
  padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:8px;
  min-height:44px;cursor:text;transition:border-color .2s;
}
.tag-input-area:focus-within { border-color:#4ade80;box-shadow:0 0 0 3px rgba(74,222,128,.15); }
.tag-pill {
  display:flex;align-items:center;gap:5px;
  background:#d1fae5;color:#065f46;
  font-size:.78rem;font-weight:600;padding:3px 9px;border-radius:20px;
}
.tag-pill button { background:none;border:none;cursor:pointer;color:#059669;font-size:.75rem;padding:0;line-height:1; }
.tag-input-field {
  border:none;outline:none;font-size:.85rem;color:#111827;
  flex:1;min-width:120px;background:transparent;font-family:inherit;
}

/* DYNAMIC ROWS */
.dyn-table { width:100%;border-collapse:collapse; }
.dyn-table th {
  font-size:.75rem;font-weight:700;color:#6b7280;
  text-transform:uppercase;letter-spacing:.5px;
  padding:8px 10px;border-bottom:2px solid #f3f4f6;text-align:left;
}
.dyn-table td { padding:8px 6px;vertical-align:middle; }
.dyn-table tr:not(:last-child) td { border-bottom:1px solid #f9fafb; }
.dyn-input {
  width:100%;padding:7px 9px;border:1.5px solid #e5e7eb;border-radius:7px;
  font-size:.84rem;color:#111827;background:#fff;font-family:inherit;transition:border-color .2s;
}
.dyn-input:focus { outline:none;border-color:#4ade80; }
.btn-delete-row {
  padding:5px 8px;background:#fee2e2;border:none;border-radius:6px;
  color:#b91c1c;cursor:pointer;font-size:.8rem;transition:all .2s;
}
.btn-delete-row:hover { background:#fecaca; }
.btn-add-row {
  display:flex;align-items:center;gap:6px;
  margin-top:12px;padding:8px 14px;
  border:1.5px dashed #d1d5db;border-radius:8px;
  background:#fafafa;color:#6b7280;font-size:.83rem;font-weight:600;
  cursor:pointer;transition:all .2s;
}
.btn-add-row:hover { border-color:#4ade80;color:#059669;background:#f0fdf4; }

/* ── CALENDAR DAY PICKER ── */
.day-picker { display:flex;gap:5px;flex-wrap:wrap; }
.day-chip {
  padding:5px 9px;border-radius:6px;border:1.5px solid #e5e7eb;
  font-size:.76rem;font-weight:700;cursor:pointer;transition:all .15s;
  background:#fff;color:#6b7280;user-select:none;
}
.day-chip.sel { background:#059669;color:#fff;border-color:#059669; }

/* ── CLOCK TIME PICKER ── */
.time-picker-wrap { position:relative; }
.time-picker-popup {
  display:none;
  position:absolute;top:calc(100% + 4px);left:0;z-index:500;
  background:#fff;border:1.5px solid #e5e7eb;border-radius:10px;
  box-shadow:0 6px 20px rgba(0,0,0,.13);
  padding:14px 16px;min-width:200px;
}
.time-picker-popup.open { display:block; }
.tp-row { display:flex;align-items:center;gap:8px;margin-bottom:10px; }
.tp-label { font-size:.75rem;font-weight:700;color:#6b7280;width:50px; }
.tp-num {
  width:52px;padding:5px 8px;border:1.5px solid #e5e7eb;border-radius:6px;
  font-size:.88rem;font-weight:700;color:#111827;text-align:center;font-family:inherit;
}
.tp-num:focus { outline:none;border-color:#4ade80; }
.tp-ampm { display:flex;gap:4px; }
.tp-ampm button {
  padding:4px 8px;border:1.5px solid #e5e7eb;border-radius:6px;
  font-size:.76rem;font-weight:700;cursor:pointer;background:#fff;color:#6b7280;transition:all .15s;
}
.tp-ampm button.sel { background:#059669;color:#fff;border-color:#059669; }
.tp-btn-apply {
  width:100%;padding:7px;border:none;border-radius:7px;
  background:linear-gradient(135deg,#059669,#4ade80);
  color:#fff;font-weight:700;font-size:.82rem;cursor:pointer;margin-top:4px;
}
.time-display {
  display:flex;align-items:center;gap:6px;
  padding:7px 10px;border:1.5px solid #e5e7eb;border-radius:7px;
  font-size:.84rem;color:#111827;cursor:pointer;background:#fff;
  transition:border-color .2s;min-width:130px;
}
.time-display:hover { border-color:#4ade80; }
.time-display i { color:#9ca3af;font-size:.9rem; }
.dash-sep { color:#6b7280;font-size:.84rem;font-weight:600;padding:0 4px; }

/* REGISTRATIONS TAB */
.metric-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px; }
.metric-card {
  background:#fff;border:1px solid var(--cb-border);
  border-radius:12px;padding:16px 18px;
  box-shadow:0 1px 4px rgba(0,0,0,.04);
}
.metric-card .mc-label { font-size:.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px; }
.metric-card .mc-value { font-size:1.9rem;font-weight:800;color:#111827;line-height:1; }
.metric-card .mc-sub   { font-size:.78rem;color:#9ca3af;margin-top:4px; }
.metric-card.green .mc-value { color:#059669; }
.metric-card.amber .mc-value { color:#d97706; }
.metric-card.blue  .mc-value { color:#2563eb; }

.search-bar {
  display:flex;align-items:center;gap:8px;
  background:#fff;border:1.5px solid #e5e7eb;
  border-radius:9px;padding:8px 12px;margin-bottom:14px;
}
.search-bar input { border:none;outline:none;flex:1;font-size:.87rem;color:#111827;background:transparent;font-family:inherit; }
.search-bar i { color:#9ca3af; }

.reg-table { width:100%;border-collapse:collapse; }
.reg-table th {
  font-size:.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;
  padding:10px 12px;border-bottom:2px solid #f3f4f6;text-align:left;white-space:nowrap;
}
.reg-table td { padding:11px 12px;border-bottom:1px solid #f9fafb;font-size:.87rem;color:#374151; }
.reg-table tr:hover td { background:#fafafa; }
.badge-paid    { background:#d1fae5;color:#065f46;font-size:.74rem;font-weight:700;padding:3px 10px;border-radius:20px; }
.badge-pending { background:#fef3c7;color:#92400e;font-size:.74rem;font-weight:700;padding:3px 10px;border-radius:20px; }

@media(max-width:900px){
  .metric-grid { grid-template-columns:1fr 1fr; }
  .form-row,.form-row-3 { grid-template-columns:1fr; }
}
</style>

<!-- PAGE HEADER -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:8px;">
  <div>
    <h1 style="font-size:1.3rem;font-weight:800;margin:0;color:#111827;">
      <i class="bi bi-layout-text-window-reverse me-2" style="color:#4ade80;"></i>Campaign Builder
    </h1>
    <p style="color:#6b7280;font-size:.84rem;margin:4px 0 0;">Build and publish enrollment campaigns for your gym members.</p>
  </div>
</div>

<!-- PUBLISH BAR -->
<div class="publish-bar mt-3">
  <span id="statusPill" class="status-pill draft">
    <span class="dot"></span>
    <span id="statusLabel">Draft</span>
  </span>
  <span class="publish-bar-title">
    <span id="publishBarCampaignName" style="font-weight:700;">Untitled Campaign</span>
    &nbsp;<span id="publishBarSub" style="color:#9ca3af;font-weight:400;">· Not yet published</span>
  </span>
  <button class="btn-publish" id="btnPublish" onclick="publishCampaign()">
    <i class="bi bi-broadcast"></i> Publish to Members
  </button>
</div>

<!-- TABS -->
<div class="builder-tabs">
  <button class="builder-tab active" id="tabBuilder" onclick="switchBuilderTab('builder')">
    <i class="bi bi-pencil-square me-1"></i>Campaign Builder
  </button>
  <button class="builder-tab" id="tabRegistrations" onclick="switchBuilderTab('registrations')">
    <i class="bi bi-people me-1"></i>Registrations
    <span id="regBadge" style="background:#4ade80;color:#065f46;font-size:.7rem;font-weight:800;padding:2px 7px;border-radius:20px;margin-left:5px;">0</span>
  </button>
</div>

<!-- ─── BUILDER PANEL ─── -->
<div id="panelBuilder">

  <!-- BASIC INFO -->
  <div class="builder-card">
    <div class="builder-card-header">
      <div class="card-icon" style="background:#dbeafe;color:#2563eb;"><i class="bi bi-info-circle"></i></div>
      <h3>Basic Info</h3>
    </div>
    <div class="builder-card-body">
      <div class="form-row">
        <div class="cb-form-group">
          <label class="cb-label">Campaign Title <span style="color:#ef4444;">*</span></label>
          <input class="cb-input" id="fTitle" type="text" placeholder="e.g. Boxing Bootcamp Summer 2025"
                 oninput="document.getElementById('publishBarCampaignName').textContent=this.value||'Untitled Campaign'">
        </div>
        <div class="cb-form-group">
          <label class="cb-label">Coach / Instructor <span style="color:#ef4444;">*</span></label>
          <select class="cb-select" id="fCoach">
            <option value="">— Select fitness trainer —</option>
            <?php foreach ($trainers as $t): ?>
            <option value="<?= htmlspecialchars($t['fullname']) ?>">
              <?= htmlspecialchars($t['fullname']) ?><?= !empty($t['specialization']) ? ' — '.htmlspecialchars($t['specialization']) : '' ?>
            </option>
            <?php endforeach; ?>
            <?php if (empty($trainers)): ?>
            <option value="" disabled>No trainers found in database</option>
            <?php endif; ?>
          </select>
        </div>
      </div>

      <div class="cb-form-group">
        <label class="cb-label">Service Description</label>
        <textarea class="cb-textarea" id="fDesc" placeholder="Describe what members will experience, goals, benefits..."></textarea>
      </div>

      <div class="form-row">
        <div class="cb-form-group">
          <label class="cb-label">Start Date <span style="color:#ef4444;">*</span></label>
          <input class="cb-input" id="fStart" type="date">
        </div>
        <div class="cb-form-group">
          <label class="cb-label">End Date <span style="color:#ef4444;">*</span></label>
          <input class="cb-input" id="fEnd" type="date">
        </div>
      </div>

      <div class="form-row">
        <div class="cb-form-group">
          <label class="cb-label">Max Class Size</label>
          <input class="cb-input" id="fMaxSize" type="number" min="1" value="20" placeholder="20">
        </div>
        <div class="cb-form-group">
          <label class="cb-label">Target Audience</label>
          <select class="cb-select" id="fAudience">
            <option value="all">All members</option>
            <option value="new">New members only</option>
            <option value="premium">Premium members</option>
            <option value="walkin">Walk-ins welcome</option>
          </select>
        </div>
      </div>

      <div class="cb-form-group">
        <label class="cb-label">Feature Tags <span style="color:#9ca3af;font-weight:400;">(type and press Enter to add)</span></label>
        <div class="tag-input-area" id="tagArea" onclick="document.getElementById('tagField').focus()">
          <input class="tag-input-field" id="tagField" placeholder="e.g. Equipment included" onkeydown="handleTagKey(event)">
        </div>
      </div>

      <div class="cb-form-group">
        <label class="cb-label">Banner Accent Color</label>
        <div class="color-picker-row" id="colorPicker">
          <div class="color-circle selected" data-color="red"    style="background:linear-gradient(135deg,#ef4444,#b91c1c);" onclick="selectColor(this,'red')"></div>
          <div class="color-circle"          data-color="purple" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);" onclick="selectColor(this,'purple')"></div>
          <div class="color-circle"          data-color="blue"   style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);" onclick="selectColor(this,'blue')"></div>
          <div class="color-circle"          data-color="green"  style="background:linear-gradient(135deg,#10b981,#059669);" onclick="selectColor(this,'green')"></div>
          <div class="color-circle"          data-color="amber"  style="background:linear-gradient(135deg,#f59e0b,#d97706);" onclick="selectColor(this,'amber')"></div>
          <div class="color-circle"          data-color="pink"   style="background:linear-gradient(135deg,#ec4899,#be185d);" onclick="selectColor(this,'pink')"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- PRICING TIERS -->
  <div class="builder-card">
    <div class="builder-card-header">
      <div class="card-icon" style="background:#d1fae5;color:#059669;"><i class="bi bi-currency-dollar"></i></div>
      <h3>Pricing Tiers</h3>
      <span style="font-size:.78rem;color:#9ca3af;margin-left:auto;">Add tiers for members to choose from during registration</span>
    </div>
    <div class="builder-card-body">
      <div id="pricingEmpty" style="text-align:center;padding:28px;color:#9ca3af;font-size:.87rem;">
        <i class="bi bi-cash-coin" style="font-size:2rem;display:block;margin-bottom:8px;color:#d1d5db;"></i>
        No pricing tiers yet. Click "Add pricing tier" to get started.
      </div>
      <table class="dyn-table" id="pricingTable" style="display:none;">
        <thead>
          <tr>
            <th>Tier Name</th>
            <th>Duration Label</th>
            <th>Sessions</th>
            <th>Price (₱)</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="pricingBody"></tbody>
      </table>
      <button class="btn-add-row" onclick="addPricingRow()">
        <i class="bi bi-plus-circle"></i> Add pricing tier
      </button>
    </div>
  </div>

  <!-- SCHEDULES -->
  <div class="builder-card">
    <div class="builder-card-header">
      <div class="card-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-calendar3"></i></div>
      <h3>Schedules</h3>
      <span style="font-size:.78rem;color:#9ca3af;margin-left:auto;">Pick days from the calendar chips and times from the clock picker</span>
    </div>
    <div class="builder-card-body">
      <div id="schedEmpty" style="text-align:center;padding:28px;color:#9ca3af;font-size:.87rem;">
        <i class="bi bi-calendar-week" style="font-size:2rem;display:block;margin-bottom:8px;color:#d1d5db;"></i>
        No schedules yet. Click "Add schedule" to get started.
      </div>
      <div id="schedList"></div>
      <button class="btn-add-row" onclick="addSchedRow()">
        <i class="bi bi-plus-circle"></i> Add schedule
      </button>
    </div>
  </div>

</div><!-- end #panelBuilder -->

<!-- ─── REGISTRATIONS PANEL ─── -->
<div id="panelRegistrations" style="display:none;">
  <div class="metric-grid">
    <div class="metric-card">
      <div class="mc-label">Total Registered</div>
      <div class="mc-value" id="mTotal">0</div>
      <div class="mc-sub">across all packs</div>
    </div>
    <div class="metric-card blue">
      <div class="mc-label">Slots Remaining</div>
      <div class="mc-value" id="mSlots">—</div>
      <div class="mc-sub" id="mSlotsOf">set max size above</div>
    </div>
    <div class="metric-card green">
      <div class="mc-label">Confirmed Paid</div>
      <div class="mc-value" id="mPaid">0</div>
      <div class="mc-sub">payment verified</div>
    </div>
    <div class="metric-card amber">
      <div class="mc-label">Pending</div>
      <div class="mc-value" id="mPending">0</div>
      <div class="mc-sub">awaiting payment</div>
    </div>
  </div>
  <div class="builder-card">
    <div class="builder-card-header">
      <div class="card-icon" style="background:#f3e8ff;color:#7c3aed;"><i class="bi bi-table"></i></div>
      <h3>Registration Records</h3>
      <span style="margin-left:auto;font-size:.78rem;color:#9ca3af;">View-only — payment confirmed by Administrative Officer</span>
    </div>
    <div class="builder-card-body">
      <div class="search-bar">
        <i class="bi bi-search"></i>
        <input type="text" id="regSearch" placeholder="Search member name, email or pack..." oninput="renderRegTable()">
      </div>
      <div style="overflow-x:auto;">
        <table class="reg-table">
          <thead>
            <tr>
              <th>Member</th>
              <th>Pack Selected</th>
              <th>Schedule</th>
              <th>Date Registered</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="regTableBody"></tbody>
        </table>
      </div>
      <div id="regEmpty" style="text-align:center;padding:32px;color:#9ca3af;font-size:.87rem;display:none;">
        <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
        No registrations yet. Publish the campaign so members can register.
      </div>
    </div>
  </div>
</div><!-- end #panelRegistrations -->


<!-- ════════════════════════════════════════
     JAVASCRIPT ENGINE
════════════════════════════════════════ -->
<script>
/* ─── GLOBAL STATE ─── */
const STATE = {
  published:    false,
  campaignId:   <?= $existingCampaign ? (int)$existingCampaign['id'] : 0 ?>,
  accentColor:  'red',
  tags:         [],
  pricing:      [],   // empty by default
  schedules:    [],   // empty by default
  registrations:[],
  nextPriceId:  1,
  nextSchedId:  1,
  nextRegId:    1,
};

const COLORS = {
  red:    'linear-gradient(135deg,#ef4444 0%,#b91c1c 100%)',
  purple: 'linear-gradient(135deg,#8b5cf6 0%,#6d28d9 100%)',
  blue:   'linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%)',
  green:  'linear-gradient(135deg,#10b981 0%,#059669 100%)',
  amber:  'linear-gradient(135deg,#f59e0b 0%,#d97706 100%)',
  pink:   'linear-gradient(135deg,#ec4899 0%,#be185d 100%)',
};

const DAYS = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

/* ─── SAVE/BROADCAST STATE to localStorage so member & owner pages can read it ─── */
function broadcastState() {
  const data = readBuilderState();
  data.published    = STATE.published;
  data.registrations= STATE.registrations;
  try { localStorage.setItem('cb_campaign_state', JSON.stringify(data)); } catch(e){}
}

/* ─── BUILDER TAB ─── */
function switchBuilderTab(t) {
  document.getElementById('panelBuilder').style.display       = t==='builder'       ? 'block':'none';
  document.getElementById('panelRegistrations').style.display = t==='registrations' ? 'block':'none';
  document.getElementById('tabBuilder').classList.toggle('active',       t==='builder');
  document.getElementById('tabRegistrations').classList.toggle('active', t==='registrations');
  if (t==='registrations') renderRegTable();
}

/* ─── PUBLISH ─── */
function publishCampaign() {
  const title = document.getElementById('fTitle').value.trim();
  const coach = document.getElementById('fCoach').value;
  const start = document.getElementById('fStart').value;
  const end   = document.getElementById('fEnd').value;
  if (!title)  { showToast('Please enter a campaign title.','error'); return; }
  if (!coach)  { showToast('Please select a coach/instructor.','error'); return; }
  if (!start||!end) { showToast('Please set start and end dates.','error'); return; }
  if (STATE.pricing.length===0) { showToast('Please add at least one pricing tier.','error'); return; }
  const incompletePrice = STATE.pricing.some(p => !p.name || !p.duration || p.price <= 0);
  if (incompletePrice) { showToast('Please complete all fields for the pricing tiers (price must be greater than 0).','error'); return; }
  if (STATE.schedules.length===0) { showToast('Please add at least one schedule.','error'); return; }
  const incompleteSched = STATE.schedules.some(s => !s.date || !s.startTime || !s.endTime);
  if (incompleteSched) { showToast('Please select a date, start time, and end time for all schedules.','error'); return; }

  // Save to database via AJAX
  const btnPublish = document.getElementById('btnPublish');
  btnPublish.disabled = true;
  btnPublish.innerHTML = '<i class="bi bi-hourglass-split"></i> Publishing...';

  const extraData = {
    pricing:   STATE.pricing,
    schedules: STATE.schedules,
    tags:      STATE.tags,
    color:     STATE.accentColor,
    maxSize:   parseInt(document.getElementById('fMaxSize').value) || 20,
  };

  const body = new FormData();
  body.append('title', title);
  body.append('description', document.getElementById('fDesc').value.trim());
  body.append('coach', coach);
  body.append('start_date', start);
  body.append('end_date', end);
  body.append('target_audience', document.getElementById('fAudience').value);
  body.append('status', 'active');
  body.append('extra_json', JSON.stringify(extraData));
  if (STATE.campaignId > 0) {
    body.append('campaign_id', STATE.campaignId);
  }

  fetch('index.php?r=marketing/savecampaignbuilder', { method: 'POST', body })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        STATE.published = true;
        if (data.campaign_id) STATE.campaignId = data.campaign_id;
        updatePublishBar();
        broadcastState();
        showToast('Campaign published! Members can now see it on Events & Promotions.','success');
      } else {
        showToast(data.error || 'Failed to publish campaign.','error');
        btnPublish.disabled = false;
        btnPublish.innerHTML = '<i class="bi bi-broadcast"></i> Publish to Members';
      }
    })
    .catch(err => {
      showToast('Network error. Please try again.','error');
      btnPublish.disabled = false;
      btnPublish.innerHTML = '<i class="bi bi-broadcast"></i> Publish to Members';
    });
}
function unpublishCampaign() {
  if (STATE.campaignId <= 0) {
    STATE.published = false;
    updatePublishBar();
    broadcastState();
    showToast('Campaign set back to draft.','info');
    return;
  }

  const body = new FormData();
  body.append('campaign_id', STATE.campaignId);

  fetch('index.php?r=marketing/unpublishcampaignbuilder', { method: 'POST', body })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        STATE.published = false;
        updatePublishBar();
        broadcastState();
        showToast('Campaign unpublished. Members will no longer see it.','info');
      } else {
        showToast(data.error || 'Failed to unpublish.','error');
      }
    })
    .catch(() => {
      showToast('Network error. Please try again.','error');
    });
}
function updatePublishBar() {
  const pill  = document.getElementById('statusPill');
  const label = document.getElementById('statusLabel');
  const sub   = document.getElementById('publishBarSub');
  const btn   = document.getElementById('btnPublish');
  if (STATE.published) {
    pill.className = 'status-pill live';
    label.textContent = 'Live';
    sub.textContent = '· Published — visible to members';
    btn.innerHTML = '<i class="bi bi-stop-circle"></i> Unpublish';
    btn.className = 'btn-unpublish';
    btn.onclick   = unpublishCampaign;
    btn.disabled  = false;
  } else {
    pill.className = 'status-pill draft';
    label.textContent = 'Draft';
    sub.textContent = '· Not yet published';
    btn.innerHTML = '<i class="bi bi-broadcast"></i> Publish to Members';
    btn.className = 'btn-publish';
    btn.onclick   = publishCampaign;
    btn.disabled  = false;
  }
}

/* ─── TAGS ─── */
function handleTagKey(e) {
  if (e.key==='Enter') {
    e.preventDefault();
    const v = e.target.value.trim();
    if (v && !STATE.tags.includes(v)) { STATE.tags.push(v); renderTags(); broadcastState(); }
    e.target.value='';
  }
}
function removeTag(tag) { STATE.tags=STATE.tags.filter(t=>t!==tag); renderTags(); broadcastState(); }
function renderTags() {
  const area=document.getElementById('tagArea');
  const field=document.getElementById('tagField');
  area.innerHTML='';
  STATE.tags.forEach(t=>{
    const pill=document.createElement('span');
    pill.className='tag-pill';
    pill.innerHTML=`${esc(t)} <button type="button" onclick="removeTag('${esc(t)}')" title="Remove">✕</button>`;
    area.appendChild(pill);
  });
  area.appendChild(field);
}

/* ─── COLOR ─── */
function selectColor(el,color) {
  document.querySelectorAll('.color-circle').forEach(c=>c.classList.remove('selected'));
  el.classList.add('selected');
  STATE.accentColor=color;
  broadcastState();
}

/* ─── PRICING ─── */
function renderPricing() {
  const tbody=document.getElementById('pricingBody');
  const table=document.getElementById('pricingTable');
  const empty=document.getElementById('pricingEmpty');
  tbody.innerHTML='';
  if(STATE.pricing.length===0){ table.style.display='none'; empty.style.display='block'; return; }
  table.style.display='table'; empty.style.display='none';
  STATE.pricing.forEach(row=>{
    const tr=document.createElement('tr');
    tr.innerHTML=`
      <td><input class="dyn-input" value="${esc(row.name)}" oninput="updatePricing(${row.id},'name',this.value)" placeholder="Tier name"></td>
      <td><input class="dyn-input" value="${esc(row.duration)}" oninput="updatePricing(${row.id},'duration',this.value)" placeholder="e.g. 1 Month" style="width:110px;"></td>
      <td><input class="dyn-input" type="number" value="${row.sessions}" oninput="updatePricing(${row.id},'sessions',+this.value)" style="width:80px;" min="1"></td>
      <td><input class="dyn-input" type="number" value="${row.price}" oninput="updatePricing(${row.id},'price',+this.value)" style="width:110px;" min="0" step="50"></td>
      <td><button class="btn-delete-row" onclick="deletePricingRow(${row.id})" title="Remove"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
  });
}
function updatePricing(id,f,v){ const r=STATE.pricing.find(x=>x.id===id); if(r){r[f]=v;broadcastState();} }
function addPricingRow(){ STATE.pricing.push({id:STATE.nextPriceId++,name:'',duration:'',sessions:1,price:0}); renderPricing(); }
function deletePricingRow(id){ STATE.pricing=STATE.pricing.filter(r=>r.id!==id); renderPricing(); broadcastState(); }

/* ─── SCHEDULES with Native HTML5 Calendar (Date) + Clock (Time) Pickers ─── */
function renderSchedList() {
  const list=document.getElementById('schedList');
  const empty=document.getElementById('schedEmpty');
  list.innerHTML='';
  if(STATE.schedules.length===0){ empty.style.display='block'; return; }
  empty.style.display='none';

  STATE.schedules.forEach(row=>{
    const div=document.createElement('div');
    div.style.cssText='border:1.5px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin-bottom:10px;background:#fff;';
    div.innerHTML=buildSchedRow(row);
    list.appendChild(div);
  });
}
function buildSchedRow(row){
  return `
    <div data-sid="${row.id}" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:14px;align-items:end;">
      <div>
        <label class="cb-label">Session Date <span style="color:#ef4444;">*</span></label>
        <input type="date" class="cb-input" value="${row.date || ''}" oninput="updateSched(${row.id},'date',this.value)">
      </div>
      <div>
        <label class="cb-label">Start Time <span style="color:#ef4444;">*</span></label>
        <input type="time" class="cb-input" value="${row.startTime || ''}" oninput="updateSched(${row.id},'startTime',this.value)">
      </div>
      <div>
        <label class="cb-label">End Time <span style="color:#ef4444;">*</span></label>
        <input type="time" class="cb-input" value="${row.endTime || ''}" oninput="updateSched(${row.id},'endTime',this.value)">
      </div>
      <div style="padding-bottom:2px;">
        <button class="btn-delete-row" onclick="deleteSchedRow(${row.id})" title="Remove" style="height:42px;width:42px;display:flex;align-items:center;justify-content:center;"><i class="bi bi-trash" style="font-size:1.1rem;"></i></button>
      </div>
    </div>
  `;
}
function updateSched(id,f,v){
  const r=STATE.schedules.find(x=>x.id===id);
  if(r){ r[f]=v; broadcastState(); }
}
function addSchedRow(){
  STATE.schedules.push({id:STATE.nextSchedId++,date:'',startTime:'',endTime:''});
  renderSchedList();
  broadcastState();
}
function deleteSchedRow(id){
  STATE.schedules=STATE.schedules.filter(r=>r.id!==id);
  renderSchedList();
  broadcastState();
}

/* ─── REGISTRATIONS TABLE ─── */
function renderRegTable(){
  const q=(document.getElementById('regSearch').value||'').toLowerCase();
  const tbody=document.getElementById('regTableBody');
  const empty=document.getElementById('regEmpty');
  // Pull registrations from database first, fallback to localStorage
  if (!STATE.registrations || STATE.registrations.length === 0) {
    try{
      const s=JSON.parse(localStorage.getItem('cb_campaign_state')||'{}');
      STATE.registrations=s.registrations||[];
    }catch(e){}
  }
  const filtered=STATE.registrations.filter(r=>
    r.name.toLowerCase().includes(q)||
    r.email.toLowerCase().includes(q)||
    r.pack.toLowerCase().includes(q)
  );
  tbody.innerHTML='';
  if(filtered.length===0){ empty.style.display='block'; }
  else{
    empty.style.display='none';
    filtered.forEach(r=>{
      const badge=r.status==='Paid'
        ?'<span class="badge-paid">Paid</span>'
        :'<span class="badge-pending">Pending</span>';
      const tr=document.createElement('tr');
      tr.innerHTML=`
        <td><div style="font-weight:600;color:#111827;">${esc(r.name)}</div><div style="font-size:.77rem;color:#9ca3af;">${esc(r.email)}</div></td>
        <td>${esc(r.pack)}</td>
        <td style="font-size:.82rem;">${esc(r.schedule)}</td>
        <td style="font-size:.82rem;color:#9ca3af;">${r.date}</td>
        <td>${badge}</td>
      `;
      tbody.appendChild(tr);
    });
  }
  updateMetrics();
}
function updateMetrics(){
  if (!STATE.registrations || STATE.registrations.length === 0) {
    try{
      const s=JSON.parse(localStorage.getItem('cb_campaign_state')||'{}');
      STATE.registrations=s.registrations||[];
    }catch(e){}
  }
  const total  =STATE.registrations.length;
  const paid   =STATE.registrations.filter(r=>r.status==='Paid').length;
  const pending=total-paid;
  const maxSize=parseInt(document.getElementById('fMaxSize').value)||0;
  const slots  =maxSize>0?Math.max(0,maxSize-total):'—';
  document.getElementById('mTotal').textContent  =total;
  document.getElementById('mSlots').textContent  =slots;
  document.getElementById('mPending').textContent=pending;
  document.getElementById('mPaid').textContent   =paid;
  if(maxSize>0) document.getElementById('mSlotsOf').textContent=`of ${maxSize} max size`;
  document.getElementById('regBadge').textContent=total;
}

/* ─── READ BUILDER STATE ─── */
function readBuilderState(){
  return {
    title:    document.getElementById('fTitle').value.trim()||'Untitled Campaign',
    desc:     document.getElementById('fDesc').value.trim(),
    coach:    document.getElementById('fCoach').value,
    start:    document.getElementById('fStart').value,
    end:      document.getElementById('fEnd').value,
    maxSize:  parseInt(document.getElementById('fMaxSize').value)||20,
    audience: document.getElementById('fAudience').value,
    color:    STATE.accentColor,
    tags:     [...STATE.tags],
    pricing:  STATE.pricing.map(p=>({...p})),
    schedules:STATE.schedules.map(s=>{
      const tLabel = s.startTime && s.endTime ? `${formatTime12(s.startTime)} – ${formatTime12(s.endTime)}` : '';
      const dLabel = s.date ? new Date(s.date + 'T00:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) : '';
      return {
        id: s.id,
        date: s.date,
        startTime: s.startTime,
        endTime: s.endTime,
        dateLabel: dLabel,
        timeLabel: tLabel
      };
    }),
  };
}

function formatTime12(t) {
  if (!t) return '';
  const parts = t.split(':');
  if (parts.length < 2) return t;
  let h = parseInt(parts[0]);
  const m = parts[1];
  const ampm = h >= 12 ? 'PM' : 'AM';
  h = h % 12;
  h = h ? h : 12;
  return `${h}:${m} ${ampm}`;
}

/* ─── HELPERS ─── */
function esc(s){ if(s==null)return''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function showToast(msg,type='info'){
  const colors={success:'#059669',error:'#dc2626',info:'#2563eb'};
  const icons ={success:'bi-check-circle-fill',error:'bi-exclamation-circle-fill',info:'bi-info-circle-fill'};
  const t=document.createElement('div');
  t.style.cssText=`position:fixed;bottom:24px;right:24px;z-index:99999;background:#111827;color:#fff;padding:12px 18px;border-radius:10px;font-size:.87rem;font-weight:600;display:flex;align-items:center;gap:8px;box-shadow:0 4px 20px rgba(0,0,0,.3);border-left:4px solid ${colors[type]||colors.info};max-width:340px;`;
  t.innerHTML=`<i class="bi ${icons[type]||icons.info}" style="color:${colors[type]};font-size:1rem;flex-shrink:0;"></i>${esc(msg)}`;
  document.body.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(()=>t.remove(),300);},3500);
}

/* ─── INIT ─── */
document.addEventListener('DOMContentLoaded',()=>{
  renderTags();
  renderPricing();
  renderSchedList();
  updateMetrics();
  updatePublishBar();
  // Set default start = today
  const today=new Date().toISOString().split('T')[0];
  if(!document.getElementById('fStart').value) document.getElementById('fStart').value=today;

  // Load existing campaign from database (passed from PHP)
  <?php if ($existingCampaign): ?>
  (function() {
    const ec = <?= json_encode($existingCampaign) ?>;
    STATE.campaignId = parseInt(ec.id) || 0;
    if (ec.title) {
      document.getElementById('fTitle').value = ec.title;
      document.getElementById('publishBarCampaignName').textContent = ec.title;
    }
    // Parse description — remove "Coach: ..." prefix if present
    let desc = ec.description || '';
    if (desc.startsWith('Coach: ')) {
      const nlIdx = desc.indexOf('\n');
      if (nlIdx > 0) {
        const coachName = desc.substring(7, nlIdx);
        desc = desc.substring(nlIdx + 1);
        document.getElementById('fCoach').value = coachName;
      }
    }
    document.getElementById('fDesc').value = desc;
    if (ec.start_date) document.getElementById('fStart').value = ec.start_date;
    if (ec.end_date) document.getElementById('fEnd').value = ec.end_date;
    if (ec.target_audience) {
      const revMap = {'all_members':'all','active_members':'new'};
      document.getElementById('fAudience').value = revMap[ec.target_audience] || 'all';
    }
    // Restore extra JSON (pricing, schedules, tags, color, registrations)
    if (ec.extra_json) {
      try {
        const extra = JSON.parse(ec.extra_json);
        if (extra.color) {
          STATE.accentColor = extra.color;
          document.querySelectorAll('.color-circle').forEach(c => c.classList.toggle('selected', c.dataset.color === extra.color));
        }
        if (Array.isArray(extra.tags)) { STATE.tags = extra.tags; renderTags(); }
        if (Array.isArray(extra.pricing) && extra.pricing.length) {
          STATE.pricing = extra.pricing;
          STATE.nextPriceId = Math.max(...extra.pricing.map(p => p.id)) + 1;
          renderPricing();
        }
        if (Array.isArray(extra.schedules) && extra.schedules.length) {
          STATE.schedules = extra.schedules;
          STATE.nextSchedId = Math.max(...extra.schedules.map(s => s.id)) + 1;
          renderSchedList();
        }
        if (extra.maxSize) document.getElementById('fMaxSize').value = extra.maxSize;
        if (Array.isArray(extra.registrations)) {
          STATE.registrations = extra.registrations;
        }
      } catch(e) {}
    }
    // Set published state from DB status
    if (ec.status === 'active' || ec.status === 'published') {
      STATE.published = true;
      updatePublishBar();
    }
  })();
  <?php else: ?>
  // Restore from localStorage as fallback
  try{
    const saved=JSON.parse(localStorage.getItem('cb_campaign_state')||'{}');
    if(saved.title){ document.getElementById('fTitle').value=saved.title; document.getElementById('publishBarCampaignName').textContent=saved.title; }
    if(saved.desc)  document.getElementById('fDesc').value=saved.desc;
    if(saved.coach) document.getElementById('fCoach').value=saved.coach;
    if(saved.start) document.getElementById('fStart').value=saved.start;
    if(saved.end)   document.getElementById('fEnd').value=saved.end;
    if(saved.maxSize) document.getElementById('fMaxSize').value=saved.maxSize;
    if(saved.audience) document.getElementById('fAudience').value=saved.audience;
    if(saved.color){
      STATE.accentColor=saved.color;
      document.querySelectorAll('.color-circle').forEach(c=>c.classList.toggle('selected',c.dataset.color===saved.color));
    }
    if(Array.isArray(saved.tags)){ STATE.tags=saved.tags; renderTags(); }
    if(Array.isArray(saved.pricing) && saved.pricing.length){
      STATE.pricing=saved.pricing;
      STATE.nextPriceId=Math.max(...saved.pricing.map(p=>p.id))+1;
      renderPricing();
    }
    if(Array.isArray(saved.schedules) && saved.schedules.length){
      STATE.schedules=saved.schedules;
      STATE.nextSchedId=Math.max(...saved.schedules.map(s=>s.id))+1;
      renderSchedList();
    }
    if(saved.published){ STATE.published=true; updatePublishBar(); }
    if(Array.isArray(saved.registrations)) STATE.registrations=saved.registrations;
    updateMetrics();
  } catch(e){}
  <?php endif; ?>

  updateMetrics();
});

// Auto-save on any input change
document.addEventListener('input', ()=>{ setTimeout(broadcastState,200); });
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
