<?php
// includes/auth.php

require_once 'config.php';

// بدء الجلسة إذا لم تكن قد بدأت
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * التحقق من تسجيل الدخول
 * @return bool
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * التحقق من الصلاحية
 * @param string $requiredRole
 * @return void
 */
function requireRole(string $requiredRole): void {
    if (!isLoggedIn()) {
        header("Location: /college_admission/login.php");
        exit();
    }

    if ($_SESSION['user_role'] !== $requiredRole) {
        header("Location: /college_admission/unauthorized.php");
        exit();
    }
}

/**
 * تسجيل الدخول باستخدام البريد الإلكتروني
 * @param string $email
 * @param string $password
 * @return bool
 * @throws PDOException
 */
function login(string $email, string $password): bool {
    global $pdo;
    
    // التحقق من أن البريد الإلكتروني وكلمة المرور غير فارغة
    if (empty($email) || empty($password)) {
        return false;
    }
    
    try {
$stmt = $pdo->prepare("SELECT id, username, password, role, full_name, email FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            
            $_SESSION = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'user_role' => $user['role'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'last_activity' => time()
            ];
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * تسجيل الخروج
 * @return void
 */
function logout(): void {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * إنشاء مستخدم جديد (للمدير)
 * @param string $username
 * @param string $password
 * @param string $full_name
 * @param string $email
 * @param string $role
 * @return bool
 * @throws PDOException
 */
function createUser(string $username, string $password, string $full_name, string $email, string $role): bool {
    global $pdo;
    
    // التحقق من المدخلات
    if (empty($username) || empty($password) || empty($full_name) || empty($email) || empty($role)) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            return false;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");

        return $stmt->execute([$username, $hashedPassword, $full_name, $email, $role]);
    } catch (PDOException $e) {
        error_log("Error creating user: " . $e->getMessage());
        throw $e;
    }
}

/**
 * التحقق من صلاحية المدير
 * @return bool
 */
function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

/**
 * الحصول على معلومات المستخدم الحالي
 * @return array|null
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['user_role'],
        'full_name' => $_SESSION['full_name'],
        'email' => $_SESSION['email']
    ];
}

/**
 * التحقق من النشاط الأخير وتجديد الجلسة إذا لزم الأمر
 * @param int $timeout الدقائق قبل انتهاء الجلسة
 * @return void
 */
function checkSessionTimeout(int $timeout = 30): void {
    if (isLoggedIn() && isset($_SESSION['last_activity'])) {
        $timeoutSeconds = $timeout * 60;
        if (time() - $_SESSION['last_activity'] > $timeoutSeconds) {
            logout();
            header("Location: /college_admission/login.php?timeout=1");
            exit();
        }
        $_SESSION['last_activity'] = time();
    }
}