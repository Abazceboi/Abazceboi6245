<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/plain');

$pdo = getDbConnection();
if (!$pdo) {
    die("Error: Could not connect to the database. Check your POSTGRES_URL environment variable.");
}

$sql = "
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    fullName VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50) NOT NULL,
    passwordHash VARCHAR(255) NOT NULL,
    pointsBalance INTEGER DEFAULT 100,
    cashBalance DECIMAL(10, 2) DEFAULT 0.0,
    referralCode VARCHAR(100) UNIQUE NOT NULL,
    referredBy VARCHAR(100),
    isPro BOOLEAN DEFAULT true,
    couponPinUsed VARCHAR(100),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vendors (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    rating DECIMAL(3, 1) DEFAULT 5.0,
    codesSold INTEGER DEFAULT 0,
    phone VARCHAR(50) NOT NULL,
    avatarBg VARCHAR(50) DEFAULT '#9333EA',
    active BOOLEAN DEFAULT true,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS coupon_pins (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    code VARCHAR(100) UNIQUE NOT NULL,
    amount DECIMAL(10, 2) DEFAULT 500.0,
    isUsed BOOLEAN DEFAULT false,
    usedBy VARCHAR(100),
    vendorId UUID,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usedAt TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tasks (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50) DEFAULT 'social',
    rewardPoints INTEGER DEFAULT 150,
    targetUrl TEXT,
    dailyLimit INTEGER DEFAULT 10,
    active BOOLEAN DEFAULT true,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS task_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    userId UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    taskId UUID NOT NULL REFERENCES tasks(id) ON DELETE CASCADE,
    pointsEarned INTEGER NOT NULL,
    completedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transactions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    userId UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'COMPLETED',
    reference VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

try {
    $pdo->exec($sql);
    echo "SUCCESS: Database tables have been successfully initialized.\n";
    echo "You can now register users and log in.";
} catch (PDOException $e) {
    echo "ERROR executing SQL:\n" . $e->getMessage();
}
