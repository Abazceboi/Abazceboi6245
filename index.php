<?php
$pageTitle = 'INNOVATIONX | Nigeria\'s Premier Digital Earning Platform';
$pageDesc = 'Join INNOVATIONX — earn real cash daily through sponsored micro-tasks, referral bonuses, and instant automated bank payouts. One-time ₦500 activation. No monthly fees.';
$siteContentFile = __DIR__ . '/config/site_content.json';
$siteContent = [];
if (file_exists($siteContentFile)) {
    $siteContent = json_decode(file_get_contents($siteContentFile), true) ?: [];
}
if (!empty($_GET['ref'])) {
    $refParam = trim($_GET['ref']);
    setcookie('ix_ref', $refParam, time() + 86400 * 30, '/');
}
require_once __DIR__ . '/includes/header.php';
?>

<!-- ===== INLINE PAGE-SPECIFIC STYLES ===== -->
<style>
/* ---- Landing page custom styles (scoped, non-breaking) ---- */

/* Hero */
.lp-hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: clamp(100px, 14vw, 148px) 0 clamp(60px, 8vw, 100px);
    overflow: hidden;
}
.lp-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 80% 55% at 60% -10%, rgba(56,189,248,0.12) 0%, transparent 70%),
        radial-gradient(ellipse 45% 40% at 0% 80%, rgba(2,132,199,0.07) 0%, transparent 65%);
    pointer-events: none;
}

.lp-hero-inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: clamp(40px, 6vw, 80px);
    align-items: center;
}

/* Hero left */
.lp-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px 6px 8px;
    background: rgba(56,189,248,0.08);
    border: 1px solid rgba(56,189,248,0.22);
    border-radius: 999px;
    font-size: 0.73rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--sky-vibrant);
    margin-bottom: 24px;
}
.lp-hero-badge-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--sky-vibrant);
    box-shadow: 0 0 8px rgba(56,189,248,0.7);
    animation: blink-dot 2s infinite;
}
@keyframes blink-dot {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.35; }
}

.lp-hero-h1 {
    font-size: clamp(2.6rem, 5.5vw, 4.2rem);
    font-weight: 900;
    line-height: 1.08;
    letter-spacing: -0.03em;
    color: var(--text-white);
    margin-bottom: 22px;
}
.lp-hero-h1 .accent {
    background: linear-gradient(135deg, #38BDF8 0%, #7DD3FC 60%, #BAE6FD 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.lp-hero-sub {
    font-size: clamp(1rem, 1.8vw, 1.15rem);
    color: var(--text-gray);
    line-height: 1.7;
    max-width: 520px;
    margin-bottom: 36px;
}

.lp-hero-btns {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 36px;
}
.lp-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 14px 28px;
    background: linear-gradient(135deg, #0284C7 0%, #38BDF8 100%);
    color: #fff;
    border-radius: 12px;
    font-weight: 800;
    font-size: 0.95rem;
    text-decoration: none;
    box-shadow: 0 4px 20px rgba(56,189,248,0.32);
    transition: transform 0.2s, box-shadow 0.2s;
    white-space: nowrap;
}
.lp-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(56,189,248,0.45);
    color: #fff;
}
.lp-btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 14px 26px;
    background: transparent;
    color: var(--text-light);
    border: 1.5px solid rgba(56,189,248,0.28);
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: none;
    transition: border-color 0.2s, background 0.2s, transform 0.2s;
    white-space: nowrap;
}
.lp-btn-outline:hover {
    background: rgba(56,189,248,0.06);
    border-color: rgba(56,189,248,0.55);
    color: var(--sky-vibrant);
    transform: translateY(-2px);
}

.lp-hero-trust {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.85rem;
    color: var(--text-muted);
}
.lp-hero-trust-badges {
    display: flex;
    align-items: center;
    gap: -4px;
}
.lp-trust-dot {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0284C7, #38BDF8);
    border: 2px solid var(--bg-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 0.68rem;
    color: #fff;
}
.lp-hero-trust strong { color: var(--text-white); }

/* Hero right — console card */
.lp-console-card {
    background: rgba(12,22,45,0.82);
    border: 1px solid rgba(56,189,248,0.18);
    border-radius: 22px;
    padding: clamp(22px, 3vw, 32px);
    backdrop-filter: blur(18px);
    box-shadow: 0 24px 64px rgba(2,10,28,0.55), 0 0 0 1px rgba(56,189,248,0.06);
    position: relative;
    overflow: hidden;
}
.lp-console-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(56,189,248,0.5), transparent);
}
.lp-console-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.lp-console-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--text-light);
    display: flex;
    align-items: center;
    gap: 8px;
}
.lp-live-pill {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: rgba(56,189,248,0.1);
    border: 1px solid rgba(56,189,248,0.2);
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--sky-vibrant);
    letter-spacing: 0.5px;
}
.lp-live-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #38BDF8;
    box-shadow: 0 0 6px rgba(56,189,248,0.8);
    animation: blink-dot 1.5s infinite;
}

.lp-balance-section {
    background: linear-gradient(135deg, rgba(2,132,199,0.12) 0%, rgba(56,189,248,0.06) 100%);
    border: 1px solid rgba(56,189,248,0.14);
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 18px;
}
.lp-balance-label {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.lp-instant-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    background: rgba(56,189,248,0.1);
    border-radius: 6px;
    font-size: 0.68rem;
    font-weight: 700;
    color: #38BDF8;
}
.lp-balance-amt {
    font-size: clamp(2rem, 4vw, 2.6rem);
    font-weight: 900;
    color: var(--text-white);
    letter-spacing: -0.03em;
    margin-bottom: 14px;
}
.lp-balance-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.lp-balance-item-label {
    font-size: 0.72rem;
    color: var(--text-muted);
    margin-bottom: 3px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.lp-balance-item-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
}
.lp-balance-item-val {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--text-white);
}

.lp-task-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 18px;
}
.lp-task-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(56,189,248,0.09);
    border-radius: 11px;
    transition: background 0.2s;
}
.lp-task-row:hover {
    background: rgba(56,189,248,0.05);
}
.lp-task-icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    background: rgba(56,189,248,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: var(--sky-vibrant);
}
.lp-task-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-light);
    flex: 1;
}
.lp-task-badge {
    font-size: 0.73rem;
    font-weight: 800;
    padding: 4px 9px;
    background: rgba(56,189,248,0.12);
    border: 1px solid rgba(56,189,248,0.2);
    border-radius: 7px;
    color: var(--sky-vibrant);
    white-space: nowrap;
}

.lp-console-cta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 13px 20px;
    background: linear-gradient(135deg, #0284C7 0%, #38BDF8 100%);
    color: #fff;
    border-radius: 12px;
    font-weight: 800;
    font-size: 0.88rem;
    text-decoration: none;
    box-shadow: 0 4px 16px rgba(56,189,248,0.28);
    transition: transform 0.2s, box-shadow 0.2s;
}
.lp-console-cta:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(56,189,248,0.4);
    color: #fff;
}

/* Stats strip */
.lp-stats {
    background: rgba(12,22,45,0.6);
    border-top: 1px solid rgba(56,189,248,0.1);
    border-bottom: 1px solid rgba(56,189,248,0.1);
    padding: 0;
}
.lp-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
}
.lp-stat {
    padding: 28px 20px;
    text-align: center;
    border-right: 1px solid rgba(56,189,248,0.08);
    position: relative;
}
.lp-stat:last-child { border-right: none; }
.lp-stat-num {
    font-size: clamp(1.5rem, 2.5vw, 2rem);
    font-weight: 900;
    color: var(--sky-vibrant);
    letter-spacing: -0.02em;
    margin-bottom: 4px;
    display: block;
}
.lp-stat-lbl {
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 500;
}

/* How it works */
.lp-section {
    padding: clamp(60px, 8vw, 100px) 0;
}
.lp-section-tag {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--sky-vibrant);
    background: rgba(56,189,248,0.08);
    border: 1px solid rgba(56,189,248,0.18);
    border-radius: 6px;
    padding: 4px 12px;
    margin-bottom: 14px;
}
.lp-section-title {
    font-size: clamp(1.7rem, 3.5vw, 2.6rem);
    font-weight: 900;
    letter-spacing: -0.025em;
    color: var(--text-white);
    margin-bottom: 14px;
    line-height: 1.15;
}
.lp-section-sub {
    font-size: clamp(0.9rem, 1.6vw, 1.05rem);
    color: var(--text-muted);
    line-height: 1.7;
    max-width: 580px;
}
.lp-section-header { margin-bottom: clamp(36px, 5vw, 58px); }
.lp-section-header.center { text-align: center; }
.lp-section-header.center .lp-section-sub { margin: 0 auto; }

/* Steps */
.lp-steps {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}
.lp-step {
    background: rgba(12,22,45,0.55);
    border: 1px solid rgba(56,189,248,0.12);
    border-radius: 18px;
    padding: 30px 26px;
    position: relative;
    transition: border-color 0.25s, transform 0.25s;
    overflow: hidden;
}
.lp-step::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(56,189,248,0.03) 0%, transparent 60%);
    pointer-events: none;
}
.lp-step:hover {
    border-color: rgba(56,189,248,0.3);
    transform: translateY(-3px);
}
.lp-step-num {
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--sky-vibrant);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.lp-step-num::before {
    content: '';
    display: block;
    width: 32px; height: 32px;
    border-radius: 9px;
    background: rgba(56,189,248,0.1);
    border: 1px solid rgba(56,189,248,0.22);
    flex-shrink: 0;
}
.lp-step-num span {
    position: absolute;
    margin-left: 7px;
    font-size: 0.85rem;
}
.lp-step-icon-wrap {
    width: 48px; height: 48px;
    border-radius: 13px;
    background: rgba(56,189,248,0.1);
    border: 1px solid rgba(56,189,248,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 18px;
    color: var(--sky-vibrant);
}
.lp-step-n {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: var(--sky-vibrant);
    opacity: 0.7;
    margin-bottom: 12px;
}
.lp-step-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--text-white);
    margin-bottom: 10px;
}
.lp-step-desc {
    font-size: 0.88rem;
    color: var(--text-muted);
    line-height: 1.65;
}

/* Features grid */
.lp-features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.lp-feature-card {
    background: rgba(12,22,45,0.55);
    border: 1px solid rgba(56,189,248,0.1);
    border-radius: 16px;
    padding: 26px 22px;
    transition: border-color 0.22s, transform 0.22s;
}
.lp-feature-card:hover {
    border-color: rgba(56,189,248,0.28);
    transform: translateY(-2px);
}
.lp-feature-icon {
    width: 44px; height: 44px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    color: var(--sky-vibrant);
    flex-shrink: 0;
}
.lp-feature-title {
    font-size: 0.98rem;
    font-weight: 800;
    color: var(--text-white);
    margin-bottom: 8px;
}
.lp-feature-desc {
    font-size: 0.84rem;
    color: var(--text-muted);
    line-height: 1.6;
}
.lp-feature-badge {
    display: inline-block;
    margin-top: 14px;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--sky-vibrant);
    background: rgba(56,189,248,0.08);
    border: 1px solid rgba(56,189,248,0.16);
    border-radius: 6px;
    padding: 3px 9px;
}

/* Opportunities pills bar */
.lp-opps-section {
    background: rgba(8,15,30,0.6);
    border-top: 1px solid rgba(56,189,248,0.08);
    border-bottom: 1px solid rgba(56,189,248,0.08);
    padding: clamp(50px, 7vw, 80px) 0;
}
.lp-opps-inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: clamp(36px, 6vw, 72px);
    align-items: center;
}
.lp-opps-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 28px;
}
.lp-opp-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    background: rgba(12,22,45,0.7);
    border: 1px solid rgba(56,189,248,0.14);
    border-radius: 10px;
    font-size: 0.83rem;
    font-weight: 600;
    color: var(--text-light);
    text-decoration: none;
    transition: border-color 0.2s, background 0.2s, color 0.2s;
}
.lp-opp-pill svg { color: var(--sky-vibrant); flex-shrink: 0; }
.lp-opp-pill:hover {
    border-color: rgba(56,189,248,0.35);
    background: rgba(56,189,248,0.07);
    color: var(--sky-vibrant);
}
.lp-opps-cta {
    margin-top: 28px;
}

/* Why trust section */
.lp-trust-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.lp-trust-card {
    background: rgba(12,22,45,0.55);
    border: 1px solid rgba(56,189,248,0.1);
    border-radius: 18px;
    padding: 28px 24px;
    position: relative;
    overflow: hidden;
    transition: border-color 0.22s, transform 0.22s;
}
.lp-trust-card:hover {
    border-color: rgba(56,189,248,0.28);
    transform: translateY(-2px);
}
.lp-trust-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(56,189,248,0.4), transparent);
}
.lp-trust-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--sky-vibrant);
    background: rgba(56,189,248,0.08);
    border: 1px solid rgba(56,189,248,0.16);
    border-radius: 6px;
    padding: 3px 8px;
    margin-bottom: 14px;
}
.lp-trust-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--text-white);
    margin-bottom: 10px;
}
.lp-trust-desc {
    font-size: 0.86rem;
    color: var(--text-muted);
    line-height: 1.65;
}

/* FAQ */
.lp-faq-list {
    max-width: 760px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.lp-faq-item {
    background: rgba(12,22,45,0.55);
    border: 1px solid rgba(56,189,248,0.1);
    border-radius: 14px;
    overflow: hidden;
    transition: border-color 0.2s;
}
.lp-faq-item.open {
    border-color: rgba(56,189,248,0.25);
}
.lp-faq-q {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 18px 22px;
    cursor: pointer;
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--text-white);
    user-select: none;
    -webkit-user-select: none;
}
.lp-faq-q:hover { color: var(--sky-vibrant); }
.lp-faq-chevron {
    width: 18px; height: 18px;
    color: var(--text-muted);
    flex-shrink: 0;
    transition: transform 0.28s;
}
.lp-faq-item.open .lp-faq-chevron { transform: rotate(180deg); color: var(--sky-vibrant); }
.lp-faq-a {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s ease, padding 0.25s;
}
.lp-faq-item.open .lp-faq-a {
    max-height: 300px;
    padding-bottom: 18px;
}
.lp-faq-a p {
    padding: 0 22px;
    font-size: 0.87rem;
    color: var(--text-muted);
    line-height: 1.7;
    margin: 0;
}

/* CTA section */
.lp-cta-section {
    padding: clamp(60px, 9vw, 110px) 0;
    position: relative;
    text-align: center;
    overflow: hidden;
}
.lp-cta-section::before {
    content: '';
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: min(800px, 100%);
    height: 340px;
    background: radial-gradient(ellipse at center, rgba(56,189,248,0.09) 0%, transparent 70%);
    pointer-events: none;
}
.lp-cta-box {
    background: rgba(12,22,45,0.75);
    border: 1px solid rgba(56,189,248,0.18);
    border-radius: 24px;
    padding: clamp(40px, 6vw, 64px) clamp(28px, 5vw, 60px);
    max-width: 700px;
    margin: 0 auto;
    position: relative;
    overflow: hidden;
}
.lp-cta-box::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(56,189,248,0.55), transparent);
}
.lp-cta-h {
    font-size: clamp(1.8rem, 3.5vw, 2.6rem);
    font-weight: 900;
    color: var(--text-white);
    letter-spacing: -0.025em;
    margin-bottom: 14px;
    line-height: 1.1;
}
.lp-cta-p {
    font-size: clamp(0.9rem, 1.5vw, 1rem);
    color: var(--text-muted);
    line-height: 1.7;
    margin-bottom: 32px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}
.lp-cta-btns {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
}
.lp-cta-note {
    margin-top: 20px;
    font-size: 0.8rem;
    color: var(--text-muted);
    opacity: 0.7;
}

/* Light theme overrides */
[data-theme="light"] .lp-console-card {
    background: #FFFFFF;
    border-color: #E2E8F0;
    box-shadow: 0 20px 50px rgba(0,0,0,0.06);
}
[data-theme="light"] .lp-balance-section {
    background: #F0F9FF;
    border-color: #BAE6FD;
}
[data-theme="light"] .lp-task-row {
    background: #F8FAFC;
    border-color: #E2E8F0;
}
[data-theme="light"] .lp-step,
[data-theme="light"] .lp-feature-card,
[data-theme="light"] .lp-trust-card,
[data-theme="light"] .lp-faq-item,
[data-theme="light"] .lp-opp-pill {
    background: #FFFFFF;
    border-color: #E2E8F0;
}
[data-theme="light"] .lp-step:hover,
[data-theme="light"] .lp-feature-card:hover,
[data-theme="light"] .lp-trust-card:hover,
[data-theme="light"] .lp-faq-item.open,
[data-theme="light"] .lp-opp-pill:hover {
    border-color: #0284C7;
}
[data-theme="light"] .lp-stats {
    background: #FFFFFF;
    border-color: #E2E8F0;
}
[data-theme="light"] .lp-opps-section {
    background: #F8FAFC;
    border-color: #E2E8F0;
}
[data-theme="light"] .lp-cta-box {
    background: #FFFFFF;
    border-color: #E2E8F0;
    box-shadow: 0 20px 50px rgba(0,0,0,0.05);
}
[data-theme="light"] .lp-hero-h1 { color: #0F172A; }
[data-theme="light"] .lp-hero-sub { color: #475569; }
[data-theme="light"] .lp-step-title,
[data-theme="light"] .lp-feature-title,
[data-theme="light"] .lp-trust-title,
[data-theme="light"] .lp-section-title,
[data-theme="light"] .lp-faq-q,
[data-theme="light"] .lp-cta-h { color: #0F172A; }
[data-theme="light"] .lp-step-desc,
[data-theme="light"] .lp-feature-desc,
[data-theme="light"] .lp-trust-desc,
[data-theme="light"] .lp-section-sub,
[data-theme="light"] .lp-faq-a p,
[data-theme="light"] .lp-cta-p,
[data-theme="light"] .lp-stat-lbl,
[data-theme="light"] .lp-task-name { color: #64748B; }
[data-theme="light"] .lp-balance-amt,
[data-theme="light"] .lp-balance-item-val { color: #0F172A; }
[data-theme="light"] .lp-stat-num { color: #0284C7; }
[data-theme="light"] .lp-opp-pill { color: #334155; }

/* Responsive */
@media (max-width: 1024px) {
    .lp-features-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
    .lp-hero-inner { grid-template-columns: 1fr; gap: 48px; }
    .lp-steps { grid-template-columns: 1fr; }
    .lp-opps-inner { grid-template-columns: 1fr; }
    .lp-trust-cards { grid-template-columns: 1fr; }
    .lp-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .lp-stat { border-right: none; border-bottom: 1px solid rgba(56,189,248,0.08); }
    .lp-stat:nth-child(odd) { border-right: 1px solid rgba(56,189,248,0.08); }
    .lp-stat:nth-last-child(-n+2) { border-bottom: none; }
}
@media (max-width: 640px) {
    .lp-features-grid { grid-template-columns: 1fr; }
    .lp-hero-btns { flex-direction: column; }
    .lp-btn-primary, .lp-btn-outline { justify-content: center; width: 100%; }
    .lp-cta-btns { flex-direction: column; align-items: center; }
    .lp-stats-grid { grid-template-columns: 1fr 1fr; }
    .lp-trust-cards { grid-template-columns: 1fr; }
}

/* Scroll reveal */
.lp-reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.55s ease, transform 0.55s ease;
}
.lp-reveal.visible {
    opacity: 1;
    transform: translateY(0);
}
</style>

<!-- ===== HERO SECTION ===== -->
<section class="lp-hero" id="home">
    <div class="container">
        <div class="lp-hero-inner">
            <!-- Left: Copy -->
            <div class="lp-hero-copy">
                <div class="lp-hero-badge">
                    <span class="lp-hero-badge-dot"></span>
                    Nigeria's Premier Digital Earning Platform
                </div>
                <h1 class="lp-hero-h1">
                    Turn Screen Time<br>Into <span class="accent">Real Naira.</span>
                </h1>
                <p class="lp-hero-sub">
                    Complete sponsored micro-tasks, share links with friends, and earn real cash directly to any Nigerian bank account. One-time activation. Zero monthly fees. Start today.
                </p>
                <div class="lp-hero-btns">
                    <a href="register.php" class="lp-btn-primary">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="5 12 12 5 19 12"></polyline><polyline points="12 19 12 5"></polyline></svg>
                        Start Earning Now
                    </a>
                    <a href="forecaster.php" class="lp-btn-outline">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        Estimate Your Earnings
                    </a>
                </div>
                <div class="lp-hero-trust">
                    <div class="lp-trust-dot">IX</div>
                    <span>Verified daily settlements to <strong>GTBank, OPay, Kuda, PalmPay</strong> and more.</span>
                </div>
            </div>

            <!-- Right: Earnings Console -->
            <div class="lp-console-card lp-reveal">
                <div class="lp-console-header">
                    <div class="lp-console-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path></svg>
                        Member Wallet
                    </div>
                    <div class="lp-live-pill">
                        <span class="lp-live-dot"></span>
                        Live Earnings
                    </div>
                </div>

                <div class="lp-balance-section">
                    <div class="lp-balance-label">
                        Available Balance
                        <span class="lp-instant-badge">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Instant
                        </span>
                    </div>
                    <div class="lp-balance-amt" id="heroBalance">&#8358;18,450.00</div>
                    <div class="lp-balance-grid">
                        <div>
                            <div class="lp-balance-item-label">
                                <span class="lp-balance-item-dot" style="background:#38BDF8;box-shadow:0 0 4px rgba(56,189,248,0.6)"></span>
                                Task Points
                            </div>
                            <div class="lp-balance-item-val"><span id="heroPoints">2,450</span> PTS</div>
                        </div>
                        <div>
                            <div class="lp-balance-item-label">
                                <span class="lp-balance-item-dot" style="background:#7DD3FC;box-shadow:0 0 4px rgba(125,211,252,0.5)"></span>
                                Referral Cash
                            </div>
                            <div class="lp-balance-item-val"><span id="heroRef">&#8358;16,000</span>.00</div>
                        </div>
                    </div>
                </div>

                <div class="lp-task-list">
                    <div class="lp-task-row">
                        <div class="lp-task-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                        </div>
                        <span class="lp-task-name"><?= htmlspecialchars($siteContent['hero_task1_title'] ?? 'Watch 30s clip & complete social task') ?></span>
                        <span class="lp-task-badge"><?= htmlspecialchars($siteContent['hero_task1_badge'] ?? '+150 PTS') ?></span>
                    </div>
                    <div class="lp-task-row">
                        <div class="lp-task-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <span class="lp-task-name"><?= htmlspecialchars($siteContent['hero_task2_title'] ?? 'Daily Lucky Spin Wheel reward') ?></span>
                        <span class="lp-task-badge"><?= htmlspecialchars($siteContent['hero_task2_badge'] ?? 'Free Spin') ?></span>
                    </div>
                    <div class="lp-task-row">
                        <div class="lp-task-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                        </div>
                        <span class="lp-task-name"><?= htmlspecialchars($siteContent['hero_task3_title'] ?? 'Redeem points to 1GB mobile data') ?></span>
                        <span class="lp-task-badge"><?= htmlspecialchars($siteContent['hero_task3_badge'] ?? 'Instant') ?></span>
                    </div>
                    <div class="lp-task-row">
                        <div class="lp-task-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <span class="lp-task-name"><?= htmlspecialchars($siteContent['hero_task4_title'] ?? 'Earn cash for every referral') ?></span>
                        <span class="lp-task-badge"><?= htmlspecialchars($siteContent['hero_task4_badge'] ?? '+&#8358;250 Cash') ?></span>
                    </div>
                </div>

                <a href="register.php" class="lp-console-cta">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    <?= htmlspecialchars($siteContent['hero_wallet_btn_text'] ?? 'Claim 100 PTS Welcome Bonus') ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ===== STATS STRIP ===== -->
<div class="lp-stats">
    <div class="container">
        <div class="lp-stats-grid">
            <div class="lp-stat lp-reveal">
                <span class="lp-stat-num">24/7</span>
                <span class="lp-stat-lbl">Automated Task Delivery</span>
            </div>
            <div class="lp-stat lp-reveal">
                <span class="lp-stat-num">&#8358;1,000</span>
                <span class="lp-stat-lbl">Low Minimum Payout</span>
            </div>
            <div class="lp-stat lp-reveal">
                <span class="lp-stat-num">&#8358;250+</span>
                <span class="lp-stat-lbl">Per Referral Earned</span>
            </div>
            <div class="lp-stat lp-reveal">
                <span class="lp-stat-num">100%</span>
                <span class="lp-stat-lbl">Direct Bank Settlement</span>
            </div>
        </div>
    </div>
</div>

<!-- ===== HOW IT WORKS ===== -->
<section class="lp-section" id="how">
    <div class="container">
        <div class="lp-section-header center">
            <div class="lp-section-tag">How It Works</div>
            <h2 class="lp-section-title">Start Earning in <span style="color:var(--sky-vibrant)">3 Simple Steps</span></h2>
            <p class="lp-section-sub">No complicated requirements. No monthly subscriptions. Just direct access to daily earning opportunities and instant bank payouts.</p>
        </div>

        <div class="lp-steps">
            <div class="lp-step lp-reveal">
                <div class="lp-step-n">Step 01</div>
                <div class="lp-step-icon-wrap">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                </div>
                <h3 class="lp-step-title">Register & Activate</h3>
                <p class="lp-step-desc">Create your account using an authorized vendor coupon code. One-time activation fee of &#8358;<?= number_format(MEMBERSHIP_FEE) ?> unlocks full lifetime access. You immediately receive 100 PTS as a welcome bonus.</p>
            </div>
            <div class="lp-step lp-reveal" style="transition-delay:0.1s">
                <div class="lp-step-n">Step 02</div>
                <div class="lp-step-icon-wrap">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                </div>
                <h3 class="lp-step-title">Complete Tasks & Refer</h3>
                <p class="lp-step-desc">Engage with daily sponsored micro-tasks, video campaigns, and surveys — each earning <?= TASK_POINTS_RATE ?> PTS. Share your referral link and earn &#8358;<?= number_format(REFERRAL_CASH_BONUS) ?> instant cash for every person who joins.</p>
            </div>
            <div class="lp-step lp-reveal" style="transition-delay:0.2s">
                <div class="lp-step-n">Step 03</div>
                <div class="lp-step-icon-wrap">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="12" y1="8" x2="12" y2="12"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                </div>
                <h3 class="lp-step-title">Withdraw to Your Bank</h3>
                <p class="lp-step-desc">Withdraw your referral earnings directly to any Nigerian commercial bank — GTBank, Kuda, OPay, PalmPay, Access Bank, and more. Or convert task points to 1GB mobile data instantly.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== FEATURES ===== -->
<section class="lp-section" id="features" style="padding-top:0">
    <div class="container">
        <div class="lp-section-header">
            <div class="lp-section-tag">Platform Features</div>
            <h2 class="lp-section-title">Everything You Need to <span style="color:var(--sky-vibrant)">Earn Daily</span></h2>
            <p class="lp-section-sub">A full suite of earning tools, community features, and financial utilities — all in one platform.</p>
        </div>

        <div class="lp-features-grid">
            <div class="lp-feature-card lp-reveal">
                <div class="lp-feature-icon" style="background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.18);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                </div>
                <div class="lp-feature-title">Daily Sponsored Tasks</div>
                <div class="lp-feature-desc">Complete short video campaigns, link-sharing gigs, and sponsored social tasks for 150 PTS each — credited immediately to your wallet.</div>
                <span class="lp-feature-badge">150 PTS / Task</span>
            </div>
            <div class="lp-feature-card lp-reveal" style="transition-delay:0.07s">
                <div class="lp-feature-icon" style="background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.18);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <div class="lp-feature-title">Referral Cash Rewards</div>
                <div class="lp-feature-desc">Earn &#8358;<?= number_format(REFERRAL_CASH_BONUS) ?> in real cash for every verified member you refer. Build your affiliate network and multiply your passive income stream effortlessly.</div>
                <span class="lp-feature-badge">&#8358;<?= number_format(REFERRAL_CASH_BONUS) ?> / Referral</span>
            </div>
            <div class="lp-feature-card lp-reveal" style="transition-delay:0.14s">
                <div class="lp-feature-icon" style="background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.18);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                </div>
                <div class="lp-feature-title">Daily Lucky Spin Wheel</div>
                <div class="lp-feature-desc">Get one free spin of the Lucky Wheel every single day. Win bonus task points, cash prizes, airtime vouchers, or special bonus multipliers.</div>
                <span class="lp-feature-badge">Free Daily Spin</span>
            </div>
            <div class="lp-feature-card lp-reveal" style="transition-delay:0.07s">
                <div class="lp-feature-icon" style="background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.18);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                </div>
                <div class="lp-feature-title">VTU Airtime & Data</div>
                <div class="lp-feature-desc">Redeem your accumulated task points for discounted 30-day SME mobile data bundles or airtime on MTN, Airtel, Glo, and 9mobile instantly from your dashboard.</div>
                <span class="lp-feature-badge">Instant Top-Up</span>
            </div>
            <div class="lp-feature-card lp-reveal" style="transition-delay:0.14s">
                <div class="lp-feature-icon" style="background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.18);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><line x1="12" y1="6" x2="12" y2="8"></line><line x1="12" y1="16" x2="12" y2="18"></line></svg>
                </div>
                <div class="lp-feature-title">OTC Token Trading</div>
                <div class="lp-feature-desc">Buy and sell unlisted tokens (VERY, RUBI) through our trusted peer-to-peer OTC desk with escrow protection and direct NGN bank settlement at competitive rates.</div>
                <span class="lp-feature-badge">P2P Escrow</span>
            </div>
            <div class="lp-feature-card lp-reveal" style="transition-delay:0.21s">
                <div class="lp-feature-icon" style="background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.18);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <div class="lp-feature-title">Advertise Your Business</div>
                <div class="lp-feature-desc">Promote your product or service to thousands of active platform members through our sponsored campaign system. Targeted audience, measurable results, and affordable pricing.</div>
                <span class="lp-feature-badge">Reach Members</span>
            </div>
        </div>
    </div>
</section>

<!-- ===== OPPORTUNITIES / QUICK LINKS ===== -->
<section class="lp-opps-section" id="explore">
    <div class="container">
        <div class="lp-opps-inner">
            <div class="lp-reveal">
                <div class="lp-section-tag">Platform Access</div>
                <h2 class="lp-section-title">Everything Is a<br><span style="color:var(--sky-vibrant)">Click Away</span></h2>
                <p class="lp-section-sub">From revenue calculators to top earner boards — explore all the tools and opportunities we have for you.</p>
                <div class="lp-opps-cta">
                    <a href="jobbers.php" class="lp-btn-primary" style="display:inline-flex;width:auto;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        Explore All Opportunities
                    </a>
                </div>
            </div>
            <div>
                <div class="lp-opps-pills lp-reveal" style="transition-delay:0.1s">
                    <a href="forecaster.php" class="lp-opp-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        Revenue Forecaster
                    </a>
                    <a href="membership.php" class="lp-opp-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12l4 6-10 13L2 9z"></path></svg>
                        Membership Access
                    </a>
                    <a href="jobbers.php" class="lp-opp-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path><rect x="2" y="6" width="20" height="14" rx="2"></rect></svg>
                        Jobbers &amp; Opportunities
                    </a>
                    <a href="tokens.php" class="lp-opp-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><line x1="12" y1="6" x2="12" y2="8"></line><line x1="12" y1="16" x2="12" y2="18"></line></svg>
                        OTC Token Desk
                    </a>
                    <a href="advertise.php" class="lp-opp-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        Advertise Your Product
                    </a>
                    <a href="vendors.php" class="lp-opp-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                        Verified Vendors
                    </a>
                    <a href="verify.php" class="lp-opp-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        Verify Activation PIN
                    </a>
                    <a href="leaderboard.php" class="lp-opp-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2z"></path></svg>
                        Top Earners Board
                    </a>
                    <a href="javascript:void(0)" onclick="openTelegramCommunityModal()" class="lp-opp-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
                        Telegram Community
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== WHY TRUST US ===== -->
<section class="lp-section" id="about">
    <div class="container">
        <div class="lp-section-header center">
            <div class="lp-section-tag">About INNOVATIONX</div>
            <h2 class="lp-section-title">Built for <span style="color:var(--sky-vibrant)">Transparency & Speed</span></h2>
            <p class="lp-section-sub">INNOVATIONX is a verified Nigerian digital earning platform designed to give everyday people genuine access to automated income streams — no experience required.</p>
        </div>

        <div class="lp-trust-cards">
            <div class="lp-trust-card lp-reveal">
                <div class="lp-trust-badge">Automated Payouts</div>
                <div class="lp-trust-title">Direct Bank Transfers</div>
                <div class="lp-trust-desc">Withdraw your referral cash earnings directly to any Nigerian commercial or microfinance bank — GTBank, Kuda, OPay, PalmPay, Access, Zenith. Real-time automated settlement with zero manual delays.</div>
            </div>
            <div class="lp-trust-card lp-reveal" style="transition-delay:0.1s">
                <div class="lp-trust-badge">Instant Crediting</div>
                <div class="lp-trust-title">Live Task Crediting Engine</div>
                <div class="lp-trust-desc">Each completed sponsored task, video campaign, and survey link is verified and credited to your wallet immediately. No manual reviews. No waiting. Your points balance updates in real time.</div>
            </div>
            <div class="lp-trust-card lp-reveal" style="transition-delay:0.2s">
                <div class="lp-trust-badge">Flexible Rewards</div>
                <div class="lp-trust-title">Multi-Wallet Freedom</div>
                <div class="lp-trust-desc">Your earnings split across two wallets: a Task Points wallet for VTU data redemptions, surveys, and spin wins — and a Referral Cash wallet for direct NGN bank withdrawals. Maximum flexibility.</div>
            </div>
        </div>
    </div>
</section>

<!-- ===== FAQ ===== -->
<section class="lp-section" id="faq" style="padding-top:0">
    <div class="container">
        <div class="lp-section-header center">
            <div class="lp-section-tag">FAQ</div>
            <h2 class="lp-section-title">Frequently Asked <span style="color:var(--sky-vibrant)">Questions</span></h2>
            <p class="lp-section-sub">Everything you need to know before you start earning with INNOVATIONX.</p>
        </div>

        <div class="lp-faq-list">
<?php
$faqPath = __DIR__ . '/faq.json';
$faqData = file_exists($faqPath) ? json_decode(file_get_contents($faqPath), true) : [];
if (empty($faqData) || !is_array($faqData)) {
    $faqData = [
        [
            'question' => 'How much does it cost to join INNOVATIONX?',
            'answer' => 'Joining INNOVATIONX requires a one-time lifetime membership activation fee of only &#8358;' . number_format(MEMBERSHIP_FEE) . '. There are zero hidden monthly maintenance fees, and you receive an immediate 100 PTS welcome bonus upon activation.'
        ],
        [
            'question' => 'How do I withdraw my earnings to my bank account?',
            'answer' => 'Withdrawals can be requested directly from your dashboard to any commercial or microfinance bank in Nigeria (e.g. GTBank, Kuda, OPay, PalmPay, Access, Zenith). Once you meet the minimum threshold of &#8358;' . number_format(MIN_WITHDRAWAL_NAIRA) . ', payouts are processed swiftly via our automated payout system.'
        ],
        [
            'question' => 'Can I earn without referring anyone?',
            'answer' => 'Yes, absolutely! Referrals are 100% optional. You can earn and withdraw consistently purely by completing your daily sponsored tasks, participating in surveys, spinning the Lucky Wheel, and claiming daily login bonuses.'
        ],
        [
            'question' => 'What is the activation coupon and where do I get one?',
            'answer' => 'An activation coupon is a unique PIN that unlocks your full platform membership. You obtain one from our list of verified authorized vendors on the Vendors page. Each coupon is &#8358;' . number_format(MEMBERSHIP_FEE) . ' and grants lifetime access.'
        ],
        [
            'question' => 'What services does INNOVATIONX offer?',
            'answer' => 'INNOVATIONX provides daily sponsored tasks, referral affiliate rewards, a Lucky Spin Wheel, OTC unlisted token trading (VERY, RUBI), VTU airtime and data top-up, a sponsored advertising marketplace, an Opportunities Hub with Web3 mining and digital gigs, revenue forecasting tools, and direct NGN bank withdrawals.'
        ],
        [
            'question' => 'Is this platform safe and legitimate?',
            'answer' => 'INNOVATIONX is a transparent, verified digital earning platform. All task completions, survey submissions, and referral credits are tracked and processed automatically. We do not ask for sensitive personal or banking login credentials — only your bank name, account number, and account name for withdrawal settlement.'
        ],
    ];
}
foreach ($faqData as $idx => $item): ?>
            <div class="lp-faq-item<?= $idx === 0 ? ' open' : '' ?>" onclick="toggleFaq(this)">
                <div class="lp-faq-q">
                    <span><?= htmlspecialchars($item['question'] ?? '') ?></span>
                    <svg class="lp-faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="lp-faq-a">
                    <p><?= $item['answer'] ?? '' ?></p>
                </div>
            </div>
<?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== FINAL CTA ===== -->
<section class="lp-cta-section">
    <div class="container">
        <div class="lp-cta-box lp-reveal">
            <div class="lp-cta-h">Ready to Start Earning?</div>
            <p class="lp-cta-p">Join INNOVATIONX today. One-time activation, lifetime access. Complete tasks, grow your referral network, and withdraw real cash to your bank account every day.</p>
            <div class="lp-cta-btns">
                <a href="register.php" class="lp-btn-primary">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="5 12 12 5 19 12"></polyline><polyline points="12 19 12 5"></polyline></svg>
                    Create Account — &#8358;<?= number_format(MEMBERSHIP_FEE) ?> Lifetime
                </a>
                <a href="vendors.php" class="lp-btn-outline">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                    Find Verified Vendors
                </a>
            </div>
            <div class="lp-cta-note">No monthly fees. No hidden charges. Withdraw anytime after meeting minimum threshold.</div>
        </div>
    </div>
</section>

<script>
// Referral param to localStorage
(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var ref = urlParams.get('ref');
    if (ref) {
        try { localStorage.setItem('ix_ref', ref); } catch(e) {}
    }
})();

// FAQ toggle
function toggleFaq(el) {
    var isOpen = el.classList.contains('open');
    document.querySelectorAll('.lp-faq-item').forEach(function(item) {
        item.classList.remove('open');
    });
    if (!isOpen) el.classList.add('open');
}

// Scroll reveal
(function() {
    var els = document.querySelectorAll('.lp-reveal');
    var io = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    els.forEach(function(el) { io.observe(el); });

    // Hero card is visible above fold — trigger immediately
    var heroCard = document.querySelector('.lp-hero .lp-reveal');
    if (heroCard) {
        setTimeout(function() { heroCard.classList.add('visible'); }, 150);
    }
})();

// Animated wallet balance counter
(function() {
    var balances = [12750, 18450, 9200, 15800, 22100, 8600, 16340];
    var pts = [1850, 2450, 900, 3200, 1100, 2850, 1650];
    var refs = [8000, 16000, 5500, 12500, 19500, 7250, 14000];
    var i = 0;
    function fmt(n) { return n.toLocaleString('en-NG'); }

    function tick() {
        var b = document.getElementById('heroBalance');
        var p = document.getElementById('heroPoints');
        var r = document.getElementById('heroRef');
        if (b && p && r) {
            b.textContent = '\u20A6' + fmt(balances[i]);
            p.textContent = fmt(pts[i]);
            r.textContent = '\u20A6' + fmt(refs[i]);
            i = (i + 1) % balances.length;
        }
    }
    setInterval(tick, 2800);
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
