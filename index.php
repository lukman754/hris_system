<?php
/**
 * index.php – Router utama HRIS System
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/attendance_v2.php';

// ─── Handle Actions ──────────────────────────────────────────

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && !isset($_GET['action'])) {
    $user = auth_login(trim($_POST['email']), $_POST['password'] ?? '');
    if ($user) {
        $_SESSION['user'] = $user;
        header('Location: /hris_system/');
        exit;
    }
    $login_error = 'Email atau password salah.';
}

// Logout
if (($_GET['action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: /hris_system/');
    exit;
}

// Generic Action Handlers (Real Database CRUD)
if (isset($_SESSION['user']) && (isset($_GET['action']) || isset($_POST['action']))) {
    $pdo    = db();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $page   = $_GET['page'] ?? $_POST['page'] ?? 'dashboard';
    $uid    = $_SESSION['user']['id'];
    
    // Employee Actions (HRD Only)
    if ($page === 'employees' && auth_is_hrd()) {
        if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO users (id, name, email, phone_number, password, role, position, department, salary, join_date, can_attendance) VALUES (?, ?, ?, ?, ?, 'employee', ?, ?, ?, ?, ?)");
            $new_id = 'emp' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
            $stmt->execute([
                $new_id, $_POST['name'], $_POST['email'], $_POST['phone_number'] ?? null, $_POST['password'], 
                $_POST['position'], $_POST['department'], $_POST['salary'], $_POST['join_date'],
                isset($_POST['can_attendance']) ? 1 : 0
            ]);
            header("Location: /hris_system/?page=employees&success=Karyawan berhasil ditambahkan");
            exit;
        }
        if ($action === 'delete' && isset($_GET['id'])) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'employee'");
            $stmt->execute([$_GET['id']]);
            header("Location: /hris_system/?page=employees&success=Karyawan berhasil dihapus");
            exit;
        }
        if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone_number=?, position=?, department=?, salary=?, can_attendance=? WHERE id = ?");
            $stmt->execute([
                $_POST['name'], $_POST['email'], $_POST['phone_number'] ?? null, $_POST['position'], 
                $_POST['department'], $_POST['salary'], 
                isset($_POST['can_attendance']) ? 1 : 0,
                $_POST['id']
            ]);
            header("Location: /hris_system/?page=employees&success=Karyawan berhasil diperbarui");
            exit;
        }
        if ($action === 'reset-password' && isset($_GET['id'])) {
            $emp_id = $_GET['id'];
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $new_password = '';
            for ($i = 0; $i < 8; $i++) {
                $new_password .= $chars[rand(0, strlen($chars) - 1)];
            }
            
            $stmt = $pdo->prepare("SELECT name, phone_number FROM users WHERE id = ? AND role = 'employee'");
            $stmt->execute([$emp_id]);
            $emp = $stmt->fetch();
            if ($emp) {
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$new_password, $emp_id]);
                $_SESSION['reset_password_success'] = [
                    'name' => $emp['name'],
                    'password' => $new_password
                ];
                
                $phone = $emp['phone_number'] ?? '';
                $clean_phone = preg_replace('/[^0-9]/', '', $phone);
                if (str_starts_with($clean_phone, '0')) {
                    $clean_phone = '62' . substr($clean_phone, 1);
                } else if (!empty($clean_phone) && !str_starts_with($clean_phone, '62')) {
                    $clean_phone = '62' . $clean_phone;
                }

                if (!empty($clean_phone)) {
                    $msg = "Yth. *" . $emp['name'] . "*,\n\nPassword akun HRIS Anda telah direset oleh Administrator dengan password sementara berikut:\n\n🔑 *Password Baru:* `" . $new_password . "`\n\nSilakan segera login ke sistem portal HRIS dan ganti password ini melalui menu *Profil Saya* demi menjaga keamanan akun Anda.\n\nTerima kasih,\n*HRD - Perkasa Abadi Logistik*";
                    $wa_link = "https://wa.me/" . $clean_phone . "?text=" . urlencode($msg);
                    header("Location: " . $wa_link);
                } else {
                    header("Location: /hris_system/?page=employees&success=Password berhasil direset (karyawan belum mengisi No. HP)");
                }
                exit;
            }
        }
    }

    // Profile Actions (Any logged-in user)
    if ($page === 'profile') {
        if ($action === 'change-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $old_pass = $_POST['old_password'] ?? '';
            $new_pass = $_POST['new_password'] ?? '';
            $confirm_pass = $_POST['confirm_password'] ?? '';
            
            if ($old_pass === $_SESSION['user']['password']) {
                if ($new_pass === $confirm_pass) {
                    if (strlen($new_pass) >= 8) {
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$new_pass, $uid]);
                        $_SESSION['user']['password'] = $new_pass;
                        header("Location: /hris_system/?page=profile&success=Password berhasil diperbarui");
                    } else {
                        header("Location: /hris_system/?page=profile&error=Password baru minimal 8 karakter");
                    }
                } else {
                    header("Location: /hris_system/?page=profile&error=Konfirmasi password baru tidak cocok");
                }
            } else {
                header("Location: /hris_system/?page=profile&error=Password lama salah");
            }
            exit;
        }
    }

    // Leave Actions
    if ($page === 'leaves') {
        if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO leaves (user_id, leave_type, leave_start, leave_end, leave_reason, approval_status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$uid, $_POST['leave_type'], $_POST['leave_start'], $_POST['leave_end'], $_POST['leave_reason']]);
            header("Location: /hris_system/?page=leaves&success=Pengajuan izin terkirim");
            exit;
        }
        if (auth_is_hrd() && ($action === 'approve' || $action === 'reject')) {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            $stmt = $pdo->prepare("UPDATE leaves SET approval_status = ? WHERE id = ?");
            $stmt->execute([$status, $_GET['id']]);
            header("Location: /hris_system/?page=leaves&success=Pengajuan izin " . ($status === 'approved' ? 'disetujui' : 'ditolak'));
            exit;
        }
    }

    // Attendance Actions
    if ($page === 'attendance') {
        if ($action === 'qr') {
            $token = $_GET['code'] ?? '';
            $lat   = $_GET['lat']  ?? 0;
            $lng   = $_GET['lng']  ?? 0;
            
            $result = handle_scan_attendance($uid, $token, $lat, $lng);
            
            if ($result['status'] === 'success') {
                header("Location: /hris_system/?page=attendance&success=" . urlencode($result['message']));
            } else {
                header("Location: /hris_system/?page=attendance&error=" . urlencode($result['message']));
            }
            exit;
        }
        if ($action === 'submit-photo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $today = date('Y-m-d');
            $holiday_title = is_holiday_date($today);
            if ($holiday_title) {
                header("Location: /hris_system/?page=attendance&error=" . urlencode("Hari ini adalah hari libur resmi ($holiday_title). Anda tidak dapat melakukan absensi."));
                exit;
            }
            $photo_data = $_POST['photo_data'] ?? '';
            $photo_path = null;
            $lat = $_POST['lat'] ?? 0;
            $lng = $_POST['lng'] ?? 0;
            $addr = $_POST['address'] ?? 'Remote/WFH';

            if ($photo_data) {
                $dir = __DIR__ . '/public/uploads/attendance';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $img = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $photo_data);
                $data = base64_decode($img);
                $filename = 'att_' . $uid . '_' . time() . '.jpg';
                file_put_contents($dir . '/' . $filename, $data);
                $photo_path = '/hris_system/public/uploads/attendance/' . $filename;
            }

            // Determine In vs Out
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ? AND attendance_date = CURRENT_DATE AND attendance_flow = 'in'");
            $stmt->execute([$uid]);
            $has_in = $stmt->fetchColumn() > 0;
            $flow = $has_in ? 'out' : 'in';

            $stmt = $pdo->prepare("INSERT INTO attendance (user_id, attendance_date, attendance_time, attendance_type, location, latitude, longitude, photo_path, attendance_flow, approval_status) VALUES (?, CURRENT_DATE, CURRENT_TIME, 'photo', ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$uid, $addr, $lat, $lng, $photo_path, $flow]);
            
            header("Location: /hris_system/?page=attendance&success=Absensi " . ($flow === 'in' ? 'Masuk' : 'Pulang') . " terverifikasi (Pending)");
            exit;
        }
    }

    // Photo Approvals (HRD)
    if ($page === 'photo-approvals' && auth_is_hrd()) {
        if ($action === 'approve' || $action === 'reject') {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            $stmt = $pdo->prepare("UPDATE attendance SET approval_status = ? WHERE id = ?");
            $stmt->execute([$status, $_GET['id']]);
            header("Location: /hris_system/?page=photo-approvals&success=Absensi foto " . ($status === 'approved' ? 'disetujui' : 'ditolak'));
            exit;
        }
    }

    // Announcement Actions (HRD)
    if ($page === 'announcements' && auth_is_hrd()) {
        if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO announcements (title, content, priority, author_id, expires_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['title'], $_POST['content'], $_POST['priority'], $uid, $_POST['expires_at'] ?: null]);
            header("Location: /hris_system/?page=announcements&success=Pengumuman dipublikasikan");
            exit;
        }
        if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ?, priority = ?, expires_at = ? WHERE id = ?");
            $stmt->execute([$_POST['title'], $_POST['content'], $_POST['priority'], $_POST['expires_at'] ?: null, $_POST['id']]);
            header("Location: /hris_system/?page=announcements&success=Pengumuman diperbarui");
            exit;
        }
        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            header("Location: /hris_system/?page=announcements&success=Pengumuman dihapus");
            exit;
        }
    }

    // Performance Reviews (HRD)
    if ($page === 'performance' && auth_is_hrd() && $action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $overall = ($_POST['work_quality'] + $_POST['productivity'] + $_POST['communication'] + $_POST['teamwork'] + $_POST['initiative']) / 5;
        $stmt = $pdo->prepare("INSERT INTO performance_reviews (user_id, reviewer_id, work_quality, productivity, communication, teamwork, initiative, overall, feedback, review_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE)");
        $stmt->execute([
            $_POST['employee_id'], $uid, $_POST['work_quality'], $_POST['productivity'], 
            $_POST['communication'], $_POST['teamwork'], $_POST['initiative'], $overall, $_POST['feedback']
        ]);
        header("Location: /hris_system/?page=performance&success=Review kinerja disimpan");
        exit;
    }
    if ($page === 'performance' && auth_is_hrd() && $action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM performance_reviews WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: /hris_system/?page=performance&success=Review kinerja dihapus");
        exit;
    }

    // Calendar Actions (HRD)
    if ($page === 'calendar' && auth_is_hrd()) {
        if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO calendar_events (title, event_date, category, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['title'], $_POST['date'], $_POST['category'], $_POST['desc']]);
            header("Location: /hris_system/?page=calendar&success=Event kalender ditambahkan");
            exit;
        }
    }

    // QR & Location Management (Admin)
    if ($page === 'locations' && auth_is_hrd()) {
        if ($action === 'generate-qr' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['admin_password'] ?? '';
            if (auth_verify_password($uid, $password)) {
                admin_generate_token($_POST['location_id']);
                header("Location: /hris_system/?page=locations&success=Token QR Baru Berhasil Dipublikasikan");
            } else {
                header("Location: /hris_system/?page=locations&error=Password Admin Salah!");
            }
            exit;
        }

        if ($action === 'save-location' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $params = [
                $_POST['name'], $_POST['latitude'], $_POST['longitude'], $_POST['radius_meters'],
                $_POST['check_in_start'], $_POST['check_in_end'], $_POST['check_out_start'], $_POST['check_out_end']
            ];

            if ($id) {
                $stmt = $pdo->prepare("UPDATE locations SET name=?, latitude=?, longitude=?, radius_meters=?, check_in_start=?, check_in_end=?, check_out_start=?, check_out_end=? WHERE id = ?");
                $params[] = $id;
                $stmt->execute($params);
                header("Location: /hris_system/?page=locations&success=Konfigurasi Kantor Diperbarui");
            } else {
                $stmt = $pdo->prepare("INSERT INTO locations (name, latitude, longitude, radius_meters, check_in_start, check_in_end, check_out_start, check_out_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute($params);
                header("Location: /hris_system/?page=locations&success=Kantor Baru Berhasil Didaftarkan");
            }
            exit;
        }
        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM locations WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            header("Location: /hris_system/?page=locations&success=Kantor berhasil dihapus");
            exit;
        }
    }

    // Payroll Settings (HRD Only)
    if ($page === 'payroll' && auth_is_hrd()) {
        if ($action === 'save-settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            set_setting('payroll_deduction_type', $_POST['deduction_type'] ?? 'flat');
            set_setting('payroll_deduction_rate', $_POST['deduction_rate'] ?? '150000');
            set_setting('payroll_overtime_rate', $_POST['overtime_rate'] ?? '50000');
            
            $month = $_POST['month'] ?? date('n');
            $year = $_POST['year'] ?? date('Y');
            header("Location: /hris_system/?page=payroll&month=$month&year=$year&success=" . urlencode("Pengaturan payroll berhasil diperbarui"));
            exit;
        }
    }
}

// ─── Halaman Login ──────────────────────────────────────────
if (empty($_SESSION['user'])):
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | HRIS System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; color: #111827; }
        .login-card { background: #ffffff; border: 1px solid #D1D5DB; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .btn-primary { background: #2563EB; color: #ffffff; border-radius: 6px; font-weight: 500; font-size: 14px; transition: background 0.2s; }
        .btn-primary:hover { background: #1D4ED8; }
        .input-field { border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; padding: 8px 12px; width: 100%; transition: border-color 0.2s; }
        .input-field:focus { border-color: #2563EB; outline: none; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

<div class="w-full max-w-sm px-4">
    <div class="text-center mb-6">
        <div class="w-12 h-12 mx-auto bg-white border border-gray-200 rounded-md p-1.5 mb-3">
            <img src="/hris_system/public/img/logo.jpg" alt="Logo" class="w-full h-full object-contain">
        </div>
        <h1 class="text-xl font-bold text-gray-900">Perkasa Abadi Logistik</h1>
        <p class="text-sm text-gray-500 mt-1">HRIS Authentication</p>
    </div>

    <div class="login-card p-6">
        <?php if (!empty($login_error)): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-md flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">error</span>
            <span><?= htmlspecialchars($login_error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-[13px] font-semibold text-gray-700 mb-1" for="email">Email</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[18px]">mail</span>
                    <input class="input-field pl-9" id="email" name="email" type="email" placeholder="admin@company.com" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
                </div>
            </div>
            
            <div>
                <label class="block text-[13px] font-semibold text-gray-700 mb-1" for="password">Password</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[18px]">lock</span>
                    <input class="input-field pl-9 pr-9" id="password" name="password" type="password" placeholder="••••••••" required/>
                </div>
            </div>
            
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]" type="checkbox"/>
                    <span class="text-[13px] text-gray-600">Remember session</span>
                </label>
            </div>
            
            <button class="w-full btn-primary py-2.5 mt-2 flex justify-center items-center gap-2" type="submit">
                Sign In
                <span class="material-symbols-outlined text-[18px]">login</span>
            </button>
        </form>
    </div>

    <div class="mt-6 text-center">
        <p class="text-xs text-gray-400">© <?= date('Y') ?> Perkasa Abadi Logistik.<br>Enterprise Resource Planning v2.0</p>
    </div>
</div>

</body>
</html>
<?php
exit;
endif;

// ─── Main App ────────────────────────────────────────────────

auth_check();
$user = auth_user();

$page = preg_replace('/[^a-z0-9\-]/', '', $_GET['page'] ?? 'dashboard');

// Intercept Printable Pay Slip Action
if ($page === 'payroll' && ($_GET['action'] ?? '') === 'slip') {
    auth_check();
    $current_user = auth_user();
    $emp_id = $_GET['id'] ?? '';
    
    // Only HRD can print anyone's slip, employees can only print their own
    if ($current_user['role'] !== 'hrd' && $current_user['id'] !== $emp_id) {
        die("Unauthorized access.");
    }
    
    $sel_month = (int)($_GET['month'] ?? date('n'));
    $sel_year  = (int)($_GET['year']  ?? date('Y'));
    
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$emp_id]);
    $emp_data = $stmt->fetch();
    
    if (!$emp_data) {
        die("Employee not found.");
    }
    
    $slip = calculate_emp_payroll_details($emp_data, $sel_month, $sel_year);
    
    require_once __DIR__ . '/pages/payroll_slip.php';
    exit;
}
$page_files = [
    'dashboard'          => 'pages/dashboard.php',
    'employees'          => 'pages/employees.php',
    'attendance'         => 'pages/attendance.php',
    'attendance-reports' => 'pages/attendance-reports.php',
    'leaves'             => 'pages/leaves.php',
    'photo-approvals'    => 'pages/photo-approvals.php',
    'payroll'            => 'pages/payroll.php',
    'performance'        => 'pages/performance.php',
    'announcements'      => 'pages/announcements.php',
    'calendar'           => 'pages/calendar.php',
    'people'             => 'pages/people.php',
    'locations'          => 'pages/locations.php',
    'profile'            => 'pages/profile.php',
];

$page_titles = [
    'dashboard'          => 'Dashboard',
    'employees'          => 'Manajemen Karyawan',
    'attendance'         => 'Absensi',
    'attendance-reports' => 'Laporan Absensi',
    'leaves'             => auth_is_hrd() ? 'Approval Izin' : 'Pengajuan Izin',
    'photo-approvals'    => 'Approval Foto',
    'payroll'            => 'Penggajian',
    'performance'        => 'Performance',
    'announcements'      => 'Pengumuman',
    'calendar'           => 'Company Calendar',
    'people'             => 'Pegawai',
    'locations'          => 'QR Office',
    'profile'            => 'Profil Saya',
];

// Validasi akses HRD-only
$hrd_only = ['employees','attendance-reports','photo-approvals','locations'];
if (in_array($page, $hrd_only) && !auth_is_hrd()) {
    $page = 'dashboard';
}

// Validasi akses Absensi
if ($page === 'attendance' && ($user['can_attendance'] ?? 0) == 0) {
    $page = 'dashboard';
}

$page_file  = __DIR__ . '/' . ($page_files[$page] ?? 'pages/dashboard.php');
$page_title = $page_titles[$page] ?? 'HRIS';

// Pastikan file ada
if (!file_exists($page_file)) {
    $page_file = __DIR__ . '/pages/dashboard.php';
    $page_title = 'Dashboard';
}

require_once __DIR__ . '/includes/header.php';
require $page_file;
require_once __DIR__ . '/includes/footer.php';
