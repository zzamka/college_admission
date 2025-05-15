<?php include 'includes/header.php'; ?>

<style>
    body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f5f5;
        color: #2a2a2a;
        margin: 0;
        padding: 0;
        direction: rtl;
    }
    .welcome-message {
        background: #f0e6d2; /* بيج فاتح */
        border-radius: 10px;
        padding: 25px;
        margin: 20px auto;
        max-width: 900px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        text-align: center;
    }
    .btn {
        background-color: #8B4513; /* بني */
        color: white;
        padding: 12px 24px;
        text-decoration: none;
        border-radius: 6px;
        font-weight: bold;
        transition: background-color 0.3s ease;
        display: inline-block;
        margin: 10px 5px;
    }
    .btn:hover {
        background-color: #a05e2a;
    }

    /* أقسام الكلية */
    .departments-section {
        max-width: 1100px;
        margin: 40px auto;
        padding: 10px 20px;
    }
    .departments-section h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #8B4513;
    }
    .departments-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 25px;
    }
    .department-card {
        background: #fff9f0;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        width: 280px;
        padding: 15px;
        text-align: center;
        transition: transform 0.3s ease;
    }
    .department-card:hover {
        transform: translateY(-7px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .department-card img {
        width: 100%;
        border-radius: 10px;
    }
    .head-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        margin: 15px auto 10px;
        border: 3px solid #8B4513;
    }
    .department-card h3 {
        color: #8B4513;
        margin-bottom: 5px;
    }

    /* قسم الصور المتحركة */
    .animated-gallery {
        max-width: 900px;
        margin: 50px auto 30px;
        padding: 15px 20px;
        background: #f0e6d2;
        border-radius: 12px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        text-align: center;
    }
    .animated-gallery h2 {
        color: #8B4513;
        margin-bottom: 20px;
    }
    .slider {
        position: relative;
        height: 300px;
        overflow: hidden;
        border-radius: 12px;
    }
    .slider img.slide {
        width: 100%;
        height: 300px;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
        transition: opacity 1s ease-in-out;
        border-radius: 12px;
    }
    .slider img.slide.active {
        opacity: 1;
        position: relative;
    }

    /* لجعل الصفحة متجاوبة */
    @media (max-width: 900px) {
        .departments-container {
            flex-direction: column;
            align-items: center;
        }
        .department-card {
            width: 90%;
        }
        .slider, .slider img.slide {
            height: 200px;
        }
    }
</style>

<div class="welcome-message">
    <h2>مرحباً بكم في نظام إدارة طلبات الالتحاق بالكلية</h2>
    
    <?php if (!isLoggedIn()): ?>
        <p>لتقديم طلب الالتحاق أو إدارة الطلبات، يرجى تسجيل الدخول أولاً.</p>
        <a href="login.php" class="btn">تسجيل الدخول</a>
    <?php else: ?>
        <p>مرحباً بعودتك، <?php echo isset($_SESSION["full_name"]) ? htmlspecialchars($_SESSION["full_name"]) : 'مستخدم'; ?></p>

        <p>يمكنك الآن الوصول إلى جميع الميزات المتاحة لك حسب صلاحياتك.</p>
        
        <?php if ($_SESSION['user_role'] === ROLE_STUDENT): ?>
            <a href="student/apply.php" class="btn">تقديم طلب التحاق جديد</a>
            <a href="student/status.php" class="btn">التحقق من حالة طلبي</a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- قسم أقسام الكلية -->
<section class="departments-section">
    <h2>أقسام الكلية</h2>
    <div class="departments-container">
        <!-- قسم 1 -->
        <div class="department-card">
            <img src="assets/images/department_computer.jpg" alt="قسم الحاسوب">
            <h3>قسم الحاسوب</h3>
            <img src="assets/images/head_computer.jpg" alt="رئيس قسم الحاسوب" class="head-image">
            <p>د. أحمد العلي - رئيس قسم الحاسوب</p>
        </div>

        <!-- قسم 2 -->
        <div class="department-card">
            <img src="assets/images/department_electronics.jpg" alt="قسم الإلكترونيات">
            <h3>قسم الإلكترونيات</h3>
            <img src="assets/images/head_electronics.jpg" alt="رئيس قسم الإلكترونيات" class="head-image">
            <p>د. سارة محمد - رئيس قسم الإلكترونيات</p>
        </div>

        <!-- قسم 3 -->
        <div class="department-card">
            <img src="assets/images/department_management.jpg" alt="قسم الإدارة">
            <h3>قسم الإدارة</h3>
            <img src="assets/images/head_management.jpg" alt="رئيس قسم الإدارة" class="head-image">
            <p>د. خالد يوسف - رئيس قسم الإدارة</p>
        </div>
    </div>
</section>

<!-- قسم الصور المتحركة -->
<section class="animated-gallery">
    <h2>صور متحركة عن الكلية</h2>
    <div class="slider">
        <img src="assets/images/slide1.jpg" alt="صورة الكلية 1" class="slide active">
        <img src="assets/images/slide2.jpg" alt="صورة الكلية 2" class="slide">
        <img src="assets/images/slide3.jpg" alt="صورة الكلية 3" class="slide">
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- إضافة سكريبت صغير لتغيير الصور تلقائياً -->
<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');

    setInterval(() => {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
    }, 3000);
</script>
