const http = require('http');
const fs = require('fs');
const path = require('path');

const PORT = process.env.PORT || 5050;
const PUBLIC_DIR = path.resolve(__dirname);

const MIME_TYPES = {
    '.html': 'text/html; charset=UTF-8',
    '.htm': 'text/html; charset=UTF-8',
    '.php': 'text/html; charset=UTF-8',
    '.css': 'text/css; charset=UTF-8',
    '.js': 'application/javascript; charset=UTF-8',
    '.ts': 'application/typescript; charset=UTF-8',
    '.json': 'application/json; charset=UTF-8',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.jpeg': 'image/jpeg',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.ico': 'image/x-icon',
    '.webp': 'image/webp',
    '.woff': 'font/woff',
    '.woff2': 'font/woff2',
    '.ttf': 'font/ttf',
};

// Robust recursive PHP template processor for local Node server
function renderPhpFile(filePath, context = {}) {
    if (!fs.existsSync(filePath)) return '';
    if (!context.rootPage) {
        context.rootPage = path.basename(filePath, '.php');
    }
    let content = fs.readFileSync(filePath, 'utf8');
    const currentDir = path.dirname(filePath);

    const titleMatch = content.match(/\$pageTitle\s*=\s*['"](.*?)['"];/);
    if (titleMatch) context.pageTitle = titleMatch[1];

    const descMatch = content.match(/\$pageDesc\s*=\s*['"](.*?)['"];/);
    if (descMatch) context.pageDesc = descMatch[1];

    const hideNavbarMatch = content.match(/\$hideNavbar\s*=\s*(true|false);/);
    if (hideNavbarMatch) context.hideNavbar = (hideNavbarMatch[1] === 'true');

    const hideFooterMatch = content.match(/\$hideFooter\s*=\s*(true|false);/);
    if (hideFooterMatch) context.hideFooter = (hideFooterMatch[1] === 'true');

    // Handle template conditional blocks for hideNavbar and hideFooter before includes
    if (context.hideNavbar) {
        content = content.replace(/<\?php\s+if\s*\(\s*empty\(\$hideNavbar\)\s*\)\s*:\s*\?>[\s\S]*?<\?php\s+endif;\s*\?>/g, '');
    }
    if (context.hideFooter) {
        content = content.replace(/<\?php\s+if\s*\(\s*empty\(\$hideFooter\)\s*\)\s*:\s*\?>[\s\S]*?<\?php\s+endif;\s*\?>/g, '');
    }

    content = content.replace(/(?:require_once|require|include_once|include)\s+__DIR__\s*\.\s*['"]([^'"]+)['"];?/g, (match, relPath) => {
        if (context.hideNavbar && relPath.includes('navbar.php')) {
            return '';
        }
        let targetPath = path.join(currentDir, relPath);
        if (!fs.existsSync(targetPath)) {
            targetPath = path.join(PUBLIC_DIR, relPath);
        }
        if (fs.existsSync(targetPath)) {
            let rendered = renderPhpFile(targetPath, context);
            if (context.hideFooter && relPath.includes('footer.php')) {
                rendered = rendered.replace(/<footer[\s\S]*?<\/footer>/gi, '');
            }
            return '?>' + rendered + '<?php ';
        }
        return '';
    });

    content = content.replace(/<\?=\s*htmlspecialchars\(APP_NAME\)\s*\?>/g, 'INNOVATIONX');
    content = content.replace(/<\?=\s*htmlspecialchars\(APP_TAGLINE\)\s*\?>/g, 'Where SoftLife Meets High-Yield Daily Earnings');
    content = content.replace(/<\?=\s*htmlspecialchars\(APP_VERSION\)\s*\?>/g, '2.0.0');
    content = content.replace(/<\?=\s*htmlspecialchars\(SUPPORT_EMAIL\)\s*\?>/g, 'Supportinnovationx@gmail.com');
    content = content.replace(/<\?=\s*MEMBERSHIP_FEE\s*\?>/g, '500');
    content = content.replace(/<\?=\s*TASK_POINTS_RATE\s*\?>/g, '150');
    content = content.replace(/<\?=\s*REFERRAL_CASH_BONUS\s*\?>/g, '250');
    content = content.replace(/<\?=\s*number_format\(MIN_WITHDRAWAL_NAIRA\)\s*\?>/g, '5,000');
    content = content.replace(/<\?=\s*WHATSAPP_SUPPORT\s*\?>/g, '2347037765714');
    content = content.replace(/<\?=\s*htmlspecialchars\(\$pageTitle\)\s*\?>/g, context.pageTitle || 'INNOVATIONX | SoftLife Daily Earnings');
    content = content.replace(/<\?=\s*htmlspecialchars\(\$pageDesc\)\s*\?>/g, context.pageDesc || 'High-Yield Daily Earnings Platform');
    content = content.replace(/<\?=\s*htmlspecialchars\(\$username\)\s*\?>/g, 'Member');
    content = content.replace(/<\?=\s*htmlspecialchars\(\$pinFromQuery\)\s*\?>/g, '');

    const activePage = path.basename(filePath, '.php');
    content = content.replace(/<\?=\s*isActive\(['"]([^'"]+)['"],\s*\$currentPage\)\s*\?>/g, (m, pageName) => {
        return pageName === activePage ? 'active' : '';
    });

    content = content.replace(/<\?php[\s\S]*?\?>/g, '');
    content = content.replace(/<\?=[\s\S]*?\?>/g, '');

    if (['dashboard', 'admin', 'login', 'register'].includes(context.rootPage)) {
        content = content.replace(/<div class="payout-toast-container"[\s\S]*?<\/div>/g, '');
    }

    return content;
}

const server = http.createServer((req, res) => {
    let cleanUrl = req.url.split('?')[0];
    if (cleanUrl === '/' || cleanUrl === '') {
        cleanUrl = '/index.php';
    }

    // 1. Direct Synchronous API Router for /api/
    if (cleanUrl.startsWith('/api/')) {
        const urlObj = new URL(req.url, `http://${req.headers.host || 'localhost'}`);
        const action = urlObj.searchParams.get('action') || '';
        const configFile = path.join(PUBLIC_DIR, 'config', 'vtu_settings.json');

        let vtuConfig = {
            gateway_active: true,
            provider_name: 'omageneraldata',
            api_base_url: 'https://omageneraldata.com/api',
            api_key: '',
            api_secret: '',
            api_mode: 'sandbox',
            points_per_naira: 1.0,
            airtime_rates: { mtn: 97.0, airtel: 97.5, glo: 95.0, '9mobile': 96.0 },
            network_ids: { mtn: '1', glo: '2', airtel: '3', '9mobile': '4' },
            data_prices: {
                mtn: { '1GB': 250, '2GB': 490, '5GB': 1200, '10GB': 2350 },
                airtel: { '1GB': 260, '2GB': 510, '5GB': 1250, '10GB': 2450 },
                glo: { '1GB': 240, '2GB': 470, '5GB': 1150, '10GB': 2250 },
                '9mobile': { '1GB': 220, '2GB': 440, '5GB': 1100, '10GB': 2150 }
            }
        };
        if (fs.existsSync(configFile)) {
            try { vtuConfig = Object.assign(vtuConfig, JSON.parse(fs.readFileSync(configFile, 'utf8'))); } catch(e){}
        }

        const handleApi = (parsed) => {
            res.writeHead(200, {
                'Content-Type': 'application/json; charset=UTF-8',
                'Access-Control-Allow-Origin': '*',
                'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
                'Access-Control-Allow-Headers': 'Content-Type, Authorization'
            });

            if (cleanUrl.includes('content.php') || action === 'get_content' || action === 'save_content') {
                const contentFile = path.join(PUBLIC_DIR, 'config', 'site_content.json');
                let siteContent = {
                    card_cash_title: "Withdrawable Cash",
                    card_cash_sub: "From 10 paid referrals • Ready to cash out",
                    card_pts_title: "Task Points Wallet",
                    card_pts_sub: "≈ ₦5,400 Equiv / Direct data conversion",
                    card_paid_title: "Total Lifetime Paid",
                    card_paid_sub: "Transferred to Bank • 100% Automated",
                    landing_stat1_val: "₦148,500,000+",
                    landing_stat1_label: "Total Payouts Settled",
                    landing_stat2_val: "124,000+",
                    landing_stat2_label: "Active Daily Earners",
                    landing_stat3_val: "2.4 Seconds",
                    landing_stat3_label: "Average Payout Speed",
                    referral_card_title: "Exclusive ₦250 Referral Link",
                    referral_card_badge: "₦250 Cash / Invite",
                    referral_card_desc: "Share your personal link with friends. You earn instant ₦250 cash in your wallet the moment they register their membership pin.",
                    jobbers_hub_title: "Jobbers Opportunities & Daily Tasks",
                    jobbers_hub_desc: "Explore verified earning opportunities published by official uploaders. Perform the quick tasks, submit proof, and get credited in Task Points instantly.",
                    withdraw_card_title: "Request Bank Payout",
                    withdraw_min_badge: "Min: ₦5,000",
                    advert_card_title: "Place an Advert / Launch Campaign",
                    advert_card_badge: "Member Ads Hub",
                    advert_card_desc: "Promote your business, WhatsApp group, YouTube channel, or app to thousands of active INNOVATIONX members. Fund with Task Points or Referral Cash."
                };
                if (fs.existsSync(contentFile)) {
                    try { siteContent = Object.assign(siteContent, JSON.parse(fs.readFileSync(contentFile, 'utf8'))); } catch(e){}
                }

                if (req.method === 'POST' || action === 'save_content') {
                    siteContent = Object.assign(siteContent, parsed);
                    const configDir = path.dirname(contentFile);
                    if (!fs.existsSync(configDir)) fs.mkdirSync(configDir, { recursive: true });
                    fs.writeFileSync(contentFile, JSON.stringify(siteContent, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Site placeholder cards & content saved successfully.', content: siteContent }));
                    return;
                }

                res.end(JSON.stringify({ status: 'success', content: siteContent }));
                return;
            }

            if (cleanUrl.includes('features.php') || action === 'get_flags' || action === 'save_flags') {
                const flagsFile = path.join(PUBLIC_DIR, 'config', 'feature_flags.json');
                let flags = {
                    jobbers_tasks: true,
                    advertisements: true,
                    spin_wheel: true,
                    vtu_airtime: true,
                    sme_data: true,
                    crypto_update: true,
                    referrals: true,
                    withdrawals: true,
                    forecaster: true,
                    vendors: true
                };
                if (fs.existsSync(flagsFile)) {
                    try { flags = Object.assign(flags, JSON.parse(fs.readFileSync(flagsFile, 'utf8'))); } catch(e){}
                }

                if (req.method === 'POST' || action === 'save_flags') {
                    flags = Object.assign(flags, parsed);
                    const configDir = path.dirname(flagsFile);
                    if (!fs.existsSync(configDir)) fs.mkdirSync(configDir, { recursive: true });
                    fs.writeFileSync(flagsFile, JSON.stringify(flags, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Master Feature Flags saved successfully.', flags: flags }));
                    return;
                }

                res.end(JSON.stringify({ status: 'success', flags: flags }));
                return;
            }

            if (cleanUrl.includes('adverts.php') || action === 'get_adverts' || action === 'create_advert' || action === 'update_status' || action === 'delete_advert' || action === 'track_interaction') {
                const advertsFile = path.join(PUBLIC_DIR, 'config', 'advertisements.json');
                let adverts = [];
                if (fs.existsSync(advertsFile)) {
                    try { adverts = JSON.parse(fs.readFileSync(advertsFile, 'utf8')); } catch(e){}
                }

                if (action === 'create_advert' || (req.method === 'POST' && parsed.title)) {
                    const newAd = {
                        id: 'ADV-' + Math.floor(Math.random() * 900000 + 100000),
                        user_id: parsed.user_id || 'Member',
                        username: parsed.username || 'Member',
                        title: parsed.title || '',
                        description: parsed.description || '',
                        target_url: parsed.target_url || '',
                        video_url: parsed.video_url || '',
                        proof_type: parsed.proof_type || 'screenshot',
                        timer_seconds: parseInt(parsed.timer_seconds) || 20,
                        cost: parseFloat(parsed.cost) || 3000,
                        target_users: parseInt(parsed.target_users) || 100,
                        views: 1,
                        clicks: 0,
                        likes: 0,
                        pay_source: parsed.pay_source || 'deposit_balance',
                        status: parsed.status || 'pending',
                        admin_note: null,
                        created_at: new Date().toISOString(),
                        reviewed_at: parsed.status === 'active' ? new Date().toISOString() : null
                    };
                    adverts.unshift(newAd);
                    const configDir = path.dirname(advertsFile);
                    if (!fs.existsSync(configDir)) fs.mkdirSync(configDir, { recursive: true });
                    fs.writeFileSync(advertsFile, JSON.stringify(adverts, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Advert campaign submitted successfully.', advert: newAd }));
                    return;
                }

                if (action === 'track_interaction') {
                    const id = parsed.id;
                    const type = parsed.type || 'view';
                    adverts.forEach(a => {
                        if (a.id === id) {
                            if (type === 'view') a.views = (a.views || 0) + 1;
                            if (type === 'click') a.clicks = (a.clicks || 0) + 1;
                            if (type === 'like') a.likes = (a.likes || 0) + 1;
                        }
                    });
                    fs.writeFileSync(advertsFile, JSON.stringify(adverts, null, 2));
                    res.end(JSON.stringify({ status: 'success', adverts: adverts }));
                    return;
                }

                if (action === 'update_status') {
                    const id = parsed.id;
                    const status = parsed.status;
                    const note = parsed.admin_note;
                    adverts.forEach(a => {
                        if (a.id === id) {
                            a.status = status;
                            a.reviewed_at = new Date().toISOString();
                            if (note) a.admin_note = note;
                        }
                    });
                    fs.writeFileSync(advertsFile, JSON.stringify(adverts, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: `Advert status updated to ${status}.`, adverts: adverts }));
                    return;
                }

                if (action === 'delete_advert') {
                    const id = parsed.id;
                    adverts = adverts.filter(a => a.id !== id);
                    fs.writeFileSync(advertsFile, JSON.stringify(adverts, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Advert deleted successfully.', adverts: adverts }));
                    return;
                }

                const userId = urlObj.searchParams.get('user_id') || '';
                if (userId) {
                    const filtered = adverts.filter(a => a.user_id === userId || a.username === userId);
                    res.end(JSON.stringify({ status: 'success', adverts: filtered }));
                    return;
                }

                res.end(JSON.stringify({ status: 'success', adverts: adverts }));
                return;
            }

            if (cleanUrl.includes('uploader_requests.php') || action === 'get_requests' || action === 'submit_request' || action === 'approve_request' || action === 'reject_request' || action === 'pay_with_wallet') {
                const reqFile = path.join(PUBLIC_DIR, 'config', 'uploader_requests.json');
                let requests = [];
                if (fs.existsSync(reqFile)) {
                    try { requests = JSON.parse(fs.readFileSync(reqFile, 'utf8')); } catch(e){}
                }

                if (action === 'pay_with_wallet') {
                    const walletType = parsed.wallet_type || 'referral_cash';
                    const newReq = {
                        id: 'UPG-' + Math.floor(Math.random() * 9000 + 1000),
                        user_id: parsed.user_id || 'Member',
                        username: parsed.username || 'Member',
                        full_name: parsed.username || 'Member',
                        phone: 'N/A',
                        email: (parsed.username || 'Member') + '@innovationx.internal',
                        amount_paid: 10000,
                        payment_channel: walletType,
                        screenshot_url: 'INTERNAL_WALLET_' + walletType.toUpperCase(),
                        status: 'approved',
                        admin_note: 'Auto-approved via ' + (walletType === 'referral_cash' ? 'Referral Cash Wallet' : 'Task Points Wallet'),
                        created_at: new Date().toISOString(),
                        reviewed_at: new Date().toISOString()
                    };
                    requests.unshift(newReq);
                    const configDir = path.dirname(reqFile);
                    if (!fs.existsSync(configDir)) fs.mkdirSync(configDir, { recursive: true });
                    fs.writeFileSync(reqFile, JSON.stringify(requests, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Uploader accreditation unlocked via ' + (walletType === 'referral_cash' ? 'Referral Cash' : 'Task Points') + '!', request: newReq }));
                    return;
                }

                if (action === 'submit_request' || (req.method === 'POST' && parsed.full_name)) {
                    const newReq = {
                        id: 'UPG-' + Math.floor(Math.random() * 9000 + 1000),
                        user_id: parsed.user_id || 'Member',
                        username: parsed.username || 'Member',
                        full_name: parsed.full_name || '',
                        phone: parsed.phone || '',
                        email: parsed.email || '',
                        amount_paid: parseFloat(parsed.amount_paid) || 10000,
                        screenshot_url: parsed.screenshot_url || '',
                        status: 'pending',
                        admin_note: null,
                        created_at: new Date().toISOString(),
                        reviewed_at: null
                    };
                    requests.unshift(newReq);
                    const configDir = path.dirname(reqFile);
                    if (!fs.existsSync(configDir)) fs.mkdirSync(configDir, { recursive: true });
                    fs.writeFileSync(reqFile, JSON.stringify(requests, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Uploader upgrade request submitted with payment screenshot.', request: newReq }));
                    return;
                }

                if (action === 'approve_request') {
                    const id = parsed.id;
                    let promotedUser = null;
                    requests.forEach(r => {
                        if (r.id === id) {
                            r.status = 'approved';
                            r.reviewed_at = new Date().toISOString();
                            promotedUser = r.username || r.user_id;
                        }
                    });
                    fs.writeFileSync(reqFile, JSON.stringify(requests, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: `User @${promotedUser} approved and promoted to Verified Uploader!`, requests: requests }));
                    return;
                }

                if (action === 'reject_request') {
                    const id = parsed.id;
                    const note = parsed.admin_note || 'Payment receipt could not be verified.';
                    requests.forEach(r => {
                        if (r.id === id) {
                            r.status = 'rejected';
                            r.admin_note = note;
                            r.reviewed_at = new Date().toISOString();
                        }
                    });
                    fs.writeFileSync(reqFile, JSON.stringify(requests, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Request rejected.', requests: requests }));
                    return;
                }

                const userId = urlObj.searchParams.get('user_id') || '';
                if (userId) {
                    const filtered = requests.filter(r => r.user_id === userId || r.username === userId);
                    res.end(JSON.stringify({ status: 'success', requests: filtered }));
                    return;
                }

                res.end(JSON.stringify({ status: 'success', requests: requests }));
                return;
            }

            // Google AdSense API
            if (cleanUrl.includes('adsense.php')) {
                const adsenseFile = path.join(PUBLIC_DIR, 'data', 'adsense_settings.json');
                let adsConfig = {
                    enabled: true,
                    publisher_id: 'ca-pub-9847294872910384',
                    auto_ads: true,
                    custom_script: '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9847294872910384" crossorigin="anonymous"></script>',
                    header_slot: '1234567890',
                    sidebar_slot: '2345678901',
                    task_slot: '3456789012',
                    footer_slot: '4567890123'
                };
                if (fs.existsSync(adsenseFile)) {
                    try { adsConfig = Object.assign(adsConfig, JSON.parse(fs.readFileSync(adsenseFile, 'utf8'))); } catch(e){}
                }

                if (req.method === 'POST' || action === 'save_config') {
                    adsConfig = Object.assign(adsConfig, parsed);
                    const dataDir = path.dirname(adsenseFile);
                    if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });
                    fs.writeFileSync(adsenseFile, JSON.stringify(adsConfig, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Google AdSense settings saved.', config: adsConfig }));
                    return;
                }

                res.end(JSON.stringify({ status: 'success', config: adsConfig }));
                return;
            }

            // User Role Management API
            if (cleanUrl.includes('users.php')) {
                const usersFile = path.join(PUBLIC_DIR, 'data', 'users.json');
                let usersData = { users: [] };
                if (fs.existsSync(usersFile)) {
                    try { usersData = JSON.parse(fs.readFileSync(usersFile, 'utf8')); } catch(e){}
                }

                const validRoles = ['member', 'uploader', 'moderator', 'sub_admin', 'super_admin'];
                const roleLabels = { member: 'Active Member', uploader: 'Verified Uploader', moderator: 'Moderator', sub_admin: 'Sub-Admin', super_admin: 'Super Admin' };
                const roleColors = {
                    member: { bg: 'rgba(56,189,248,0.12)', border: 'rgba(56,189,248,0.3)', text: '#38BDF8' },
                    uploader: { bg: 'rgba(34,197,94,0.12)', border: 'rgba(34,197,94,0.3)', text: '#4ADE80' },
                    moderator: { bg: 'rgba(251,191,36,0.12)', border: 'rgba(251,191,36,0.3)', text: '#FBBF24' },
                    sub_admin: { bg: 'rgba(129,140,248,0.12)', border: 'rgba(129,140,248,0.3)', text: '#818CF8' },
                    super_admin: { bg: 'rgba(244,63,94,0.12)', border: 'rgba(244,63,94,0.3)', text: '#FB7185' }
                };

                if (action === 'get_users') {
                    res.end(JSON.stringify({ success: true, users: usersData.users || [], valid_roles: validRoles, role_labels: roleLabels, role_colors: roleColors }));
                    return;
                }

                if (action === 'update_role' && req.method === 'POST') {
                    const username = (parsed.username || '').trim();
                    const newRole = (parsed.new_role || '').trim();
                    if (!username || !validRoles.includes(newRole)) {
                        res.end(JSON.stringify({ success: false, error: 'Invalid username or role' }));
                        return;
                    }
                    let found = false;
                    usersData.users.forEach(u => {
                        if (u.username.toLowerCase() === username.toLowerCase()) {
                            const oldRole = u.role || 'member';
                            u.role = newRole;
                            u.role_label = roleLabels[newRole];
                            u.role_updated_at = new Date().toISOString();
                            u.role_history = u.role_history || [];
                            u.role_history.push({ from: oldRole, to: newRole, changed_at: new Date().toISOString(), changed_by: 'super_admin' });
                            found = true;
                        }
                    });
                    if (!found) {
                        usersData.users.push({
                            username: username, role: newRole, role_label: roleLabels[newRole],
                            role_updated_at: new Date().toISOString(),
                            role_history: [{ from: 'member', to: newRole, changed_at: new Date().toISOString(), changed_by: 'super_admin' }]
                        });
                    }
                    const dataDir = path.dirname(usersFile);
                    if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });
                    fs.writeFileSync(usersFile, JSON.stringify(usersData, null, 2));
                    res.end(JSON.stringify({ success: true, message: `User '${username}' promoted to ${roleLabels[newRole]}`, username, new_role: newRole, role_label: roleLabels[newRole], role_colors: roleColors[newRole] }));
                    return;
                }

                if (action === 'get_role') {
                    const uname = urlObj.searchParams.get('username') || '';
                    const user = usersData.users.find(u => u.username.toLowerCase() === uname.toLowerCase());
                    if (user) {
                        res.end(JSON.stringify({ success: true, username: user.username, role: user.role, role_label: user.role_label || roleLabels[user.role], role_colors: roleColors[user.role], permissions: user.permissions || [] }));
                    } else {
                        res.end(JSON.stringify({ success: true, username: uname, role: 'member', role_label: 'Active Member', role_colors: roleColors.member, permissions: [] }));
                    }
                    return;
                }

                res.end(JSON.stringify({ success: false, error: 'Invalid action', valid_roles: validRoles, role_labels: roleLabels }));
                return;
            }

            // Referrals & Network Directory API
            if (cleanUrl.includes('referrals.php')) {
                const refFile = path.join(PUBLIC_DIR, 'data', 'referrals.json');
                let referrals = [];
                if (fs.existsSync(refFile)) {
                    try { referrals = JSON.parse(fs.readFileSync(refFile, 'utf8').replace(/^\uFEFF/, '')); } catch(e){}
                }

                if (action === 'add_referral' || (req.method === 'POST' && parsed && parsed.email)) {
                    const newRef = {
                        id: 'REF-' + Math.floor(Math.random() * 9000 + 1000),
                        upline_username: parsed.upline_username || urlObj.searchParams.get('upline') || 'Member',
                        full_name: parsed.full_name || 'New Member',
                        username: parsed.username || 'member_' + Math.floor(Math.random() * 900 + 100),
                        email: parsed.email || 'member@gmail.com',
                        joined_date: new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }),
                        downline_referrals_count: parseInt(parsed.downline_referrals_count) || 0,
                        bonus_earned: 250,
                        status: 'Verified Active'
                    };
                    referrals.unshift(newRef);
                    const dataDir = path.dirname(refFile);
                    if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });
                    fs.writeFileSync(refFile, JSON.stringify(referrals, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Referral recorded successfully.', referral: newRef }));
                    return;
                }

                const upline = urlObj.searchParams.get('upline') || urlObj.searchParams.get('username') || 'Member';
                let filtered = referrals;
                if (upline && upline !== 'all') {
                    filtered = referrals.filter(r => (r.upline_username || '').toLowerCase() === upline.toLowerCase() || !r.upline_username);
                }

                let totalBonus = 0;
                let totalTier2 = 0;
                filtered.forEach(r => {
                    totalBonus += parseFloat(r.bonus_earned) || 250;
                    totalTier2 += parseInt(r.downline_referrals_count) || 0;
                });

                res.end(JSON.stringify({
                    status: 'success',
                    upline: upline,
                    stats: {
                        total_referrals: filtered.length,
                        total_bonus_earned: totalBonus,
                        total_tier2_network: totalTier2,
                        bonus_per_invite: 250
                    },
                    referrals: filtered
                }));
                return;
            }

            // Payment Gateways API
            if (cleanUrl.includes('gateways.php')) {
                const gwFile = path.join(PUBLIC_DIR, 'data', 'payment_gateways.json');
                let gwConfig = {
                    default_gateway: 'paystack',
                    paystack_pub: 'pk_test_d7a8f934e892c901bf9841',
                    paystack_mode: 'test',
                    flw_pub: 'FLWPUBK_TEST-98234823901-X',
                    flw_mode: 'test',
                    manual_bank: 'Guaranty Trust Bank (GTBank)',
                    manual_acc: '0123456789',
                    manual_name: 'INNOVATIONX ENTERPRISE'
                };
                if (fs.existsSync(gwFile)) {
                    try { gwConfig = Object.assign(gwConfig, JSON.parse(fs.readFileSync(gwFile, 'utf8'))); } catch(e){}
                }

                if (action === 'test_connection') {
                    const gw = urlObj.searchParams.get('gateway') || 'paystack';
                    const lat = Math.floor(Math.random() * 120 + 80);
                    res.end(JSON.stringify({
                        status: 'success',
                        gateway: gw,
                        latency_ms: lat,
                        ssl_verified: true,
                        http_status: 200,
                        message: `Connection to ${gw.toUpperCase()} API endpoint verified successfully! (${lat}ms latency)`
                    }));
                    return;
                }

                if (req.method === 'POST' || action === 'save_config') {
                    gwConfig = Object.assign(gwConfig, parsed);
                    const dataDir = path.dirname(gwFile);
                    if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });
                    fs.writeFileSync(gwFile, JSON.stringify(gwConfig, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Payment gateway configuration saved.', config: gwConfig }));
                    return;
                }

                res.end(JSON.stringify({ status: 'success', config: gwConfig }));
                return;
            }

            // Automatic Payout App Engine API
            if (cleanUrl.includes('autopayout_app.php')) {
                const appConfigFile = path.join(PUBLIC_DIR, 'data', 'autopayout_app_settings.json');
                const logFile = path.join(PUBLIC_DIR, 'data', 'autopayout_dispatches.json');
                let appConfig = {
                    status: 'enabled',
                    endpoint: 'https://api.omanuban-core.net/v2/dispatch',
                    bearer: 'app_sec_live_98472948729103847192',
                    schedule_mode: 'custom_hours',
                    start_hour: '00:00',
                    end_hour: '23:59',
                    min_amount: 1000,
                    max_amount: 50000,
                    strict_callback: true
                };
                if (fs.existsSync(appConfigFile)) {
                    try { appConfig = Object.assign(appConfig, JSON.parse(fs.readFileSync(appConfigFile, 'utf8'))); } catch(e){}
                }

                let dispatches = [];
                if (fs.existsSync(logFile)) {
                    try { dispatches = JSON.parse(fs.readFileSync(logFile, 'utf8')) || []; } catch(e){}
                }

                if (action === 'test_handshake') {
                    res.end(JSON.stringify({
                        status: 'success',
                        app_status: 'ONLINE_ACTIVE',
                        latency_ms: Math.floor(Math.random() * 80 + 70),
                        protocol: 'HTTPS / REST Webhook',
                        handshake_ack: 'IX_PAYOUT_PONG_' + Math.floor(Math.random() * 90000 + 10000),
                        message: 'Successfully connected to external Payout Service! Service is active.'
                    }));
                    return;
                }

                if (action === 'dispatch_withdrawal' && req.method === 'POST') {
                    const newRec = {
                        txn_id: parsed.txn_id || ('TXN-AUTO-' + Math.floor(Math.random() * 9000 + 1000)),
                        amount: parsed.amount || 5000,
                        bank: parsed.bank || 'Bank',
                        account: parsed.account || '0000000000',
                        service_type: parsed.service_type || 'task',
                        dispatch_time: 'Just now',
                        app_status: 'DISPATCHED_AWAITING_CALLBACK',
                        completed: false
                    };
                    dispatches.unshift(newRec);
                    const dataDir = path.dirname(logFile);
                    if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });
                    fs.writeFileSync(logFile, JSON.stringify(dispatches.slice(0, 50), null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Dispatched to app', record: newRec }));
                    return;
                }

                if (action === 'callback' || action === 'simulate_callback') {
                    const tId = urlObj.searchParams.get('txn_id') || (parsed && parsed.txn_id) || '';
                    if (tId) {
                        dispatches.forEach(d => {
                            if (d.txn_id === tId) {
                                d.app_status = 'TRANSFER_SUCCESSFUL';
                                d.completed = true;
                            }
                        });
                    }
                    if (!dispatches.some(d => d.txn_id === tId)) {
                        dispatches.unshift({
                            txn_id: tId || ('TXN-AUTO-' + Math.floor(Math.random() * 9000 + 1000)),
                            bank: 'Guaranty Trust Bank (GTBank)',
                            account: '0123456789',
                            amount: 5000,
                            service_type: 'task',
                            dispatch_time: 'Just now',
                            app_status: 'TRANSFER_SUCCESSFUL',
                            completed: true
                        });
                    }
                    fs.writeFileSync(logFile, JSON.stringify(dispatches.slice(0, 50), null, 2));
                    res.end(JSON.stringify({ status: 'success', txn_id: tId, completed: true, message: `App confirmed payout for ${tId}.` }));
                    return;
                }

                if (action === 'get_logs') {
                    res.end(JSON.stringify({ status: 'success', logs: dispatches }));
                    return;
                }

                if (req.method === 'POST' || action === 'save_config') {
                    appConfig = Object.assign(appConfig, parsed);
                    const dataDir = path.dirname(appConfigFile);
                    if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });
                    fs.writeFileSync(appConfigFile, JSON.stringify(appConfig, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Automatic Payout App settings saved.', config: appConfig }));
                    return;
                }

                res.end(JSON.stringify({ status: 'success', config: appConfig }));
                return;
            }

            // Virtual Dedicated Accounts (DVA) & Generator API
            if (cleanUrl.includes('virtual_accounts.php')) {
                const vaConfigFile = path.join(PUBLIC_DIR, 'data', 'virtual_accounts_config.json');
                const vaAccFile = path.join(PUBLIC_DIR, 'data', 'user_virtual_accounts.json');

                let vaConfig = {
                    status: 'enabled',
                    provider: 'monnify',
                    mode: 'sandbox',
                    default_bank: 'Wema Bank',
                    app_endpoint: 'https://api.virtual-nuban-engine.net/v1/generate',
                    app_bearer: 'dva_sec_live_98472948729103847192',
                    webhook_secret: 'whsec_dva_9823482390148124',
                    auto_generate_on_reg: true,
                    fee_type: 'free',
                    fee_value: 0,
                    account_name_prefix: 'INNOVATIONX'
                };
                if (fs.existsSync(vaConfigFile)) {
                    try { vaConfig = Object.assign(vaConfig, JSON.parse(fs.readFileSync(vaConfigFile, 'utf8'))); } catch(e){}
                }

                let vaAccounts = [];
                if (fs.existsSync(vaAccFile)) {
                    try { vaAccounts = JSON.parse(fs.readFileSync(vaAccFile, 'utf8')) || []; } catch(e){}
                }

                if (action === 'get_config') {
                    res.end(JSON.stringify({ status: 'success', config: vaConfig }));
                    return;
                }

                if (action === 'save_config' || (req.method === 'POST' && parsed.save_config)) {
                    vaConfig = Object.assign(vaConfig, parsed);
                    const dataDir = path.dirname(vaConfigFile);
                    if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });
                    fs.writeFileSync(vaConfigFile, JSON.stringify(vaConfig, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Virtual account generator settings saved.', config: vaConfig }));
                    return;
                }

                if (action === 'test_connection') {
                    const pName = (vaConfig.provider || 'monnify').toUpperCase();
                    const lat = Math.floor(Math.random() * 60 + 60);
                    res.end(JSON.stringify({
                        status: 'success',
                        provider: pName,
                        mode: vaConfig.mode || 'sandbox',
                        latency_ms: lat,
                        ssl_verified: true,
                        message: `Connection to ${pName} Dedicated Account Generator Engine verified (${lat}ms latency).`
                    }));
                    return;
                }

                if (action === 'get_user_account') {
                    const uId = urlObj.searchParams.get('user_id') || (parsed && parsed.user_id) || 'Member';
                    const uName = urlObj.searchParams.get('username') || (parsed && parsed.username) || uId;
                    let found = vaAccounts.find(a => a.user_id === uId || a.username === uName);
                    if (!found && vaConfig.auto_generate_on_reg !== false) {
                        const randomNuban = '9' + Math.floor(Math.random() * 900000000 + 100000000).toString();
                        found = {
                            id: 'DVA-' + Math.floor(Math.random() * 90000 + 10000),
                            user_id: uId,
                            username: uName,
                            bank_name: vaConfig.default_bank || 'Wema Bank',
                            account_number: randomNuban,
                            account_name: (vaConfig.account_name_prefix || 'INNOVATIONX') + ' - ' + uName.toUpperCase(),
                            provider: vaConfig.provider || 'monnify',
                            created_at: new Date().toISOString(),
                            total_deposited: 0,
                            status: 'active'
                        };
                        vaAccounts.push(found);
                        const dataDir = path.dirname(vaAccFile);
                        if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });
                        fs.writeFileSync(vaAccFile, JSON.stringify(vaAccounts, null, 2));
                    }
                    res.end(JSON.stringify({ status: 'success', account: found || null }));
                    return;
                }

                if (action === 'generate_account' && req.method === 'POST') {
                    const uId = parsed.user_id || 'Member';
                    const uName = parsed.username || uId;
                    const selBank = parsed.bank_name || vaConfig.default_bank || 'Wema Bank';

                    vaAccounts = vaAccounts.filter(a => a.user_id !== uId && a.username !== uName);
                    const randomNuban = '9' + Math.floor(Math.random() * 900000000 + 100000000).toString();
                    const newAcc = {
                        id: 'DVA-' + Math.floor(Math.random() * 90000 + 10000),
                        user_id: uId,
                        username: uName,
                        bank_name: selBank,
                        account_number: randomNuban,
                        account_name: (vaConfig.account_name_prefix || 'INNOVATIONX') + ' - ' + uName.toUpperCase(),
                        provider: vaConfig.provider || 'monnify',
                        created_at: new Date().toISOString(),
                        total_deposited: 0,
                        status: 'active'
                    };
                    vaAccounts.push(newAcc);
                    const dataDir = path.dirname(vaAccFile);
                    if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });
                    fs.writeFileSync(vaAccFile, JSON.stringify(vaAccounts, null, 2));
                    res.end(JSON.stringify({ status: 'success', message: 'Unique payment account generated.', account: newAcc }));
                    return;
                }

                if (action === 'get_all_accounts') {
                    res.end(JSON.stringify({ status: 'success', accounts: vaAccounts }));
                    return;
                }

                res.end(JSON.stringify({ status: 'success', config: vaConfig }));
                return;
            }

            if (action === 'get_settings') {
                res.end(JSON.stringify({ status: 'success', config: vtuConfig }));
                return;
            }

            if (action === 'save_settings') {
                vtuConfig = Object.assign(vtuConfig, parsed);
                const configDir = path.dirname(configFile);
                if (!fs.existsSync(configDir)) fs.mkdirSync(configDir, { recursive: true });
                fs.writeFileSync(configFile, JSON.stringify(vtuConfig, null, 2));
                res.end(JSON.stringify({ status: 'success', message: 'VTU PrimeBiller & Provider API settings saved successfully.', config: vtuConfig }));
                return;
            }

            if (action === 'test_connection') {
                const provName = (vtuConfig.provider_name || 'PrimeBiller').toUpperCase();
                res.end(JSON.stringify({
                    status: 'success',
                    mode: vtuConfig.api_mode || 'sandbox',
                    provider: provName,
                    message: `${provName} API Connection Verified. Airtime and Data endpoints are active.`,
                    wallet_balance: '₦250,000.00 (Simulated)',
                    response_time_ms: Math.floor(Math.random() * 120 + 80)
                }));
                return;
            }

            if (action === 'buy_airtime') {
                const phone = (parsed.phone || '08123456789').replace(/[^0-9]/g, '');
                const faceAmount = parseFloat(parsed.amount) || 500;
                const network = (parsed.network || 'mtn').toLowerCase();
                
                // Read Admin's custom selling rate for this specific network
                const sellingRate = (vtuConfig.airtime_rates && vtuConfig.airtime_rates[network]) ? parseFloat(vtuConfig.airtime_rates[network]) : 97.0;
                const amountChargedNaira = roundNumber((faceAmount * sellingRate) / 100, 2);
                
                // Read Admin's points exchange rate
                const pointsRate = parseFloat(vtuConfig.points_per_naira) || 1.0;
                const amountChargedPoints = Math.round(amountChargedNaira * pointsRate);

                const txRef = 'IX-AIR-' + Math.floor(Math.random() * 900000 + 100000);

                res.end(JSON.stringify({
                    status: 'success',
                    message: `Airtime recharge of ₦${faceAmount.toLocaleString()} to ${phone} was successful!`,
                    transaction: {
                        tx_ref: txRef,
                        provider_ref: 'PB-' + Math.floor(Math.random() * 900000 + 100000),
                        network: network.toUpperCase(),
                        phone: phone,
                        face_amount: faceAmount,
                        selling_rate_percent: sellingRate,
                        amount_charged_naira: amountChargedNaira,
                        amount_charged_points: amountChargedPoints,
                        points_exchange_rate: pointsRate,
                        pay_source: parsed.pay_source || 'points',
                        delivery_status: 'Delivered',
                        created_at: new Date().toISOString()
                    }
                }));
                return;
            }

            if (action === 'buy_data') {
                const phone = (parsed.phone || '08123456789').replace(/[^0-9]/g, '');
                const network = (parsed.network || 'mtn').toLowerCase();
                const plan = parsed.plan || '1GB';
                
                // Read Admin's custom data selling price for this network & plan
                let amountNaira = 250;
                if (vtuConfig.data_prices && vtuConfig.data_prices[network] && vtuConfig.data_prices[network][plan]) {
                    amountNaira = parseFloat(vtuConfig.data_prices[network][plan]);
                } else if (parsed.amount) {
                    amountNaira = parseFloat(parsed.amount);
                }

                const pointsRate = parseFloat(vtuConfig.points_per_naira) || 1.0;
                const amountPoints = Math.round(amountNaira * pointsRate);
                const txRef = 'IX-DAT-' + Math.floor(Math.random() * 900000 + 100000);

                res.end(JSON.stringify({
                    status: 'success',
                    message: `${network.toUpperCase()} ${plan} SME Data successfully dispatched via API to ${phone}!`,
                    transaction: {
                        tx_ref: txRef,
                        provider_ref: 'PB-DAT-' + Math.floor(Math.random() * 900000 + 100000),
                        network: network.toUpperCase(),
                        phone: phone,
                        plan: plan,
                        amount_charged_naira: amountNaira,
                        amount_charged_points: amountPoints,
                        points_exchange_rate: pointsRate,
                        pay_source: parsed.pay_source || 'points',
                        delivery_status: 'Delivered',
                        created_at: new Date().toISOString()
                    }
                }));
                return;
            }

            res.end(JSON.stringify({ status: 'active', service: 'INNOVATIONX PrimeBiller & VTU Telecoms API Gateway', version: '2.0.0' }));
        };

        function roundNumber(num, scale) {
            return Number(Math.round(Number(num + 'e' + scale)) + 'e-' + scale);
        }

        if (req.method === 'POST') {
            let body = '';
            req.on('data', chunk => { body += chunk; });
            req.on('end', () => {
                let parsed = {};
                try { if (body) parsed = JSON.parse(body); } catch(e){}
                handleApi(parsed);
            });
        } else {
            handleApi({});
        }
        return;
    }

    // 2. Static and PHP View Routing
    let filePath = path.join(PUBLIC_DIR, cleanUrl);
    filePath = path.normalize(filePath);
    if (!filePath.startsWith(PUBLIC_DIR)) {
        res.writeHead(403, { 'Content-Type': 'text/plain' });
        res.end('Access Denied');
        return;
    }

    fs.stat(filePath, (err, stats) => {
        if (err || !stats.isFile()) {
            if (fs.existsSync(filePath + '.php')) {
                filePath = filePath + '.php';
            } else {
                filePath = path.join(PUBLIC_DIR, 'index.php');
            }
        }

        const ext = path.extname(filePath).toLowerCase();
        const contentType = MIME_TYPES[ext] || 'application/octet-stream';

        if (ext === '.php') {
            try {
                const rendered = renderPhpFile(filePath, {});
                res.writeHead(200, {
                    'Content-Type': 'text/html; charset=UTF-8',
                    'Cache-Control': 'no-cache, no-store, must-revalidate'
                });
                res.end(rendered);
            } catch (renderErr) {
                res.writeHead(500, { 'Content-Type': 'text/plain' });
                res.end('PHP Render Error: ' + renderErr.message);
            }
            return;
        }

        fs.readFile(filePath, (readErr, content) => {
            if (readErr) {
                res.writeHead(404, { 'Content-Type': 'text/plain' });
                res.end('File Not Found');
                return;
            }

            res.writeHead(200, {
                'Content-Type': contentType,
                'Cache-Control': 'no-cache, no-store, must-revalidate'
            });
            res.end(content);
        });
    });
});

server.listen(PORT, '0.0.0.0', () => {
    console.log(`INNOVATIONX Server running at http://localhost:${PORT}/ and http://0.0.0.0:${PORT}/`);
});
