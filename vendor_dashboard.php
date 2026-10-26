<?php
/**
 * KAIRUSNETWORK - Vendor Portal
 * Manage assigned registration activation codes and public vendor directory details.
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

$authUser = function_exists('getAuthenticatedUser') ? getAuthenticatedUser() : null;
if (!$authUser) {
    header("Location: login.php");
    exit;
}

$username = $authUser['username'] ?? $_SESSION['username'] ?? 'Vendor';
$userRole = strtolower($authUser['role'] ?? $_SESSION['role'] ?? 'member');
$isAdmin = in_array(strtolower($username), ['admin', 'abas6245', 'abazceboi']) || in_array($userRole, ['admin', 'super_admin']);

// Dynamically check live user role from data/users.json
require_once __DIR__ . '/includes/storage_helper.php';
$uData = readStorageJson('data/users.json', ['users' => []]);
$allUsers = $uData['users'] ?? (is_array($uData) ? $uData : []);
foreach ($allUsers as $u) {
    if (strtolower($u['username'] ?? '') === strtolower($username)) {
        if (!empty($u['role'])) {
            $userRole = strtolower($u['role']);
            $_SESSION['role'] = $userRole;
        }
        break;
    }
}

$isVendor = $isAdmin || ($userRole === 'vendor');

// If not a vendor, display simple access restricted page
if (!$isVendor) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vendor Access Required | <?= htmlspecialchars(APP_NAME) ?></title>
        <link rel="icon" type="image/png" href="assets/images/logo.png">
        <link rel="apple-touch-icon" href="assets/images/logo.png">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family:'Plus Jakarta Sans',sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; background:#0A0D14; color:#F1F5F9; padding:20px; }
            .card { background:#121722; border:1px solid #1E2638; border-radius:14px; padding:36px; max-width:460px; text-align:center; }
            .icon { width:52px; height:52px; border-radius:12px; background:rgba(217,119,6,0.12); color:#F59E0B; display:flex; align-items:center; justify-content:center; margin:0 auto 18px; }
            h1 { font-size:1.3rem; font-weight:800; margin-bottom:8px; color:#F8FAFC; }
            p { font-size:0.88rem; color:#94A3B8; margin-bottom:24px; line-height:1.55; }
            .btn { display:inline-flex; align-items:center; justify-content:center; padding:10px 22px; border-radius:8px; background:#D97706; color:#FFFFFF; font-weight:700; text-decoration:none; font-size:0.88rem; border:none; cursor:pointer; }
            .btn:hover { background:#B45309; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12l4 6-10 13L2 9z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg>
            </div>
            <h1>Vendor Portal Access</h1>
            <p>This portal is for verified vendors to manage their profile and customer activation codes.</p>
            <a href="dashboard.php" class="btn">Return to Dashboard</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$pageTitle = 'Vendor Portal | ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="apple-touch-icon" href="assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #0A0D14;
            --bg-card: #121722;
            --bg-input: #182032;
            --border: #1E2638;
            --border-focus: #3B82F6;
            --text-main: #F1F5F9;
            --text-muted: #8896AB;
            --text-dim: #64748B;
            --accent: #D97706;
            --accent-hover: #B45309;
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            --font-mono: 'JetBrains Mono', Consolas, monospace;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg-body); color: var(--text-main); font-family: var(--font-sans); min-height: 100vh; padding-bottom: 50px; }

        /* Top Navigation */
        .top-nav {
            position: sticky; top: 0; z-index: 100;
            background: #0D121D; border-bottom: 1px solid var(--border);
            padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--text-main); }
        .brand-icon {
            width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden;
            filter: drop-shadow(0 2px 8px rgba(43, 175, 237, 0.45)); flex-shrink: 0;
        }
        .brand-icon img {
            width: 100%; height: 100%; object-fit: contain; display: block;
        }
        .brand-text { font-size: 0.95rem; font-weight: 800; letter-spacing: -0.2px; }

        .nav-actions { display: flex; align-items: center; gap: 12px; }
        .user-tag { font-size: 0.8rem; color: var(--text-muted); }
        .user-tag strong { color: var(--text-main); }

        .btn-link-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 12px; border-radius: var(--radius-sm);
            background: #182032; color: #CBD5E1; border: 1px solid var(--border);
            font-size: 0.8rem; font-weight: 600; text-decoration: none; transition: background 0.15s;
        }
        .btn-link-back:hover { background: #232D42; color: #FFFFFF; }

        /* Container */
        .container { max-width: 1060px; margin: 0 auto; padding: 24px 16px; }

        .header-title { font-size: 1.4rem; font-weight: 800; color: #FFFFFF; margin-bottom: 4px; }
        .header-sub { font-size: 0.84rem; color: var(--text-muted); margin-bottom: 22px; }

        /* Stats Row */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 22px; }
        @media (max-width: 800px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .stats-grid { grid-template-columns: 1fr; } }

        .stat-card {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: var(--radius-md); padding: 16px;
        }
        .stat-title { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.4px; }
        .stat-value { font-size: 1.45rem; font-weight: 800; color: #FFFFFF; margin-top: 6px; }

        /* Section Card */
        .section-card {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: var(--radius-lg); padding: 22px; margin-bottom: 20px;
        }
        .section-header { margin-bottom: 18px; }
        .section-title { font-size: 1rem; font-weight: 700; color: #FFFFFF; }
        .section-desc { font-size: 0.8rem; color: var(--text-muted); margin-top: 3px; }

        /* Form Controls */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
        @media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }

        .form-label { display: block; font-size: 0.75rem; font-weight: 700; color: #CBD5E1; margin-bottom: 6px; }
        .form-input {
            width: 100%; padding: 9px 12px; border-radius: var(--radius-sm);
            background: var(--bg-input); border: 1px solid var(--border);
            color: #FFFFFF; font-size: 0.85rem; font-family: inherit; outline: none; transition: border-color 0.15s;
        }
        .form-input:focus { border-color: var(--border-focus); }

        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 18px; border-radius: var(--radius-sm); background: var(--accent);
            color: #FFFFFF; font-weight: 700; font-size: 0.84rem; border: none; cursor: pointer; transition: background 0.15s;
        }
        .btn-primary:hover { background: var(--accent-hover); }

        .btn-secondary {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 16px; border-radius: var(--radius-sm); background: #1E2638;
            color: #E2E8F0; font-weight: 600; font-size: 0.84rem; border: 1px solid var(--border); cursor: pointer; text-decoration: none; transition: background 0.15s;
        }
        .btn-secondary:hover { background: #28334C; }

        /* Photo Upload */
        .photo-layout { display: flex; gap: 20px; align-items: flex-start; }
        @media (max-width: 640px) { .photo-layout { flex-direction: column; align-items: center; } }
        .photo-avatar {
            width: 84px; height: 84px; border-radius: 12px; background: #1E2638;
            border: 1px solid var(--border); display: flex; align-items: center; justify-content: center;
            overflow: hidden; flex-shrink: 0; font-size: 1.8rem; font-weight: 800; color: #F59E0B;
        }
        .photo-avatar img { width: 100%; height: 100%; object-fit: cover; }

        /* Table */
        .table-responsive { width: 100%; overflow-x: auto; }
        .clean-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
        .clean-table th {
            text-align: left; padding: 10px 12px; font-size: 0.72rem;
            font-weight: 700; text-transform: uppercase; color: var(--text-dim);
            border-bottom: 1px solid var(--border); background: #0E131E;
        }
        .clean-table td { padding: 12px; border-bottom: 1px solid var(--border); color: #CBD5E1; }

        .code-box {
            font-family: var(--font-mono); font-size: 0.86rem; font-weight: 700;
            color: #F1F5F9; letter-spacing: 0.5px; background: #161D2B; padding: 4px 8px; border-radius: 4px;
        }

        .badge-available { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; background: rgba(16,185,129,0.12); color: #10B981; }
        .badge-used { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; background: rgba(100,116,139,0.15); color: #94A3B8; }

        .btn-copy-code {
            padding: 4px 9px; border-radius: 4px; background: #1E2638;
            color: #E2E8F0; border: 1px solid var(--border); font-size: 0.72rem; font-weight: 600; cursor: pointer;
        }
        .btn-copy-code:hover { background: #28334C; color: #FFFFFF; }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <header class="top-nav">
        <a href="vendor_dashboard.php" class="nav-brand">
            <div class="brand-icon"><img src="assets/images/logo.png" alt="KN"></div>
            <div class="brand-text">Vendor Portal</div>
        </a>
        <div class="nav-actions">
            <span class="user-tag">Account: <strong>@<?= htmlspecialchars($username) ?></strong></span>
            <a href="dashboard.php" class="btn-link-back">← Member Dashboard</a>
        </div>
    </header>

    <main class="container">
        <div>
            <h1 class="header-title">Vendor Account</h1>
            <p class="header-sub">Manage your vendor profile and view your assigned activation codes.</p>
        </div>

        <!-- 4 Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Available Codes</div>
                <div class="stat-value" id="kpiAvailablePins">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Used Codes</div>
                <div class="stat-value" id="kpiSoldPins">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Total Assigned Codes</div>
                <div class="stat-value" id="kpiTotalPins">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Price per Code</div>
                <div class="stat-value">₦1,000</div>
            </div>
        </div>

        <!-- Card 1: Vendor Profile & Directory Handles -->
        <section class="section-card">
            <div class="section-header">
                <h2 class="section-title">Directory Profile & Contact Info</h2>
                <p class="section-desc">This information is shown on the vendor directory so buyers can reach you on WhatsApp and Telegram.</p>
            </div>

            <form id="vendorProfileForm" onsubmit="saveVendorProfile(event)">
                <div class="photo-layout">
                    <!-- Avatar Upload -->
                    <div style="display:flex;flex-direction:column;align-items:center;text-align:center;">
                        <div id="vendorAvatarContainer" class="photo-avatar">
                            <img id="vendorPhotoImg" src="" alt="Photo" style="display:none;">
                            <span id="vendorPhotoInitial"><?= strtoupper(substr($username, 0, 1)) ?></span>
                        </div>
                        <input type="file" id="vendorPhotoFile" accept="image/*" style="display:none;" onchange="handleVendorPhotoSelect(this)">
                        <button type="button" class="btn-secondary" onclick="document.getElementById('vendorPhotoFile').click()" style="margin-top:8px;padding:5px 10px;font-size:0.75rem;">
                            Change Photo
                        </button>
                    </div>

                    <!-- Input Fields -->
                    <div style="flex:1;width:100%;">
                        <div class="form-grid">
                            <div>
                                <label class="form-label">Display / Business Name</label>
                                <input type="text" id="vpName" class="form-input" placeholder="e.g. Samuel Okon" required>
                            </div>
                            <div>
                                <label class="form-label">WhatsApp Number (e.g. 2348012345678)</label>
                                <input type="text" id="vpPhone" class="form-input" placeholder="2348012345678" required>
                            </div>
                            <div>
                                <label class="form-label">Telegram Username (e.g. @username)</label>
                                <input type="text" id="vpTelegram" class="form-input" placeholder="@username">
                            </div>
                            <div>
                                <label class="form-label">Location / Coverage</label>
                                <input type="text" id="vpLocation" class="form-input" placeholder="e.g. Lagos / Nationwide">
                            </div>
                        </div>

                        <div style="margin-top:14px;display:flex;align-items:center;justify-content:space-between;">
                            <span id="profileStatusMsg" style="font-size:0.8rem;color:#10B981;font-weight:600;"></span>
                            <button type="submit" id="btnSaveProfile" class="btn-primary">
                                Save Details
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </section>

        <!-- Card 2: Send Code to Buyer -->
        <section class="section-card">
            <div class="section-header">
                <h2 class="section-title">Send Code to Buyer</h2>
                <p class="section-desc">Prepare a registration message with an activation code to dispatch to a customer.</p>
            </div>

            <div class="form-grid" style="margin-bottom:14px;">
                <div>
                    <label class="form-label">Buyer Name</label>
                    <input type="text" id="dispatchBuyerName" class="form-input" placeholder="e.g. John">
                </div>
                <div>
                    <label class="form-label">Select Available Code</label>
                    <select id="dispatchPinSelect" class="form-input">
                        <option value="">No available codes in stock</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button type="button" class="btn-secondary" onclick="copyDispatchMessage()">Copy Message</button>
                <button type="button" class="btn-primary" onclick="openWhatsAppDispatch()">Send on WhatsApp ↗</button>
            </div>
        </section>

        <!-- Card 3: Assigned Activation Codes Table -->
        <section class="section-card">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                <div>
                    <h2 class="section-title" style="margin:0;">Assigned Activation Codes</h2>
                    <p class="section-desc" style="margin:0;">Only codes assigned directly to your vendor account by the administrator appear here.</p>
                </div>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="searchPinInput" class="form-input" placeholder="Search code..." oninput="filterPinsTable()" style="width:200px;font-size:0.8rem;padding:6px 10px;">
                    <button type="button" class="btn-secondary" onclick="copyAllUnusedCodes()" style="font-size:0.78rem;padding:6px 12px;">Copy All Available</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="clean-table">
                    <thead>
                        <tr>
                            <th>Activation Code</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Redeemed By</th>
                            <th>Date Assigned</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="vendorPinsTableBody">
                        <tr><td colspan="6" style="text-align:center;color:var(--text-dim);padding:24px;">Loading assigned codes...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        const currentVendorUser = '<?= htmlspecialchars($username) ?>';
        let allVendorPins = [];
        let currentVendorRecord = null;
        let currentVendorPhotoBase64 = '';

        async function loadVendorProfile() {
            try {
                const res = await fetch('/api/vendors.php?action=get_vendor_profile&username=' + encodeURIComponent(currentVendorUser) + '&_t=' + Date.now());
                const data = await res.json();
                if (data && data.vendor) {
                    currentVendorRecord = data.vendor;
                    const v = data.vendor;
                    if (document.getElementById('vpName') && v.name) document.getElementById('vpName').value = v.name;
                    if (document.getElementById('vpPhone') && v.phone) document.getElementById('vpPhone').value = v.phone;
                    if (document.getElementById('vpTelegram') && v.telegram) document.getElementById('vpTelegram').value = v.telegram.replace('https://t.me/', '@');
                    if (document.getElementById('vpLocation') && v.location) document.getElementById('vpLocation').value = v.location;

                    const avatar = v.photo || v.avatar || '';
                    if (avatar && (avatar.startsWith('data:image') || avatar.startsWith('http'))) {
                        currentVendorPhotoBase64 = avatar;
                        const img = document.getElementById('vendorPhotoImg');
                        img.src = avatar;
                        img.style.display = 'block';
                        document.getElementById('vendorPhotoInitial').style.display = 'none';
                    }
                }
            } catch(e) {}
        }

        function handleVendorPhotoSelect(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                alert('Picture size must be under 2MB.');
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                currentVendorPhotoBase64 = e.target.result;
                const img = document.getElementById('vendorPhotoImg');
                img.src = currentVendorPhotoBase64;
                img.style.display = 'block';
                document.getElementById('vendorPhotoInitial').style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        async function saveVendorProfile(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveProfile');
            const statusEl = document.getElementById('profileStatusMsg');
            btn.disabled = true;
            btn.textContent = 'Saving...';
            statusEl.textContent = '';

            const payload = {
                username: currentVendorUser,
                name: document.getElementById('vpName').value.trim(),
                phone: document.getElementById('vpPhone').value.trim(),
                telegram: document.getElementById('vpTelegram').value.trim(),
                location: document.getElementById('vpLocation').value.trim(),
                avatar: currentVendorPhotoBase64
            };

            try {
                const res = await fetch('/api/vendors.php?action=update_vendor_profile', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data && data.success) {
                    statusEl.textContent = 'Contact details saved.';
                    setTimeout(() => { statusEl.textContent = ''; }, 3500);
                } else {
                    alert((data && data.message) || 'Failed to update details');
                }
            } catch(err) {
                alert('Network error saving details');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Save Details';
            }
        }

        async function loadVendorInventory() {
            try {
                const res = await fetch('/api/coupons.php?action=get_pins&_t=' + Date.now());
                const data = await res.json();
                const rawPins = data.coupons || data.pins || [];

                // Strictly filter to codes explicitly allocated to this vendor
                const u = currentVendorUser.toLowerCase();
                const vRecId = currentVendorRecord ? String(currentVendorRecord.id || '').toLowerCase().trim() : '';
                const vRecName = currentVendorRecord ? String(currentVendorRecord.name || '').toLowerCase().trim() : '';

                allVendorPins = rawPins.filter(p => {
                    const vId = String(p.vendor_id || p.vendorId || '').toLowerCase().trim();
                    const vName = String(p.vendor_name || p.vendorName || '').toLowerCase().trim();

                    // Reject unassigned / general pool codes
                    if (!vId && (!vName || vName === 'general pool')) return false;

                    const matchId = Boolean(vId && (vId === u || vId === ('v_' + u) || (vRecId && vId === vRecId)));
                    const matchName = Boolean(vName && (vName === u || (vRecName && vName === vRecName)));
                    return matchId || matchName;
                });

                let available = 0;
                let sold = 0;
                allVendorPins.forEach(p => {
                    if (p.is_used || p.isUsed) sold++;
                    else available++;
                });

                document.getElementById('kpiAvailablePins').textContent = available;
                document.getElementById('kpiSoldPins').textContent = sold;
                document.getElementById('kpiTotalPins').textContent = allVendorPins.length;

                renderPinsTable(allVendorPins);
                populateDispatchSelect(allVendorPins);
            } catch(e) {
                const tbody = document.getElementById('vendorPinsTableBody');
                if (tbody) tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-dim);padding:24px;">Failed to load codes.</td></tr>';
            }
        }

        function renderPinsTable(pins) {
            const tbody = document.getElementById('vendorPinsTableBody');
            if (!pins || pins.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-dim);padding:24px;">No activation codes assigned yet. Contact the admin to allocate codes to your account.</td></tr>';
                return;
            }

            tbody.innerHTML = pins.map(p => {
                const isUsed = Boolean(p.is_used || p.isUsed);
                const statusBadge = isUsed 
                    ? '<span class="badge-used">Used</span>'
                    : '<span class="badge-available">Available</span>';
                const redeemedBy = p.used_by || p.usedBy || '—';
                const dateCreated = (p.created_at || '').substring(0, 10) || 'Recently';

                return `
                    <tr>
                        <td><span class="code-box">${escapeHtml(p.code)}</span></td>
                        <td>${escapeHtml(p.type || 'AFF')}</td>
                        <td>${statusBadge}</td>
                        <td style="color:var(--text-dim);">${escapeHtml(redeemedBy)}</td>
                        <td style="color:var(--text-dim);font-size:0.78rem;">${escapeHtml(dateCreated)}</td>
                        <td>
                            <button type="button" class="btn-copy-code" onclick="copySingleCode('${escapeHtml(p.code)}')">Copy</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function populateDispatchSelect(pins) {
            const select = document.getElementById('dispatchPinSelect');
            const unused = pins.filter(p => !p.is_used && !p.isUsed);
            if (unused.length === 0) {
                select.innerHTML = '<option value="">No available codes in stock</option>';
                return;
            }
            select.innerHTML = '<option value="">Select a code...</option>' + 
                unused.map(p => `<option value="${escapeHtml(p.code)}">${escapeHtml(p.code)}</option>`).join('');
        }

        function filterPinsTable() {
            const q = document.getElementById('searchPinInput').value.trim().toUpperCase();
            if (!q) {
                renderPinsTable(allVendorPins);
                return;
            }
            const filtered = allVendorPins.filter(p => (p.code || '').toUpperCase().includes(q));
            renderPinsTable(filtered);
        }

        function copySingleCode(code) {
            navigator.clipboard.writeText(code);
            alert(`Copied ${code}`);
        }

        function copyAllUnusedCodes() {
            const unused = allVendorPins.filter(p => !p.is_used && !p.isUsed).map(p => p.code);
            if (unused.length === 0) {
                alert('No available codes to copy.');
                return;
            }
            navigator.clipboard.writeText(unused.join('\n'));
            alert(`Copied ${unused.length} code(s).`);
        }

        function getDispatchText() {
            const name = document.getElementById('dispatchBuyerName').value.trim() || 'Customer';
            const pin = document.getElementById('dispatchPinSelect').value;
            if (!pin) {
                alert('Please select an available code first.');
                return null;
            }
            const regUrl = window.location.origin + '/register.php?pin=' + encodeURIComponent(pin);
            return `Hello ${name}, here is your KairusNetwork activation code: ${pin}\n\nRegister here:\n${regUrl}`;
        }

        function copyDispatchMessage() {
            const text = getDispatchText();
            if (!text) return;
            navigator.clipboard.writeText(text);
            alert('Message copied to clipboard.');
        }

        function openWhatsAppDispatch() {
            const text = getDispatchText();
            if (!text) return;
            window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(text), '_blank');
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]);
        }

        document.addEventListener('DOMContentLoaded', async () => {
            await loadVendorProfile();
            await loadVendorInventory();
        });
    </script>
</body>
</html>
