<?php
/**
 * INNOVATIONX — Verified Vendor Wholesale PIN Terminal
 * Dedicated inventory & dispatch management portal for official coupon PIN distributors.
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
$isVendor = $isAdmin || ($userRole === 'vendor');

// If not a vendor yet, show the Vendor Accreditation Required page
if (!$isVendor) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vendor Accreditation Required | <?= htmlspecialchars(APP_NAME) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800;900&display=swap" rel="stylesheet">
        <style>
            *{margin:0;padding:0;box-sizing:border-box}
            body{font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#080B14;color:#F8FAFC;padding:20px}
            .card{background:#0F172A;border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:40px;max-width:500px;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,0.6)}
            .icon{width:64px;height:64px;border-radius:18px;background:rgba(245,158,11,0.15);color:#F59E0B;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.8rem}
            h1{font-size:1.45rem;font-weight:900;margin-bottom:8px}
            p{font-size:0.88rem;color:#94A3B8;margin-bottom:24px;line-height:1.5}
            .btn-primary{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:10px;background:#F59E0B;color:#000000;font-weight:800;text-decoration:none;font-size:0.9rem;border:none;cursor:pointer}
            .btn-secondary{display:inline-block;margin-top:14px;color:#64748B;font-size:0.82rem;text-decoration:none}
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">💎</div>
            <h1>Vendor Portal Access</h1>
            <p>The Vendor Terminal is reserved for accredited wholesale distributors to purchase wholesale coupon PINs, manage inventory stock, and track sales dispatches.</p>
            <a href="dashboard.php" class="btn-primary">Return to Member Dashboard</a>
            <div><a href="https://t.me/innovationx_support" target="_blank" class="btn-secondary">Apply for Verified Vendor License ↗</a></div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$pageTitle = 'Vendor Wholesale PIN Terminal | ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@700;800&family=SF+Mono,Consolas,monospace&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #080B14;
            --bg-card: #0F172A;
            --bg-surface: #141E33;
            --border-subtle: rgba(255, 255, 255, 0.08);
            --primary: #F59E0B;
            --primary-glow: rgba(245, 158, 11, 0.25);
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --font-main: 'Plus Jakarta Sans', sans-serif;
            --font-display: 'Space Grotesk', sans-serif;
            --font-mono: Consolas, monospace;
            --radius-md: 14px;
            --radius-lg: 20px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg-base); color: var(--text-main); font-family: var(--font-main); min-height: 100vh; padding-bottom: 60px; }

        .vendor-nav {
            position: sticky; top: 0; z-index: 100;
            background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-subtle);
            padding: 14px 24px; display: flex; align-items: center; justify-content: space-between;
        }

        .brand-logo-area { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #FFFFFF; }
        .brand-badge { width: 36px; height: 36px; border-radius: 9px; background: #F59E0B; color: #000000; display: flex; align-items: center; justify-content: center; font-weight: 900; }
        .brand-title { font-family: var(--font-display); font-weight: 800; font-size: 1.15rem; }

        .btn-dash-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 9px;
            background: rgba(56, 189, 248, 0.12); color: #38BDF8;
            border: 1px solid rgba(56, 189, 248, 0.25); font-weight: 700; font-size: 0.82rem; text-decoration: none;
        }
        .btn-dash-back:hover { background: #0284C7; color: #FFFFFF; }

        .shell { max-width: 1200px; margin: 0 auto; padding: 24px 20px; }

        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px; }
        .page-header h1 { font-size: 1.65rem; font-weight: 900; letter-spacing: -0.5px; }
        .page-header p { font-size: 0.85rem; color: var(--text-muted); margin-top: 2px; }

        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
        @media (max-width: 900px) { .grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .grid-4 { grid-template-columns: 1fr; } }

        .kpi-card {
            background: var(--bg-card); border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md); padding: 20px;
        }
        .kpi-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); }
        .kpi-val { font-family: var(--font-display); font-size: 1.65rem; font-weight: 800; color: #FFFFFF; margin-top: 6px; }

        .card-panel {
            background: var(--bg-card); border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg); padding: 24px; margin-bottom: 24px;
        }
        .card-title { font-size: 1.1rem; font-weight: 800; margin-bottom: 4px; color: #FFFFFF; }
        .card-desc { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 20px; }

        .filter-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
        .search-box {
            padding: 10px 14px; border-radius: 8px; background: var(--bg-surface);
            border: 1px solid var(--border-subtle); color: #FFFFFF; font-size: 0.85rem; outline: none; width: 260px;
        }

        .table-wrap { width: 100%; overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
        .data-table th { text-align: left; padding: 12px 14px; font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); }
        .data-table td { padding: 14px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); }

        .code-pill {
            font-family: var(--font-mono); font-size: 0.95rem; font-weight: 700;
            color: #38BDF8; letter-spacing: 1px;
        }

        .btn-copy {
            padding: 5px 10px; border-radius: 6px; background: rgba(56,189,248,0.12); color: #38BDF8;
            border: 1px solid rgba(56,189,248,0.25); font-weight: 700; font-size: 0.75rem; cursor: pointer;
        }

        .btn-action-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 11px 20px; border-radius: 8px; background: #F59E0B; color: #000000;
            font-weight: 800; font-size: 0.88rem; border: none; cursor: pointer; transition: all 0.2s;
        }
        .btn-action-primary:hover { background: #D97706; color: #FFFFFF; transform: translateY(-1px); }

        .btn-whatsapp-dispatch {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 18px; border-radius: 8px; background: #25D366; color: #FFFFFF;
            font-weight: 800; font-size: 0.85rem; border: none; cursor: pointer; text-decoration: none;
        }
    </style>
</head>
<body>
    <nav class="vendor-nav">
        <a href="vendor_dashboard.php" class="brand-logo-area">
            <div class="brand-badge">VP</div>
            <div class="brand-title">VENDOR <span>TERMINAL</span></div>
        </a>
        <div style="display:flex;align-items:center;gap:12px">
            <span style="font-size:0.8rem;color:#94A3B8">Vendor: <strong>@<?= htmlspecialchars($username) ?></strong></span>
            <a href="dashboard.php" class="btn-dash-back">← Member Dashboard</a>
        </div>
    </nav>

    <div class="shell">
        <div class="page-header">
            <div>
                <h1>Wholesale Coupon PIN Inventory</h1>
                <p>Manage activation codes, dispatch PINs to buyers via WhatsApp/SMS, and request wholesale restocks.</p>
            </div>
            <button type="button" class="btn-action-primary" onclick="copyUnusedPins()">
                <span>Copy Bulk Unused PINs</span>
            </button>
        </div>

        <!-- Vendor Analytics -->
        <div class="grid-4">
            <div class="kpi-card">
                <div class="kpi-label">Available PINs in Stock</div>
                <div class="kpi-val" id="kpiAvailablePins" style="color:#10B981">0</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Redeemed / Sold</div>
                <div class="kpi-val" id="kpiSoldPins">0</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total PINs Managed</div>
                <div class="kpi-val" id="kpiTotalPins">0</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Wholesale Price (Admin Sync)</div>
                <div class="kpi-val" style="color:#F59E0B">₦800 / PIN</div>
            </div>
        </div>

        <!-- Vendor Public Profile & Handle Settings -->
        <div class="card-panel" style="border: 1px solid rgba(245, 158, 11, 0.35); background: linear-gradient(180deg, rgba(245,158,11,0.05) 0%, rgba(15,23,42,0.95) 100%); margin-bottom: 24px;">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
                <div>
                    <div class="card-title" style="display:flex; align-items:center; gap:8px;">
                        <span>Vendor Directory Identity & Handles</span>
                        <span style="font-size:0.7rem; font-weight:800; background:rgba(245,158,11,0.2); color:#F59E0B; padding:3px 8px; border-radius:6px; text-transform:uppercase;">Self-Managed</span>
                    </div>
                    <div class="card-desc" style="margin:0;">Only you can configure your vendor profile, WhatsApp number, Telegram handle, and picture shown on the public directory.</div>
                </div>
                <div id="vendorProfileSaveStatus" style="font-size:0.85rem; font-weight:700; color:#10B981;"></div>
            </div>

            <form id="vendorProfileForm" onsubmit="saveVendorProfile(event)">
                <div style="display:flex; gap:24px; flex-wrap:wrap; align-items:flex-start;">
                    <!-- Photo Upload Area -->
                    <div style="display:flex; flex-direction:column; align-items:center; text-align:center; min-width:140px;">
                        <div id="vendorAvatarContainer" style="width:96px; height:96px; border-radius:20px; overflow:hidden; border:2px solid #F59E0B; background:#1E293B; display:flex; align-items:center; justify-content:center; margin-bottom:10px; box-shadow:0 8px 24px rgba(0,0,0,0.4);">
                            <img id="vendorPhotoImg" src="" alt="Vendor Photo" style="width:100%; height:100%; object-fit:cover; display:none;">
                            <div id="vendorPhotoInitial" style="font-size:2.2rem; font-weight:900; color:#F59E0B;"><?= strtoupper(substr($username, 0, 1)) ?></div>
                        </div>
                        <input type="file" id="vendorPhotoFile" accept="image/*" style="display:none" onchange="handleVendorPhotoSelect(this)">
                        <button type="button" class="btn-copy" onclick="document.getElementById('vendorPhotoFile').click()" style="padding:6px 12px; font-size:0.78rem;">
                            📷 Upload Picture
                        </button>
                        <span style="font-size:0.7rem; color:#94A3B8; margin-top:4px;">JPG, PNG, WebP (Max 2MB)</span>
                    </div>

                    <!-- Input Fields -->
                    <div style="flex:1; min-width:280px; display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div>
                            <label style="font-size:0.75rem;font-weight:700;color:#94A3B8;text-transform:uppercase;display:block;margin-bottom:6px">Vendor / Business Name</label>
                            <input type="text" id="vpName" class="search-box" style="width:100%" placeholder="e.g. Samuel Okon Codes">
                        </div>
                        <div>
                            <label style="font-size:0.75rem;font-weight:700;color:#94A3B8;text-transform:uppercase;display:block;margin-bottom:6px">WhatsApp Number (e.g. 2348012345678)</label>
                            <input type="text" id="vpPhone" class="search-box" style="width:100%" placeholder="2348012345678">
                        </div>
                        <div>
                            <label style="font-size:0.75rem;font-weight:700;color:#94A3B8;text-transform:uppercase;display:block;margin-bottom:6px">Telegram Handle (e.g. @my_handle)</label>
                            <input type="text" id="vpTelegram" class="search-box" style="width:100%" placeholder="@samuel_pins">
                        </div>
                        <div>
                            <label style="font-size:0.75rem;font-weight:700;color:#94A3B8;text-transform:uppercase;display:block;margin-bottom:6px">Coverage / Location & Banks</label>
                            <input type="text" id="vpLocation" class="search-box" style="width:100%" placeholder="Lagos / National (GTBank, OPay, Palmpay)">
                        </div>
                    </div>
                </div>

                <div style="margin-top:18px; display:flex; justify-content:flex-end;">
                    <button type="submit" id="btnSaveVendorProfile" class="btn-action-primary">
                        💾 Save Profile & Update Handles
                    </button>
                </div>
            </form>
        </div>

        <!-- WhatsApp Quick Dispatch Tool -->
        <div class="card-panel">
            <div class="card-title">Instant WhatsApp / SMS Dispatch Generator</div>
            <div class="card-desc">Quickly generate a formatted onboarding message for buyers purchasing activation codes.</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px">
                <div>
                    <label style="font-size:0.75rem;font-weight:700;color:#94A3B8;text-transform:uppercase;display:block;margin-bottom:6px">Customer / Buyer Name</label>
                    <input type="text" id="dispatchBuyerName" class="search-box" style="width:100%" placeholder="e.g. Samuel Okon">
                </div>
                <div>
                    <label style="font-size:0.75rem;font-weight:700;color:#94A3B8;text-transform:uppercase;display:block;margin-bottom:6px">Select Available PIN</label>
                    <select id="dispatchPinSelect" class="search-box" style="width:100%">
                        <option value="">Select an available PIN code...</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:10px">
                <button type="button" class="btn-action-primary" onclick="copyDispatchMessage()">Copy Message Template</button>
                <button type="button" class="btn-whatsapp-dispatch" onclick="openWhatsAppDispatch()">Open in WhatsApp ↗</button>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="card-panel">
            <div class="filter-row">
                <div>
                    <div class="card-title" style="margin:0">Active PIN Repository</div>
                    <div class="card-desc" style="margin:0">Full registry of your allocated activation PINs.</div>
                </div>
                <input type="text" id="searchPinInput" class="search-box" placeholder="Search by PIN code..." oninput="filterPinsTable()">
            </div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>PIN Code</th>
                            <th>Tier / Type</th>
                            <th>Status</th>
                            <th>Redeemed By</th>
                            <th>Date Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="vendorPinsTableBody">
                        <tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:24px">Loading inventory...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const currentVendorUser = '<?= htmlspecialchars($username) ?>';
        const isSuperAdmin = <?= json_encode($isAdmin) ?>;
        let allVendorPins = [];
        let currentVendorPhotoBase64 = '';

        async function loadVendorProfile() {
            try {
                const res = await fetch('/api/vendors.php?action=get_vendor_profile&username=' + encodeURIComponent(currentVendorUser));
                const data = await res.json();
                if (data && data.vendor) {
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
            } catch(e) { console.error('Failed to load vendor profile:', e); }
        }

        function handleVendorPhotoSelect(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            if (file.size > 2.5 * 1024 * 1024) {
                alert('Image too large. Please select a picture under 2MB.');
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
            const btn = document.getElementById('btnSaveVendorProfile');
            const statusEl = document.getElementById('vendorProfileSaveStatus');
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
                    statusEl.textContent = '✓ Profile & Handles Saved!';
                    setTimeout(() => { statusEl.textContent = ''; }, 4000);
                    alert('Your vendor handles, WhatsApp, and picture have been updated successfully!');
                } else {
                    alert((data && data.message) || 'Failed to save profile');
                }
            } catch(err) {
                alert('Failed to save profile: ' + (err.message || 'Server error'));
            } finally {
                btn.disabled = false;
                btn.textContent = '💾 Save Profile & Update Handles';
            }
        }

        async function loadVendorInventory() {
            try {
                const res = await fetch('/api/coupons.php?action=get_pins');
                const data = await res.json();
                const rawPins = data.coupons || [];

                // Filter to pins allocated to this vendor unless super admin
                if (isSuperAdmin) {
                    allVendorPins = rawPins;
                } else {
                    allVendorPins = rawPins.filter(p => {
                        const vId = (p.vendor_id || p.vendorId || '').toLowerCase();
                        const vName = (p.vendor_name || p.vendorName || '').toLowerCase();
                        const u = currentVendorUser.toLowerCase();
                        return vId === u || vName === u || vId === ('v_' + u);
                    });
                }

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
            } catch(e) {}
        }

        function renderPinsTable(pins) {
            const tbody = document.getElementById('vendorPinsTableBody');
            if (pins.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:24px">No PIN codes found in inventory.</td></tr>';
                return;
            }

            tbody.innerHTML = pins.map(p => {
                const isUsed = Boolean(p.is_used || p.isUsed);
                const statusBadge = isUsed 
                    ? '<span style="color:#94A3B8;font-weight:700">Redeemed</span>'
                    : '<span style="color:#10B981;font-weight:700">● Available</span>';
                const redeemedBy = p.used_by || p.usedBy || '—';

                return `
                    <tr>
                        <td><span class="code-pill">${escapeHtml(p.code)}</span></td>
                        <td><span style="font-size:0.75rem;padding:3px 8px;border-radius:4px;background:rgba(245,158,11,0.15);color:#FBBF24">${escapeHtml(p.type || 'AFFILIATE')}</span></td>
                        <td>${statusBadge}</td>
                        <td style="color:#94A3B8">${escapeHtml(redeemedBy)}</td>
                        <td style="color:#94A3B8;font-size:0.78rem">${escapeHtml((p.created_at || '').substring(0, 10) || 'Recently')}</td>
                        <td>
                            <button type="button" class="btn-copy" onclick="copySinglePin('${escapeHtml(p.code)}')">Copy</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function populateDispatchSelect(pins) {
            const select = document.getElementById('dispatchPinSelect');
            const unused = pins.filter(p => !p.is_used && !p.isUsed);
            select.innerHTML = '<option value="">Select an available PIN code...</option>' + 
                unused.map(p => `<option value="${escapeHtml(p.code)}">${escapeHtml(p.code)} (${p.type || 'AFFILIATE'})</option>`).join('');
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

        function copySinglePin(code) {
            navigator.clipboard.writeText(code);
            alert(`Copied ${code} to clipboard!`);
        }

        function copyUnusedPins() {
            const unused = allVendorPins.filter(p => !p.is_used && !p.isUsed).map(p => p.code);
            if (unused.length === 0) {
                alert('No unused PINs currently available in stock.');
                return;
            }
            navigator.clipboard.writeText(unused.join('\n'));
            alert(`Copied ${unused.length} available PIN codes to clipboard!`);
        }

        function getDispatchText() {
            const name = document.getElementById('dispatchBuyerName').value.trim() || 'Valued Member';
            const pin = document.getElementById('dispatchPinSelect').value;
            if (!pin) {
                alert('Please select an available PIN code from the dropdown first.');
                return null;
            }
            return `Hello ${name}! Here is your official INNOVATIONX Registration Activation PIN:\n\n🔑 PIN: ${pin}\n\n👉 Register and activate your account immediately at:\nhttps://innovationx.ng/register.php?pin=${pin}\n\nWelcome to high-yield daily earnings!`;
        }

        function copyDispatchMessage() {
            const text = getDispatchText();
            if (!text) return;
            navigator.clipboard.writeText(text);
            alert('WhatsApp/SMS message template copied to clipboard!');
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

        document.addEventListener('DOMContentLoaded', () => {
            loadVendorProfile();
            loadVendorInventory();
        });
    </script>
</body>
</html>
