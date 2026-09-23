-- ==========================================================
-- INNOVATIONX PostgreSQL / MySQL Schema
-- Optimized for Railway, Supabase & Standard Web Hosts
-- ==========================================================

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(36) PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    points_balance INT DEFAULT 100,
    cash_balance DECIMAL(12,2) DEFAULT 0.00,
    referral_code VARCHAR(30) NOT NULL UNIQUE,
    referred_by VARCHAR(60) NULL,
    is_pro BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Vendors Table
CREATE TABLE IF NOT EXISTS vendors (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    location VARCHAR(150) NOT NULL,
    rating DECIMAL(3,1) DEFAULT 5.0,
    codes_sold INT DEFAULT 0,
    phone VARCHAR(30) NOT NULL,
    avatar_bg VARCHAR(20) DEFAULT '#9333EA',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Coupon Pins Table
CREATE TABLE IF NOT EXISTS coupon_pins (
    id VARCHAR(36) PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    amount DECIMAL(10,2) DEFAULT 500.00,
    is_used BOOLEAN DEFAULT FALSE,
    used_by VARCHAR(36) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL
);

-- 4. Tasks Table
CREATE TABLE IF NOT EXISTS tasks (
    id VARCHAR(36) PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    category VARCHAR(50) DEFAULT 'social',
    reward_points INT DEFAULT 150,
    daily_limit INT DEFAULT 10,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Transactions Table
CREATE TABLE IF NOT EXISTS transactions (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    type VARCHAR(50) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    status VARCHAR(30) DEFAULT 'COMPLETED',
    reference VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Starter Vendors
INSERT INTO vendors (id, name, location, rating, codes_sold, phone, avatar_bg) VALUES
('v1', 'Emmanuel Eze', 'Lagos / National (GTBank, OPay, Kuda)', 5.0, 2400, '2348012345678', '#9333EA'),
('v2', 'Fatima Bello', 'Abuja / Northern Region (Access Bank, Palmpay)', 4.9, 1850, '2348023456789', '#3B82F6'),
('v3', 'Tunde Adeyemi', 'Ibadan / South West (Zenith, Moniepoint)', 4.9, 1420, '2348034567890', '#F59E0B')
ON CONFLICT (id) DO NOTHING;
