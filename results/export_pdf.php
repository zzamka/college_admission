<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole(ROLE_RESULTS);

require_once '../../vendor/autoload.php'; // إذا كنت تستخدم Composer

// جلب الطلبات المقبولة مع نتائجها
$stmt = $pdo->query("SELECT a.id, u.full_name, a.program, a.gpa, 
                     r.exam_score, r.interview_score, r.final_decision
                     FROM applications a
                     JOIN users u ON a.student_id = u.id
                     JOIN results r ON a.id = r.application_id
                     WHERE a.status = 'approved'
                     ORDER BY u.full_name");

$students = $stmt->fetchAll();

// إنشاء مستند PDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'direction' => 'rtl'
]);

$html = '
<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نتائج الطلاب المقبولين</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { text-align: center; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #2c3e50; color: white; }
        .accepted { background-color: #d4edda; }
        .rejected { background-color: #f8d7da; }
        .waiting { background-color: #fff3cd; }
    </style>
</head>
<body>
    <h1>نتائج الطلاب المقبولين</h1>
    <table>
        <tr>
            <th>رقم الطلب</th>
            <th>اسم الطالب</th>
            <th>البرنامج</th>
            <th>المعدل</th>
            <th>درجة الاختبار</th>
            <th>درجة المقابلة</th>
            <th>القرار النهائي</th>
        </tr>';

foreach ($students as $student) {
    $decisionClass = '';
    switch ($student['final_decision']) {
        case 'accepted': $decisionClass = 'accepted'; break;
        case 'rejected': $decisionClass = 'rejected'; break;
        case 'waiting': $decisionClass = 'waiting'; break;
    }
    
    $decisionLabel = '';
    switch ($student['final_decision']) {
        case 'accepted': $decisionLabel = 'مقبول'; break;
        case 'rejected': $decisionLabel = 'مرفوض'; break;
        case 'waiting': $decisionLabel = 'قيد الانتظار'; break;
    }
    
    $html .= '
        <tr>
            <td>' . $student['id'] . '</td>
            <td>' . htmlspecialchars($student['full_name']) . '</td>
            <td>' . htmlspecialchars($student['program']) . '</td>
            <td>' . $student['gpa'] . '</td>
            <td>' . $student['exam_score'] . '</td>
            <td>' . $student['interview_score'] . '</td>
            <td class="' . $decisionClass . '">' . $decisionLabel . '</td>
        </tr>';
}

$html .= '
    </table>
    <p style="text-align: left; margin-top: 30px; font-size: 12px;">
        تم إنشاء هذا التقرير في: ' . date('Y-m-d H:i:s') . '
    </p>
</body>
</html>';

$mpdf->WriteHTML($html);
$mpdf->Output('نتائج_الطلاب_المقبولين.pdf', 'D'); // 'D' للتنزيل المباشر
exit();
?>