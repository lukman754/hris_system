<?php
/**
 * api/index.php – Endpoint API minimal untuk AJAX request
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Auth check
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get-employees':
        echo json_encode(get_employees());
        break;
        
    case 'get-attendance':
        echo json_encode(get_attendance());
        break;

    case 'get-notifications':
        $uid = $_SESSION['user']['id'];
        $notifs = get_user_notifications($uid, 15);
        $unread_count = get_unread_notifications_count($uid);
        
        $formatted_notifs = [];
        foreach ($notifs as $n) {
            $n['time_ago'] = time_ago($n['created_at']);
            $formatted_notifs[] = $n;
        }
        
        echo json_encode([
            'unread_count' => $unread_count,
            'notifications' => $formatted_notifs
        ]);
        break;

    case 'mark-notification-read':
        $uid = $_SESSION['user']['id'];
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            mark_notification_read($id, $uid);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Invalid ID']);
        }
        break;

    case 'mark-all-notifications-read':
        $uid = $_SESSION['user']['id'];
        mark_all_notifications_read($uid);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
        break;
}
