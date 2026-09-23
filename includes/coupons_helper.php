<?php
/**
 * INNOVATIONX — Centralized Coupon PINs Management & Validation Helper
 * Ensures single-use coupon enforcement across registration, admin generation, and verification.
 */

require_once __DIR__ . '/../config/db.php';

function getCouponsFilePath(): string {
    return __DIR__ . '/../data/coupons.json';
}

function ensureCouponsTable(?PDO $pdo): void {
    if (!$pdo) return;
    try {
        // Create table if not exists with cross-platform compatible data types
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS coupon_pins (
                id VARCHAR(64) PRIMARY KEY,
                code VARCHAR(100) UNIQUE NOT NULL,
                amount DECIMAL(10, 2) DEFAULT 500.0,
                is_used BOOLEAN DEFAULT false,
                used_by VARCHAR(100),
                vendor_id VARCHAR(100),
                vendor_name VARCHAR(255),
                tier VARCHAR(50) DEFAULT 'AFF',
                type_label VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                used_at TIMESTAMP
            )
        ");

        // Safe column additions to cover PostgreSQL / SQLite variations
        $migrations = [
            "ALTER TABLE coupon_pins ADD COLUMN IF NOT EXISTS is_used BOOLEAN DEFAULT false",
            "ALTER TABLE coupon_pins ADD COLUMN IF NOT EXISTS used_by VARCHAR(100)",
            "ALTER TABLE coupon_pins ADD COLUMN IF NOT EXISTS vendor_id VARCHAR(100)",
            "ALTER TABLE coupon_pins ADD COLUMN IF NOT EXISTS vendor_name VARCHAR(255)",
            "ALTER TABLE coupon_pins ADD COLUMN IF NOT EXISTS tier VARCHAR(50) DEFAULT 'AFF'",
            "ALTER TABLE coupon_pins ADD COLUMN IF NOT EXISTS type_label VARCHAR(100)",
            "ALTER TABLE coupon_pins ADD COLUMN IF NOT EXISTS used_at TIMESTAMP",
            "ALTER TABLE users ADD COLUMN IF NOT EXISTS couponPinUsed VARCHAR(100)",
            "ALTER TABLE users ADD COLUMN IF NOT EXISTS coupon_pin_used VARCHAR(100)"
        ];

        foreach ($migrations as $sql) {
            try {
                $pdo->exec($sql);
            } catch (Exception $e) {
                // Ignore if column already exists or unsupported by engine
            }
        }
    } catch (Exception $e) {
        error_log("ensureCouponsTable warning: " . $e->getMessage());
    }
}

function loadCouponsFromJson(): array {
    $file = getCouponsFilePath();
    if (!file_exists($file)) {
        return [];
    }
    $raw = @file_get_contents($file);
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function saveCouponsToJson(array $coupons): bool {
    $file = getCouponsFilePath();
    $dir = dirname($file);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return (bool)@file_put_contents($file, json_encode($coupons, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function loadAllCoupons(?PDO $pdo = null): array {
    if (!$pdo) {
        $pdo = getDbConnection();
    }

    $jsonCoupons = loadCouponsFromJson();
    $couponsByCode = [];

    // Index JSON coupons first
    foreach ($jsonCoupons as $c) {
        $code = strtoupper(trim($c['code'] ?? ''));
        if (!empty($code)) {
            $couponsByCode[$code] = [
                'code' => $code,
                'channel' => $c['channel'] ?? 'AFFILIATE',
                'type' => $c['type'] ?? 'AFF',
                'type_label' => $c['typeLabel'] ?? $c['type_label'] ?? 'Member Registration PIN',
                'vendor_id' => $c['vendorId'] ?? $c['vendor_id'] ?? '',
                'vendor_name' => $c['vendorName'] ?? $c['vendor_name'] ?? 'General Pool',
                'wholesale_price' => (float)($c['wholesalePrice'] ?? $c['wholesale_price'] ?? 1000),
                'amount' => (float)($c['amount'] ?? 1000),
                'is_used' => !empty($c['isUsed']) || !empty($c['is_used']),
                'used_by' => $c['usedBy'] ?? $c['used_by'] ?? null,
                'used_at' => $c['usedAt'] ?? $c['used_at'] ?? null,
                'created_at' => $c['created_at'] ?? date('c')
            ];
        }
    }

    // Overlay/Sync from database if PDO is available
    if ($pdo) {
        ensureCouponsTable($pdo);
        try {
            $stmt = $pdo->query("SELECT * FROM coupon_pins ORDER BY created_at DESC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $r) {
                $code = strtoupper(trim($r['code'] ?? ''));
                if (empty($code)) continue;

                $isUsed = false;
                if (isset($r['is_used'])) {
                    $isUsed = filter_var($r['is_used'], FILTER_VALIDATE_BOOLEAN);
                } elseif (isset($r['isused'])) {
                    $isUsed = filter_var($r['isused'], FILTER_VALIDATE_BOOLEAN);
                }

                $usedBy = $r['used_by'] ?? $r['usedby'] ?? null;
                if (!empty($usedBy)) {
                    $isUsed = true;
                }

                $vendorId = $r['vendor_id'] ?? $r['vendorid'] ?? ($couponsByCode[$code]['vendor_id'] ?? '');
                $vendorName = $r['vendor_name'] ?? $r['vendorname'] ?? ($couponsByCode[$code]['vendor_name'] ?? 'General Pool');
                $type = $r['tier'] ?? ($couponsByCode[$code]['type'] ?? 'AFF');
                $typeLabel = $r['type_label'] ?? $r['typelabel'] ?? ($couponsByCode[$code]['type_label'] ?? 'Member Registration PIN');
                $amount = (float)($r['amount'] ?? 1000);
                $usedAt = $r['used_at'] ?? $r['usedat'] ?? ($couponsByCode[$code]['used_at'] ?? null);
                $createdAt = $r['created_at'] ?? $r['createdat'] ?? ($couponsByCode[$code]['created_at'] ?? date('c'));

                $couponsByCode[$code] = [
                    'code' => $code,
                    'channel' => (strpos($code, 'UPL') !== false || $type === 'UPL' || $type === 'JOB') ? 'UPLOADER' : 'AFFILIATE',
                    'type' => $type,
                    'type_label' => $typeLabel,
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendorName,
                    'wholesale_price' => $couponsByCode[$code]['wholesale_price'] ?? 1000,
                    'amount' => $amount,
                    'is_used' => $isUsed,
                    'used_by' => $usedBy,
                    'used_at' => $usedAt,
                    'created_at' => $createdAt
                ];
            }
        } catch (Exception $e) {
            error_log("loadAllCoupons db query notice: " . $e->getMessage());
        }
    }

    return array_values($couponsByCode);
}

function findCouponByCode(string $code, ?PDO $pdo = null): ?array {
    $code = strtoupper(trim($code));
    if (empty($code)) return null;

    if (!$pdo) {
        $pdo = getDbConnection();
    }

    if ($pdo) {
        ensureCouponsTable($pdo);
        try {
            $stmt = $pdo->prepare("SELECT * FROM coupon_pins WHERE UPPER(code) = ? LIMIT 1");
            $stmt->execute([$code]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($r) {
                $isUsed = false;
                if (isset($r['is_used'])) {
                    $isUsed = filter_var($r['is_used'], FILTER_VALIDATE_BOOLEAN);
                } elseif (isset($r['isused'])) {
                    $isUsed = filter_var($r['isused'], FILTER_VALIDATE_BOOLEAN);
                }
                $usedBy = $r['used_by'] ?? $r['usedby'] ?? null;
                if (!empty($usedBy)) {
                    $isUsed = true;
                }

                return [
                    'code' => $code,
                    'channel' => (strpos($code, 'UPL') !== false) ? 'UPLOADER' : 'AFFILIATE',
                    'type' => $r['tier'] ?? 'AFF',
                    'type_label' => $r['type_label'] ?? $r['typelabel'] ?? 'Member Registration PIN',
                    'vendor_id' => $r['vendor_id'] ?? $r['vendorid'] ?? '',
                    'vendor_name' => $r['vendor_name'] ?? $r['vendorname'] ?? 'General Pool',
                    'amount' => (float)($r['amount'] ?? 1000),
                    'is_used' => $isUsed,
                    'used_by' => $usedBy,
                    'used_at' => $r['used_at'] ?? $r['usedat'] ?? null,
                    'created_at' => $r['created_at'] ?? $r['createdat'] ?? date('c')
                ];
            }
        } catch (Exception $e) {
            error_log("findCouponByCode DB search: " . $e->getMessage());
        }
    }

    // Check JSON fallback
    $jsonCoupons = loadCouponsFromJson();
    foreach ($jsonCoupons as $c) {
        if (strtoupper(trim($c['code'] ?? '')) === $code) {
            return [
                'code' => $code,
                'channel' => $c['channel'] ?? 'AFFILIATE',
                'type' => $c['type'] ?? 'AFF',
                'type_label' => $c['typeLabel'] ?? $c['type_label'] ?? 'Member Registration PIN',
                'vendor_id' => $c['vendorId'] ?? $c['vendor_id'] ?? '',
                'vendor_name' => $c['vendorName'] ?? $c['vendor_name'] ?? 'General Pool',
                'amount' => (float)($c['amount'] ?? 1000),
                'is_used' => !empty($c['isUsed']) || !empty($c['is_used']),
                'used_by' => $c['usedBy'] ?? $c['used_by'] ?? null,
                'used_at' => $c['usedAt'] ?? $c['used_at'] ?? null,
                'created_at' => $c['created_at'] ?? date('c')
            ];
        }
    }

    return null;
}

/**
 * Validates whether a coupon PIN can be used to register a new member account.
 * Single-use check: must exist, must not be used, and must not be an uploader upgrade code.
 */
function validateCouponForRegistration(string $code, ?PDO $pdo = null): array {
    $code = strtoupper(trim($code));

    if (empty($code)) {
        return [
            'valid' => false,
            'message' => 'Activation / Vendor coupon code is required to complete registration.'
        ];
    }

    // Check code type: Uploader codes cannot be used for registration
    if (strpos($code, 'UPL') !== false || strpos($code, 'IX-UPL-') === 0 || strpos($code, 'INX-UPL-') === 0) {
        return [
            'valid' => false,
            'message' => "Invalid Code Type: \"{$code}\" is an Uploader Accreditation Code. It cannot be used for Member Registration. Please input a Member Registration PIN (e.g. INX-AFF-XXXX-XXXX)."
        ];
    }

    $coupon = findCouponByCode($code, $pdo);

    if (!$coupon) {
        return [
            'valid' => false,
            'message' => "Invalid or unrecognized coupon code: \"{$code}\". Please verify your PIN or purchase a genuine code from an authorized vendor."
        ];
    }

    if ($coupon['is_used'] || !empty($coupon['used_by'])) {
        $usedByInfo = !empty($coupon['used_by']) ? " by user @{$coupon['used_by']}" : "";
        $usedAtInfo = !empty($coupon['used_at']) ? " on " . date('M j, Y, g:i a', strtotime($coupon['used_at'])) : "";
        return [
            'valid' => false,
            'message' => "This coupon code (\"{$code}\") has already been used to activate an account{$usedByInfo}{$usedAtInfo}. Each coupon code is strictly single-use only."
        ];
    }

    return [
        'valid' => true,
        'message' => "Valid and unused coupon PIN.",
        'coupon' => $coupon
    ];
}

/**
 * Consumes/burns a coupon PIN upon successful registration.
 * Ensures the coupon cannot be reused by any subsequent account.
 */
function consumeCouponForRegistration(string $code, string $username, ?PDO $pdo = null): bool {
    $code = strtoupper(trim($code));
    $username = trim($username);
    if (empty($code) || empty($username)) return false;

    if (!$pdo) {
        $pdo = getDbConnection();
    }

    $now = date('c');

    // 1. Update Database
    if ($pdo) {
        ensureCouponsTable($pdo);
        try {
            // Update both snake_case and camelCase column variations
            $stmt = $pdo->prepare("
                UPDATE coupon_pins 
                SET is_used = true, 
                    isUsed = true, 
                    used_by = :username, 
                    usedBy = :username, 
                    used_at = CURRENT_TIMESTAMP, 
                    usedAt = CURRENT_TIMESTAMP 
                WHERE UPPER(code) = :code
            ");
            $stmt->execute([
                ':username' => $username,
                ':code' => $code
            ]);

            // If code was not present in DB, insert it as used so it cannot be used again
            if ($stmt->rowCount() === 0) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO coupon_pins (id, code, amount, is_used, used_by, used_at)
                    VALUES (:id, :code, 1000.0, true, :username, CURRENT_TIMESTAMP)
                ");
                $insertStmt->execute([
                    ':id' => 'pin-' . bin2hex(random_bytes(8)),
                    ':code' => $code,
                    ':username' => $username
                ]);
            }

            // Also record on users table
            try {
                $userStmt = $pdo->prepare("UPDATE users SET couponPinUsed = :code WHERE username = :username");
                $userStmt->execute([':code' => $code, ':username' => $username]);
            } catch (Exception $e) {}
        } catch (Exception $e) {
            error_log("consumeCouponForRegistration DB error: " . $e->getMessage());
        }
    }

    // 2. Update JSON Storage
    $jsonCoupons = loadCouponsFromJson();
    $foundInJson = false;

    foreach ($jsonCoupons as &$c) {
        if (strtoupper(trim($c['code'] ?? '')) === $code) {
            $c['isUsed'] = true;
            $c['is_used'] = true;
            $c['usedBy'] = $username;
            $c['used_by'] = $username;
            $c['usedAt'] = $now;
            $c['used_at'] = $now;
            $foundInJson = true;
            break;
        }
    }
    unset($c);

    if (!$foundInJson) {
        $jsonCoupons[] = [
            'code' => $code,
            'channel' => 'AFFILIATE',
            'type' => 'AFF',
            'typeLabel' => 'Member Registration PIN',
            'vendorId' => '',
            'vendorName' => 'General Pool',
            'wholesalePrice' => 1000,
            'amount' => 1000,
            'isUsed' => true,
            'is_used' => true,
            'usedBy' => $username,
            'used_by' => $username,
            'usedAt' => $now,
            'used_at' => $now,
            'created_at' => $now
        ];
    }

    saveCouponsToJson($jsonCoupons);
    return true;
}

/**
 * Saves a batch of newly generated coupon PINs from admin.
 */
function saveCouponsBatch(array $newCoupons, ?PDO $pdo = null): int {
    if (empty($newCoupons)) return 0;

    if (!$pdo) {
        $pdo = getDbConnection();
    }

    $existingJson = loadCouponsFromJson();
    $existingMap = [];
    foreach ($existingJson as $item) {
        $existingMap[strtoupper(trim($item['code'] ?? ''))] = true;
    }

    $insertedCount = 0;
    $now = date('c');

    if ($pdo) {
        ensureCouponsTable($pdo);
    }

    foreach ($newCoupons as $c) {
        $code = strtoupper(trim($c['code'] ?? ''));
        if (empty($code) || isset($existingMap[$code])) continue;

        $channel = $c['channel'] ?? ((strpos($code, 'UPL') !== false) ? 'UPLOADER' : 'AFFILIATE');
        $type = $c['type'] ?? ($channel === 'UPLOADER' ? 'UPL' : 'AFF');
        $typeLabel = $c['typeLabel'] ?? $c['type_label'] ?? 'Member Registration PIN';
        $vendorId = $c['vendorId'] ?? $c['vendor_id'] ?? '';
        $vendorName = $c['vendorName'] ?? $c['vendor_name'] ?? 'General Pool';
        $wholesalePrice = (float)($c['wholesalePrice'] ?? $c['wholesale_price'] ?? 1000);
        $amount = (float)($c['amount'] ?? 1000);
        $isUsed = !empty($c['isUsed']) || !empty($c['is_used']);
        $usedBy = $c['usedBy'] ?? $c['used_by'] ?? null;

        // DB Insert
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO coupon_pins (id, code, amount, is_used, used_by, vendor_id, vendor_name, tier, type_label, created_at)
                    VALUES (:id, :code, :amount, :is_used, :used_by, :vendor_id, :vendor_name, :tier, :type_label, CURRENT_TIMESTAMP)
                ");
                $stmt->execute([
                    ':id' => 'pin-' . bin2hex(random_bytes(8)),
                    ':code' => $code,
                    ':amount' => $amount,
                    ':is_used' => $isUsed ? 1 : 0,
                    ':used_by' => $usedBy,
                    ':vendor_id' => $vendorId,
                    ':vendor_name' => $vendorName,
                    ':tier' => $type,
                    ':type_label' => $typeLabel
                ]);
            } catch (Exception $e) {
                // Ignore duplicates
            }
        }

        // Add to JSON list
        $existingJson[] = [
            'code' => $code,
            'channel' => $channel,
            'type' => $type,
            'typeLabel' => $typeLabel,
            'vendorId' => $vendorId,
            'vendorName' => $vendorName,
            'wholesalePrice' => $wholesalePrice,
            'amount' => $amount,
            'isUsed' => $isUsed,
            'is_used' => $isUsed,
            'usedBy' => $usedBy,
            'used_by' => $usedBy,
            'usedAt' => null,
            'created_at' => $now
        ];
        $existingMap[$code] = true;
        $insertedCount++;
    }

    if ($insertedCount > 0) {
        saveCouponsToJson($existingJson);
    }

    return $insertedCount;
}

/**
 * Deletes or invalidates a single coupon PIN.
 */
function deleteCouponByCode(string $code, ?PDO $pdo = null): bool {
    $code = strtoupper(trim($code));
    if (empty($code)) return false;

    if (!$pdo) {
        $pdo = getDbConnection();
    }

    if ($pdo) {
        ensureCouponsTable($pdo);
        try {
            $stmt = $pdo->prepare("DELETE FROM coupon_pins WHERE UPPER(code) = ?");
            $stmt->execute([$code]);
        } catch (Exception $e) {}
    }

    $coupons = loadCouponsFromJson();
    $filtered = array_values(array_filter($coupons, function($c) use ($code) {
        return strtoupper(trim($c['code'] ?? '')) !== $code;
    }));

    saveCouponsToJson($filtered);
    return true;
}
