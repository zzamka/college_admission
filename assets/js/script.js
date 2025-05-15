document.addEventListener('DOMContentLoaded', function() {
    // التحقق من صيغة البريد الإلكتروني وكلمة المرور في النماذج
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (form.id === 'login-form' || form.id === 'register-form') {
                const email = form.querySelector('input[type="email"]');
                const password = form.querySelector('input[type="password"]');
                
                if (!validateEmail(email.value)) {
                    alert('الرجاء إدخال بريد إلكتروني صحيح');
                    e.preventDefault();
                    return;
                }
                
                if (password.value.length < 6) {
                    alert('كلمة المرور يجب أن تكون على الأقل 6 أحرف');
                    e.preventDefault();
                    return;
                }
            }
            
            if (form.enctype === 'multipart/form-data') {
                const fileInputs = form.querySelectorAll('input[type="file"]');
                let valid = true;
                
                fileInputs.forEach(input => {
                    if (input.required && !input.files.length) {
                        alert('الرجاء اختيار الملف المطلوب');
                        valid = false;
                    }
                });
                
                if (!valid) e.preventDefault();
            }
        });
    });
    
    // دالة التحقق من البريد الإلكتروني
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // إظهار/إخفاء كلمة المرور
    const togglePassword = document.querySelector('.toggle-password');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const passwordInput = document.querySelector('#password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
});