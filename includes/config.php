<?php
// includes/config.php

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'college_admission');
define('DB_USER', 'root');
define('DB_PASS', '');

// ثابت DSN جاهز لإعادة الاستخدام
define('DB_DSN', "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8");

// أدوار المستخدمين
define('ROLE_ADMIN', 'admin');
define('ROLE_REGISTRAR', 'registrar');
define('ROLE_RESULTS', 'results');
define('ROLE_STUDENT', 'student');

// إعدادات النظام
define('ALLOW_REGISTRATION', true); // true لتفعيل التسجيل | false لتعطيله

// بدء الجلسة إذا لم تكن قد بدأت
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// إنشاء اتصال PDO
try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// ✅ دالة واحدة فقط لاسترجاع جميع الطلبات مع اسم الطالب والبرنامج
if (!function_exists('getAllApplications')) {
   // عدّل هذه الدالة فقط
function getAllApplications(): array {
    global $pdo;

    $sql = "
        SELECT 
            a.*, 
            u.full_name AS student_name, 
            p.name AS program_name 
        FROM applications a
        LEFT JOIN users u ON a.student_id = u.id
        LEFT JOIN programs p ON a.program_id = p.id
        ORDER BY a.submission_date DESC
    ";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
