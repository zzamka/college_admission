<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// التحقق من أن المستخدم مسجل دخول بالفعل
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// تعريف متغير الخطأ
$error = null;

// معالجة بيانات الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // تنظيف المدخلات
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        // التحقق من صحة البريد الإلكتروني
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("صيغة البريد الإلكتروني غير صالحة");
        }
        
        // التحقق من أن كلمة المرور غير فارغة
        if (empty($password)) {
            throw new Exception("كلمة المرور مطلوبة");
        }
        
        // محاولة تسجيل الدخول
        if (login($email, $password)) {
            // تسجيل وقت تسجيل الدخول
            $_SESSION['login_time'] = time();
            
            // توجيه المستخدم حسب صلاحيته
            $redirectUrl = 'index.php'; // الصفحة الافتراضية
            
            switch ($_SESSION['user_role']) {
                case ROLE_ADMIN:
                    $redirectUrl = 'admin/dashboard.php';
                    break;
                case ROLE_REGISTRAR:
                    $redirectUrl = 'registrar/view_applications.php';
                    break;
                case ROLE_RESULTS:
                    $redirectUrl = 'results/view_students.php';
                    break;
                case ROLE_STUDENT:
                    $redirectUrl = 'student/status.php';
                    break;
            }
            
            // إعادة التوجيه مع منع التخزين المؤقت
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header("Location: " . $redirectUrl);
            exit();
        } else {
            throw new Exception("البريد الإلكتروني أو كلمة المرور غير صحيحة");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Login error: " . $error);
    }
}
?>

<?php include 'includes/header.php'; ?>
    <div class="login-container">
        <div class="login-form">
            <div class="login-header">
                <img src="assets/images/logo.png" alt="شعار الجامعة" class="login-logo">
                <h2>نظام القبول الإلكتروني</h2>
                <h3>تسجيل الدخول</h3>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="email">البريد الإلكتروني:</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           required autofocus value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">كلمة المرور:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <small class="form-text text-muted">
                        <a href="forgot_password.php">نسيت كلمة المرور؟</a>
                    </small>
                </div>
                
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">تذكرني</label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">تسجيل الدخول</button>
                
                <?php if (ALLOW_REGISTRATION): ?>
                    <div class="register-link">
                        ليس لديك حساب؟ <a href="register.php">سجل الآن</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>