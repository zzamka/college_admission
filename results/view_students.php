<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole(ROLE_RESULTS);

// جلب الطلبات المقبولة للمراجعة
$stmt = $pdo->query("SELECT a.*, u.full_name as student_name 
                    FROM applications a
                    JOIN users u ON a.student_id = u.id
                    WHERE a.status = 'approved'
                    ORDER BY a.submitted_at DESC");
$applications = $stmt->fetchAll();

// معالجة إدخال النتائج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_results'])) {
    $applicationId = $_POST['application_id'];
    $examScore = $_POST['exam_score'];
    $interviewScore = $_POST['interview_score'];
    $finalDecision = $_POST['final_decision'];
    $notes = $_POST['notes'];
    
    // التحقق مما إذا كانت هناك نتائج مسجلة بالفعل
    $stmt = $pdo->prepare("SELECT * FROM results WHERE application_id = ?");
    $stmt->execute([$applicationId]);
    $existingResult = $stmt->fetch();
    
    if ($existingResult) {
        // تحديث النتائج الموجودة
        $stmt = $pdo->prepare("UPDATE results SET 
                             exam_score = ?, 
                             interview_score = ?, 
                             final_decision = ?, 
                             notes = ?, 
                             processed_at = NOW() 
                             WHERE application_id = ?");
        $stmt->execute([$examScore, $interviewScore, $finalDecision, $notes, $applicationId]);
    } else {
        // إدخال نتائج جديدة
        $stmt = $pdo->prepare("INSERT INTO results 
                             (application_id, exam_score, interview_score, final_decision, notes, processed_by) 
                             VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$applicationId, $examScore, $interviewScore, $finalDecision, $notes, $_SESSION['user_id']]);
    }
    
    header("Location: view_students.php");
    exit();
}
?>

<?php include '../../includes/header.php'; ?>
    <h2>إدارة نتائج الطلاب</h2>
    
    <div class="students-list">
        <table>
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>اسم الطالب</th>
                    <th>البرنامج</th>
                    <th>المعدل</th>
                    <th>درجة الاختبار</th>
                    <th>درجة المقابلة</th>
                    <th>القرار النهائي</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <?php
                    // جلب النتائج إذا كانت موجودة
                    $stmt = $pdo->prepare("SELECT * FROM results WHERE application_id = ?");
                    $stmt->execute([$app['id']]);
                    $result = $stmt->fetch();
                    ?>
                    
                    <tr>
                        <td><?php echo $app['id']; ?></td>
                        <td><?php echo htmlspecialchars($app['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($app['program']); ?></td>
                        <td><?php echo $app['gpa']; ?></td>
                        <td><?php echo $result ? $result['exam_score'] : '--'; ?></td>
                        <td><?php echo $result ? $result['interview_score'] : '--'; ?></td>
                        <td>
                            <?php if ($result): ?>
                                <?php 
                                    $decisionLabels = [
                                        'accepted' => 'مقبول',
                                        'rejected' => 'مرفوض',
                                        'waiting' => 'قيد الانتظار'
                                    ];
                                    echo $decisionLabels[$result['final_decision']];
                                ?>
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm" onclick="openResultsModal(
                                <?php echo $app['id']; ?>,
                                '<?php echo htmlspecialchars($app['student_name']); ?>',
                                <?php echo $result ? $result['exam_score'] : 'null'; ?>,
                                <?php echo $result ? $result['interview_score'] : 'null'; ?>,
                                '<?php echo $result ? $result['final_decision'] : ''; ?>',
                                `<?php echo $result ? htmlspecialchars($result['notes']) : ''; ?>`
                            )">إدخال النتائج</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Modal لإدخال النتائج -->
    <div id="resultsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle">إدخال النتائج للطالب: <span id="studentName"></span></h3>
            <form method="POST" id="resultsForm">
                <input type="hidden" name="application_id" id="modalApplicationId">
                
                <div class="form-group">
                    <label for="exam_score">درجة الاختبار (0-100):</label>
                    <input type="number" id="exam_score" name="exam_score" min="0" max="100">
                </div>
                
                <div class="form-group">
                    <label for="interview_score">درجة المقابلة (0-100):</label>
                    <input type="number" id="interview_score" name="interview_score" min="0" max="100">
                </div>
                
                <div class="form-group">
                    <label for="final_decision">القرار النهائي:</label>
                    <select id="final_decision" name="final_decision" required>
                        <option value="accepted">مقبول</option>
                        <option value="rejected">مرفوض</option>
                        <option value="waiting">قيد الانتظار</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">ملاحظات:</label>
                    <textarea id="notes" name="notes" rows="4"></textarea>
                </div>
                
                <button type="submit" name="save_results" class="btn">حفظ النتائج</button>
            </form>
        </div>
    </div>
    
    <script>
        function openResultsModal(applicationId, studentName, examScore, interviewScore, finalDecision, notes) {
            document.getElementById('modalApplicationId').value = applicationId;
            document.getElementById('studentName').textContent = studentName;
            
            if (examScore !== null) {
                document.getElementById('exam_score').value = examScore;
            }
            
            if (interviewScore !== null) {
                document.getElementById('interview_score').value = interviewScore;
            }
            
            if (finalDecision) {
                document.getElementById('final_decision').value = finalDecision;
            }
            
            if (notes) {
                document.getElementById('notes').value = notes;
            }
            
            document.getElementById('resultsModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('resultsModal').style.display = 'none';
            document.getElementById('statusModal').style.display = 'none';
        }
        
        // إغلاق المودال عند النقر خارجها
        window.onclick = function(event) {
            const resultsModal = document.getElementById('resultsModal');
            const statusModal = document.getElementById('statusModal');
            
            if (event.target === resultsModal) {
                closeModal();
            }
            
            if (event.target === statusModal) {
                closeModal();
            }
        }
    </script>
<?php include '../../includes/footer.php'; ?>