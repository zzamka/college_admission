<?php
require_once 'config.php';

/**
 * إرجاع جميع الطلبات من قاعدة البيانات
 */
function getAllApplications(): array {
    global $pdo;

    $stmt = $pdo->query("SELECT * FROM applications ORDER BY submission_date DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * تحديث حالة الطلب
 */
function updateApplicationStatus(int $id, string $status): void {
    global $pdo;

    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}
