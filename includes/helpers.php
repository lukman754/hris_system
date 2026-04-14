<?php
/**
 * Fungsi-fungsi helper umum
 */

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function format_date(?string $date): string {
    if (!$date) return '-';
    return (new DateTime($date))->format('d M Y');
}

function format_rupiah(int|float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function badge(string $status): string {
    $map = [
        'pending'  => ['warning', 'Menunggu'],
        'approved' => ['success', 'Disetujui'],
        'rejected' => ['danger',  'Ditolak'],
        'active'   => ['success', 'Aktif'],
        'inactive' => ['gray',    'Tidak Aktif'],
    ];
    [$cls, $label] = $map[$status] ?? ['gray', $status];
    return "<span class=\"badge badge-{$cls}\">{$label}</span>";
}

function avatar_initials(string $name): string {
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) return strtoupper($parts[0][0] . $parts[1][0]);
    return strtoupper($parts[0][0] ?? '?');
}

function avatar_color(string $name): string {
    $colors = [
        'from-blue-500 to-indigo-600',
        'from-emerald-500 to-green-600',
        'from-purple-500 to-pink-600',
        'from-orange-500 to-red-600',
        'from-teal-500 to-cyan-600',
        'from-rose-500 to-pink-600',
    ];
    return $colors[ord($name[0]) % count($colors)];
}

function get_employees(): array {
    $pdo = db();
    if (!$pdo) return [];
    return $pdo->query("SELECT * FROM users WHERE role = 'employee' ORDER BY name ASC")->fetchAll();
}

function days_until_birthday(string $birthDate): int {
    $today = new DateTime('today');
    $birth = new DateTime($birthDate);
    $this_year = (new DateTime("today"))->format('Y');
    $next = new DateTime("{$this_year}-" . $birth->format('m-d'));
    if ($next < $today) $next->modify('+1 year');
    return $today->diff($next)->days;
}

function is_birthday_today(string $birthDate): bool {
    $birth = new DateTime($birthDate);
    $today = new DateTime('today');
    return $birth->format('m-d') === $today->format('m-d');
}

function calculate_age(string $birthDate): int {
    return (new DateTime($birthDate))->diff(new DateTime('today'))->y;
}

function calculate_work_years(string $joinDate): int {
    return (new DateTime($joinDate))->diff(new DateTime('today'))->y;
}

function leave_type_label(string $type): string {
    return ['sick'=>'Sakit','annual'=>'Cuti Tahunan','personal'=>'Keperluan Pribadi','maternity'=>'Cuti Melahirkan','other'=>'Lainnya'][$type] ?? $type;
}

function get_leaves(string $user_id = null): array {
    $pdo = db();
    if (!$pdo) return [];
    
    $sql = "SELECT l.*, u.name as employee_name, u.department 
            FROM leaves l 
            JOIN users u ON l.user_id = u.id";
    $params = [];
    
    if ($user_id) {
        $sql .= " WHERE l.user_id = ?";
        $params[] = $user_id;
    }
    
    $sql .= " ORDER BY l.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_attendance(string $user_id = null): array {
    $pdo = db();
    if (!$pdo) return [];
    
    $sql = "SELECT a.*, u.name as employee_name, u.department 
            FROM attendance a 
            JOIN users u ON a.user_id = u.id";
    $params = [];
    
    if ($user_id) {
        $sql .= " WHERE a.user_id = ?";
        $params[] = $user_id;
    }
    
    $sql .= " ORDER BY a.attendance_date DESC, a.attendance_time DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_announcements(): array {
    $pdo = db();
    if (!$pdo) return [];
    return $pdo->query("SELECT a.*, u.name as author FROM announcements a 
                        JOIN users u ON a.author_id = u.id 
                        WHERE a.expires_at IS NULL OR a.expires_at >= CURDATE()
                        ORDER BY a.created_at DESC")->fetchAll();
}


function get_calendar_events(): array {
    $pdo = db();
    if (!$pdo) return [];
    return $pdo->query("SELECT * FROM calendar_events ORDER BY event_date ASC")->fetchAll();
}

function get_performance_reviews(): array {
    $pdo = db();
    if (!$pdo) return [];
    $data = $pdo->query("SELECT * FROM performance_reviews ORDER BY review_date DESC")->fetchAll();
    $reviews = [];
    foreach ($data as $r) {
        $reviews[$r['user_id']] = $r;
    }
    return $reviews;
}
