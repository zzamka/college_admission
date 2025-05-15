<?php
require_once 'config.php';

// تسجيل الدخول
function login($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        return true;
    }
    
    return false;
}

// تسجيل الطالب
function registerStudent($data) {
    global $pdo;
    
    // التحقق من البريد الإلكتروني
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) return false;
    
    // إنشاء حساب المستخدم
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['name'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        ROLE_STUDENT
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // تسجيل بيانات الطالب
    $stmt = $pdo->prepare("INSERT INTO students (user_id, national_id, phone, address, birth_date, gender) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $data['national_id'],
        $data['phone'],
        $data['address'],
        $data['birth_date'],
        $data['gender']
    ]);
    
    return $userId;
}

// تقديم طلب الالتحاق
function submitApplication($userId, $data, $files) {
    global $pdo;
    
    // رفع الملفات
    $certificatePath = uploadFile($files['certificate'], 'certificates');
    $imagePath = uploadFile($files['image'], 'images');
    
    if (!$certificatePath || !$imagePath) return false;
    
    // تسجيل طلب الالتحاق
    $stmt = $pdo->prepare("INSERT INTO applications 
                          (student_id, program_id, certificate_path, image_path, status, submission_date) 
                          VALUES (?, ?, ?, ?, 'pending', NOW())");
    $stmt->execute([
        $userId,
        $data['program'],
        $certificatePath,
        $imagePath
    ]);
    
    return true;
}

// رفع الملفات
function uploadFile($file, $type) {
    $targetDir = "../assets/uploads/$type/";
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $targetDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    
    return false;
}

// الحصول على قائمة البرامج
function getPrograms() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM programs");
    return $stmt->fetchAll();
}

// الحصول على طلبات الطالب
function getStudentApplications($studentId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, p.name as program_name 
                          FROM applications a 
                          JOIN programs p ON a.program_id = p.id 
                          WHERE a.student_id = ?");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

// الحصول على جميع الطلبات (للمدير والمسجل)
function getAllApplications() {
    global $pdo;
    $stmt = $pdo->query("SELECT a.*, p.name as program_name, u.name as student_name 
                         FROM applications a 
                         JOIN programs p ON a.program_id = p.id 
                         JOIN users u ON a.student_id = u.id");
    return $stmt->fetchAll();
}

// تحديث حالة الطلب
function updateApplicationStatus($applicationId, $status) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $applicationId]);
}

// الحصول على بيانات الطالب
function getStudentData($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT u.*, s.* FROM users u 
                          JOIN students s ON u.id = s.user_id 
                          WHERE u.id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}
?>