<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$pdo = getDbConnection();

if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

if ($action === 'register') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $fullName = trim($data['fullName'] ?? '');
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';
    $referredBy = trim($data['ref'] ?? '');
    
    if (strlen($username) < 3 || strlen($password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password length.']);
        exit;
    }
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Username or Email already exists.']);
        exit;
    }
    
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $referralCode = 'REF-' . strtoupper(substr(md5(uniqid()), 0, 8));
require_once __DIR__ . '/../config/app.php';
    
    $stmt = $pdo->prepare("INSERT INTO users (fullName, username, email, phone, passwordHash, referralCode, referredBy) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    try {
        $stmt->execute([$fullName, $username, $email, $phone, $passwordHash, $referralCode, $referredBy]);
        
        // Retrieve the generated user safely without lastval/lastInsertId sequence error
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        $userId = $user['id'] ?? $username;
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        
        // Set browser session cookie so refreshing on Vercel never logs the user out
        if (function_exists('setAuthCookie')) {
            setAuthCookie($userId, $username, false);
        }
        
        echo json_encode(['status' => 'success', 'username' => $username]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    require_once __DIR__ . '/../config/app.php';
    
    $stmt = $pdo->prepare("SELECT id, username, passwordHash FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['passwordhash'] ?? $user['passwordHash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        if (function_exists('setAuthCookie')) {
            setAuthCookie($user['id'], $user['username'], false);
        }
        
        echo json_encode(['status' => 'success', 'username' => $user['username']]);
    } else {
        // Secure Admin Login using Environment Variables
        $adminUser = getenv('ADMIN_USERNAME') ?: 'admin';
        $adminPass = getenv('ADMIN_PASSWORD') ?: 'ADMIN_NOT_CONFIGURED';
        
        if ($username === $adminUser && $password === $adminPass) {
            $_SESSION['user_id'] = 'admin-dev-id';
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = true;
            
            if (function_exists('setAuthCookie')) {
                setAuthCookie('admin-dev-id', $username, true);
            }
            
            echo json_encode(['status' => 'success', 'username' => $username, 'isAdmin' => true]);
            exit;
        }
        
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
    }
    exit;
}

if ($action === 'logout') {
    require_once __DIR__ . '/../config/app.php';
    if (function_exists('clearAuthCookie')) {
        clearAuthCookie();
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    echo json_encode(['status' => 'success']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
