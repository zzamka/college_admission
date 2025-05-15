<?php
require_once '../includes/auth.php';
requireRole(ROLE_STUDENT);

// الحصول على طلبات الطالب
$applications = getStudentApplications($_SESSION['user_id']);
?>

<?php include '../includes/header.php'; ?>
<h2>حالة طلب الالتحاق</h2>

<?php if (empty($applications)): ?>
    <div class="alert alert-info">
        لم تقم بتقديم أي طلب حتى الآن. <a href="apply.php">اضغط هنا لتقديم طلب جديد</a>.
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>البرنامج</th>
                <th>تاريخ التقديم</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($applications as $app): ?>
                <tr>
                    <td><?php echo $app['id']; ?></td>
                    <td><?php echo $app['program_name']; ?></td>
                    <td><?php echo $app['submission_date']; ?></td>
                    <td>
                        <span class="status-<?php echo $app['status']; ?>">
                            <?php 
                                switch($app['status']) {
                                    case 'pending': echo 'قيد المراجعة'; break;
                                    case 'approved': echo 'مقبول'; break;
                                    case 'rejected': echo 'مرفوض'; break;
                                }
                            ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>