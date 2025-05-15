<?php
// student/submit_application.php

require_once __DIR__ . '/../includes/config.php';   // إعدادات قاعدة البيانات
require_once __DIR__ . '/../includes/auth.php';     // التحقق من الجلسة (اختياري)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // لا نقبل إلا POST
    header('Location: ../index.php');
    exit;
}

// تحقق من تسجيل الدخول (اختياري لكن يفضل)
// if (!isLoggedIn() || $_SESSION['user_role'] !== ROLE_STUDENT) {
//     header('Location: ../login.php');
//     exit;
// }

// جمع البيانات
$student_name = trim($_POST['student_name'] ?? '');
$mother_name = trim($_POST['mother_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$national_id = trim($_POST['national_id'] ?? '');

// تحقق من البيانات الأساسية
if (!$student_name || !$mother_name || !$address || !$national_id) {
    die('يرجى تعبئة جميع الحقول المطلوبة.');
}

// التحقق من وجود ملفات مرفوعة
if (!isset($_FILES['national_id_image']) || !isset($_FILES['certificate_pdf'])) {
    die('يرجى رفع الملفات المطلوبة.');
}

// التحقق من رفع صورة الرقم الوطني
$allowedImageTypes = ['image/jpeg', 'image/png', 'image/jpg'];
$nationalIdFile = $_FILES['national_id_image'];
if ($nationalIdFile['error'] !== UPLOAD_ERR_OK) {
    die('خطأ في رفع صورة الرقم الوطني.');
}
if (!in_array(mime_content_type($nationalIdFile['tmp_name']), $allowedImageTypes)) {
    die('صيغة صورة الرقم الوطني غير مدعومة. الرجاء رفع JPG أو PNG.');
}

// التحقق من رفع الشهادة بصيغة PDF
$certificateFile = $_FILES['certificate_pdf'];
if ($certificateFile['error'] !== UPLOAD_ERR_OK) {
    die('خطأ في رفع صورة الشهادة.');
}
if (mime_content_type($certificateFile['tmp_name']) !== 'application/pdf') {
    die('صيغة شهادة غير مدعومة. الرجاء رفع ملف PDF فقط.');
}

// إنشاء مجلدات للرفع إذا لم تكن موجودة
$uploadDir = __DIR__ . '/../uploads/applications/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// توليد أسماء فريدة للملفات
$nationalIdFileName = uniqid('nid_') . '-' . basename($nationalIdFile['name']);
$certificateFileName = uniqid('cert_') . '-' . basename($certificateFile['name']);

// مسارات الحفظ
$nationalIdPath = $uploadDir . $nationalIdFileName;
$certificatePath = $uploadDir . $certificateFileName;

// نقل الملفات من المجلد المؤقت إلى المجلد الدائم
if (!move_uploaded_file($nationalIdFile['tmp_name'], $nationalIdPath)) {
    die('فشل في حفظ صورة الرقم الوطني.');
}
if (!move_uploaded_file($certificateFile['tmp_name'], $certificatePath)) {
    die('فشل في حفظ صورة الشهادة.');
}

// الاتصال بقاعدة البيانات (PDO)
try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // استعلام الإدخال
    $stmt = $pdo->prepare("INSERT INTO applications 
        (student_name, mother_name, address, national_id, national_id_image, certificate_pdf, submission_date, status)
        VALUES (:student_name, :mother_name, :address, :national_id, :national_id_image, :certificate_pdf, NOW(), 'pending')");

    $stmt->execute([
        ':student_name' => $student_name,
        ':mother_name' => $mother_name,
        ':address' => $address,
        ':national_id' => $national_id,
        ':national_id_image' => $nationalIdFileName,
        ':certificate_pdf' => $certificateFileName
    ]);

    // إعادة التوجيه مع رسالة نجاح (يمكن تطويره)
    header('Location: ../index.php?success=1');
    exit;

} catch (PDOException $e) {
    die("حدث خطأ في قاعدة البيانات: " . $e->getMessage());
}
