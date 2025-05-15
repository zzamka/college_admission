<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole(ROLE_STUDENT);

// جلب طلب الطالب
$stmt = $pdo->prepare("SELECT a.*, r.exam_score, r.interview_score, r.final_decision, r.notes as result_notes
                      FROM applications a
                      LEFT JOIN results r ON a.id = r.application_id
                      WHERE a.student_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$application = $stmt->fetch();

if (!$application) {
    header("Location: apply.php");
    exit();
}
?>

<?php include '../../includes/header.php'; ?>
    <h2>حالة طلبي</h2>
    
    <div class="application-status">
        <div class="status-card">
            <h3>معلومات الطلب</h3>
            <p><strong>البرنامج:</strong> <?php echo htmlspecialchars($application['program']); ?></p>
            <p><strong>اسم المدرسة:</strong> <?php echo htmlspecialchars($application['high_school_name']); ?></p>
            <p><strong>سنة التخرج:</strong> <?php echo htmlspecialchars($application['graduation_year']); ?></p>
            <p><strong>المعدل التراكمي:</strong> <?php echo htmlspecialchars($application['gpa']); ?></p>
            <p><strong>حالة الطلب:</strong> 
                <span class="status-<?php echo $application['status']; ?>">
                    <?php 
                        $statusLabels = [
                            'pending' => 'قيد الانتظار',
                            'under_review' => 'قيد المراجعة',
                            'approved' => 'مقبول',
                            'rejected' => 'مرفوض'
                        ];
                        echo $statusLabels[$application['status']];
                    ?>
                </span>
            </p>
            <p><strong>تاريخ التقديم:</strong> <?php echo date('Y-m-d', strtotime($application['submitted_at'])); ?></p>
            
            <?php if ($application['certificate_path']): ?>
                <p><strong>شهادة الثانوية:</strong> 
                    <a href="/college_admission/assets/uploads/<?php echo $application['certificate_path']; ?>" target="_blank">عرض الشهادة</a>
                </p>
            <?php endif; ?>
        </div>
        
        <?php if ($application['exam_score'] !== null || $application['interview_score'] !== null): ?>
            <div class="status-card">
                <h3>نتائج التقييم</h3>
                <?php if ($application['exam_score'] !== null): ?>
                    <p><strong>درجة الاختبار:</strong> <?php echo $application['exam_score']; ?></p>
                <?php endif; ?>
                
                <?php if ($application['interview_score'] !== null): ?>
                    <p><strong>درجة المقابلة:</strong> <?php echo $application['interview_score']; ?></p>
                <?php endif; ?>
                
                <?php if ($application['final_decision']): ?>
                    <p><strong>القرار النهائي:</strong> 
                        <?php 
                            $decisionLabels = [
                                'accepted' => 'مقبول',
                                'rejected' => 'مرفوض',
                                'waiting' => 'قيد الانتظار'
                            ];
                            echo $decisionLabels[$application['final_decision']];
                        ?>
                    </p>
                <?php endif; ?>
                
                <?php if ($application['result_notes']): ?>
                    <p><strong>ملاحظات:</strong> <?php echo htmlspecialchars($application['result_notes']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php include '../../includes/footer.php'; ?>