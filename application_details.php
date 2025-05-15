<?php
require_once 'includes/auth.php';
requireRole([ROLE_ADMIN, ROLE_REGISTRAR, ROLE_STUDENT]);

if (!isset($_GET['id'])) {
    header("Location: unauthorized.php");
    exit();
}

$applicationId = $_GET['id'];

// الحصول على تفاصيل الطلب
$stmt = $pdo->prepare("SELECT a.*, p.name as program_name, u.name as student_name, u.email,
                       s.national_id, s.phone, s.address, s.birth_date, s.gender
                       FROM applications a
                       JOIN programs p ON a.program_id = p.id
                       JOIN users u ON a.student_id = u.id
                       JOIN students s ON u.id = s.user_id
                       WHERE a.id = ?");
$stmt->execute([$applicationId]);
$application = $stmt->fetch();

// التحقق من أن الطالب يمكنه رؤية طلبه فقط
if (hasRole(ROLE_STUDENT) && $application['student_id'] != $_SESSION['user_id']) {
    header("Location: unauthorized.php");
    exit();
}

?>

<?php include 'includes/header.php'; ?>
<h2>تفاصيل الطلب #<?php echo $application['id']; ?></h2>

<div class="application-details">
    <div class="detail-row">
        <span class="detail-label">اسم الطالب:</span>
        <span class="detail-value"><?php echo $application['student_name']; ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">البريد الإلكتروني:</span>
        <span class="detail-value"><?php echo $application['email']; ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">رقم الهوية:</span>
        <span class="detail-value"><?php echo $application['national_id']; ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">الهاتف:</span>
        <span class="detail-value"><?php echo $application['phone']; ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">العنوان:</span>
        <span class="detail-value"><?php echo $application['address']; ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">تاريخ الميلاد:</span>
        <span class="detail-value"><?php echo $application['birth_date']; ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">الجنس:</span>
        <span class="detail-value">
            <?php echo $application['gender'] == 'male' ? 'ذكر' : 'أنثى'; ?>
        </span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">البرنامج:</span>
        <span class="detail-value"><?php echo $application['program_name']; ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">تاريخ التقديم:</span>
        <span class="detail-value"><?php echo $application['submission_date']; ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">الحالة:</span>
        <span class="detail-value status-<?php echo $application['status']; ?>">
            <?php 
                switch($application['status']) {
                    case 'pending': echo 'قيد المراجعة'; break;
                    case 'approved': echo 'مقبول'; break;
                    case 'rejected': echo 'مرفوض'; break;
                }
            ?>
        </span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">صورة الشهادة:</span>
        <span class="detail-value">
            <a href="../assets/uploads/certificates/<?php echo $application['certificate_path']; ?>" target="_blank">عرض الملف</a>
        </span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">الصورة الشخصية:</span>
        <span class="detail-value">
            <a href="../assets/uploads/images/<?php echo $application['image_path']; ?>" target="_blank">عرض الصورة</a>
        </span>
    </div>
    
    <?php if (!empty($application['notes'])): ?>
    <div class="detail-row">
        <span class="detail-label">ملاحظات:</span>
        <span class="detail-value"><?php echo $application['notes']; ?></span>
    </div>
    <?php endif; ?>
</div>

<?php if (hasRole(ROLE_ADMIN) || hasRole(ROLE_REGISTRAR)): ?>
<div class="admin-actions">
    <h3>إجراءات المسؤول</h3>
    
    <form method="post" action="update_application.php">
        <input type="hidden" name="id" value="<?php echo $application['id']; ?>">
        
        <div class="form-group">
            <label for="status">تغيير الحالة:</label>
            <select id="status" name="status">
                <option value="pending" <?php echo $application['status'] == 'pending' ? 'selected' : ''; ?>>قيد المراجعة</option>
                <option value="approved" <?php echo $application['status'] == 'approved' ? 'selected' : ''; ?>>مقبول</option>
                <option value="rejected" <?php echo $application['status'] == 'rejected' ? 'selected' : ''; ?>>مرفوض</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="notes">ملاحظات:</label>
            <textarea id="notes" name="notes"><?php echo $application['notes'] ?? ''; ?></textarea>
        </div>
        
        <button type="submit" class="btn">حفظ التغييرات</button>
    </form>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>