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

function is_holiday_date(string $date): ?string {
    $pdo = db();
    if (!$pdo) return null;
    $stmt = $pdo->prepare("SELECT title FROM calendar_events WHERE event_date = ? AND category = 'holiday' LIMIT 1");
    $stmt->execute([$date]);
    return $stmt->fetchColumn() ?: null;
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

function get_setting(string $key, string $default): string {
    $pdo = db();
    if (!$pdo) return $default;
    
    // Ensure table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value VARCHAR(255) NOT NULL
    )");
    
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    if ($val === false) {
        // Pre-seed
        $stmt = $pdo->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute([$key, $default]);
        return $default;
    }
    return $val;
}

function set_setting(string $key, string $value): bool {
    $pdo = db();
    if (!$pdo) return false;
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value VARCHAR(255) NOT NULL
    )");
    
    $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    return $stmt->execute([$key, $value]);
}

function calculate_emp_payroll_details(array $emp, int $month, int $year): array {
    $pdo = db();
    
    // 1. Get settings
    $deduction_type = get_setting('payroll_deduction_type', 'flat');
    $flat_deduction_rate = (int)get_setting('payroll_deduction_rate', '150000');
    $daily_allowance_rate = (int)get_setting('payroll_daily_allowance_rate', '100000');
    $overtime_rate = (int)get_setting('payroll_overtime_rate', '50000');
    
    $salary = (int)($emp['salary'] ?? 0);
    $allowance = 500000; // Standard allowance
    
    // Total calendar days in month
    $total_days_in_month = (int)date('t', strtotime("$year-$month-01"));
    
    // 2. Generate expected workdays (weekdays Mon-Fri, excluding holidays)
    $expected_workdays = [];
    $num_days = (int)date('t', strtotime("$year-$month-01"));
    $today_str = date('Y-m-d');
    
    // Query holidays in this month/year
    $holidays = [];
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT event_date FROM calendar_events WHERE category = 'holiday' AND MONTH(event_date) = ? AND YEAR(event_date) = ?");
        $stmt->execute([$month, $year]);
        $holidays = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
    
    for ($d = 1; $d <= $num_days; $d++) {
        $date_str = sprintf('%04d-%02d-%02d', $year, $month, $d);
        
        // If it's a future date, we don't expect work
        if ($date_str > $today_str) {
            continue;
        }
        
        $day_of_week = date('N', strtotime($date_str)); // 1 (Mon) - 7 (Sun)
        if ($day_of_week <= 5) { // Weekday
            if (!in_array($date_str, $holidays)) {
                $expected_workdays[] = $date_str;
            }
        }
    }
    
    // 3. Query actual attendance logs
    $actual_attendance = [];
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ? AND approval_status = 'approved' ORDER BY attendance_date ASC, attendance_time ASC");
        $stmt->execute([$emp['id'], $month, $year]);
        $actual_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    // Group attendance by date
    $att_by_date = [];
    foreach ($actual_attendance as $att) {
        $date = $att['attendance_date'];
        $flow = $att['attendance_flow'];
        if (!isset($att_by_date[$date])) {
            $att_by_date[$date] = [];
        }
        $att_by_date[$date][$flow] = $att['attendance_time'];
    }
    
    // Query approved leaves
    $approved_leaves = [];
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT leave_start, leave_end FROM leaves WHERE user_id = ? AND approval_status = 'approved'");
        $stmt->execute([$emp['id']]);
        $approved_leaves = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    // 4. Calculate attendance metrics
    $attended_days_count = 0;
    $leave_days_count = 0;
    $absent_days_count = 0;
    $overtime_hours = 0.0;
    
    foreach ($expected_workdays as $day) {
        if (isset($att_by_date[$day]['in'])) {
            $attended_days_count++;
            
            // Overtime hours logic
            if (isset($att_by_date[$day]['out'])) {
                $in_time = $att_by_date[$day]['in'];
                $out_time = $att_by_date[$day]['out'];
                
                $in_ts = strtotime("$day $in_time");
                $out_ts = strtotime("$day $out_time");
                
                if ($out_ts > $in_ts) {
                    $duration_hours = ($out_ts - $in_ts) / 3600.0;
                    // Standard shift is 8 hours
                    $ot = max(0.0, $duration_hours - 8.0);
                    // Round to 1 decimal place per day
                    $overtime_hours += round($ot, 1);
                }
            }
        } else {
            // Check if falls in approved leave
            $is_leave = false;
            foreach ($approved_leaves as $lv) {
                if ($day >= $lv['leave_start'] && $day <= $lv['leave_end']) {
                    $is_leave = true;
                    break;
                }
            }
            
            if ($is_leave) {
                $leave_days_count++;
            } else {
                $absent_days_count++;
            }
        }
    }
    
    // Calculate dynamic deduction rate per day
    $expected_workdays_count = count($expected_workdays);
    if ($deduction_type === 'salary_proportional_calendar') {
        $deduction_rate = $total_days_in_month > 0 ? ($salary / $total_days_in_month) : 0;
    } elseif ($deduction_type === 'salary_proportional_workdays') {
        $deduction_rate = $expected_workdays_count > 0 ? ($salary / $expected_workdays_count) : 0;
    } else {
        $deduction_rate = $flat_deduction_rate;
    }
    
    $overtime_pay = (int)round($overtime_hours * $overtime_rate);
    $daily_allowance_pay = (int)round($attended_days_count * $daily_allowance_rate);
    $deductions = 0; // No deductions from base salary now
    
    $gross = $salary + $allowance + $daily_allowance_pay + $overtime_pay;
    $net = $gross - $deductions;
    
    return [
        'emp' => $emp,
        'salary' => $salary,
        'allowance' => $allowance,
        'expected_workdays' => count($expected_workdays),
        'attended_days' => $attended_days_count,
        'leave_days' => $leave_days_count,
        'absent_days' => $absent_days_count,
        'overtime_hours' => $overtime_hours,
        'overtime_pay' => $overtime_pay,
        'daily_allowance_rate' => $daily_allowance_rate,
        'daily_allowance_pay' => $daily_allowance_pay,
        'deductions' => $deductions,
        'gross' => $gross,
        'net' => $net,
        'deduction_rate' => $deduction_rate,
        'deduction_type' => $deduction_type,
        'overtime_rate' => $overtime_rate
    ];
}

/**
 * Menyiapkan/inisialisasi tabel notifications jika belum ada secara dinamis
 */
function init_notifications_table() {
    $pdo = db();
    if (!$pdo) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(20) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            link VARCHAR(255) DEFAULT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
    } catch (PDOException $e) {
        // Abaikan jika gagal
    }
}

/**
 * Menambahkan notifikasi baru
 */
function add_notification(string $user_id, string $title, string $message, ?string $link = null): bool {
    $pdo = db();
    if (!$pdo) return false;
    
    init_notifications_table();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, link, is_read) VALUES (?, ?, ?, ?, 0)");
        return $stmt->execute([$user_id, $title, $message, $link]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Mendapatkan daftar notifikasi untuk user
 */
function get_user_notifications(string $user_id, int $limit = 10): array {
    $pdo = db();
    if (!$pdo) return [];
    
    init_notifications_table();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Menghitung notifikasi belum dibaca
 */
function get_unread_notifications_count(string $user_id): int {
    $pdo = db();
    if (!$pdo) return 0;
    
    init_notifications_table();
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Menandai notifikasi sebagai dibaca
 */
function mark_notification_read(int $id, string $user_id): bool {
    $pdo = db();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $user_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Menandai semua notifikasi user sebagai dibaca
 */
function mark_all_notifications_read(string $user_id): bool {
    $pdo = db();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Mengubah format timestamp ke teks waktu relatif
 */
function time_ago(string $datetime): string {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Baru saja';
    }
    
    $minutes = round($diff / 60);
    if ($minutes < 60) {
        return $minutes . ' menit yang lalu';
    }
    
    $hours = round($diff / 3600);
    if ($hours < 24) {
        return $hours . ' jam yang lalu';
    }
    
    $days = round($diff / 86400);
    if ($days < 30) {
        return $days . ' hari yang lalu';
    }
    
    return format_date($datetime);
}


