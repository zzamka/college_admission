<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(ROLE_ADMIN);

// معالجة إضافة مستخدم جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    if (createUser($username, $password, $full_name, $email, $role)) {
        $success = "تم إنشاء المستخدم بنجاح";
    } else {
        $error = "حدث خطأ أثناء إنشاء المستخدم";
    }
}

// جلب جميع المستخدمين
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<h2>إدارة المستخدمين</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php elseif (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="user-management">
    <div class="add-user-form">
        <h3>إضافة مستخدم جديد</h3>
        <form method="POST">
            <div class="form-group">
                <label for="username">اسم المستخدم:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="full_name">الاسم الكامل:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>

            <div class="form-group">
                <label for="email">البريد الإلكتروني:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="role">الدور:</label>
                <select id="role" name="role" required>
                    <option value="<?php echo ROLE_ADMIN; ?>">مدير</option>
                    <option value="<?php echo ROLE_REGISTRAR; ?>">مسجل</option>
                    <option value="<?php echo ROLE_RESULTS; ?>">مسؤول النتائج</option>
                    <option value="<?php echo ROLE_STUDENT; ?>">طالب</option>
                </select>
            </div>

            <button type="submit" name="add_user" class="btn">إضافة مستخدم</button>
        </form>
    </div>

    <div class="users-list">
        <h3>قائمة المستخدمين</h3>
        <table>
            <thead>
                <tr>
                    <th>اسم المستخدم</th>
                    <th>الاسم الكامل</th>
                    <th>البريد الإلكتروني</th>
                    <th>الدور</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($user['email'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($user['role'] ?? '—'); ?></td>
                        <td><?php echo isset($user['created_at']) ? date('Y-m-d', strtotime($user['created_at'])) : '—'; ?></td>
                        <td>
                            <a href="#" class="btn btn-sm">تعديل</a>
                            <a href="#" class="btn btn-sm btn-danger">حذف</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
