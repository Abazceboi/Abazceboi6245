<?php
/**
 * INNOVATIONX — Database Connection Handler
 * Supports PostgreSQL (Railway / Supabase / Render) & MySQL with PDO
 * Includes strict connection timeouts to prevent serverless execution hangs.
 */

function getDbConnection(): ?PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $databaseUrl = getenv('DATABASE_URL') ?: getenv('STORAGE_URL') ?: getenv('POSTGRES_URL') ?: getenv('STORAGE_POSTGRES_URL');

    try {
        if ($databaseUrl) {
            // Parse PostgreSQL connection string: postgres://user:pass@host:port/dbname
            $dbParts = parse_url($databaseUrl);
            $driver = ($dbParts['scheme'] === 'postgres' || $dbParts['scheme'] === 'postgresql') ? 'pgsql' : 'mysql';
            $host = $dbParts['host'] ?? 'localhost';
            $port = $dbParts['port'] ?? ($driver === 'pgsql' ? '5432' : '3306');
            $user = $dbParts['user'] ?? '';
            $pass = $dbParts['pass'] ?? '';
            $dbname = ltrim($dbParts['path'] ?? '', '/');

            $dsn = "{$driver}:host={$host};port={$port};dbname={$dbname}";
            if ($driver === 'pgsql') {
                $dsn .= ";connect_timeout=2";
            }

            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 2,
            ]);
        } else {
            // Local fallback (SQLite / MySQL)
            $dbPath = __DIR__ . '/../database.sqlite';
            if (file_exists($dbPath) || !getenv('VERCEL')) {
                $pdo = new PDO("sqlite:" . $dbPath);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_TIMEOUT, 2);
            } else {
                $pdo = null;
            }
        }
        return $pdo;
    } catch (Throwable $e) {
        error_log("Database Connection Failed: " . $e->getMessage());
        return null;
    }
}
