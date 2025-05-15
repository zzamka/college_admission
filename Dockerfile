FROM php:8.2-apache

# نسخ كل ملفات المشروع إلى مجلد HTML داخل الحاوية
COPY . /var/www/html/

# تفعيل mod_rewrite إذا كنت تحتاجه (مفيد للروابط النظيفة)
RUN a2enmod rewrite

# إعداد صلاحيات الملفات (اختياري)
RUN chown -R www-data:www-data /var/www/html

# فتح المنفذ الافتراضي لـ Apache
EXPOSE 80
