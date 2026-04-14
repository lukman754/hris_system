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
            $stmt = $pdo->prepare("INSERT INTO users (id, name, email, password, role, position, department, salary, join_date) VALUES (?, ?, ?, ?, 'employee', ?, ?, ?, ?)");
            $new_id = 'emp' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
            $stmt->execute([
                $new_id, $_POST['name'], $_POST['email'], $_POST['password'], 
                $_POST['position'], $_POST['department'], $_POST['salary'], $_POST['join_date']
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
    <title>Login | Perkasa Abadi Logistik</title>
    <!-- Google Fonts: Manrope & Inter -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@700;800&display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-background": "#191c1e",
                        "primary": "#003d9b",
                        "surface-container": "#edeef0",
                        "secondary": "#4c616c",
                        "on-primary-container": "#c4d2ff",
                        "inverse-primary": "#b2c5ff",
                        "tertiary": "#653900",
                        "tertiary-fixed-dim": "#ffb870",
                        "surface": "#f8f9fb",
                        "error": "#ba1a1a",
                        "outline-variant": "#c3c6d6",
                        "tertiary-fixed": "#ffdcbe",
                        "on-secondary-container": "#526772",
                        "on-secondary-fixed": "#071e27",
                        "on-primary": "#ffffff",
                        "surface-container-highest": "#e1e2e4",
                        "on-error-container": "#93000a",
                        "surface-container-high": "#e7e8ea",
                        "primary-container": "#0052cc",
                        "outline": "#737685",
                        "secondary-fixed-dim": "#b4cad6",
                        "on-primary-fixed-variant": "#0040a2",
                        "on-secondary": "#ffffff",
                        "on-tertiary-container": "#ffc995",
                        "surface-container-low": "#f3f4f6",
                        "secondary-fixed": "#cfe6f2",
                        "surface-tint": "#0c56d0",
                        "on-surface-variant": "#434654",
                        "on-error": "#ffffff",
                        "on-tertiary": "#ffffff",
                        "inverse-surface": "#2e3132",
                        "on-primary-fixed": "#001848",
                        "surface-variant": "#e1e2e4",
                        "on-surface": "#191c1e",
                        "on-tertiary-fixed": "#2c1600",
                        "tertiary-container": "#864d00",
                        "surface-bright": "#f8f9fb",
                        "primary-fixed-dim": "#b2c5ff",
                        "background": "#f8f9fb",
                        "secondary-container": "#cfe6f2",
                        "surface-dim": "#d9dadc",
                        "surface-container-lowest": "#ffffff",
                        "on-tertiary-fixed-variant": "#693c00",
                        "on-secondary-fixed-variant": "#354a53",
                        "inverse-on-surface": "#f0f1f3",
                        "error-container": "#ffdad6",
                        "primary-fixed": "#dae2ff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "fontFamily": {
                        "headline": ["Manrope"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, .font-headline { font-family: 'Manrope', sans-serif; }
        
        .kp-input {
            background-color: transparent;
            border: none;
            border-bottom: 2px solid #737685;
            transition: border-bottom-color 0.2s ease;
            padding-left: 0;
            padding-right: 0;
        }
        .kp-input:focus {
            border-bottom-color: #003d9b;
            ring: 0;
            outline: none;
            box-shadow: none;
        }

        .grid-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        /* Re-adding essential project classes */
        .card { background: white; border-radius: 12px; border: 1px solid rgba(0,0,0,.06); box-shadow: 0 4px 20px rgba(0,0,0,.04); }
        .fade-up { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-white min-h-screen">
<main class="flex min-h-screen flex-col md:flex-row">
    <!-- Left Column: Branding & Illustration (Adaptive Navbar on Mobile) -->
    <div class="relative w-full md:w-1/2 bg-primary flex flex-col md:items-center md:justify-center p-5 md:p-12 text-on-primary overflow-hidden shrink-0">
        <!-- Decorative Grid Pattern (Desktop Only) -->
        <div class="absolute inset-0 grid-pattern opacity-20 hidden md:block"></div>
        
        <!-- Content Container -->
        <div class="relative z-10 flex flex-row md:flex-col items-center md:justify-center text-left md:text-center w-full max-w-md gap-4 md:gap-0">
            <!-- Logo Bin (Clean, No Effects) -->
            <div class="bg-white p-1.5 md:p-6 rounded-xl md:rounded-2xl shadow-sm">
                <img alt="Perkasa Abadi Logo" class="w-10 h-10 md:w-48 md:h-48 object-contain" src="/hris_system/public/img/logo.jpg"/>
            </div>
            
            <div class="flex flex-col md:items-center">
                <h2 class="text-lg md:text-4xl font-extrabold font-headline tracking-tight uppercase leading-none md:mt-12 md:mb-4">Perkasa Abadi Logistik</h2>
                <div class="hidden md:block h-1 w-20 bg-inverse-primary mb-6"></div>
                <p class="hidden md:block text-xl font-medium text-on-primary-container opacity-90 leading-relaxed">
                    Logistics Excellence through People. <br/>
                    Empowering movement, delivering trust.
                </p>
            </div>
        </div>
        
        <!-- Floating Decorative Element (Desktop Only) -->
        <div class="absolute bottom-[-10%] right-[-10%] opacity-10 hidden md:block">
            <span class="material-symbols-outlined text-[300px]" style="font-variation-settings: 'wght' 100;">precision_manufacturing</span>
        </div>
    </div>
    
    <!-- Right Column: Login Form -->
    <div class="w-full md:w-1/2 bg-white flex flex-col justify-center items-center p-8 md:p-24">
        <div class="w-full max-w-md fade-up">
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-4xl font-extrabold text-on-surface font-headline mb-3">Employee Access</h1>
                <p class="text-on-surface-variant text-lg">Enter your credentials to secure your shift.</p>
            </div>

            <!-- Functional Error Message -->
            <?php if (!empty($login_error)): ?>
            <div class="p-4 bg-error/5 border border-error/10 rounded-xl text-error text-sm mb-8 flex items-center gap-3 animate-pulse">
                <span class="material-symbols-outlined">error</span>
                <span class="font-bold"><?= htmlspecialchars($login_error) ?></span>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" class="space-y-8">
                <!-- Email / ID Field -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-outline uppercase tracking-widest" for="employee-id">Employee ID or Email</label>
                    <input class="kp-input w-full py-4 text-on-surface text-lg font-medium" 
                           id="employee-id" name="email" 
                           placeholder="e.g. name@perkasaabadi.com" 
                           type="email" required autofocus
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
                </div>
                <!-- Password Field -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-outline uppercase tracking-widest" for="password">Password</label>
                    <div class="relative">
                        <input class="kp-input w-full py-4 pr-12 text-on-surface text-lg font-medium" 
                               id="password" name="password" 
                               placeholder="••••••••" type="password" required/>
                        <button class="absolute right-0 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors" 
                                type="button" onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password';">
                            <span class="material-symbols-outlined" data-icon="visibility">visibility</span>
                        </button>
                    </div>
                </div>
                <!-- Actions Row -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input class="w-5 h-5 rounded-sm border-2 border-outline text-primary focus:ring-0 cursor-pointer" type="checkbox"/>
                        <span class="text-sm font-semibold text-on-surface-variant group-hover:text-on-surface transition-colors">Remember me</span>
                    </label>
                    <a class="text-sm font-bold text-primary hover:underline transition-all" href="#">Forgot Password?</a>
                </div>
                <!-- Primary Action -->
                <button class="w-full bg-primary text-on-primary font-bold py-5 rounded-lg hover:bg-primary-container transition-colors flex items-center justify-center gap-3 text-lg shadow-md" type="submit">
                    <span>Login to Dashboard</span>
                    <span class="material-symbols-outlined" data-icon="arrow_forward">arrow_forward</span>
                </button>
            </form>

            <!-- Demo credentials -->
            <div class="mt-10 p-6 bg-surface-container rounded-2xl border border-outline-variant/30">
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-primary">info</span>
                    <span class="text-xs font-black uppercase tracking-widest text-on-surface">Demo Credentials</span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-outline">HRD:</span>
                        <span class="font-bold text-on-surface">hrd@company.com / admin123</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-outline">Employee:</span>
                        <span class="font-bold text-on-surface">ayu@company.com / emp123</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Status & Footer -->
            <div class="mt-12 flex flex-col items-center gap-6">
                <div class="flex items-center gap-3 bg-surface-container-low px-6 py-2.5 rounded-full border border-outline-variant/30">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-widest">System Status: Excellence Operational</span>
                </div>
                <div class="text-center">
                    <p class="text-[10px] text-outline uppercase tracking-[0.2em] leading-relaxed">
                        Unauthorized access is strictly prohibited <br/> under security protocol 14-B.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- Minimal Footer -->
<footer class="bg-white border-t border-outline-variant/30 py-8 px-8">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl" data-icon="precision_manufacturing">precision_manufacturing</span>
            <span class="font-bold text-on-surface text-sm">Perkasa Abadi Logistik</span>
        </div>
        <div class="flex gap-8">
            <a class="text-on-surface-variant hover:text-primary transition-colors text-xs font-semibold uppercase tracking-wider" href="#">Support</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors text-xs font-semibold uppercase tracking-wider" href="#">Privacy</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors text-xs font-semibold uppercase tracking-wider" href="#">Terms</a>
        </div>
        <p class="text-on-surface-variant text-[11px] font-medium">© 2026 Perkasa Abadi Logistik. All rights reserved.</p>
    </div>
</footer>
</body>
</html>
<?php
exit;
endif;

// ─── Main App ────────────────────────────────────────────────

auth_check();
$user = auth_user();

// Mapping halaman ke file
$page = preg_replace('/[^a-z0-9\-]/', '', $_GET['page'] ?? 'dashboard');
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
];

// Validasi akses HRD-only
$hrd_only = ['employees','attendance-reports','photo-approvals','payroll','locations'];
if (in_array($page, $hrd_only) && !auth_is_hrd()) {
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
