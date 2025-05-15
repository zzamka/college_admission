<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(ROLE_ADMIN);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

    <h2>لوحة تحكم المدير</h2>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>إجمالي المستخدمين</h3>
            <p>
                <?php 
                    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                    echo $stmt->fetchColumn();
                ?>
            </p>
        </div>
        
        <div class="stat-card">
            <h3>الطلبات الجديدة</h3>
            <p>
                <?php 
                    $stmt = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'pending'");
                    echo $stmt->fetchColumn();
                ?>
            </p>
        </div>
        
        <div class="stat-card">
            <h3>طلبات مقبولة</h3>
            <p>
                <?php 
                    $stmt = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'approved'");
                    echo $stmt->fetchColumn();
                ?>
            </p>
        </div>
        
        <div class="stat-card">
            <h3>طلبات مرفوضة</h3>
            <p>
                <?php 
                    $stmt = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'rejected'");
                    echo $stmt->fetchColumn();
                ?>
            </p>
        </div>
    </div>
    
    <div class="actions">
        <a href="manage_users.php" class="btn">إدارة المستخدمين</a>
        <a href="view_applications.php" class="btn">عرض جميع الطلبات</a>
    </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

