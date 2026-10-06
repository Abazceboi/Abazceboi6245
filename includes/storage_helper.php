<?php
/**
 * INNOVATIONX — Centralized Safe Storage Helper
 * Provides seamless read/write persistence across both local development
 * and Vercel serverless functions (where /var/task is read-only).
 */

function getStorageBaseDir(): string {
    static $baseDir = null;
    if ($baseDir !== null) {
        return $baseDir;
    }

    $projectRoot = dirname(__DIR__);
    $testFile = $projectRoot . '/data/.writable_test';

    // Test if project root/data is directly writable (local dev)
    $isDirectlyWritable = false;
    try {
        if (!is_dir($projectRoot . '/data')) {
            @mkdir($projectRoot . '/data', 0755, true);
        }
        if (@file_put_contents($testFile, '1') !== false) {
            @unlink($testFile);
            $isDirectlyWritable = true;
        }
    } catch (Throwable $e) {
        $isDirectlyWritable = false;
    }

    if ($isDirectlyWritable) {
        $baseDir = $projectRoot;
    } else {
        // Fallback to system temp directory on Vercel serverless
        $tmpDir = sys_get_temp_dir() . '/innovationx_store';
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }
        $baseDir = $tmpDir;
    }

    return $baseDir;
}

function getStorageFilePath(string $relativePath): string {
    $relativePath = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
    $baseDir = getStorageBaseDir();
    $target = $baseDir . DIRECTORY_SEPARATOR . $relativePath;
    $targetDir = dirname($target);
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0777, true);
    }
    return $target;
}

function getDbStorageJson(string $key) {
    if (!function_exists('getDbConnection')) {
        $dbConfig = dirname(__DIR__) . '/config/db.php';
        if (file_exists($dbConfig)) {
            require_once $dbConfig;
        }
    }
    if (!function_exists('getDbConnection')) return null;
    $pdo = getDbConnection();
    if (!$pdo) return null;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS app_storage (key VARCHAR(191) PRIMARY KEY, value TEXT, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $stmt = $pdo->prepare("SELECT value FROM app_storage WHERE key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && isset($row['value']) && $row['value'] !== null) {
            return json_decode($row['value'], true);
        }
    } catch (Throwable $e) {}
    return null;
}

function setDbStorageJson(string $key, $data): bool {
    if (!function_exists('getDbConnection')) {
        $dbConfig = dirname(__DIR__) . '/config/db.php';
        if (file_exists($dbConfig)) {
            require_once $dbConfig;
        }
    }
    if (!function_exists('getDbConnection')) return false;
    $pdo = getDbConnection();
    if (!$pdo) return false;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS app_storage (key VARCHAR(191) PRIMARY KEY, value TEXT, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'pgsql') {
            $stmt = $pdo->prepare("INSERT INTO app_storage (key, value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP) ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value, updated_at = CURRENT_TIMESTAMP");
        } else if ($driver === 'mysql') {
            $stmt = $pdo->prepare("INSERT INTO app_storage (key, value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP");
        } else {
            // SQLite
            $stmt = $pdo->prepare("INSERT OR REPLACE INTO app_storage (key, value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)");
        }
        return $stmt->execute([$key, $json]);
    } catch (Throwable $e) {
        return false;
    }
}

function readStorageJson(string $relativePath, $default = []) {
    $relativePathClean = ltrim(str_replace(['\\', '/'], '/', $relativePath), '/');
    $storageFile = getStorageFilePath($relativePathClean);
    $bundleFile = dirname(__DIR__) . '/' . $relativePathClean;

    // 0. Check database persistent storage first
    $dbData = getDbStorageJson($relativePathClean);
    if ($dbData !== null) {
        return $dbData;
    }

    // 1. Check updated storage copy first (in /tmp or writable dir)
    if (file_exists($storageFile)) {
        $raw = @file_get_contents($storageFile);
        if ($raw !== false && trim($raw) !== '') {
            $data = json_decode($raw, true);
            if ($data !== null) {
                return $data;
            }
        }
    }

    // 2. Check bundled original file
    if (file_exists($bundleFile)) {
        $raw = @file_get_contents($bundleFile);
        if ($raw !== false && trim($raw) !== '') {
            $data = json_decode($raw, true);
            if ($data !== null) {
                return $data;
            }
        }
    }

    return $default;
}

function writeStorageJson(string $relativePath, $data): bool {
    $relativePathClean = ltrim(str_replace(['\\', '/'], '/', $relativePath), '/');
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    // 0. Also write to database
    setDbStorageJson($relativePathClean, $data);

    // 1. Write to storage file (guaranteed writable)
    $storageFile = getStorageFilePath($relativePathClean);
    $res1 = @file_put_contents($storageFile, $json, LOCK_EX);

    // 2. Also try writing to project repository bundle if writable (e.g. local development)
    $bundleFile = dirname(__DIR__) . '/' . $relativePathClean;
    $bundleDir = dirname($bundleFile);
    if (is_dir($bundleDir) && is_writable($bundleDir)) {
        @file_put_contents($bundleFile, $json, LOCK_EX);
    }

    return ($res1 !== false);
}

function readStorageString(string $relativePath, string $default = ''): string {
    $relativePathClean = ltrim(str_replace(['\\', '/'], '/', $relativePath), '/');
    $storageFile = getStorageFilePath($relativePathClean);
    $bundleFile = dirname(__DIR__) . '/' . $relativePathClean;

    if (file_exists($storageFile)) {
        $raw = @file_get_contents($storageFile);
        if ($raw !== false) return $raw;
    }
    if (file_exists($bundleFile)) {
        $raw = @file_get_contents($bundleFile);
        if ($raw !== false) return $raw;
    }
    return $default;
}

function writeStorageString(string $relativePath, string $content): bool {
    $relativePathClean = ltrim(str_replace(['\\', '/'], '/', $relativePath), '/');
    $storageFile = getStorageFilePath($relativePathClean);
    $res = @file_put_contents($storageFile, $content, LOCK_EX);

    $bundleFile = dirname(__DIR__) . '/' . $relativePathClean;
    $bundleDir = dirname($bundleFile);
    if (is_dir($bundleDir) && is_writable($bundleDir)) {
        @file_put_contents($bundleFile, $content, LOCK_EX);
    }
    return ($res !== false);
}
