<?php
require_once 'config.php';

$table = $_GET['table'] ?? '';
$employeeId = $_GET['employee_id'] ?? '';

try {
    switch ($table) {
        case 'employees':
            if ($employeeId) {
                $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
                $stmt->execute([$employeeId]);
            } else {
                $stmt = $pdo->query("SELECT * FROM employees ORDER BY created_at DESC");
            }
            break;

        case 'attendance':
            if ($employeeId) {
                $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? ORDER BY attendance_date DESC, attendance_time DESC");
                $stmt->execute([$employeeId]);
            } else {
                $stmt = $pdo->query("SELECT * FROM attendance ORDER BY attendance_date DESC, attendance_time DESC");
            }
            break;

        case 'leave_requests':
            if ($employeeId) {
                $stmt = $pdo->prepare("SELECT * FROM leave_requests WHERE employee_id = ? ORDER BY created_at DESC");
                $stmt->execute([$employeeId]);
            } else {
                $stmt = $pdo->query("SELECT * FROM leave_requests ORDER BY created_at DESC");
            }
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid table']);
            exit;
    }

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $result]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
