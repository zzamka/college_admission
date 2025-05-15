<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';  // تضمين الاتصال والدوال

requireRole(ROLE_ADMIN);

// دالة لتحديث حالة الطلب (إذا لم تكن موجودة مسبقاً)
if (!function_exists('updateApplicationStatus')) {
    function updateApplicationStatus($applicationId, $status) {
        global $pdo;
        $allowedStatuses = ['pending', 'approved', 'rejected'];

        if (!in_array($status, $allowedStatuses)) {
            return false; // حالة غير صحيحة
        }

        $stmt = $pdo->prepare("UPDATE applications SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $applicationId]);
    }
}

// تحديث حالة الطلب
if (isset($_GET['update_status']) && isset($_GET['id']) && isset($_GET['status'])) {
    $applicationId = (int)$_GET['id'];
    $status = $_GET['status'];

    if (updateApplicationStatus($applicationId, $status)) {
        header("Location: view_applications.php"); // إعادة التوجيه بعد التحديث لتجنب التحديث المتكرر بالريفريش
        exit;
    } else {
        echo "<p>فشل تحديث الحالة.</p>";
    }
}

// جلب جميع الطلبات
$applications = getAllApplications();

include '../includes/header.php';
?>

<h2>عرض جميع الطلبات</h2>

<?php if (empty($applications)): ?>
    <p>لا توجد طلبات حالياً.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>اسم الطالب</th>
            <th>البرنامج</th>
            <th>تاريخ التقديم</th>
            <th>الحالة</th>
            <th>الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($applications as $app): ?>
            <tr>
                <td><?php echo htmlspecialchars($app['id']); ?></td>
                <td><?php echo htmlspecialchars($app['student_name']); ?></td>
                <td><?php echo htmlspecialchars($app['program_name']); ?></td>
                <td><?php echo htmlspecialchars($app['submission_date']); ?></td>
                <td>
                    <span class="status-<?php echo htmlspecialchars($app['status']); ?>">
                        <?php 
                            switch($app['status']) {
                                case 'pending': echo 'قيد المراجعة'; break;
                                case 'approved': echo 'مقبول'; break;
                                case 'rejected': echo 'مرفوض'; break;
                                default: echo 'غير معروف';
                            }
                        ?>
                    </span>
                </td>
                <td>
                    <a href="application_details.php?id=<?php echo $app['id']; ?>" class="btn">تفاصيل</a>
                    <div class="status-actions">
                        <a href="?update_status&id=<?php echo $app['id']; ?>&status=approved" class="btn btn-success">قبول</a>
                        <a href="?update_status&id=<?php echo $app['id']; ?>&status=rejected" class="btn btn-danger">رفض</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
