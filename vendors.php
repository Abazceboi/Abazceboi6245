<?php
$pageTitle = 'Verified Vendors | INNOVATIONX Code Distributors';
$pageDesc = 'Purchase your activation coupon PIN from certified WhatsApp vendors across Nigeria or access your vendor inventory vault.';
require_once __DIR__ . '/includes/header.php';

$vendors = [
    [
        'id' => 'v1',
        'name' => 'Emmanuel Eze',
        'location' => 'Lagos / National (GTBank, OPay, Kuda)',
        'rating' => 5.0,
        'codes' => '2,400+ Codes Sold',
        'phone' => '2348012345678',
        'avatar' => '#0284C7'
    ],
    [
        'id' => 'v2',
        'name' => 'Fatima Bello',
        'location' => 'Abuja / Northern Region (Access Bank, Palmpay)',
        'rating' => 4.9,
        'codes' => '1,850+ Codes Sold',
        'phone' => '2348023456789',
        'avatar' => '#38BDF8'
    ],
    [
        'id' => 'v3',
        'name' => 'Tunde Adeyemi',
        'location' => 'Ibadan / South West (Zenith, Moniepoint)',
        'rating' => 4.9,
        'codes' => '1,420+ Codes Sold',
        'phone' => '2348034567890',
        'avatar' => '#0369A1'
    ]
];
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <a href="index.php#features-bar" class="btn-back-home" aria-label="Back to Home">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Home</span>
        </a>
        <h1 class="hero-title" style="font-size:clamp(2.4rem, 4.5vw, 3.8rem);margin-bottom:16px">
            Official Verified <span class="glow-word">Vendors.</span>
        </h1>
        <p class="hero-desc" style="max-width:640px;margin:0 auto 24px">
            Purchase your activation coupon PIN directly from certified distributors via WhatsApp. Instant delivery guaranteed.
        </p>

        <!-- Vendor Inventory Vault Trigger Button -->
        <div style="display:flex;justify-content:center;margin-bottom:10px">
            <button type="button" onclick="openVendorVaultModal()" class="btn-dash-action" style="background:rgba(56,189,248,0.12);color:#38BDF8;border:1px solid rgba(56,189,248,0.35);padding:10px 22px;border-radius:12px;font-size:0.85rem;font-weight:800;gap:8px;box-shadow:0 4px 16px rgba(56,189,248,0.2)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <span>Vendor Private PIN Vault (Vendors Only)</span>
            </button>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="section" style="padding-top:10px;padding-bottom:100px">
    <div class="container">
        <!-- Search Bar -->
        <div class="vendor-search-bar reveal" style="max-width:640px;margin:0 auto 36px">
            <div style="position:relative">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--text-gray)"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="vendorSearchInput" class="form-input" placeholder="Search vendor by name, location, or bank..." oninput="filterVendors(this.value)" style="padding-left:46px;border-radius:var(--radius-full);background:rgba(255,255,255,0.06);border:1px solid var(--white-border)">
            </div>
        </div>

        <!-- Vendors Grid -->
        <div class="vendors-grid reveal" id="vendorsGrid" style="max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:20px">
            <?php foreach ($vendors as $v): ?>
            <div class="vendor-card" data-search="<?= strtolower($v['name'] . ' ' . $v['location']) ?>" style="background:rgba(13,21,40,0.92);border:1px solid rgba(56,189,248,0.2);border-radius:16px;padding:24px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 8px 30px rgba(2,6,23,0.3)">
                <div>
                    <div class="vendor-top" style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
                        <div class="vendor-avatar" style="width:48px;height:48px;border-radius:12px;background:<?= htmlspecialchars($v['avatar']) ?>;color:#FFF;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.1rem">
                            <?= strtoupper(substr($v['name'], 0, 1)) ?>
                        </div>
                        <div class="vendor-meta">
                            <h3 class="vendor-name" style="font-size:1.05rem;font-weight:800;color:#FFFFFF;margin-bottom:3px"><?= htmlspecialchars($v['name']) ?></h3>
                            <div class="vendor-location" style="font-size:0.75rem;color:#7DD3FC;line-height:1.3"><?= htmlspecialchars($v['location']) ?></div>
                        </div>
                    </div>
                    
                    <div class="vendor-stats-row" style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:rgba(10,16,32,0.6);border:1px solid rgba(56,189,248,0.12);border-radius:10px;margin-bottom:18px">
                        <div class="vendor-rating" style="display:flex;align-items:center;gap:5px;font-size:0.78rem;font-weight:800;color:#38BDF8">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#38BDF8" stroke="#38BDF8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <span><?= number_format($v['rating'], 1) ?> Rating</span>
                        </div>
                        <div class="vendor-sales" style="font-size:0.75rem;font-weight:700;color:#94A3B8"><?= htmlspecialchars($v['codes']) ?></div>
                    </div>
                </div>

                <a href="https://wa.me/<?= htmlspecialchars($v['phone']) ?>?text=Hello%20<?= urlencode($v['name']) ?>,%20I%20want%20to%20buy%20an%20INNOVATIONX%20Activation%20Coupon%20Code" target="_blank" rel="noopener noreferrer" class="btn-vendor-chat" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;background:linear-gradient(135deg, #0284C7, #38BDF8);color:#FFF;border-radius:10px;font-weight:800;font-size:0.85rem;text-decoration:none;box-shadow:0 4px 14px rgba(56,189,248,0.3)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                    <span>Buy PIN on WhatsApp</span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<!-- VENDOR PRIVATE INVENTORY VAULT MODAL (EXCLUSIVE FOR VENDORS) -->
<div id="vendorVaultModalOverlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(5,7,15,0.85);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;padding:20px">
    <div style="background:rgba(13,21,40,0.98);border:1px solid rgba(56,189,248,0.3);border-top:3px solid #38BDF8;border-radius:16px;max-width:560px;width:100%;padding:26px;box-shadow:0 20px 60px rgba(0,0,0,0.7)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(56,189,248,0.15)">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:36px;height:36px;border-radius:10px;background:rgba(56,189,248,0.15);color:#38BDF8;display:flex;align-items:center;justify-content:center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <div>
                    <h3 style="font-size:1.1rem;font-weight:900;color:#FFFFFF;line-height:1.2">Vendor PIN Inventory Vault</h3>
                    <p style="font-size:0.72rem;color:#7DD3FC">Exclusive distributor access to allocated wholesale activation codes</p>
                </div>
            </div>
            <button type="button" onclick="closeVendorVaultModal()" style="background:none;border:none;color:#94A3B8;cursor:pointer;font-size:1.4rem;line-height:1">&times;</button>
        </div>

        <!-- Vendor Selection Screen -->
        <div id="vendorAuthSection">
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:0.75rem;font-weight:800;color:#FFFFFF;margin-bottom:6px;text-transform:uppercase">Select Verified Vendor Profile</label>
                <select id="vaultVendorSelect" class="form-input" style="width:100%;height:42px;background:rgba(255,255,255,0.06);border:1px solid rgba(56,189,248,0.3);color:#FFFFFF;padding:0 12px;border-radius:10px;font-size:0.85rem">
                    <option value="">-- Choose Your Official Profile --</option>
                    <option value="v1">Emmanuel Eze (Lagos • 08012345678)</option>
                    <option value="v2">Fatima Bello (Abuja • 08023456789)</option>
                    <option value="v3">Tunde Adeyemi (Ibadan • 08034567890)</option>
                </select>
            </div>
            <div style="margin-bottom:18px">
                <label style="display:block;font-size:0.75rem;font-weight:800;color:#FFFFFF;margin-bottom:6px;text-transform:uppercase">Vendor Security Access Code</label>
                <input type="password" id="vaultVendorPin" class="form-input" placeholder="Enter default PIN (1234)" value="1234" style="width:100%;height:42px;background:rgba(255,255,255,0.06);border:1px solid rgba(56,189,248,0.3);color:#FFFFFF;padding:0 12px;border-radius:10px;font-size:0.85rem">
                <span style="font-size:0.68rem;color:#7DD3FC;display:block;margin-top:4px">Certified vendors access their exclusive assigned codes only.</span>
            </div>
            <button type="button" onclick="unlockVendorVault()" class="btn-primary" style="width:100%;height:44px;display:flex;align-items:center;justify-content:center;gap:8px;font-weight:800">
                <span>Unlock Assigned PIN Inventory</span>
            </button>
        </div>

        <!-- Vendor Inventory Results Screen (Segregated) -->
        <div id="vendorInventorySection" style="display:none">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;background:rgba(56,189,248,0.08);border:1px solid rgba(56,189,248,0.25);border-radius:10px;padding:10px 14px">
                <div>
                    <span style="font-size:0.7rem;color:#7DD3FC;text-transform:uppercase;font-weight:800;display:block">Active Vendor</span>
                    <strong id="activeVaultVendorName" style="color:#FFFFFF;font-size:0.95rem">Emmanuel Eze</strong>
                </div>
                <div style="text-align:right">
                    <span style="font-size:0.7rem;color:#7DD3FC;text-transform:uppercase;font-weight:800;display:block">Assigned Balance</span>
                    <span id="activeVaultPinCount" style="color:#38BDF8;font-weight:900;font-size:1.1rem">0 PINs</span>
                </div>
            </div>

            <!-- PINs List -->
            <div id="vaultPinsList" style="max-height:220px;overflow-y:auto;display:flex;flex-direction:column;gap:6px;margin-bottom:14px;padding-right:2px">
                <!-- Dynamically injected -->
            </div>

            <div style="display:flex;gap:8px">
                <button type="button" onclick="copyAllVendorVaultPins()" class="btn-dash-action" style="flex:1;height:40px;background:linear-gradient(135deg, #0284C7, #38BDF8);color:#FFF;font-weight:800;border:none;border-radius:8px">
                    Copy All My Assigned PINs
                </button>
                <button type="button" onclick="lockVendorVault()" class="btn-dash-action" style="padding:0 14px;background:rgba(255,255,255,0.06);color:#FFF;border:1px solid rgba(255,255,255,0.12);border-radius:8px">
                    Switch Vendor
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function filterVendors(query) {
    const q = (query || '').toLowerCase().trim();
    document.querySelectorAll('#vendorsGrid .vendor-card').forEach(card => {
        const text = card.getAttribute('data-search') || '';
        card.style.display = (!q || text.includes(q)) ? 'flex' : 'none';
    });
}

function openVendorVaultModal() {
    const modal = document.getElementById('vendorVaultModalOverlay');
    if (modal) modal.style.display = 'flex';
}

function closeVendorVaultModal() {
    const modal = document.getElementById('vendorVaultModalOverlay');
    if (modal) modal.style.display = 'none';
}

function unlockVendorVault() {
    const select = document.getElementById('vaultVendorSelect');
    const vendorId = select ? select.value : '';
    const pin = document.getElementById('vaultVendorPin')?.value;

    if (!vendorId) {
        alert('Please select your official vendor profile.');
        return;
    }

    const vendorMap = {
        'v1': 'Emmanuel Eze',
        'v2': 'Fatima Bello',
        'v3': 'Tunde Adeyemi'
    };

    const vendorName = vendorMap[vendorId] || 'Vendor';

    // Retrieve sitewide stored coupons
    let storedCoupons = JSON.parse(localStorage.getItem('ix_coupons') || '[]');
    if (storedCoupons.length === 0) {
        storedCoupons = [
            { code: 'INX-JOB-3104-8842', channel: 'UPLOADER', typeLabel: 'Jobber Quota PIN', vendorId: 'v1', vendorName: 'Emmanuel Eze' },
            { code: 'INX-AFF-5521-4409', channel: 'AFFILIATE', typeLabel: 'Member Registration PIN', vendorId: 'v2', vendorName: 'Fatima Bello' },
            { code: 'INX-AFF-4412-9908', channel: 'AFFILIATE', typeLabel: 'Affiliate VIP Promo PIN', vendorId: 'v3', vendorName: 'Tunde Adeyemi' },
            { code: 'INX-AFF-9012-7741', channel: 'AFFILIATE', typeLabel: 'Member Registration PIN', vendorId: 'v1', vendorName: 'Emmanuel Eze' },
            { code: 'INX-UPL-1892-6630', channel: 'UPLOADER', typeLabel: 'Uploader Upgrade PIN', vendorId: 'v1', vendorName: 'Emmanuel Eze' }
        ];
        localStorage.setItem('ix_coupons', JSON.stringify(storedCoupons));
    }

    // STRICT SEGREGATION: Filter ONLY coupons assigned to this specific vendor
    const vendorCoupons = storedCoupons.filter(c => c.vendorId === vendorId);

    document.getElementById('activeVaultVendorName').textContent = vendorName;
    document.getElementById('activeVaultPinCount').textContent = `${vendorCoupons.length} PINs`;

    const listEl = document.getElementById('vaultPinsList');
    if (vendorCoupons.length === 0) {
        listEl.innerHTML = `
            <div style="padding:24px;text-align:center;color:#94A3B8;background:rgba(10,16,32,0.6);border:1px dashed rgba(56,189,248,0.2);border-radius:10px">
                <p style="font-size:0.85rem;margin-bottom:6px;color:#FFF;font-weight:700">No PINs Assigned To ${vendorName}</p>
                <p style="font-size:0.75rem;margin:0">Administration has not allocated wholesale codes to your account yet. Generate or assign codes from the Admin Center.</p>
            </div>
        `;
    } else {
        listEl.innerHTML = vendorCoupons.map(c => `
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:rgba(10,16,32,0.8);border:1px solid rgba(56,189,248,0.18);border-radius:8px;font-size:0.78rem">
                <div>
                    <span style="font-family:monospace;font-weight:800;color:#38BDF8">${c.code}</span>
                    <span style="font-size:0.7rem;color:#7DD3FC;margin-left:8px;font-weight:600">${c.typeLabel}</span>
                </div>
                <button type="button" class="btn-dash-action" onclick="copyToClipboard('${c.code}')" style="padding:3px 10px;font-size:0.7rem;background:rgba(56,189,248,0.15);color:#FFF;border:none;border-radius:5px">
                    Copy
                </button>
            </div>
        `).join('');
    }

    document.getElementById('vendorAuthSection').style.display = 'none';
    document.getElementById('vendorInventorySection').style.display = 'block';
}

function lockVendorVault() {
    document.getElementById('vendorAuthSection').style.display = 'block';
    document.getElementById('vendorInventorySection').style.display = 'none';
}

function copyAllVendorVaultPins() {
    const listEl = document.getElementById('vaultPinsList');
    const codes = [];
    listEl.querySelectorAll('span[style*="monospace"]').forEach(sp => {
        codes.push(sp.textContent.trim());
    });
    if (codes.length === 0) {
        alert('No PINs to copy.');
        return;
    }
    copyToClipboard(codes.join('\n'));
    alert(`Copied all ${codes.length} assigned PINs to clipboard!`);
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text);
    } else {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
