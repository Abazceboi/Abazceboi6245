<?php
/**
 * INNOVATIONX — Verified Uploader Publishing Studio
 * Dedicated creator workspace for sponsored advertisers and task creators.
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

$authUser = function_exists('getAuthenticatedUser') ? getAuthenticatedUser() : null;
if (!$authUser) {
    header("Location: login.php");
    exit;
}

$username = $authUser['username'] ?? $_SESSION['username'] ?? 'Creator';
$userRole = strtolower($authUser['role'] ?? $_SESSION['role'] ?? 'member');
$isAdmin = in_array(strtolower($username), ['admin', 'abas6245', 'abazceboi']) || in_array($userRole, ['admin', 'super_admin']);
$isUploader = $isAdmin || ($userRole === 'uploader');

// If not an uploader yet, show the Accreditation Required page
if (!$isUploader) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Uploader Accreditation Required | <?= htmlspecialchars(APP_NAME) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800;900&display=swap" rel="stylesheet">
        <style>
            *{margin:0;padding:0;box-sizing:border-box}
            body{font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#080B14;color:#F8FAFC;padding:20px}
            .card{background:#0F172A;border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:40px;max-width:500px;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,0.6)}
            .icon{width:64px;height:64px;border-radius:18px;background:rgba(16,185,129,0.15);color:#10B981;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.8rem}
            h1{font-size:1.45rem;font-weight:900;margin-bottom:8px}
            p{font-size:0.88rem;color:#94A3B8;margin-bottom:24px;line-height:1.5}
            .btn-primary{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:10px;background:#10B981;color:#FFFFFF;font-weight:800;text-decoration:none;font-size:0.9rem;border:none;cursor:pointer}
            .btn-secondary{display:inline-block;margin-top:14px;color:#64748B;font-size:0.82rem;text-decoration:none}
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg></div>
            <h1>Uploader Studio Access</h1>
            <p>The Uploader Publishing Hub is reserved for accredited sponsors and creators to post sponsored tasks, review submissions, and manage marketing campaigns.</p>
            <a href="dashboard.php" class="btn-primary">Return to Member Dashboard</a>
            <div><a href="https://t.me/innovationx_support" target="_blank" class="btn-secondary">Contact Support to Apply for Uploader Badge ↗</a></div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$pageTitle = 'Uploader Publishing Studio | ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #080B14;
            --bg-card: #0F172A;
            --bg-surface: #141E33;
            --border-subtle: rgba(255, 255, 255, 0.08);
            --primary: #10B981;
            --primary-glow: rgba(16, 185, 129, 0.25);
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --font-main: 'Plus Jakarta Sans', sans-serif;
            --font-display: 'Space Grotesk', sans-serif;
            --radius-md: 14px;
            --radius-lg: 20px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg-base); color: var(--text-main); font-family: var(--font-main); min-height: 100vh; padding-bottom: 60px; }

        .uploader-nav {
            position: sticky; top: 0; z-index: 100;
            background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-subtle);
            padding: 14px 24px; display: flex; align-items: center; justify-content: space-between;
        }

        .brand-logo-area { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #FFFFFF; }
        .brand-badge { width: 36px; height: 36px; border-radius: 9px; background: #10B981; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 900; }
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

        .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 14px; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
        .form-label { font-size: 0.76rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); }
        .form-input, .form-select, .form-textarea {
            width: 100%; padding: 12px 14px; border-radius: 8px;
            background: var(--bg-surface); border: 1px solid var(--border-subtle);
            color: #FFFFFF; font-family: inherit; font-size: 0.88rem; outline: none;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--primary); }

        .btn-action-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 12px 24px; border-radius: 8px; background: #10B981; color: #FFFFFF;
            font-weight: 800; font-size: 0.9rem; border: none; cursor: pointer; transition: all 0.2s;
        }
        .btn-action-primary:hover { background: #059669; transform: translateY(-1px); }

        .table-wrap { width: 100%; overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
        .data-table th { text-align: left; padding: 12px 14px; font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); }
        .data-table td { padding: 14px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); }

        .btn-sm-approve {
            padding: 5px 12px; border-radius: 6px; background: rgba(16,185,129,0.15); color: #34D399;
            border: 1px solid rgba(16,185,129,0.3); font-weight: 700; font-size: 0.75rem; cursor: pointer;
        }
        .btn-sm-reject {
            padding: 5px 12px; border-radius: 6px; background: rgba(244,63,94,0.15); color: #FB7185;
            border: 1px solid rgba(244,63,94,0.3); font-weight: 700; font-size: 0.75rem; cursor: pointer; margin-left: 6px;
        }
    </style>
</head>
<body>
    <nav class="uploader-nav">
        <a href="uploader_dashboard.php" class="brand-logo-area">
            <div class="brand-badge">UP</div>
            <div class="brand-title">UPLOADER <span>STUDIO</span></div>
        </a>
        <div style="display:flex;align-items:center;gap:12px">
            <span style="font-size:0.8rem;color:#94A3B8">Logged in as <strong>@<?= htmlspecialchars($username) ?></strong></span>
            <a href="dashboard.php" class="btn-dash-back">← Member Dashboard</a>
        </div>
    </nav>

    <div class="shell">
        <div class="page-header">
            <div>
                <h1>Uploader Campaign Studio</h1>
                <p>Publish sponsored earning gigs, inspect submitted proof evidence, and distribute task points to active members.</p>
            </div>
            <button type="button" class="btn-action-primary" onclick="document.getElementById('taskPublishCard').scrollIntoView({behavior:'smooth'})">
                <span>+ Create New Task</span>
            </button>
        </div>

        <!-- Uploader Analytics -->
        <div class="grid-4">
            <div class="kpi-card">
                <div class="kpi-label">Active Tasks Published</div>
                <div class="kpi-val" id="kpiActiveTasks">0</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Submissions Reviewed</div>
                <div class="kpi-val" id="kpiSubmissions">0</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Points Awarded</div>
                <div class="kpi-val" id="kpiPointsAwarded">0 PTS</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Creator Status</div>
                <div class="kpi-val" style="color:#10B981;font-size:1.3rem">Verified Partner</div>
            </div>
        </div>

        <!-- Task Publisher Studio -->
        <div class="card-panel" id="taskPublishCard">
            <div class="card-title">Publish New Earning Opportunity</div>
            <div class="card-desc">Your task will immediately appear on the Member Dashboard task feed upon publishing.</div>

            <form onsubmit="handlePublishTask(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Task Title</label>
                        <input type="text" id="taskTitle" class="form-input" required placeholder="e.g. Join Official WhatsApp Channel &amp; React to Post">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select id="taskCategory" class="form-select">
                            <option value="WhatsApp Status">WhatsApp Status &amp; Channel</option>
                            <option value="Sponsored Video">Sponsored Video &amp; YouTube</option>
                            <option value="Telegram Follow">Telegram Group / Channel</option>
                            <option value="App Review">Play Store / App Review</option>
                            <option value="Website Visit">Website Engagement</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Reward Points per User</label>
                        <input type="number" id="taskReward" class="form-input" required value="150" min="50" step="10">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Participant Slots</label>
                        <input type="number" id="taskSlots" class="form-input" required value="500" min="10">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Destination / Action URL</label>
                        <input type="url" id="taskUrl" class="form-input" required placeholder="https://...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Proof Verification Type</label>
                        <select id="taskProofType" class="form-select">
                            <option value="link_timer">Link Verification / Proof URL</option>
                            <option value="screenshot">Screenshot Upload Link</option>
                            <option value="username">User Handle / Social Username</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Step-by-Step Member Instructions</label>
                    <textarea id="taskInstructions" class="form-textarea" rows="3" required placeholder="1. Open the URL&#10;2. Follow / Subscribe&#10;3. Submit verification evidence below"></textarea>
                </div>

                <button type="submit" class="btn-action-primary" id="btnPublishTask">
                    <span>Deploy Task to Network</span>
                </button>
            </form>
        </div>

        <!-- Submissions Verification Queue -->
        <div class="card-panel">
            <div class="card-title">Member Submissions &amp; Proof Queue</div>
            <div class="card-desc">Review submitted evidence and approve to disburse task reward points.</div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Submission ID</th>
                            <th>Task Title</th>
                            <th>Member</th>
                            <th>Proof Link</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="submissionsTableBody">
                        <tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:24px">Loading submissions...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Active Tasks Table -->
        <div class="card-panel">
            <div class="card-title">My Deployed Campaigns</div>
            <div class="card-desc">Live status of your campaigns currently active across the member network.</div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Campaign ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Reward</th>
                            <th>Remaining Slots</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="tasksTableBody">
                        <tr><td colspan="7" style="text-align:center;color:#94A3B8;padding:24px">Loading campaigns...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        async function loadUploaderData() {
            try {
                // 1. Load Tasks
                const tRes = await fetch('/api/tasks.php?action=get_tasks');
                const tData = await tRes.json();
                const tasks = tData.tasks || [];
                document.getElementById('kpiActiveTasks').textContent = tasks.length;

                const tBody = document.getElementById('tasksTableBody');
                if (tasks.length === 0) {
                    tBody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#94A3B8;padding:24px">No active tasks deployed yet. Create your first task above!</td></tr>';
                } else {
                    tBody.innerHTML = tasks.map(t => `
                        <tr>
                            <td><code>${t.id}</code></td>
                            <td><strong>${escapeHtml(t.title)}</strong></td>
                            <td><span style="font-size:0.75rem;padding:3px 8px;border-radius:4px;background:rgba(56,189,248,0.1);color:#38BDF8">${escapeHtml(t.category || 'Gig')}</span></td>
                            <td style="color:#10B981;font-weight:700">+${t.reward_points || 150} PTS</td>
                            <td>${t.remaining_slots || 0} / ${t.total_slots || 0}</td>
                            <td><span style="color:#10B981;font-weight:700">Active</span></td>
                            <td>
                                <button type="button" class="btn-sm-reject" onclick="deleteTask('${t.id}')">Delete</button>
                            </td>
                        </tr>
                    `).join('');
                }

                // 2. Load Submissions
                const sRes = await fetch('/api/tasks.php?action=get_submissions');
                const sData = await sRes.json();
                const subs = sData.submissions || [];
                document.getElementById('kpiSubmissions').textContent = subs.length;

                const sBody = document.getElementById('submissionsTableBody');
                if (subs.length === 0) {
                    sBody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:24px">No pending submissions in queue.</td></tr>';
                } else {
                    sBody.innerHTML = subs.map(s => `
                        <tr>
                            <td><code>${s.id}</code></td>
                            <td>${escapeHtml(s.task_title)}</td>
                            <td><strong>@${escapeHtml(s.username)}</strong></td>
                            <td><a href="${escapeHtml(s.proof_url)}" target="_blank" rel="noopener" style="color:#38BDF8;text-decoration:none">View Proof URL ↗</a></td>
                            <td style="color:#94A3B8;font-size:0.78rem">${escapeHtml(s.submitted_at || 'Recently')}</td>
                            <td>
                                ${s.status === 'approved' ? '<span style="color:#10B981;font-weight:700">Approved</span>' :
                                  s.status === 'rejected' ? '<span style="color:#F43F5E;font-weight:700">Rejected</span>' : `
                                    <button type="button" class="btn-sm-approve" onclick="approveProof('${s.id}')">Approve &amp; Pay</button>
                                    <button type="button" class="btn-sm-reject" onclick="rejectProof('${s.id}')">Reject</button>
                                  `}
                            </td>
                        </tr>
                    `).join('');
                }
            } catch(e) {}
        }

        async function handlePublishTask(e) {
            e.preventDefault();
            const btn = document.getElementById('btnPublishTask');
            btn.disabled = true;
            btn.textContent = 'Deploying...';

            const payload = {
                title: document.getElementById('taskTitle').value.trim(),
                category: document.getElementById('taskCategory').value,
                reward_points: parseInt(document.getElementById('taskReward').value),
                total_slots: parseInt(document.getElementById('taskSlots').value),
                action_url: document.getElementById('taskUrl').value.trim(),
                proof_type: document.getElementById('taskProofType').value,
                instructions: document.getElementById('taskInstructions').value.trim()
            };

            try {
                const res = await fetch('/api/tasks.php?action=publish_task', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.status === 'success') {
                    alert('Task published successfully! Now visible across the member network.');
                    document.getElementById('taskTitle').value = '';
                    document.getElementById('taskUrl').value = '';
                    document.getElementById('taskInstructions').value = '';
                    loadUploaderData();
                } else {
                    alert(data.message || 'Failed to publish task');
                }
            } catch(e) {
                alert('Server error deploying task');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Deploy Task to Network';
            }
        }

        async function approveProof(subId) {
            if (!await fancyConfirm('Approve Submission', 'Approve this submission and credit reward points to the member?')) return;
            const res = await fetch('/api/tasks.php?action=approve_task_proof', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ submission_id: subId })
            });
            const d = await res.json();
            alert(d.message || 'Approved');
            loadUploaderData();
        }

        async function rejectProof(subId) {
            if (!await fancyConfirm('Reject Submission', 'Reject this proof submission?')) return;
            const res = await fetch('/api/tasks.php?action=reject_task_proof', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ submission_id: subId })
            });
            const d = await res.json();
            alert(d.message || 'Rejected');
            loadUploaderData();
        }

        async function deleteTask(id) {
            if (!await fancyConfirm('Delete Campaign', 'Permanently remove this campaign?')) return;
            await fetch('/api/tasks.php?action=delete_task', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            loadUploaderData();
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]);
        }

        document.addEventListener('DOMContentLoaded', loadUploaderData);
    </script>
</body>
</html>
