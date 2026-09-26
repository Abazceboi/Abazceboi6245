<?php
$pageTitle = 'Unlisted Tokens Desk | Buy & Sell VERY, RUBI & OTC Assets';
$pageDesc = 'Secure peer-to-peer and OTC trading desk for unlisted and pre-market crypto tokens including VERY, RUBI, SIDRA, and PI with instant verified proofs.';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$tokensConfigFile = __DIR__ . '/config/tokens_config.json';
$tokensConfig = file_exists($tokensConfigFile) ? json_decode(file_get_contents($tokensConfigFile), true) : [];
$tokensList = $tokensConfig['tokens'] ?? [];
$platformBank = $tokensConfig['platform_bank'] ?? [
    'bank_name' => 'OPay Digital Services',
    'account_number' => '8102345678',
    'account_name' => 'INNOVATIONX OTC TRADING',
    'instructions' => 'Transfer exact amount to the account above and upload receipt proof.'
];
?>

<main class="tokens-page" style="min-height:100vh;padding:120px 20px 80px;background:radial-gradient(circle at 50% 10%, rgba(56, 189, 248, 0.12), transparent 60%), #0A0A0F">
    <div class="container" style="max-width:1180px;margin:0 auto">
        
        <!-- Header Title Banner -->
        <div style="text-align:center;margin-bottom:36px">
            <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 16px;border-radius:20px;background:rgba(56, 189, 248, 0.1);border:1px solid rgba(56, 189, 248, 0.25);color:#38BDF8;font-size:0.8rem;font-weight:700;margin-bottom:14px">
                <span class="live-dot" style="background:#38BDF8;box-shadow:0 0 8px #38BDF8"></span>
                <span>P2P &amp; OTC Token Market Live</span>
            </div>
            <h1 style="font-size:clamp(1.9rem, 4vw, 2.7rem);font-weight:900;letter-spacing:-0.5px;color:#FFFFFF;margin-bottom:10px;line-height:1.2">
                Buy &amp; Sell <span style="background:linear-gradient(135deg, #38BDF8, #818CF8);-webkit-background-clip:text;-webkit-text-fill-color:transparent">Unlisted Tokens</span>
            </h1>
            <p style="font-size:0.95rem;color:#94A3B8;max-width:650px;margin:0 auto;line-height:1.5">
                Trade pre-market and mining tokens like <strong>VERY</strong>, <strong>RUBI</strong>, <strong>SIDRA</strong>, and <strong>PI</strong> at premium rates with verified escrow, instant proof uploading, and direct bank payouts.
            </p>
        </div>

        <!-- Token Market Cards Grid (Shows views & trades count per token) -->
        <div style="margin-bottom:34px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
                <div style="font-size:1.05rem;font-weight:800;color:#FFFFFF;display:flex;align-items:center;gap:8px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.5"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    Live Token Rates &amp; Activity
                </div>
                <div style="font-size:0.78rem;color:#64748B">
                    Real-time market view counts &amp; completed trades
                </div>
            </div>

            <div id="tokensMarketGrid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px">
                <?php foreach ($tokensList as $tok): ?>
                <div class="token-market-card" id="card_<?= htmlspecialchars($tok['symbol']) ?>" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:20px;position:relative;overflow:hidden;transition:all 0.25s ease">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:42px;height:42px;border-radius:12px;background:rgba(56, 189, 248, 0.12);border:1px solid rgba(56, 189, 248, 0.3);display:flex;align-items:center;justify-content:center;font-size:1.3rem">
                                <?= htmlspecialchars($tok['icon'] ?? '🪙') ?>
                            </div>
                            <div>
                                <div style="font-size:1.05rem;font-weight:900;color:#FFFFFF"><?= htmlspecialchars($tok['symbol']) ?></div>
                                <div style="font-size:0.72rem;color:#94A3B8"><?= htmlspecialchars($tok['name']) ?></div>
                            </div>
                        </div>
                        <span style="font-size:0.68rem;padding:3px 8px;border-radius:6px;background:rgba(56, 189, 248, 0.12);color:#38BDF8;font-weight:700">
                            <?= htmlspecialchars($tok['network']) ?>
                        </span>
                    </div>

                    <!-- Live Views & Trades Metric Pill -->
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;border-radius:9px;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.05);margin-bottom:14px;font-size:0.74rem">
                        <div style="display:flex;align-items:center;gap:5px;color:#7DD3FC">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <span id="viewCount_<?= htmlspecialchars($tok['symbol']) ?>"><?= number_format($tok['views_count'] ?? 1200) ?></span> views
                        </div>
                        <div style="display:flex;align-items:center;gap:5px;color:#34D399">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span id="tradeCount_<?= htmlspecialchars($tok['symbol']) ?>"><?= number_format($tok['trades_count'] ?? 450) ?></span> trades
                        </div>
                    </div>

                    <!-- Buy / Sell Rates -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
                        <div style="padding:8px 10px;border-radius:9px;background:rgba(56, 189, 248, 0.06);border:1px solid rgba(56, 189, 248, 0.18)">
                            <div style="font-size:0.65rem;color:#7DD3FC;font-weight:700;text-transform:uppercase">We Sell (Buy Rate)</div>
                            <div style="font-size:1.05rem;font-weight:900;color:#FFFFFF">₦<?= number_format($tok['buy_rate']) ?></div>
                        </div>
                        <div style="padding:8px 10px;border-radius:9px;background:rgba(52, 211, 153, 0.06);border:1px solid rgba(52, 211, 153, 0.18)">
                            <div style="font-size:0.65rem;color:#6EE7B7;font-weight:700;text-transform:uppercase">We Buy (Sell Rate)</div>
                            <div style="font-size:1.05rem;font-weight:900;color:#FFFFFF">₦<?= number_format($tok['sell_rate']) ?></div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                        <button type="button" onclick="selectTokenToTrade('<?= htmlspecialchars($tok['symbol']) ?>', 'buy')" class="btn-dash-action btn-dash-primary" style="justify-content:center;height:36px;font-size:0.78rem">
                            Buy <?= htmlspecialchars($tok['symbol']) ?>
                        </button>
                        <button type="button" onclick="selectTokenToTrade('<?= htmlspecialchars($tok['symbol']) ?>', 'sell')" class="btn-dash-action btn-dash-secondary" style="justify-content:center;height:36px;font-size:0.78rem">
                            Sell <?= htmlspecialchars($tok['symbol']) ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Interactive Trade Desk & Proof Upload Section -->
        <div id="tradeDeskSection" style="background:linear-gradient(180deg, rgba(255,255,255,0.04) 0%, rgba(16, 20, 35, 0.95) 100%);border:1px solid rgba(56, 189, 248, 0.22);border-radius:20px;padding:32px 28px;box-shadow:0 15px 45px rgba(0,0,0,0.5);margin-bottom:34px">
            
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:24px;border-bottom:1px solid rgba(255,255,255,0.08);padding-bottom:18px">
                <div>
                    <h2 style="font-size:1.35rem;font-weight:900;color:#FFFFFF;margin-bottom:4px;display:flex;align-items:center;gap:10px">
                        <span id="tradeTypeHeading">Buy Tokens</span>
                        <span id="selectedTokenBadge" style="font-size:0.75rem;padding:3px 10px;border-radius:8px;background:rgba(56, 189, 248, 0.15);color:#38BDF8;border:1px solid rgba(56, 189, 248, 0.3)">VERY</span>
                    </h2>
                    <div style="font-size:0.8rem;color:#94A3B8">Fill in your trade details, attach transfer/payment proof, and submit for verification.</div>
                </div>

                <!-- Buy / Sell Toggle Switcher -->
                <div style="display:flex;align-items:center;background:rgba(0,0,0,0.4);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:4px">
                    <button type="button" id="tabBtnBuy" onclick="setTradeType('buy')" style="padding:8px 20px;border-radius:9px;background:linear-gradient(135deg, #0284C7, #38BDF8);color:#FFFFFF;border:none;font-size:0.82rem;font-weight:800;cursor:pointer;transition:all 0.2s ease">
                        Buy Tokens
                    </button>
                    <button type="button" id="tabBtnSell" onclick="setTradeType('sell')" style="padding:8px 20px;border-radius:9px;background:transparent;color:#94A3B8;border:none;font-size:0.82rem;font-weight:800;cursor:pointer;transition:all 0.2s ease">
                        Sell Tokens
                    </button>
                </div>
            </div>

            <!-- Trade Form -->
            <form id="tokenTradeForm" onsubmit="handleTokenTradeSubmit(event)">
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:20px;margin-bottom:20px">
                    
                    <!-- Token Selector -->
                    <div>
                        <label style="display:block;font-size:0.78rem;font-weight:700;color:#BAE6FD;margin-bottom:6px">Select Token to Trade</label>
                        <select id="tradeTokenSelect" onchange="handleTokenSelectChange(this.value)" class="admin-select" style="width:100%;height:44px;font-size:0.88rem;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.15);color:#FFFFFF;border-radius:10px;padding:0 12px">
                            <?php foreach ($tokensList as $tok): ?>
                            <option value="<?= htmlspecialchars($tok['symbol']) ?>" data-buy="<?= htmlspecialchars($tok['buy_rate']) ?>" data-sell="<?= htmlspecialchars($tok['sell_rate']) ?>" data-min="<?= htmlspecialchars($tok['min_trade']) ?>" data-max="<?= htmlspecialchars($tok['max_trade']) ?>" data-network="<?= htmlspecialchars($tok['network']) ?>" data-wallet="<?= htmlspecialchars($tok['platform_deposit_address']) ?>" data-memo="<?= htmlspecialchars($tok['deposit_memo']) ?>">
                                <?= htmlspecialchars($tok['symbol']) ?> — <?= htmlspecialchars($tok['name']) ?> (<?= htmlspecialchars($tok['network']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Token Amount -->
                    <div>
                        <label style="display:block;font-size:0.78rem;font-weight:700;color:#BAE6FD;margin-bottom:6px">
                            Token Quantity <span id="tokenQtyLimits" style="color:#64748B;font-weight:500">(Min: 10)</span>
                        </label>
                        <input type="number" step="any" min="1" id="tradeTokenAmount" oninput="calculateTradeTotal()" placeholder="e.g. 100" required class="admin-input" style="width:100%;height:44px;font-size:0.95rem;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.15);color:#FFFFFF;border-radius:10px;padding:0 12px">
                    </div>

                    <!-- Calculated Naira Total -->
                    <div>
                        <label style="display:block;font-size:0.78rem;font-weight:700;color:#BAE6FD;margin-bottom:6px">
                            Calculated Total (NGN) <span id="tradeRateDisplay" style="color:#38BDF8;font-weight:600">@ ₦350/token</span>
                        </label>
                        <div style="height:44px;background:rgba(56, 189, 248, 0.08);border:1px solid rgba(56, 189, 248, 0.25);border-radius:10px;display:flex;align-items:center;padding:0 14px;color:#38BDF8;font-weight:900;font-size:1.1rem">
                            <span id="tradeCalculatedNaira">₦0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Platform Instructions Box -->
                <div id="paymentInstructionsCard" style="background:rgba(56, 189, 248, 0.05);border:1px dashed rgba(56, 189, 248, 0.3);border-radius:14px;padding:16px 20px;margin-bottom:22px">
                    
                    <!-- BUY MODE Instructions -->
                    <div id="instructionsBuy">
                        <div style="font-size:0.8rem;font-weight:800;color:#38BDF8;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px">
                            Step 1: Make Naira Transfer to Platform Escrow
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:12px">
                            <div>
                                <div style="font-size:0.72rem;color:#94A3B8">Bank Name &amp; Account Number:</div>
                                <div style="font-size:1.05rem;font-weight:900;color:#FFFFFF">
                                    <span id="dispBankName"><?= htmlspecialchars($platformBank['bank_name']) ?></span> — <span id="dispBankAcc"><?= htmlspecialchars($platformBank['account_number']) ?></span>
                                </div>
                                <div style="font-size:0.74rem;color:#7DD3FC">Account Name: <span id="dispBankTitle"><?= htmlspecialchars($platformBank['account_name']) ?></span></div>
                            </div>
                            <button type="button" onclick="copyText('<?= htmlspecialchars($platformBank['account_number']) ?>', this)" class="btn-dash-action btn-dash-secondary" style="height:34px;font-size:0.76rem">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                Copy Account
                            </button>
                        </div>
                        <div class="withdraw-form-group" style="margin-bottom:0">
                            <label style="display:block;font-size:0.78rem;font-weight:700;color:#BAE6FD;margin-bottom:6px">Your Receiving Wallet Address / App ID *</label>
                            <input type="text" id="tradeUserWallet" class="admin-input" placeholder="e.g. your VERY wallet address or Rubi Username" style="width:100%;height:42px;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.15);color:#FFFFFF;border-radius:10px;padding:0 12px;font-size:0.88rem">
                        </div>
                    </div>

                    <!-- SELL MODE Instructions -->
                    <div id="instructionsSell" style="display:none">
                        <div style="font-size:0.8rem;font-weight:800;color:#34D399;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px">
                            Step 1: Transfer Tokens to Platform Receiving Vault
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:12px">
                            <div style="min-width:0;flex:1">
                                <div style="font-size:0.72rem;color:#94A3B8">Platform Deposit Address / Username:</div>
                                <div style="font-size:0.95rem;font-weight:900;color:#FFFFFF;word-break:break-all" id="dispDepositAddress">
                                    very1q84m5z9g3k2p7x6w0c1v8b4n7m9l2j5h4k3e
                                </div>
                                <div style="font-size:0.74rem;color:#6EE7B7">Memo / Transfer Note: <span id="dispDepositMemo">IX-VERY-OTC</span></div>
                            </div>
                            <button type="button" id="btnCopyDepositAddress" onclick="copyDepositAddress(this)" class="btn-dash-action btn-dash-secondary" style="height:34px;font-size:0.76rem">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                Copy Address
                            </button>
                        </div>
                        
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <div>
                                <label style="display:block;font-size:0.78rem;font-weight:700;color:#BAE6FD;margin-bottom:6px">Your Bank Name (For Payout) *</label>
                                <input type="text" id="tradePayoutBank" class="admin-input" placeholder="e.g. OPay / PalmPay / GTBank" style="width:100%;height:42px;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.15);color:#FFFFFF;border-radius:10px;padding:0 12px;font-size:0.88rem">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.78rem;font-weight:700;color:#BAE6FD;margin-bottom:6px">Your Account Number &amp; Name *</label>
                                <input type="text" id="tradePayoutAccount" class="admin-input" placeholder="e.g. 0123456789 - John Doe" style="width:100%;height:42px;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.15);color:#FFFFFF;border-radius:10px;padding:0 12px;font-size:0.88rem">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proof Submission & File Upload Section (Requested by User) -->
                <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:20px;margin-bottom:24px">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                        <div style="font-size:0.92rem;font-weight:800;color:#FFFFFF;display:flex;align-items:center;gap:8px">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            Payment / Transfer Proof Submission
                        </div>
                        <span style="font-size:0.7rem;color:#7DD3FC;font-weight:600">Verification Required</span>
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="display:block;font-size:0.78rem;font-weight:700;color:#BAE6FD;margin-bottom:6px">Transaction Reference / Hash / Sender Details *</label>
                        <input type="text" id="tradeTxReference" class="admin-input" placeholder="e.g. Session ID, TxHash, or Sender Account Name" required style="width:100%;height:42px;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.15);color:#FFFFFF;border-radius:10px;padding:0 12px;font-size:0.88rem">
                    </div>

                    <!-- Direct Screenshot Proof Uploader -->
                    <div>
                        <label style="display:block;font-size:0.78rem;font-weight:700;color:#BAE6FD;margin-bottom:6px">Upload Payment Receipt Screenshot *</label>
                        <div style="border:2px dashed rgba(56, 189, 248, 0.3);border-radius:12px;padding:20px;text-align:center;background:rgba(0,0,0,0.25);position:relative;cursor:pointer" onclick="document.getElementById('tokenProofFileInput').click()">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="1.8" style="margin-bottom:8px"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <div style="font-size:0.85rem;font-weight:700;color:#FFFFFF;margin-bottom:4px">Tap or Click to Select Screenshot Proof</div>
                            <div style="font-size:0.72rem;color:#94A3B8">PNG, JPG, or WEBP receipt screenshot (Max 5MB)</div>
                            <input type="file" id="tokenProofFileInput" accept="image/*" onchange="handleTokenProofFileSelect(event)" style="display:none">
                            <input type="hidden" id="tokenProofBase64" value="">
                        </div>

                        <!-- Live Proof Preview -->
                        <div id="tokenProofPreviewWrap" style="display:none;margin-top:12px;padding:12px;background:rgba(0,0,0,0.4);border-radius:10px;border:1px solid rgba(56, 189, 248, 0.3);display:none;align-items:center;justify-content:space-between">
                            <div style="display:flex;align-items:center;gap:12px">
                                <img id="tokenProofPreviewImg" src="" alt="Proof Preview" style="width:50px;height:50px;border-radius:8px;object-fit:cover;border:1px solid rgba(255,255,255,0.15)">
                                <div>
                                    <div style="font-size:0.82rem;font-weight:700;color:#38BDF8">Receipt Attached</div>
                                    <div id="tokenProofFileName" style="font-size:0.7rem;color:#94A3B8">screenshot.png</div>
                                </div>
                            </div>
                            <button type="button" onclick="clearTokenProofUpload(event)" class="btn-dash-action btn-dash-logout" style="height:32px;font-size:0.75rem">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="btnSubmitTokenOrder" class="btn-dash-action btn-dash-primary" style="width:100%;height:48px;font-size:0.95rem;font-weight:800;border-radius:12px;justify-content:center;box-shadow:0 4px 20px rgba(56, 189, 248, 0.3)">
                    <span>Submit Trade &amp; Proof for Verification</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </form>
        </div>

        <!-- Order History & Ledger Section -->
        <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:18px;padding:24px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
                <div style="font-size:1.05rem;font-weight:800;color:#FFFFFF;display:flex;align-items:center;gap:8px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    Recent Market Trades &amp; Status
                </div>
                <button type="button" onclick="loadTokenOrders()" class="btn-dash-action btn-dash-secondary" style="height:32px;font-size:0.75rem">
                    Refresh Orders
                </button>
            </div>

            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:0.84rem;text-align:left">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.1);color:#94A3B8;font-size:0.75rem;text-transform:uppercase">
                            <th style="padding:10px 8px">Order ID</th>
                            <th style="padding:10px 8px">Type</th>
                            <th style="padding:10px 8px">Token &amp; Qty</th>
                            <th style="padding:10px 8px">Naira Value</th>
                            <th style="padding:10px 8px">Reference</th>
                            <th style="padding:10px 8px">Proof</th>
                            <th style="padding:10px 8px">Status</th>
                            <th style="padding:10px 8px">Date</th>
                        </tr>
                    </thead>
                    <tbody id="tokenOrdersTableBody">
                        <tr>
                            <td colspan="8" style="text-align:center;padding:24px;color:#64748B">Loading trades...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- Full-Screen Proof Lightbox Modal -->
<div id="tokenProofLightbox" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.85);z-index:99999;align-items:center;justify-content:center;padding:20px" onclick="closeProofLightbox()">
    <div style="position:relative;max-width:90%;max-height:90%" onclick="event.stopPropagation()">
        <img id="lightboxImg" src="" alt="Proof Screenshot" style="max-width:100%;max-height:85vh;border-radius:12px;box-shadow:0 0 35px rgba(0,0,0,0.8);border:1px solid rgba(255,255,255,0.2)">
        <button type="button" onclick="closeProofLightbox()" style="position:absolute;top:-15px;right:-15px;width:34px;height:34px;border-radius:50%;background:#EF4444;color:#FFF;border:none;font-weight:900;cursor:pointer">&times;</button>
    </div>
</div>

<script>
let currentTradeType = 'buy';
let tokensData = <?= json_encode($tokensList) ?>;
let activePlatformBank = <?= json_encode($platformBank) ?>;

function setTradeType(type) {
    currentTradeType = type;
    const btnBuy = document.getElementById('tabBtnBuy');
    const btnSell = document.getElementById('tabBtnSell');
    const heading = document.getElementById('tradeTypeHeading');
    const instBuy = document.getElementById('instructionsBuy');
    const instSell = document.getElementById('instructionsSell');
    const btnSubmit = document.getElementById('btnSubmitTokenOrder');

    if (type === 'buy') {
        btnBuy.style.background = 'linear-gradient(135deg, #0284C7, #38BDF8)';
        btnBuy.style.color = '#FFFFFF';
        btnSell.style.background = 'transparent';
        btnSell.style.color = '#94A3B8';
        heading.textContent = 'Buy Tokens';
        instBuy.style.display = 'block';
        instSell.style.display = 'none';
        btnSubmit.innerHTML = `<span>Submit Buy Order &amp; Proof for Verification</span> <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>`;
    } else {
        btnSell.style.background = 'linear-gradient(135deg, #059669, #34D399)';
        btnSell.style.color = '#FFFFFF';
        btnBuy.style.background = 'transparent';
        btnBuy.style.color = '#94A3B8';
        heading.textContent = 'Sell Tokens';
        instBuy.style.display = 'none';
        instSell.style.display = 'block';
        btnSubmit.innerHTML = `<span>Submit Sell Order &amp; Proof for Payout</span> <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>`;
    }
    calculateTradeTotal();
}

function selectTokenToTrade(symbol, type) {
    setTradeType(type);
    const select = document.getElementById('tradeTokenSelect');
    if (select) {
        select.value = symbol;
        handleTokenSelectChange(symbol);
    }
    document.getElementById('tradeDeskSection').scrollIntoView({ behavior: 'smooth' });
}

function handleTokenSelectChange(symbol) {
    document.getElementById('selectedTokenBadge').textContent = symbol;
    const select = document.getElementById('tradeTokenSelect');
    const opt = select.options[select.selectedIndex];
    if (!opt) return;

    const min = opt.getAttribute('data-min') || '1';
    const wallet = opt.getAttribute('data-wallet') || '';
    const memo = opt.getAttribute('data-memo') || '';

    document.getElementById('tokenQtyLimits').textContent = `(Min: ${min})`;
    document.getElementById('dispDepositAddress').textContent = wallet;
    document.getElementById('dispDepositMemo').textContent = memo;

    // Trigger API view counter increment silently
    fetch(`api/tokens.php?action=get_tokens&view_symbol=${symbol}`).catch(() => {});

    calculateTradeTotal();
}

function calculateTradeTotal() {
    const select = document.getElementById('tradeTokenSelect');
    const opt = select.options[select.selectedIndex];
    if (!opt) return;

    const rate = currentTradeType === 'buy' ? parseFloat(opt.getAttribute('data-buy') || 1) : parseFloat(opt.getAttribute('data-sell') || 1);
    const qty = parseFloat(document.getElementById('tradeTokenAmount').value) || 0;
    const total = qty * rate;

    document.getElementById('tradeRateDisplay').textContent = `@ ₦${rate.toLocaleString()}/token`;
    document.getElementById('tradeCalculatedNaira').textContent = `₦${total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
}

function handleTokenProofFileSelect(e) {
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        alert('File size exceeds 5MB limit. Please upload a smaller screenshot.');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(evt) {
        const b64 = evt.target.result;
        document.getElementById('tokenProofBase64').value = b64;
        document.getElementById('tokenProofPreviewImg').src = b64;
        document.getElementById('tokenProofFileName').textContent = file.name;
        document.getElementById('tokenProofPreviewWrap').style.display = 'flex';
    };
    reader.readAsDataURL(file);
}

function clearTokenProofUpload(e) {
    if (e) e.stopPropagation();
    document.getElementById('tokenProofFileInput').value = '';
    document.getElementById('tokenProofBase64').value = '';
    document.getElementById('tokenProofPreviewWrap').style.display = 'none';
}

function copyText(txt, btn) {
    navigator.clipboard.writeText(txt).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = `✓ Copied`;
        setTimeout(() => btn.innerHTML = orig, 1800);
    }).catch(() => {
        alert('Copied: ' + txt);
    });
}

function copyDepositAddress(btn) {
    const address = document.getElementById('dispDepositAddress').textContent.trim();
    copyText(address, btn);
}

function openProofLightbox(imgSrc) {
    document.getElementById('lightboxImg').src = imgSrc;
    document.getElementById('tokenProofLightbox').style.display = 'flex';
}

function closeProofLightbox() {
    document.getElementById('tokenProofLightbox').style.display = 'none';
}

async function handleTokenTradeSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitTokenOrder');
    const symbol = document.getElementById('tradeTokenSelect').value;
    const amount = parseFloat(document.getElementById('tradeTokenAmount').value);
    const txRef = document.getElementById('tradeTxReference').value.trim();
    const proofB64 = document.getElementById('tokenProofBase64').value.trim();
    const userWallet = document.getElementById('tradeUserWallet').value.trim();
    const payoutBank = document.getElementById('tradePayoutBank').value.trim();
    const payoutAccount = document.getElementById('tradePayoutAccount').value.trim();

    if (!amount || amount <= 0) {
        alert('Please enter a valid token quantity.');
        return;
    }

    if (currentTradeType === 'buy' && !userWallet) {
        alert(`Please enter your receiving ${symbol} wallet address or UID.`);
        return;
    }

    if (currentTradeType === 'sell' && (!payoutBank || !payoutAccount)) {
        alert('Please provide your bank name and account details to receive your Naira payout.');
        return;
    }

    if (!proofB64) {
        alert('Please attach your payment or token transfer screenshot proof.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = `<span>Submitting Trade &amp; Proof...</span>`;

    const currentUser = localStorage.getItem('ix_current_user') || 'Member';

    try {
        const res = await fetch('api/tokens.php?action=create_order', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                type: currentTradeType,
                token_symbol: symbol,
                token_amount: amount,
                user_id: currentUser,
                username: currentUser,
                wallet_address: userWallet,
                bank_name: payoutBank,
                account_number: payoutAccount,
                tx_reference: txRef,
                proof_image: proofB64
            })
        });
        const data = await res.json();

        if (data.status === 'success') {
            alert(`Trade Order Placed!\n\nOrder ID: ${data.order.order_id}\nToken: ${data.order.token_amount} ${data.order.token_symbol}\nValue: ₦${data.order.total_naira.toLocaleString()}\n\nYour proof has been received and queued for Super Admin verification.`);
            document.getElementById('tokenTradeForm').reset();
            clearTokenProofUpload();
            calculateTradeTotal();
            loadTokenOrders();
        } else {
            alert(data.message || 'Failed to submit order. Please check your inputs.');
        }
    } catch(err) {
        alert('Network error submitting trade. Please check your connection.');
    } finally {
        btn.disabled = false;
        setTradeType(currentTradeType);
    }
}

async function loadTokenOrders() {
    const tbody = document.getElementById('tokenOrdersTableBody');
    if (!tbody) return;

    try {
        const res = await fetch('api/tokens.php?action=get_orders');
        const data = await res.json();
        const orders = data.orders || [];

        if (orders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:#64748B">No token trades submitted yet. Be the first to trade!</td></tr>`;
            return;
        }

        tbody.innerHTML = orders.map(o => {
            const isBuy = (o.type || 'buy').toLowerCase() === 'buy';
            const typeBadge = isBuy
                ? `<span style="background:rgba(56, 189, 248, 0.15);color:#38BDF8;padding:2px 8px;border-radius:6px;font-size:0.72rem;font-weight:700">BUY</span>`
                : `<span style="background:rgba(52, 211, 153, 0.15);color:#34D399;padding:2px 8px;border-radius:6px;font-size:0.72rem;font-weight:700">SELL</span>`;

            let statusBadge = '';
            if (o.status === 'approved') {
                statusBadge = `<span style="background:rgba(16, 185, 129, 0.15);color:#10B981;padding:2px 8px;border-radius:6px;font-size:0.72rem;font-weight:700">Approved</span>`;
            } else if (o.status === 'rejected') {
                statusBadge = `<span style="background:rgba(239, 68, 68, 0.15);color:#EF4444;padding:2px 8px;border-radius:6px;font-size:0.72rem;font-weight:700">Declined</span>`;
            } else {
                statusBadge = `<span style="background:rgba(245, 158, 11, 0.15);color:#F59E0B;padding:2px 8px;border-radius:6px;font-size:0.72rem;font-weight:700">Pending Review</span>`;
            }

            const proofBtn = o.proof_image
                ? `<button type="button" onclick="openProofLightbox('${o.proof_image.replace(/'/g, "\\'")}')" class="btn-dash-action" style="height:26px;padding:0 8px;font-size:0.7rem;background:rgba(56, 189, 248, 0.1);color:#7DD3FC;border-color:rgba(56, 189, 248, 0.3)">View Proof</button>`
                : `<span style="color:#64748B;font-size:0.72rem">None</span>`;

            return `
                <tr style="border-bottom:1px solid rgba(255,255,255,0.05);color:#E2E8F0">
                    <td style="padding:10px 8px;font-weight:700;color:#38BDF8">${o.order_id}</td>
                    <td style="padding:10px 8px">${typeBadge}</td>
                    <td style="padding:10px 8px"><strong>${parseFloat(o.token_amount).toLocaleString()}</strong> ${o.token_symbol}</td>
                    <td style="padding:10px 8px;font-weight:700;color:#FFFFFF">₦${parseFloat(o.total_naira || 0).toLocaleString()}</td>
                    <td style="padding:10px 8px;font-family:monospace;font-size:0.72rem;color:#94A3B8">${o.tx_reference || 'N/A'}</td>
                    <td style="padding:10px 8px">${proofBtn}</td>
                    <td style="padding:10px 8px">${statusBadge}</td>
                    <td style="padding:10px 8px;font-size:0.72rem;color:#64748B">${(o.created_at || '').substring(0, 16)}</td>
                </tr>
            `;
        }).join('');
    } catch(err) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:20px;color:#EF4444">Error loading order ledger.</td></tr>`;
    }
}

// Initial setup
document.addEventListener('DOMContentLoaded', () => {
    handleTokenSelectChange(document.getElementById('tradeTokenSelect').value);
    loadTokenOrders();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
