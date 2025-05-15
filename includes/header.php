<?php
// includes/header.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>نظام إدارة طلبات الالتحاق بالكلية</title>
    <link rel="stylesheet" href="/college_admission/assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        /* ألوان البيج والبني */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f1ea; /* بيج فاتح */
            color: #5a3e1b; /* بني داكن */
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #8b5e3c; /* بني متوسط */
            color: #f5f1ea;
            padding: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        header .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
        }
        header h1 {
            font-size: 1.5rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        header h1 img {
            height: 50px;
            width: auto;
        }
        nav a {
            color: #f5f1ea;
            margin-left: 15px;
            text-decoration: none;
            font-weight: 600;
        }
        nav a:hover {
            text-decoration: underline;
        }
        nav span {
            margin-left: 15px;
            font-weight: 600;
        }
        /* مودال نموذج التقديم */
        .modal {
            display: none; /* مخفي */
            position: fixed;
            z-index: 1000;
            left: 0; top: 0; width: 100%; height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #f5f1ea;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 0 10px #5a3e1b;
            color: #5a3e1b;
            position: relative;
        }
        .close-btn {
            color: #5a3e1b;
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
        .modal form label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
        }
        .modal form input[type="text"],
        .modal form input[type="file"],
        .modal form input[type="number"],
        .modal form input[type="email"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #8b5e3c;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .modal form textarea {
            width: 100%;
            min-height: 80px;
            padding: 8px;
            border: 1px solid #8b5e3c;
            border-radius: 4px;
            resize: vertical;
            margin-top: 5px;
        }
        .modal form .checkbox-group {
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal form button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #8b5e3c;
            color: #f5f1ea;
            border: none;
            border-radius: 5px;
            font-weight: 700;
            cursor: pointer;
            opacity: 0.5;
        }
        .modal form button.enabled {
            opacity: 1;
        }
        .modal form button:hover.enabled {
            background-color: #6e4c2b;
        }

        /* ريسبونسيف */
        @media (max-width: 600px) {
            header .container {
                flex-direction: column;
                gap: 10px;
            }
            header h1 {
                justify-content: center;
            }
            nav a {
                margin-left: 0;
                margin-right: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>
                <img src="/college_admission/assets/images/logo.png" alt="شعار الكلية" />
                نظام إدارة طلبات الالتحاق بالكلية
            </h1>
            <nav>
                <a href="#" id="applyBtn"><i class="fas fa-file-signature"></i> تقديم للكلية</a>

                <?php if (isLoggedIn()): ?>
                    <span>مرحباً، <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'مستخدم'); ?></span>
                    <a href="/college_admission/logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
                    <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
                        <a href="/college_admission/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a>
                    <?php elseif ($_SESSION['user_role'] === ROLE_REGISTRAR): ?>
                        <a href="/college_admission/registrar/view_applications.php"><i class="fas fa-file-alt"></i> الطلبات</a>
                    <?php elseif ($_SESSION['user_role'] === ROLE_RESULTS): ?>
                        <a href="/college_admission/results/view_students.php"><i class="fas fa-graduation-cap"></i> النتائج</a>
                    <?php elseif ($_SESSION['user_role'] === ROLE_STUDENT): ?>
                        <a href="/college_admission/student/status.php"><i class="fas fa-user-graduate"></i> حالة طلبي</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/college_admission/login.php"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- مودال نموذج التقديم -->
    <div id="applyModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeModal">&times;</span>
            <h2>نموذج تقديم طلب الالتحاق</h2>
            <form id="applyForm" action="/college_admission/student/submit_application.php" method="POST" enctype="multipart/form-data">
                <label for="student_name">اسم الطالب رباعي:</label>
                <input type="text" id="student_name" name="student_name" required />

                <label for="mother_name">اسم الوالدة:</label>
                <input type="text" id="mother_name" name="mother_name" required />

                <label for="address">السكن:</label>
                <input type="text" id="address" name="address" required />

                <label for="national_id">الرقم الوطني:</label>
                <input type="text" id="national_id" name="national_id" required />

                <label for="national_id_image">صورة الرقم الوطني (jpg, png):</label>
                <input type="file" id="national_id_image" name="national_id_image" accept=".jpg,.jpeg,.png" required />

                <label for="certificate_pdf">صورة الشهادة (PDF):</label>
                <input type="file" id="certificate_pdf" name="certificate_pdf" accept=".pdf" required />

                <p style="margin-top:15px; font-size:0.9rem;">
                    يرجى قراءة الشروط التالية بعناية:
                    <br />
                    1. يجب أن تكون جميع البيانات صحيحة.
                    <br />
                    2. يجب إرفاق صورة واضحة للرقم الوطني.
                    <br />
                    3. يجب إرفاق الشهادة بصيغة PDF فقط.
                    <br />
                    4. لا يمكن التراجع عن التقديم بعد الإرسال.
                </p>

                <div class="checkbox-group">
                    <input type="checkbox" id="agreeTerms" />
                    <label for="agreeTerms">أوافق على الشروط والأحكام</label>
                </div>

                <button type="submit" id="submitBtn" disabled>تقديم الطلب</button>
            </form>
        </div>
    </div>

    <script>
        // فتح المودال
        const applyBtn = document.getElementById('applyBtn');
        const applyModal = document.getElementById('applyModal');
        const closeModal = document.getElementById('closeModal');
        const agreeTerms = document.getElementById('agreeTerms');
        const submitBtn = document.getElementById('submitBtn');

        applyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            applyModal.style.display = 'block';
        });

        closeModal.addEventListener('click', function() {
            applyModal.style.display = 'none';
            resetForm();
        });

        window.addEventListener('click', function(e) {
            if (e.target == applyModal) {
                applyModal.style.display = 'none';
                resetForm();
            }
        });

        agreeTerms.addEventListener('change', function() {
            submitBtn.disabled = !this.checked;
            if (this.checked) {
                submitBtn.classList.add('enabled');
            } else {
                submitBtn.classList.remove('enabled');
            }
        });

        function resetForm() {
            document.getElementById('applyForm').reset();
            submitBtn.disabled = true;
            submitBtn.classList.remove('enabled');
        }
    </script>
    <main class="container">
