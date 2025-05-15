<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole(ROLE_STUDENT);

// التحقق مما إذا كان الطالب قد قدم طلباً بالفعل
$stmt = $pdo->prepare("SELECT * FROM applications WHERE student_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$existingApplication = $stmt->fetch();

if ($existingApplication) {
    header("Location: status.php");
    exit();
}

// معالجة تقديم الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من الملفات المرفوعة
    $certificatePath = '';
    $photoPath = '';
    
    // معالجة شهادة الثانوية
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
        $certificateDir = '../../assets/uploads/certificates/';
        $certificateExt = pathinfo($_FILES['certificate']['name'], PATHINFO_EXTENSION);
        $certificateName = 'certificate_' . $_SESSION['user_id'] . '_' . time() . '.' . $certificateExt;
        $certificatePath = $certificateDir . $certificateName;
        
        if (move_uploaded_file($_FILES['certificate']['tmp_name'], $certificatePath)) {
            $certificatePath = 'certificates/' . $certificateName;
        } else {
            $error = "حدث خطأ أثناء رفع شهادة الثانوية";
        }
    }
    
    // معالجة الصورة الشخصية
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoDir = '../../assets/uploads/images/';
        $photoExt = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photoName = 'photo_' . $_SESSION['user_id'] . '_' . time() . '.' . $photoExt;
        $photoPath = $photoDir . $photoName;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
            $photoPath = 'images/' . $photoName;
        } else {
            $error = "حدث خطأ أثناء رفع الصورة الشخصية";
        }
    }
    
    // إدخال البيانات في قاعدة البيانات
    if (!isset($error)) {
        $stmt = $pdo->prepare("INSERT INTO applications 
            (student_id, program, high_school_name, graduation_year, gpa, certificate_path, photo_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $success = $stmt->execute([
            $_SESSION['user_id'],
            $_POST['program'],
            $_POST['high_school_name'],
            $_POST['graduation_year'],
            $_POST['gpa'],
            $certificatePath,
            $photoPath
        ]);
        
        if ($success) {
            header("Location: status.php");
            exit();
        } else {
            $error = "حدث خطأ أثناء تقديم الطلب";
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>
    <h2>تقديم طلب الالتحاق</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="application-form">
        <div class="form-group">
            <label for="program">البرنامج الدراسي:</label>
            <select id="program" name="program" required>
                <option value="">اختر البرنامج</option>
                <option value="علوم الحاسب">علوم الحاسب</option>
                <option value="الهندسة">الهندسة</option>
                <option value="الطب">الطب</option>
                <option value="الصيدلة">الصيدلة</option>
                <option value="إدارة الأعمال">إدارة الأعمال</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="high_school_name">اسم المدرسة الثانوية:</label>
            <input type="text" id="high_school_name" name="high_school_name" required>
        </div>
        
        <div class="form-group">
            <label for="graduation_year">سنة التخرج:</label>
            <input type="number" id="graduation_year" name="graduation_year" min="2000" max="<?php echo date('Y'); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="gpa">المعدل التراكمي:</label>
            <input type="number" id="gpa" name="gpa" step="0.01" min="50" max="100" required>
        </div>
        
        <div class="form-group">
            <label for="certificate">شهادة الثانوية العامة (PDF فقط):</label>
            <input type="file" id="certificate" name="certificate" accept=".pdf" required>
        </div>
        
        <div class="form-group">
            <label for="photo">الصورة الشخصية (JPG/PNG فقط):</label>
            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png" required>
        </div>
        
        <button type="submit" class="btn">تقديم الطلب</button>
    </form>
<?php include '../../includes/footer.php'; ?>