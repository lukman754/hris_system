<?php
require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

$action = $input['action'] ?? '';
$table = $input['table'] ?? '';
$data = $input['data'] ?? [];

try {
    switch ($action) {
        case 'create':
            if ($table === 'employees') {
                $stmt = $pdo->prepare("
                    INSERT INTO employees (employee_id, name, email, password, role, position, department, salary, phone, address, join_date, birth_date, status, photo_url) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $data['employee_id'],
                    $data['name'],
                    $data['email'],
                    $data['password'],
                    $data['role'],
                    $data['position'],
                    $data['department'],
                    $data['salary'],
                    $data['phone'],
                    $data['address'],
                    $data['join_date'],
                    $data['birth_date'],
                    $data['status'],
                    $data['photo_url']
                ]);
            } elseif ($table === 'attendance') {
                $stmt = $pdo->prepare("
                    INSERT INTO attendance (employee_id, attendance_date, attendance_time, attendance_type, location, latitude, longitude, status, photo_url, approval_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $data['employee_id'],
                    $data['attendance_date'],
                    $data['attendance_time'],
                    $data['attendance_type'],
                    $data['location'],
                    $data['latitude'],
                    $data['longitude'],
                    $data['status'],
                    $data['photo'] ?? null,
                    $data['approval_status']
                ]);
            } elseif ($table === 'leave_requests') {
                $stmt = $pdo->prepare("
                    INSERT INTO leave_requests (employee_id, leave_type, leave_start, leave_end, leave_reason, approval_status) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $data['employee_id'],
                    $data['leave_type'],
                    $data['leave_start'],
                    $data['leave_end'],
                    $data['leave_reason'],
                    $data['approval_status']
                ]);
            }
            break;

        case 'update':
            if ($table === 'employees') {
                $stmt = $pdo->prepare("
                    UPDATE employees SET name=?, email=?, position=?, department=?, salary=?, phone=?, address=?, status=?, photo_url=? 
                    WHERE employee_id=?
                ");
                $stmt->execute([
                    $data['name'],
                    $data['email'],
                    $data['position'],
                    $data['department'],
                    $data['salary'],
                    $data['phone'],
                    $data['address'],
                    $data['status'],
                    $data['photo_url'],
                    $data['employee_id']
                ]);
            } elseif ($table === 'attendance') {
                $stmt = $pdo->prepare("UPDATE attendance SET approval_status=? WHERE id=?");
                $stmt->execute([$data['approval_status'], $data['id']]);
            } elseif ($table === 'leave_requests') {
                $stmt = $pdo->prepare("UPDATE leave_requests SET approval_status=? WHERE id=?");
                $stmt->execute([$data['approval_status'], $data['id']]);
            }
            break;

        case 'read':
            if ($table === 'employees') {
                $stmt = $pdo->query("SELECT * FROM employees ORDER BY created_at DESC");
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $result]);
                exit;
            }
            break;
    }

    echo json_encode(['success' => true, 'message' => 'Operation completed successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
